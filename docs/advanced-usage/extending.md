---
title: Extending the generators
sidebar_position: 7
---

The generators are a few small classes you can use and extend.

| Class | What |
| --- | --- |
| `Javaabu\Generators\Schema\SchemaInspector` | Reads a table: `table($name)` returns a `Table` of normalized `Column`s, `ForeignKey`s and the primary key. Also `tableNames()`, `hasTable()` and `driver()`. A singleton, reading `generators.connection` |
| `Javaabu\Generators\Schema\RelationResolver` | `belongsTo($table)`, `hasMany($table)`, `belongsToMany($table)` and `isPivotTable($table)` |
| `Javaabu\Generators\Resource\ResourceResolver` | `resolve($table, ResourceOptions)` returns a `ResourceDefinition`. `makeField($table, $column)` maps a column to a field type, and `nameFieldOf($table)` gives a table's name column |
| `Javaabu\Generators\Resource\ResourceDefinition` | The table, its fields and relations, and the choices. It gives every class, route, view and page name, e.g. `modelClass()`, `routeName('index')`, `viewName('show')`, `pageName('Index')`, `permission('edit')` |
| `Javaabu\Generators\Fields\*` | One class per column type, each knowing its cast, factory value, stencil field, filter, cells, entries, TypeScript type and test values |
| `Javaabu\Generators\Generators\*Generator` | Each part; `files()` returns `GeneratedFile`s (path, contents, and a merge callback for files that are added to) |
| `Javaabu\Generators\Commands\GenerateCommand` | The base command: options, prompts, printing and writing files |

# A generator of your own

```php
namespace App\Generators;

use Javaabu\Generators\Generators\GeneratedFile;
use Javaabu\Generators\Generators\Generator;

class ApiResourceGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;
        $class = "App\\Http\\Resources\\Api\\{$resource->model()}Resource";

        return [new GeneratedFile($resource->classPath($class), $this->render(), description: 'API resource')];
    }

    public function render(): string
    {
        $fields = collect($this->resource->fields)
            ->filter(fn ($field) => $field->isShown())
            ->map(fn ($field, $column) => "            '{$column}' => {$field->getResourceValue()},")
            ->implode("\n");

        return "<?php\n\n// ...\n{$fields}\n";
    }
}
```

```php
namespace App\Console\Commands;

use App\Generators\ApiResourceGenerator;
use Javaabu\Generators\Commands\GenerateCommand;
use Javaabu\Generators\Resource\ResourceDefinition;

class GenerateApiResourceCommand extends GenerateCommand
{
    protected $name = 'generate:api-resource';

    protected $description = 'Generate the API resource of a table';

    protected function generators(ResourceDefinition $resource): array
    {
        return [ApiResourceGenerator::class];
    }
}
```

The command gets the table argument and every option (`--create`, `--force`, `--columns`, `--interactive` and the rest).

# Changing how columns map to fields

Bind your own resolver:

```php
use App\Generators\Fields\PhoneField;
use Javaabu\Generators\Fields\Field;
use Javaabu\Generators\Resource\ResourceResolver;
use Javaabu\Generators\Schema\Column;
use Javaabu\Generators\Schema\Table;

class AppResourceResolver extends ResourceResolver
{
    public function makeField(Table $table, Column $column): ?Field
    {
        if (str_ends_with($column->name, '_phone')) {
            return new PhoneField($column->name, $column->nullable);
        }

        return parent::makeField($table, $column);
    }
}

// AppServiceProvider::register()
$this->app->bind(ResourceResolver::class, AppResourceResolver::class);
```

A field type extends `Javaabu\Generators\Fields\Field`, or one of its subclasses such as `StringField`. Implement `fakerCall()`, `stencilClass()`, `getValidValue()` and `getOtherValidValue()`, and override the other methods where it differs.
