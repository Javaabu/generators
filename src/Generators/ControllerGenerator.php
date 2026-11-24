<?php

namespace Javaabu\Generators\Generators;

use Illuminate\Support\Str;
use Javaabu\Generators\Fields\ForeignKeyField;
use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

/**
 * The admin resource controller: index with filters and bulk actions, the
 * stencil form for create and edit, show, delete, and restore and force
 * delete for soft-deleting models. Renders Blade views or Inertia pages.
 */
class ControllerGenerator extends Generator
{
    protected Imports $imports;

    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->controllerClass()), $this->renderController(), description: 'controller')];
    }

    public function renderController(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('controllers');

        $this->imports = new Imports([
            'App\\Http\\Controllers\\Controller',
            'Illuminate\\Http\\RedirectResponse',
            'Illuminate\\Support\\Facades\\Gate',
        ], $namespace);

        $this->imports->add($resource->modelClass());

        if ($resource->isNested()) {
            $this->imports->add($resource->parentModelClass());
        }

        $methods = [
            $this->index(),
            $this->create(),
            $this->store(),
            $this->show(),
            $this->edit(),
            $this->update(),
            $this->destroy(),
        ];

        if ($resource->hasSoftDeletes()) {
            $methods[] = $this->restore();
            $methods[] = $this->forceDelete();
        }

        return Stub::make('controller/Controller.stub')->with([
            'namespace' => $namespace,
            'class' => class_basename($resource->controllerClass()),
            'methods' => implode("\n\n", $methods),
            'imports' => $this->imports->render(),
        ])->render();
    }

    protected function index(): string
    {
        $resource = $this->resource;
        $model = $this->model();
        $filters = $this->imports->add($resource->filtersClass());
        $bulk_actions = $this->imports->add($resource->bulkActionsClass());
        $this->imports->add('Illuminate\\Http\\Request');

        $plural = $resource->pluralVariable();

        $query = "{$model}::query()";

        if ($resource->isNested()) {
            $query .= "->whereBelongsTo(\${$resource->parentVariable()}, '{$resource->parent->getRelationName()}')";
        }

        if ($with = $this->eagerLoads($resource->indexFields(), $resource->indexRelations())) {
            $query .= "->with({$with})";
        }

        $props = $this->parentProps();

        if ($resource->isInertia()) {
            $collection = $this->imports->add($resource->resourceClass());

            $props[] = "'" . Str::camel($plural) . "' => {$collection}::collection(\${$plural}),";
            $props[] = "'filters' => \$filters,";
            $props[] = "'bulkActions' => {$bulk_actions}::make()->paginator(\${$plural}),";
            $props[] = "'createUrl' => Gate::allows('create', {$model}::class) ? {$resource->route('create')} : null,";
        } else {
            $props[] = "'{$plural}' => \${$plural},";
            $props[] = "'filters' => \$filters,";
            $props[] = "'bulk_actions' => {$bulk_actions}::make()->paginator(\${$plural}),";
        }

        $body = <<<PHP
        Gate::authorize('viewAny', {$model}::class);

        \$filters = {$filters}::make();

        \${$plural} = \$filters->apply({$query})
            ->paginate(\$request->integer('per_page', 20))
            ->withQueryString();

        {$this->respond('index', 'Index', $props)}
        PHP;

        return $this->method(
            "List the {$resource->pluralLower()}.",
            'index',
            ['Request $request', ...$this->parentParameter()],
            $this->responseType(),
            $body
        );
    }

    protected function create(): string
    {
        $resource = $this->resource;
        $model = $this->model();
        $form = $this->imports->add($resource->formClass());

        $props = [
            ...$this->parentProps(),
            "'form' => {$form}::make()->model({$model}::class)->action({$resource->route('store')}),",
        ];

        $body = <<<PHP
        Gate::authorize('create', {$model}::class);

        {$this->respond('create', 'Create', $props)}
        PHP;

        return $this->method("Show the form for a new {$resource->singularLower()}.", 'create', $this->parentParameter(), $this->responseType(), $body);
    }

    protected function store(): string
    {
        $resource = $this->resource;
        $model = $this->model();
        $form = $this->imports->add($resource->formClass());
        $variable = $resource->variable();

        $record = $resource->isNested()
            ? "(new {$model})->{$resource->parent->getRelationName()}()->associate(\${$resource->parentVariable()})"
            : "{$model}::class";

        $message = $this->message("{$resource->singularLabel()} created.");

        $body = <<<PHP
        Gate::authorize('create', {$model}::class);

        \${$variable} = {$form}::make()->model({$record})->save();

        return redirect({$resource->route('show', "\${$variable}")})
            ->with('status', {$message});
        PHP;

        return $this->method("Save a new {$resource->singularLower()}.", 'store', $this->parentParameter(), 'RedirectResponse', $body);
    }

    protected function show(): string
    {
        $resource = $this->resource;
        $variable = $resource->variable();

        $load = '';

        if ($with = $this->eagerLoads($resource->showFields(), $resource->showRelations())) {
            $load = "\n\n\${$variable}->load({$with});";
        }

        $props = $this->parentProps();

        $props[] = $resource->isInertia()
            ? "'" . Str::camel($variable) . "' => " . $this->imports->add($resource->resourceClass()) . "::make(\${$variable})->resolve(),"
            : "'{$variable}' => \${$variable},";

        $body = <<<PHP
        Gate::authorize('view', \${$variable});{$load}

        {$this->respond('show', 'Show', $props)}
        PHP;

        return $this->method("Show a {$resource->singularLower()}.", 'show', [...$this->parentParameter(), $this->recordParameter()], $this->responseType(), $body);
    }

    protected function edit(): string
    {
        $resource = $this->resource;
        $variable = $resource->variable();
        $form = $this->imports->add($resource->formClass());

        $props = $this->parentProps();

        if ($resource->isInertia()) {
            $props[] = "'" . Str::camel($variable) . "' => " . $this->imports->add($resource->resourceClass()) . "::make(\${$variable})->resolve(),";
        } else {
            $props[] = "'{$variable}' => \${$variable},";
        }

        $props[] = "'form' => {$form}::make()->model(\${$variable})->action({$resource->route('update', "\${$variable}")}),";

        $body = <<<PHP
        Gate::authorize('update', \${$variable});

        {$this->respond('edit', 'Edit', $props)}
        PHP;

        return $this->method("Show the form for editing a {$resource->singularLower()}.", 'edit', [...$this->parentParameter(), $this->recordParameter()], $this->responseType(), $body);
    }

    protected function update(): string
    {
        $resource = $this->resource;
        $variable = $resource->variable();
        $form = $this->imports->add($resource->formClass());

        $message = $this->message("{$resource->singularLabel()} updated.");

        $body = <<<PHP
        Gate::authorize('update', \${$variable});

        {$form}::make()->model(\${$variable})->save();

        return redirect({$resource->route('show', "\${$variable}")})
            ->with('status', {$message});
        PHP;

        return $this->method("Save the changes to a {$resource->singularLower()}.", 'update', [...$this->parentParameter(), $this->recordParameter()], 'RedirectResponse', $body);
    }

    protected function destroy(): string
    {
        $resource = $this->resource;
        $variable = $resource->variable();

        $message = $this->message("{$resource->singularLabel()} deleted.");

        $body = <<<PHP
        Gate::authorize('delete', \${$variable});

        \${$variable}->delete();

        return redirect({$resource->route('index')})
            ->with('status', {$message});
        PHP;

        return $this->method("Delete a {$resource->singularLower()}.", 'destroy', [...$this->parentParameter(), $this->recordParameter()], 'RedirectResponse', $body);
    }

    protected function restore(): string
    {
        $resource = $this->resource;
        $variable = $resource->variable();

        $message = $this->message("{$resource->singularLabel()} restored.");

        $body = <<<PHP
        Gate::authorize('restore', \${$variable});

        \${$variable}->restore();

        return redirect({$resource->route('show', "\${$variable}")})
            ->with('status', {$message});
        PHP;

        return $this->method("Restore a deleted {$resource->singularLower()}.", 'restore', [...$this->parentParameter(), $this->recordParameter()], 'RedirectResponse', $body);
    }

    protected function forceDelete(): string
    {
        $resource = $this->resource;
        $variable = $resource->variable();

        $message = $this->message("{$resource->singularLabel()} permanently deleted.");

        $body = <<<PHP
        Gate::authorize('forceDelete', \${$variable});

        \${$variable}->forceDelete();

        return redirect({$resource->route('index')})
            ->with('status', {$message});
        PHP;

        return $this->method("Permanently delete a {$resource->singularLower()}.", 'forceDelete', [...$this->parentParameter(), $this->recordParameter()], 'RedirectResponse', $body);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * @param  array<int, string>  $props
     */
    protected function respond(string $view, string $page, array $props): string
    {
        $resource = $this->resource;
        $props = implode("\n", array_map(fn (string $prop) => "    {$prop}", $props));

        if ($resource->isInertia()) {
            $inertia = $this->imports->add('Inertia\\Inertia');

            return "return {$inertia}::render('{$resource->pageName($page)}', [\n{$props}\n]);";
        }

        return "return view('{$resource->viewName($view)}', [\n{$props}\n]);";
    }

    protected function responseType(): string
    {
        if ($this->resource->isInertia()) {
            return $this->imports->add('Inertia\\Response');
        }

        return $this->imports->add('Illuminate\\Contracts\\View\\View');
    }

    protected function model(): string
    {
        return $this->imports->add($this->resource->modelClass());
    }

    /**
     * @return array<int, string>
     */
    protected function parentParameter(): array
    {
        if (! $this->resource->isNested()) {
            return [];
        }

        $parent = $this->imports->add($this->resource->parentModelClass());

        return ["{$parent} \${$this->resource->parentVariable()}"];
    }

    /**
     * @return array<int, string>
     */
    protected function parentProps(): array
    {
        if (! $this->resource->isNested()) {
            return [];
        }

        $variable = $this->resource->parentVariable();
        $key = $this->resource->isInertia() ? Str::camel($variable) : $variable;

        return ["'{$key}' => \${$variable},"];
    }

    protected function recordParameter(): string
    {
        return "{$this->model()} \${$this->resource->variable()}";
    }

    /**
     * The relations to eager load for these fields, as a PHP array.
     *
     * @param  array<string, mixed>  $fields
     * @param  array<int, mixed>  $relations
     */
    protected function eagerLoads(array $fields, array $relations): ?string
    {
        $loads = [];

        foreach ($fields as $column => $field) {
            if (! $field instanceof ForeignKeyField) {
                continue;
            }

            if ($this->resource->parent && $column === $this->resource->parent->getName()) {
                continue;
            }

            $loads[] = $field->getRelationName();
        }

        foreach ($relations as $relation) {
            $loads[] = $relation->name;
        }

        if (! $loads) {
            return null;
        }

        return "['" . implode("', '", array_unique($loads)) . "']";
    }

    protected function message(string $message): string
    {
        return "__('" . str_replace("'", "\\'", $message) . "')";
    }

    /**
     * @param  array<int, string>  $parameters
     */
    protected function method(string $comment, string $name, array $parameters, string $return, string $body): string
    {
        $parameters = implode(', ', $parameters);
        $body = Stub::indent($body, 1);

        return <<<PHP
        /**
         * {$comment}
         */
        public function {$name}({$parameters}): {$return}
        {
        {$body}
        }
        PHP;
    }
}
