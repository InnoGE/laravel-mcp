<?php

namespace InnoGE\LaravelMcp\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InnoGE\LaravelMcp\Types\Resources\ResourceContent;
use InnoGE\LaravelMcp\Types\Resources\ResourceItem;
use InnoGE\LaravelMcp\Types\Resources\ResourceTemplate;
use Psy\Util\Str;

/**
 * EloquentResourceProvider
 *
 * A resource provider that works with Eloquent models and uses cursor pagination.
 */
class EloquentResourceProvider implements ResourceProviderInterface
{
    /**
     * The Eloquent query builder instance
     *
     * @var Builder
     */
    protected Builder $query;

    /**
     * The model class name
     *
     * @var string
     */
    protected string $modelClass;

    /**
     * The name for the resources
     *
     * @var string
     */
    protected string $resourceName;

    /**
     * The description for the resources
     *
     * @var string|null
     */
    protected ?string $resourceDescription;

    /**
     * The MIME type for the resources
     *
     * @var string
     */
    protected string $mimeType = 'application/json';

    /**
     * The default number of items per page
     *
     * @var int
     */
    protected int $defaultLimit = 100;

    /**
     * The field to use for cursor pagination
     *
     * @var string
     */
    protected string $cursorColumn = 'id';

    /**
     * Collection of resource templates
     *
     * @var array<string, ResourceTemplate>
     */
    protected array $resourceTemplates = [];

    /**
     * Create a new EloquentResourceProvider instance
     *
     * @param Builder $query The Eloquent query builder
     * @param string|null $resourceName The name for resources (defaults to model class name)
     * @param string|null $resourceDescription The description for resources
     * @param string $mimeType The MIME type for resources
     * @param int $defaultLimit The default number of items per page
     * @param string $cursorColumn The column to use for cursor pagination
     */
    public function __construct(
        Builder $query,
        ?string $resourceName = null,
        ?string $resourceDescription = null,
        string $mimeType = 'application/json',
        int $defaultLimit = 5,
        string $cursorColumn = 'id'
    ) {
        $this->query = $query;
        $this->modelClass = get_class($query->getModel());
        $this->resourceName = $resourceName ?? class_basename($this->modelClass);
        $this->resourceDescription = $resourceDescription;
        $this->mimeType = $mimeType;
        $this->defaultLimit = $defaultLimit;
        $this->cursorColumn = $cursorColumn;

        // Add the default template for this model
        $this->addResourceTemplate(new ResourceTemplate(
            'laravel://'. $this->modelClass . '/{id}',
            $this->resourceName,
            $this->resourceDescription,
            $this->mimeType
        ));
    }

    /**
     * Add a resource template to the provider
     *
     * @param ResourceTemplate $template The template to add
     * @return void
     */
    public function addResourceTemplate(ResourceTemplate $template): void
    {
        $this->resourceTemplates[$template->uriTemplate] = $template;
    }

    /**
     * Generate a URI for a model
     *
     * @param Model $model The model
     * @return string The URI
     */
    protected function getModelUri(Model $model): string
    {
        return 'laravel://'. $this->modelClass . '/' . $model->getKey();
    }

    /**
     * Convert an Eloquent model to a ResourceItem
     *
     * @param Model $model The Eloquent model
     * @return ResourceItem The resource item
     */
    protected function modelToResourceItem(Model $model): ResourceItem
    {
        $uri = $this->getModelUri($model);

        return new ResourceItem(
            $uri,
            $this->resourceName . ' ' . $model->getKey(),
            $this->resourceDescription,
            $this->mimeType
        );
    }

    /**
     * Convert an Eloquent model to ResourceContent
     *
     * @param Model $model The Eloquent model
     * @return ResourceContent The resource content
     */
    protected function modelToResourceContent(Model $model): ResourceContent
    {
        $uri = $this->getModelUri($model);
        $json = $model->toJson();

        return ResourceContent::text($uri, $json, $this->mimeType);
    }

    /**
     * Extract the model ID from a URI
     *
     * @param string $uri The URI
     * @return mixed The model ID
     * @throws \Exception If the URI is not valid
     */
    protected function getModelIdFromUri(string $uri): mixed
    {
        return \Illuminate\Support\Str::afterLast($uri, '/');
    }

    /**
     * List resources
     *
     * @param string|null $cursor Pagination cursor
     * @param int|null $limit Maximum number of items to return
     * @return array{resources: ResourceItem[], nextCursor: ?string}
     */
    public function listResources(?string $cursor = null, ?int $limit = null): array
    {
        $limit = $limit ?? $this->defaultLimit;

        // Clone the query to avoid modifying the original
        $query = clone $this->query;

        // Apply cursor pagination
        if ($cursor !== null) {
            $modelId = $this->getModelIdFromUri($cursor);
            $model = $this->modelClass::find($modelId);

            if (!$model) {
                throw new \Exception("Invalid cursor: Model not found for {$cursor}");
            }

            // Apply cursor conditions
            $query->where($this->cursorColumn, '>', $model->{$this->cursorColumn});
        }

        // Get paginated results
        $results = $query->orderBy($this->cursorColumn)->limit($limit + 1)->get();

        // Check if there are more results
        $hasMoreResults = $results->count() > $limit;

        // Remove the extra item if we have more results
        if ($hasMoreResults) {
            $results = $results->slice(0, $limit);
        }

        // Convert models to ResourceItems
        $items = $results->map(fn (Model $model) => $this->modelToResourceItem($model))->all();

        // Get the next cursor
        $nextCursor = null;
        if ($hasMoreResults && count($items) > 0) {
            $lastModel = $results->last();
            $nextCursor = $this->getModelUri($lastModel);
        }

        return [
            'resources' => $items,
            'nextCursor' => $nextCursor,
        ];
    }

    /**
     * List resource templates
     *
     * @return ResourceTemplate[]
     */
    public function listResourceTemplates(): array
    {
        return array_values($this->resourceTemplates);
    }

    /**
     * Read a resource
     *
     * @param string $uri The URI of the resource to read
     * @return ResourceContent[] The resource contents
     *
     * @throws \Exception If the resource cannot be read
     */
    public function readResource(string $uri): array
    {
        if (!$this->resourceExists($uri)) {
            throw new \Exception("Resource not found: {$uri}");
        }

        $modelId = $this->getModelIdFromUri($uri);
        $model = $this->modelClass::find($modelId);

        if (!$model) {
            throw new \Exception("Model not found for URI: {$uri}");
        }

        return [$this->modelToResourceContent($model)];
    }

    /**
     * Check if a resource exists
     *
     * @param string $uri The URI to check
     * @return bool True if the resource exists
     */
    public function resourceExists(string $uri): bool
    {
        try {
            $modelId = $this->getModelIdFromUri($uri);
            return $this->modelClass::where($this->query->getModel()->getKeyName(), $modelId)->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}
