<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\Contracts\SchemaResolverInterface;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\FieldTypes\StringField;
use Javaabu\Generators\IconProviders\BaseIconProvider;
use Javaabu\GeneratorHelpers\StringCaser;
use Javaabu\GeneratorHelpers\StubRenderer;
use Javaabu\Generators\Support\TableProperties;

abstract class BaseGenerator
{
    protected array $fields;
    protected string $table;
    protected string $key_name;
    protected array $columns;
    protected bool $soft_deletes;
    protected bool $timestamps;
    protected StubRenderer $renderer;

    /**
     * Constructor
     */
    public function __construct(string $table, array $columns = [])
    {
        $this->table = $table;
        $this->columns = $columns;

        /** @var TableProperties $table_properties */
        $table_properties = app()->make(SchemaResolverInterface::class, compact('table', 'columns'))->resolve();

        $this->key_name = $table_properties->getKeyName();
        $this->fields = $table_properties->getFields();
        $this->soft_deletes = $table_properties->hasSoftDeletes();
        $this->timestamps = $table_properties->hasTimestamps();

        $this->renderer = app()->make(StubRenderer::class);
    }

    public abstract function render(): string;

    public function getField(string $column): ?Field
    {
        return $this->fields[$column] ?? null;
    }

    public function isNullable(string $column): bool
    {
        return $this->getField($column)->isNullable();
    }

    public function hasDefault(string $column): bool
    {
        return $this->getField($column)->hasDefault();
    }

    public function getRenderer(): StubRenderer
    {
        return $this->renderer;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getMorph(): string
    {
        return StringCaser::singularSnake($this->getTable());
    }

    public function getModelClass(): string
    {
        return StringCaser::singularStudly($this->getTable());
    }

    /**
     * @return bool
     */
    public function hasSoftDeletes(): bool
    {
        return $this->soft_deletes;
    }

    /**
     * @return bool
     */
    public function hasTimestamps(): bool
    {
        return $this->timestamps;
    }

    /**
     * Check if has any fillable
     */
    public function hasAnyFillable(): bool
    {
        $fields = $this->getFields();

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($fields as $column => $field) {
            if ($field->isFillable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the fillable attributes
     */
    public function getFillableAttributes(): array
    {
        $fillable = [];

        $fields = $this->getFields();

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($fields as $column => $field) {
            if ($field->isFillable()) {
                $fillable[] = $column;
            }
        }

        return $fillable;
    }

    /**
     * Get the searchable attributes
     */
    public function getSearchableAttributes(): array
    {
        $fillable = [];

        $fields = $this->getFields();

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($fields as $column => $field) {
            if ($field->isSearchable()) {
                $fillable[] = $column;
            }
        }

        return $fillable;
    }

    /**
     * Get the foreign keys
     */
    public function getForeignKeyAttributes(): array
    {
        $fillable = [];

        $fields = $this->getFields();

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($fields as $column => $field) {
            if ($field instanceof ForeignKeyField) {
                $fillable[] = $column;
            }
        }

        return $fillable;
    }

    /**
     * Get the icon provider
     */
    public function getIconProvider(): BaseIconProvider
    {
        $provider = config('generators.icon_provider');

        return new $provider;
    }

    /**
     * Get the sidebar icon prefix
     */
    public function getSidebarIconPrefix(): ?string
    {
        return config('generators.sidebar_icon_prefix');
    }

    /**
     * Get the icon
     */
    public function getIcon(bool $format = true): string
    {
        $provider = $this->getIconProvider();
        $icon = $provider->findIconFor($this->getTable());

        return $format ? $provider->formatIcon($icon) : $icon;
    }

    /**
     * Get the key name
     */
    public function getKeyName(): string
    {
        return $this->key_name;
    }

    /**
     * Get field count
     */
    public function fieldsCount(): int
    {
        return count($this->getFields());
    }

    /**
     * Check if the name field is in the columns
     */
    public function isNameFieldIncludedInColumns(): bool
    {
        $name_field = $this->getNameField();

        return array_key_exists($name_field, $this->getFields());
    }

    /**
     * Get the name field
     */
    public function getNameField(): string
    {
        // check if any reserved names exists
        $candidate_name_fields = ['name', 'title'];

        foreach ($candidate_name_fields as $column) {
            if ($this->getField($column) instanceof StringField) {
                return $column;
            }
        }

        // look for the first string field
        $fields = $this->getFields();

        foreach ($fields as $column => $field) {
            if ($field instanceof StringField) {
                return $column;
            }
        }

        // default to the key name
        return $this->getKeyName();
    }

    /**
     * Get the admin link name label
     */
    public function getNameLabel(): string
    {
        return StringCaser::title($this->getNameField());
    }
}
