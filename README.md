# Laravel MCP (Model Context Protocol)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/innoge/laravel-mcp.svg?style=flat-square)](https://packagist.org/packages/innoge/laravel-mcp)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/innoge/laravel-mcp/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/innoge/laravel-mcp/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/innoge/laravel-mcp/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/innoge/laravel-mcp/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/innoge/laravel-mcp.svg?style=flat-square)](https://packagist.org/packages/innoge/laravel-mcp)

A Laravel package that implements the [Model Context Protocol (MCP)](https://modelcontextprotocol.io/introduction), enabling seamless communication between your Laravel application and AI assistants or other systems through a standardized API.

## Installation

You can install the package via composer:

```bash
composer require innoge/laravel-mcp
```

## Basic Usage

### Setting Up an MCP Server
This package currently only supports creating MCP servers via the STDIO transport.
HTTP transport is not supported yet but will be added in the future.

Create a command to serve your MCP server:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use InnoGE\LaravelMcp\Commands\ServesMcpServer;

class McpServerCommand extends Command
{
    use ServesMcpServer;

    protected $signature = 'mcp:serve';
    protected $description = 'Start an MCP server';

    public function handle(): int
    {
        return $this->serveMcp('your-app-name', '1.0.0');
    }

    private function getTools(): array
    {
        return [
            // List your tool classes here
        ];
    }

    private function getResources(): array
    {
        return [
            // List your resource providers here
        ];
    }
}
```

### Resources

Resources allow you to expose your application's data models through the MCP protocol. The package provides two types of resource providers:

1. **EloquentResourceProvider**: Expose Eloquent models

```php
use InnoGE\LaravelMcp\Resources\EloquentResourceProvider;
use App\Models\User;

// In your getResources() method:
return [
    new EloquentResourceProvider(User::query(), 'users', 'A User of the Application')
];
```

2. **InMemoryResourceProvider**: Expose custom data structures or non-Eloquent data

```php
use InnoGE\LaravelMcp\Resources\InMemoryResourceProvider;
use InnoGE\LaravelMcp\Types\Resources\ResourceContent;
use InnoGE\LaravelMcp\Types\Resources\ResourceItem;

// Create a resource provider
$resourceProvider = new InMemoryResourceProvider();

// Add example documents as resources
$resourceProvider->addResource(
    new ResourceItem('doc://example/document1', 'Example Document 1', 'This is an example document', 'text/plain', 1024),
    new ResourceContent('doc://example/document1', 'text/plain', 'This is the content of the document')
);

$resourceProvider->addResource(
    new ResourceItem('doc://example/document2', 'Example Document 2', 'This is an example document 2', 'text/plain', 1024),
    new ResourceContent('doc://example/document2', 'text/plain', 'This is the content of the document 2')
);

// In your getResources() method:
return [
    $resourceProvider
];
```

### Tools

Tools define actions that can be performed through the MCP protocol:

- **Built-in Example Tools**:
  - `HelloTool`: A simple hello world example
  - `ClockTool`: Returns the current time

- **Custom Tools**: Create your own by implementing the `ToolInterface`

```php
use InnoGE\LaravelMcp\Tools\Examples\HelloTool;
use InnoGE\LaravelMcp\Tools\Examples\ClockTool;
use App\MCP\Tools\YourCustomTool;

// In your getTools() method:
return [
    HelloTool::class,
    ClockTool::class,
    YourCustomTool::class,
];
```

#### Creating a Tool

Tools are the core functionality of MCP, allowing AI assistants to interact with your Laravel application. They provide a way to execute specific actions in your application through a well-defined interface.

Real-world examples of MCP tools include:
- **Database Operations**: Create, read, update, or delete records
- **External API Integration**: Make API calls to third-party services
- **File Management**: Upload, download, or process files
- **Authentication**: Verify user credentials or generate tokens
- **Reporting**: Generate reports or export data
- **Email/Notification**: Send messages to users

Example Tool:

```php
<?php

namespace App\MCP\Tools;

use Illuminate\Support\Facades\Artisan;
use InnoGE\LaravelMcp\Tools\Tool;
use Symfony\Component\Console\Output\BufferedOutput;

class CallArtisanCommandTool implements Tool
{
    /**
     * Get the tool name
     */
    public function getName(): string
    {
        return 'call-artisan-command';
    }

    /**
     * Get the tool description
     */
    public function getDescription(): string
    {
        return 'Call a Laravel Artisan command';
    }

    /**
     * Get the input schema for the tool
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'command' => [
                    'type' => 'string',
                    'description' => 'The Artisan command to call (e.g. "migrate")',
                ],
            ],
            'required' => ['command'],
        ];
    }

    /**
     * Execute the tool with the provided arguments
     */
    public function execute(array $arguments): string
    {
        $command = $arguments['command'];

        $outputBuffer = new BufferedOutput;

        Artisan::call($command, [], $outputBuffer);

        return $outputBuffer->fetch();
    }
}
```



## Testing your MCP Server

Use Modelcontext Protocol Inspector to test the MCP server:

```bash
npx @modelcontextprotocol/inspector php /path/to/your/app/artisan mcp:serve
```

## Adding your MCP Server to Claude Desktop

Edit your Claude Desktop config file:

~/Library/Application Support/Claude/claude_desktop_config.json

Add your MCP server to the config file:

```json
{
  "mcpServers": {
    "laravel-mcp": {
      "command": "php",
      "args": [
        "/path/to/your/app/artisan",
        "mcp:serve"
      ]
    }
  }
}
```

Now you can use your MCP server in Claude Desktop. Please note that Claude currently does not use MCP resources. If you want to access data of your application you can use tool calls.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Tim Geisendoerfer](https://github.com/geisi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
