<?php

namespace Javaabu\Generators\Resource;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Javaabu\Generators\Exceptions\ColumnDoesNotExistException;
use Javaabu\Generators\Fields\BooleanField;
use Javaabu\Generators\Fields\DateField;
use Javaabu\Generators\Fields\DateTimeField;
use Javaabu\Generators\Fields\DecimalField;
use Javaabu\Generators\Fields\EnumField;
use Javaabu\Generators\Fields\Field;
use Javaabu\Generators\Fields\ForeignKeyField;
use Javaabu\Generators\Fields\IntegerField;
use Javaabu\Generators\Fields\JsonField;
use Javaabu\Generators\Fields\StringField;
use Javaabu\Generators\Fields\TextField;
use Javaabu\Generators\Fields\TimeField;
use Javaabu\Generators\Fields\YearField;
use Javaabu\Generators\Schema\Column;
use Javaabu\Generators\Schema\RelationResolver;
use Javaabu\Generators\Schema\Relations\BelongsToMany;
use Javaabu\Generators\Schema\SchemaInspector;
use Javaabu\Generators\Schema\Table;
use Javaabu\Generators\Support\Translations;

/**
 * Builds the resource definition of a table.
 */
class ResourceResolver
{
    public function __construct(
        protected SchemaInspector $inspector,
        protected RelationResolver $relations,
    ) {}

    public function resolve(string $table_name, ?ResourceOptions $options = null): ResourceDefinition
    {
        $options ??= new ResourceOptions;

        $table = $this->inspector->table($table_name);

        $this->ensureColumnsExist($table, $options->columns);

        $key_name = $table->keyName() ?? 'id';
        $skip = (array) config('generators.skip_columns', []);

        $fields = [];

        foreach ($table->columns as $column) {
            if ($column->name === $key_name) {
                continue;
            }

            if (in_array($column->name, $skip, true)) {
                continue;
            }

            if ($options->columns && ! in_array($column->name, $options->columns, true)) {
                continue;
            }

            if ($field = $this->makeField($table, $column)) {
                $fields[$column->name] = $field;
            }
        }

        $parent = null;
        $parent_relation = null;

        if ($options->parent) {
            $parent = $this->resolveParent($table, $fields, $options->parent);

            foreach ($this->relations->hasMany($this->inspector->table($parent->getRelatedTable())) as $has_many) {
                if ($has_many->related_table === $table->name && $has_many->foreign_key === $parent->getName()) {
                    $parent_relation = $has_many->name;
                }
            }
        }

        $belongs_to_many = $this->relations->belongsToMany($table);

        $name_field = $this->nameField($table, $fields, $key_name);

        $translatable = $this->translatableColumns($table, $fields, $options->translatable);

        $form = $this->formColumns($fields, $belongs_to_many, $options->form, $parent);
        $index = $this->indexColumns($fields, $belongs_to_many, $options->index, $name_field, $parent);
        $show = $this->showColumns($fields, $belongs_to_many, $options->show);

        $this->ensureChosenColumnsExist($fields, $belongs_to_many, [...($options->form ?? []), ...($options->index ?? []), ...($options->show ?? [])]);

        return new ResourceDefinition(
            table: $table,
            fields: $fields,
            key_name: $key_name,
            name_field: $name_field,
            belongs_to: $this->relations->belongsTo($table),
            has_many: $this->relations->hasMany($table),
            belongs_to_many: $belongs_to_many,
            index_columns: $index,
            form_columns: $form,
            show_columns: $show,
            parent: $parent,
            parent_name_field: $parent ? $parent->getRelatedNameField() : null,
            media: $options->media,
            views: $options->views ?? (string) config('generators.views', 'blade'),
            translatable: $translatable,
            user_type: $options->user_type,
            parent_relation: $parent_relation,
        );
    }

    public function makeField(Table $table, Column $column): ?Field
    {
        $name = $column->name;
        $nullable = $column->nullable;
        $unique = $column->unique;
        $default = $column->default;

        if ($foreign_key = $table->foreignKeyFor($name)) {
            return new ForeignKeyField(
                $name,
                $foreign_key->foreign_table,
                $foreign_key->foreign_column,
                $nullable,
                $default,
                $unique,
                $this->relatedNameField($foreign_key->foreign_table),
                trim((string) config('generators.namespaces.models', 'App\\Models'), '\\'),
            );
        }

        $enum_class = $this->enumClass($table, $column);

        if ($enum_class || $column->type === 'enum') {
            return new EnumField($name, $column->options, $enum_class, $nullable, $default, $unique);
        }

        return match ($column->type) {
            'boolean' => new BooleanField($name, $nullable, $this->booleanDefault($default), $unique),
            'string', 'uuid' => new StringField($name, $nullable, $default, $unique, $column->length ?? 255),
            'text' => new TextField($name, $nullable, $default, $unique, $column->length),
            'tinyint', 'smallint', 'mediumint', 'int', 'bigint' => new IntegerField($name, $nullable, $default, $unique, $column->type, $column->unsigned),
            'decimal' => new DecimalField($name, $nullable, $default, $unique, $column->precision ?? 10, $column->scale ?? 2, $column->unsigned),
            'year' => new YearField($name, $nullable, $default, $unique),
            'datetime', 'timestamp' => new DateTimeField($name, $nullable, $default, $unique),
            'date' => new DateField($name, $nullable, $default, $unique),
            'time' => new TimeField($name, $nullable, $default, $unique),
            'json' => new JsonField($name, $nullable, $default, $unique),
            default => null,
        };
    }

    /**
     * The column that names a record of a table: name, title, or the first string.
     */
    public function nameFieldOf(string $table_name): string
    {
        $table = $this->inspector->table($table_name);
        $key = $table->keyName() ?? 'id';

        foreach ((array) config('generators.name_fields', ['name', 'title']) as $candidate) {
            if ($table->column($candidate)?->type === 'string') {
                return $candidate;
            }
        }

        foreach ($table->columns as $column) {
            if ($column->type === 'string' && ! $table->foreignKeyFor($column->name) && $column->name !== $key) {
                return $column->name;
            }
        }

        return $key;
    }

    protected function relatedNameField(string $table): string
    {
        if (! $this->inspector->hasTable($table)) {
            return 'name';
        }

        return $this->nameFieldOf($table);
    }

    protected function enumClass(Table $table, Column $column): ?string
    {
        $configured = config("generators.enums.{$table->name}.{$column->name}")
            ?? ((array) config('generators.enums', []))["{$table->name}.{$column->name}"]
            ?? null;

        if (is_string($configured) && $configured !== '') {
            return ltrim($configured, '\\');
        }

        return $column->enumClassFromComment();
    }

    protected function booleanDefault(mixed $default): ?bool
    {
        if ($default === null) {
            return null;
        }

        return in_array(strtolower((string) $default), ['1', 'true', "b'1'"], true);
    }

    /**
     * @param  array<string, Field>  $fields
     */
    protected function nameField(Table $table, array $fields, string $key_name): string
    {
        foreach ((array) config('generators.name_fields', ['name', 'title']) as $candidate) {
            if (($fields[$candidate] ?? null) instanceof StringField) {
                return $candidate;
            }
        }

        foreach ($fields as $column => $field) {
            if ($field instanceof StringField) {
                return $column;
            }
        }

        return $key_name;
    }

    /**
     * @param  array<string, Field>  $fields
     */
    protected function resolveParent(Table $table, array $fields, string $parent): ForeignKeyField
    {
        foreach ($fields as $field) {
            if (! $field instanceof ForeignKeyField) {
                continue;
            }

            $matches = $field->getName() === $parent
                || $field->getRelatedTable() === $parent
                || $field->getRelationName() === Str::camel($parent)
                || $field->getRelatedTable() === Str::plural(Str::snake($parent));

            if ($matches) {
                return $field;
            }
        }

        throw new InvalidArgumentException("The parent [{$parent}] is not a foreign key of the {$table->name} table.");
    }

    /**
     * @param  array<string, Field>  $fields
     * @param  array<int, string>|bool|null  $choice
     * @return array<int, string>
     */
    protected function translatableColumns(Table $table, array $fields, array|bool|null $choice): array
    {
        $choice ??= config('generators.translatable', 'auto') === 'auto' ? true : (bool) config('generators.translatable');

        if ($choice === false) {
            return [];
        }

        if (! $table->hasTranslationColumns()) {
            if (is_array($choice) && $choice) {
                throw new InvalidArgumentException("The {$table->name} table needs the translations and lang columns to be translatable. Add them with \$table->json('translations')->nullable(); \$table->string('lang')->nullable()->index();");
            }

            return [];
        }

        if (! Translations::installed()) {
            return [];
        }

        if (is_array($choice)) {
            return array_values(array_intersect($choice, array_keys($fields)));
        }

        $columns = [];

        foreach ($fields as $column => $field) {
            if ($field instanceof StringField && ($field->isUnique() || $field->getKind() !== 'text')) {
                continue;
            }

            if ($field instanceof StringField || $field instanceof TextField) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * @param  array<string, Field>  $fields
     * @param  array<int, BelongsToMany>  $relations
     * @param  array<int, string>|null  $choice
     * @return array<int, string>
     */
    protected function formColumns(array $fields, array $relations, ?array $choice, ?ForeignKeyField $parent): array
    {
        if ($choice !== null) {
            return array_values(array_filter($choice, fn (string $column) => $column !== $parent?->getName()));
        }

        $columns = [];

        foreach ($fields as $column => $field) {
            if ($parent && $column === $parent->getName()) {
                continue;
            }

            if ($field->isFormField()) {
                $columns[] = $column;
            }
        }

        foreach ($relations as $relation) {
            $columns[] = $relation->name;
        }

        return $columns;
    }

    /**
     * @param  array<string, Field>  $fields
     * @param  array<int, BelongsToMany>  $relations
     * @param  array<int, string>|null  $choice
     * @return array<int, string>
     */
    protected function indexColumns(array $fields, array $relations, ?array $choice, string $name_field, ?ForeignKeyField $parent): array
    {
        if ($choice !== null) {
            return array_values(array_unique([$name_field, ...$choice]));
        }

        $columns = isset($fields[$name_field]) ? [$name_field] : [];
        $limit = (int) config('generators.index_columns', 5);

        foreach ($fields as $column => $field) {
            if (count($columns) >= $limit) {
                break;
            }

            if ($column === $name_field || ($parent && $column === $parent->getName())) {
                continue;
            }

            if ($field->isIndexColumn()) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * @param  array<string, Field>  $fields
     * @param  array<int, BelongsToMany>  $relations
     * @param  array<int, string>|null  $choice
     * @return array<int, string>
     */
    protected function showColumns(array $fields, array $relations, ?array $choice): array
    {
        if ($choice !== null) {
            return $choice;
        }

        return [...array_keys($fields), ...array_map(fn (BelongsToMany $relation) => $relation->name, $relations)];
    }

    /**
     * @param  array<int, string>  $columns
     */
    protected function ensureColumnsExist(Table $table, array $columns): void
    {
        $missing = array_values(array_filter($columns, fn (string $column) => ! $table->hasColumn($column)));

        if ($missing) {
            throw new ColumnDoesNotExistException("The following columns do not exist on the table '{$table->name}': " . implode(', ', $missing));
        }
    }

    /**
     * @param  array<string, Field>  $fields
     * @param  array<int, BelongsToMany>  $relations
     * @param  array<int, string>  $chosen
     */
    protected function ensureChosenColumnsExist(array $fields, array $relations, array $chosen): void
    {
        $known = [...array_keys($fields), ...array_map(fn (BelongsToMany $relation) => $relation->name, $relations)];

        $missing = array_values(array_unique(array_diff($chosen, $known)));

        if ($missing) {
            throw new ColumnDoesNotExistException('These fields are not columns or many-to-many relations of the resource: ' . implode(', ', $missing));
        }
    }
}
