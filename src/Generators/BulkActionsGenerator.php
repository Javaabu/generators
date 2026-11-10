<?php

namespace Javaabu\Generators\Generators;

use Illuminate\Support\Str;
use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

/**
 * javaabu/bulk-actions for the index table: delete, and restore and force
 * delete for soft-deleting models, run on the filtered selection.
 */
class BulkActionsGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->bulkActionsClass()), $this->renderBulkActions(), description: 'bulk actions')];
    }

    public function renderBulkActions(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('bulk_actions');
        $imports = new Imports(['Javaabu\\BulkActions\\BulkActions'], $namespace);

        $model = $imports->add($resource->modelClass());
        $filters = $imports->add($resource->filtersClass());

        $set_up = ["\$this->filters({$filters}::class);"];

        $methods = '';

        if ($resource->isNested()) {
            $parent = $imports->add($resource->parentModelClass());
            $variable = $resource->parentVariable();
            $method = Str::camel($variable);
            $relation = $resource->parent->getRelationName();
            $route = $resource->routeName('bulk');
            $key = $resource->parentKey();

            $set_up[] = '';
            $set_up[] = "// only the {$resource->pluralLower()} of the {$resource->parentVariable()} in the URL";
            $set_up[] = "\$this->query(fn () => {$model}::query()->whereBelongsTo(\$this->{$method}(), '{$relation}'));";
            $set_up[] = "\$this->url(fn () => route('{$route}', \$this->{$method}()));";

            $methods = <<<PHP
            /**
             * The {$resource->parentVariable()} in the URL.
             */
            protected function {$method}(): {$parent}
            {
                \${$variable} = request()->route('{$variable}');

                if (\${$variable} instanceof {$parent}) {
                    return \${$variable};
                }

                return {$parent}::query()->where('{$key}', \${$variable})->firstOrFail();
            }
            PHP;
        }

        $delete = $imports->add('Javaabu\\BulkActions\\Actions\\DeleteAction');
        $actions = ["{$delete}::make()->can('{$resource->permission('delete')}')"];

        if ($resource->hasSoftDeletes()) {
            $restore = $imports->add('Javaabu\\BulkActions\\Actions\\RestoreAction');
            $force = $imports->add('Javaabu\\BulkActions\\Actions\\ForceDeleteAction');

            $actions[] = "{$restore}::make()->can('{$resource->permission('delete')}')";
            $actions[] = "{$force}::make()->can('{$resource->permission('force_delete')}')";
        }

        return Stub::make('bulk-actions/BulkActions.stub')->with([
            'namespace' => $namespace,
            'class' => class_basename($resource->bulkActionsClass()),
            'model' => $model,
            'setUp' => implode("\n", $set_up),
            'actions' => implode("\n", array_map(fn (string $action) => "{$action},", $actions)),
            'methods' => $methods,
            'imports' => $imports->render(),
        ])->render();
    }
}
