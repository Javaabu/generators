<?php

namespace Javaabu\Generators\Generators;

use Javaabu\GeneratorHelpers\StringCaser;
use Javaabu\Generators\Support\PhpFile;
use Javaabu\Generators\Support\Stub;

/**
 * Adds the resource, restore, force delete and bulk action routes to the
 * admin routes file, creating it (and loading it from routes/web.php) the
 * first time.
 */
class RoutesGenerator extends Generator
{
    public const string MARKER = '// generators: routes';

    public function files(): array
    {
        $path = $this->routesPath();

        $files = [new GeneratedFile($path, $this->renderRoutes(), fn (string $code) => $this->mergeInto($code), 'routes')];

        $relative = ltrim(substr($path, strlen(base_path('routes'))), '/');

        if (str_starts_with($path, base_path('routes') . '/') && $relative !== 'web.php') {
            $files[] = new GeneratedFile(base_path('routes/web.php'), "require __DIR__ . '/{$relative}';", function (string $code) use ($relative) {
                if (str_contains($code, "/{$relative}'")) {
                    return $code;
                }

                return rtrim($code) . "\n\nrequire __DIR__ . '/{$relative}';\n";
            }, 'routes');
        }

        return $files;
    }

    public function routesPath(): string
    {
        return base_path((string) $this->resource->area('routes_file', 'routes/admin.php'));
    }

    /**
     * A new admin routes file.
     */
    public function newRoutesFile(): string
    {
        $resource = $this->resource;
        $middleware = (array) $resource->area('middleware', ['web', 'auth']);

        return Stub::make('routes/admin.stub')->with([
            'middleware' => "['" . implode("', '", $middleware) . "']",
            'prefix' => trim((string) $resource->area('route_prefix', 'admin'), '/'),
            'name' => $resource->routeNamePrefix(),
        ])->render();
    }

    public function mergeInto(string $code): string
    {
        $resource = $this->resource;

        if (trim($code) === '') {
            $code = $this->newRoutesFile();
        }

        $code = PhpFile::addImport($code, $resource->controllerClass());
        $code = PhpFile::addImport($code, $resource->bulkActionsClass());

        $code = str_contains($code, self::MARKER)
            ? PhpFile::insertBeforeMarker($code, self::MARKER, $this->resourceRoutes())
            : rtrim($code) . "\n\n" . $this->resourceRoutes() . "\n";

        if ($resource->hasMedia() && ! str_contains($code, 'Route::mediapicker(')) {
            $code = rtrim($code) . "\n\n" . $this->renderMediapickerRoutes() . "\n";
        }

        return $code;
    }

    /**
     * The media library the pickers of the admin area pick from (javaabu/mediapicker).
     */
    public function renderMediapickerRoutes(): string
    {
        $resource = $this->resource;
        $prefix = trim((string) $resource->area('route_prefix', 'admin'), '/');

        $options = ["'prefix' => '" . ltrim("{$prefix}/media", '/') . "'", "'name' => '{$resource->routeNamePrefix()}media'"];

        if ($guard = $resource->guard()) {
            array_unshift($options, "'guard' => '{$guard}'");
        }

        $options = implode(', ', $options);

        return "// The media library of the admin area\nRoute::middleware('web')->group(function () {\n    Route::mediapicker([{$options}]);\n});";
    }

    public function renderRoutes(): string
    {
        return $this->resourceRoutes() . ($this->resource->hasMedia() ? "\n\n" . $this->renderMediapickerRoutes() : '');
    }

    public function resourceRoutes(): string
    {
        $resource = $this->resource;
        $controller = class_basename($resource->controllerClass());
        $bulk_actions = class_basename($resource->bulkActionsClass());

        $name = $resource->routeResource();
        $uri = $this->uri();
        $parameter = '{' . $resource->variable() . '}';

        $resource_route = "Route::resource('{$name}', {$controller}::class)";

        if ($resource->isNested()) {
            $resource_route .= '->scoped()';
        }

        if ($resource->hasSoftDeletes()) {
            $resource_route .= "->withTrashed(['show'])";
        }

        $routes = ["// {$resource->pluralLabel()}", $resource_route . ';'];

        $scoped = $resource->isNested() ? '->scopeBindings()' : '';

        if ($resource->hasSoftDeletes()) {
            $routes[] = "Route::patch('{$uri}/{$parameter}/restore', [{$controller}::class, 'restore']){$scoped}->withTrashed()->name('{$name}.restore');";
            $routes[] = "Route::delete('{$uri}/{$parameter}/force-delete', [{$controller}::class, 'forceDelete']){$scoped}->withTrashed()->name('{$name}.force-delete');";
        }

        $options = [];

        if ($resource->isNested()) {
            $options[] = "'name' => '{$name}.bulk'";
        }

        if ($guard = $resource->guard()) {
            $options[] = "'guard' => '{$guard}'";
        }

        $options = $options ? ', [' . implode(', ', $options) . ']' : '';

        $routes[] = "Route::bulkActions('{$uri}', {$bulk_actions}::class{$options});";

        return implode("\n", $routes);
    }

    /**
     * The URI of the resource inside the admin group.
     */
    public function uri(): string
    {
        $resource = $this->resource;

        if (! $resource->isNested()) {
            return $resource->kebab();
        }

        $parent = $resource->parent->getRelatedTable();

        return StringCaser::pluralKebab($parent) . '/{' . StringCaser::singularSnake($parent) . '}/' . $resource->kebab();
    }
}
