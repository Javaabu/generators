<?php

namespace Javaabu\Generators\Resource;

use Illuminate\Support\Str;
use Javaabu\GeneratorHelpers\StringCaser;
use Javaabu\Generators\Fields\Field;
use Javaabu\Generators\Fields\ForeignKeyField;
use Javaabu\Generators\Schema\Relations\BelongsTo;
use Javaabu\Generators\Schema\Relations\BelongsToMany;
use Javaabu\Generators\Schema\Relations\HasMany;
use Javaabu\Generators\Schema\Table;

/**
 * Everything the generators need to know about one resource: its table and
 * fields, its relations, and the developer's choices (which fields appear
 * where, the parent of a nested resource, media, views, translations).
 */
class ResourceDefinition
{
    /**
     * @param  array<string, Field>  $fields  every field, keyed by column
     * @param  array<int, BelongsTo>  $belongs_to
     * @param  array<int, HasMany>  $has_many
     * @param  array<int, BelongsToMany>  $belongs_to_many
     * @param  array<int, string>  $index_columns  columns (or belongsToMany relation names) on the index table
     * @param  array<int, string>  $form_columns  columns (or belongsToMany relation names) in the form
     * @param  array<int, string>  $show_columns  columns on the show page
     * @param  array<int, MediaCollection>  $media
     * @param  array<int, string>  $translatable  translatable columns
     */
    public function __construct(
        public readonly Table $table,
        public readonly array $fields,
        public readonly string $key_name,
        public readonly string $name_field,
        public readonly array $belongs_to = [],
        public readonly array $has_many = [],
        public readonly array $belongs_to_many = [],
        public readonly array $index_columns = [],
        public readonly array $form_columns = [],
        public readonly array $show_columns = [],
        public readonly ?ForeignKeyField $parent = null,
        public readonly ?string $parent_name_field = null,
        public readonly array $media = [],
        public readonly string $views = 'blade',
        public readonly array $translatable = [],
        public readonly ?string $user_type = null,
        public readonly ?string $parent_relation = null,
        public readonly ?string $user_guard = null,
    ) {}

    /**
     * Whether the resource is a javaabu/auth user type (generate:auth).
     */
    public function isUserType(): bool
    {
        return $this->user_type !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Names
    |--------------------------------------------------------------------------
    */

    public function table(): string
    {
        return $this->table->name;
    }

    public function model(): string
    {
        return StringCaser::singularStudly($this->table());
    }

    public function modelClass(): string
    {
        return $this->namespace('models') . '\\' . $this->model();
    }

    /** product */
    public function singular(): string
    {
        return StringCaser::singularSnake($this->table());
    }

    /** products */
    public function plural(): string
    {
        return StringCaser::pluralSnake($this->table());
    }

    /** Products */
    public function pluralStudly(): string
    {
        return StringCaser::pluralStudly($this->table());
    }

    /** blog-posts */
    public function kebab(): string
    {
        return StringCaser::pluralKebab($this->table());
    }

    /** blog_post: route parameter and variable */
    public function variable(): string
    {
        return $this->singular();
    }

    /** blog_posts */
    public function pluralVariable(): string
    {
        return $this->plural();
    }

    /** product */
    public function singularLower(): string
    {
        return StringCaser::singularLower($this->table());
    }

    /** products */
    public function pluralLower(): string
    {
        return StringCaser::pluralLower($this->table());
    }

    /** Product */
    public function singularLabel(): string
    {
        return Str::ucfirst($this->singularLower());
    }

    /** Products */
    public function pluralLabel(): string
    {
        return Str::ucfirst($this->pluralLower());
    }

    /** The resource part of the permission names: products */
    public function permissionResource(): string
    {
        return $this->plural();
    }

    public function permission(string $action): string
    {
        return "{$action}_{$this->permissionResource()}";
    }

    /*
    |--------------------------------------------------------------------------
    | Classes
    |--------------------------------------------------------------------------
    */

    public function namespace(string $type): string
    {
        $defaults = [
            'models' => 'App\\Models',
            'policies' => 'App\\Policies',
            'filters' => 'App\\Filters',
            'bulk_actions' => 'App\\BulkActions',
            'forms' => 'App\\Stencil',
            'controllers' => 'App\\Http\\Controllers',
            'resources' => 'App\\Http\\Resources',
            'exports' => 'App\\Exports',
            'factories' => 'Database\\Factories',
            'tests' => 'Tests\\Feature',
        ];

        $namespace = trim((string) config("generators.namespaces.{$type}", $defaults[$type] ?? 'App'), '\\');

        if (in_array($type, ['controllers', 'resources', 'tests'], true) && ($area = $this->areaNamespace())) {
            $namespace .= '\\' . $area;
        }

        return $namespace;
    }

    public function policyClass(): string
    {
        return $this->namespace('policies') . '\\' . $this->model() . 'Policy';
    }

    public function filtersClass(): string
    {
        return $this->namespace('filters') . '\\' . $this->model() . 'Filters';
    }

    public function bulkActionsClass(): string
    {
        return $this->namespace('bulk_actions') . '\\' . $this->model() . 'BulkActions';
    }

    public function formClass(): string
    {
        return $this->namespace('forms') . '\\' . $this->model() . 'Form';
    }

    public function controllerClass(): string
    {
        return $this->namespace('controllers') . '\\' . $this->pluralStudly() . 'Controller';
    }

    public function resourceClass(): string
    {
        return $this->namespace('resources') . '\\' . $this->model() . 'Resource';
    }

    public function exportClass(): string
    {
        return $this->namespace('exports') . '\\' . $this->pluralStudly() . 'Export';
    }

    public function factoryClass(): string
    {
        return $this->namespace('factories') . '\\' . $this->model() . 'Factory';
    }

    public function testClass(): string
    {
        return $this->namespace('tests') . '\\' . $this->pluralStudly() . 'ControllerTest';
    }

    /**
     * The path of a class under the app, from its namespace.
     */
    public function classPath(string $class): string
    {
        $roots = [
            'App\\' => app_path(),
            'Database\\Factories\\' => database_path('factories'),
            'Database\\Seeders\\' => database_path('seeders'),
            'Tests\\' => base_path('tests'),
        ];

        foreach ($roots as $namespace => $path) {
            if (str_starts_with($class, $namespace)) {
                return $path . '/' . str_replace('\\', '/', substr($class, strlen($namespace))) . '.php';
            }
        }

        return base_path(str_replace('\\', '/', $class) . '.php');
    }

    /*
    |--------------------------------------------------------------------------
    | Admin area: routes, views and pages
    |--------------------------------------------------------------------------
    */

    public function area(string $key, mixed $default = null): mixed
    {
        return config("generators.admin.{$key}", $default);
    }

    /** Admin: the controllers' sub-namespace */
    public function areaNamespace(): string
    {
        return trim((string) $this->area('namespace', 'Admin'), '\\');
    }

    /** admin. */
    public function routeNamePrefix(): string
    {
        $name = (string) $this->area('route_name', 'admin.');

        return $name === '' ? '' : rtrim($name, '.') . '.';
    }

    /** admin.products or admin.categories.products */
    public function routeName(string $action = ''): string
    {
        $name = $this->routeNamePrefix() . $this->routeResource();

        return $action === '' ? $name : "{$name}.{$action}";
    }

    /** products or categories.products: the Route::resource() name */
    public function routeResource(): string
    {
        if (! $this->parent) {
            return $this->kebab();
        }

        return StringCaser::pluralKebab($this->parent->getRelatedTable()) . '.' . $this->kebab();
    }

    /** admin.products.show with its route arguments, as PHP code */
    public function route(string $action, ?string $record = null, bool $parent_from_record = false): string
    {
        $arguments = [];

        if ($this->parent) {
            $arguments[] = $parent_from_record && $record
                ? "{$record}->{$this->parent->getRelationName()}"
                : '$' . $this->parentVariable();
        }

        if ($record) {
            $arguments[] = $record;
        }

        $name = "'{$this->routeName($action)}'";

        if (! $arguments) {
            return "route({$name})";
        }

        if (count($arguments) === 1) {
            return "route({$name}, {$arguments[0]})";
        }

        return "route({$name}, [" . implode(', ', $arguments) . '])';
    }

    /** /admin/products, for tests */
    public function url(string $suffix = '', ?string $record = null): string
    {
        $prefix = trim((string) $this->area('route_prefix', 'admin'), '/');

        $segments = array_filter([$prefix]);

        if ($this->parent) {
            $segments[] = StringCaser::pluralKebab($this->parent->getRelatedTable());
            $segments[] = '{$' . $this->parentVariable() . '->' . $this->parentKey() . '}';
        }

        $segments[] = $this->kebab();

        if ($record) {
            $segments[] = '{' . $record . '->' . $this->key_name . '}';
        }

        if ($suffix !== '') {
            $segments[] = $suffix;
        }

        return '/' . implode('/', $segments);
    }

    /** admin.products, the Blade view folder */
    public function viewName(string $view): string
    {
        $prefix = trim((string) $this->area('view_prefix', 'admin'), '.');

        return ltrim("{$prefix}.{$this->kebab()}.{$view}", '.');
    }

    public function viewPath(string $view): string
    {
        return resource_path('views/' . str_replace('.', '/', $this->viewName($view)) . '.blade.php');
    }

    /** admin/products/Index, the Inertia page name */
    public function pageName(string $page): string
    {
        $prefix = trim((string) $this->area('page_prefix', 'admin'), '/');

        return ltrim("{$prefix}/{$this->kebab()}/{$page}", '/');
    }

    public function pagePath(string $page): string
    {
        return resource_path('js/pages/' . $this->pageName($page) . '.tsx');
    }

    public function guard(): ?string
    {
        return $this->area('guard') ?: null;
    }

    public function isInertia(): bool
    {
        return $this->views === 'inertia';
    }

    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    */

    public function field(string $column): ?Field
    {
        return $this->fields[$column] ?? null;
    }

    public function nameField(): string
    {
        return $this->name_field;
    }

    public function hasSoftDeletes(): bool
    {
        return $this->table->hasSoftDeletes();
    }

    public function hasTimestamps(): bool
    {
        return $this->table->hasTimestamps();
    }

    /**
     * @return array<string, Field>
     */
    public function formFields(): array
    {
        return $this->pick($this->form_columns);
    }

    /**
     * @return array<string, Field>
     */
    public function indexFields(): array
    {
        return array_filter($this->pick($this->index_columns), fn (Field $field) => $field->isShown());
    }

    /**
     * @return array<string, Field>
     */
    public function showFields(): array
    {
        return array_filter($this->pick($this->show_columns), fn (Field $field) => $field->isShown());
    }

    /**
     * belongsToMany relations in the form.
     *
     * @return array<int, BelongsToMany>
     */
    public function formRelations(): array
    {
        return $this->pickRelations($this->form_columns);
    }

    /**
     * @return array<int, BelongsToMany>
     */
    public function indexRelations(): array
    {
        return $this->pickRelations($this->index_columns);
    }

    /**
     * @return array<int, BelongsToMany>
     */
    public function showRelations(): array
    {
        return $this->pickRelations($this->show_columns);
    }

    /**
     * Fields that are searchable by the text filter.
     *
     * @return array<int, string>
     */
    public function searchableColumns(): array
    {
        $columns = [];

        foreach ($this->fields as $column => $field) {
            if ($field->isSearchable() && ! in_array($column, $this->translatable, true)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    public function isTranslatable(): bool
    {
        return $this->translatable !== [];
    }

    public function hasMedia(): bool
    {
        return $this->media !== [];
    }

    public function media(string $collection): ?MediaCollection
    {
        foreach ($this->media as $media) {
            if ($media->name === $collection) {
                return $media;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Nesting
    |--------------------------------------------------------------------------
    */

    public function isNested(): bool
    {
        return $this->parent !== null;
    }

    /** category */
    public function parentVariable(): string
    {
        return $this->parent ? StringCaser::singularSnake($this->parent->getRelatedTable()) : '';
    }

    /** Category */
    public function parentModel(): string
    {
        return $this->parent ? StringCaser::singularStudly($this->parent->getRelatedTable()) : '';
    }

    public function parentModelClass(): string
    {
        return $this->namespace('models') . '\\' . $this->parentModel();
    }

    /** The relation on the parent: products */
    public function parentRelation(): string
    {
        return $this->parent_relation ?? StringCaser::pluralCamel($this->table());
    }

    public function parentKey(): string
    {
        return $this->parent?->getRelatedKey() ?? 'id';
    }

    /**
     * @param  array<int, string>  $columns
     * @return array<string, Field>
     */
    protected function pick(array $columns): array
    {
        $fields = [];

        foreach ($columns as $column) {
            if (isset($this->fields[$column])) {
                $fields[$column] = $this->fields[$column];
            }
        }

        return $fields;
    }

    /**
     * @param  array<int, string>  $names
     * @return array<int, BelongsToMany>
     */
    protected function pickRelations(array $names): array
    {
        return array_values(array_filter(
            $this->belongs_to_many,
            fn (BelongsToMany $relation) => in_array($relation->name, $names, true)
        ));
    }
}
