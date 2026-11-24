<?php

namespace Javaabu\Generators\Generators;

use Illuminate\Support\Str;
use Javaabu\Generators\Fields\BooleanField;
use Javaabu\Generators\Fields\EnumField;
use Javaabu\Generators\Fields\Field;
use Javaabu\Generators\Fields\ForeignKeyField;
use Javaabu\Generators\Resource\ResourceResolver;
use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

/**
 * javaabu/filters for the index page: a search over the text columns,
 * selects for enums and relations, and ranges for the index columns.
 */
class FiltersGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->filtersClass()), $this->renderFilters(), description: 'filters')];
    }

    public function renderFilters(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('filters');
        $imports = new Imports(['Javaabu\\Filters\\Filters'], $namespace);

        $model = $imports->add($resource->modelClass());

        return Stub::make('filters/Filters.stub')->with([
            'namespace' => $namespace,
            'class' => class_basename($resource->filtersClass()),
            'model' => $model,
            'defaultSort' => $resource->hasTimestamps() ? 'created_at' : $resource->key_name,
            'filters' => implode("\n", array_map(fn (string $filter) => "{$filter},", $this->filters($imports))),
            'sorts' => implode("\n", array_map(fn (string $sort) => "{$sort},", $this->sorts($imports))),
            'imports' => $imports->render(),
        ])->render();
    }

    /**
     * @return array<int, string>
     */
    public function filters(Imports $imports): array
    {
        $resource = $this->resource;
        $filters = [];

        if ($columns = $resource->searchableColumns()) {
            $text = $imports->add('Javaabu\\Filters\\TextFilter');
            $list = implode(', ', array_map(fn (string $column) => "'{$column}'", $columns));

            $filters[] = "{$text}::make('search')->label(__('Search'))->columns([{$list}])";
        }

        $index = array_keys($resource->indexFields());

        /** @var Field $field */
        foreach ($resource->fields as $column => $field) {
            if ($resource->parent && $column === $resource->parent->getName()) {
                continue;
            }

            $always = $field instanceof EnumField || $field instanceof ForeignKeyField || $field instanceof BooleanField;

            if (! $always && ! in_array($column, $index, true)) {
                continue;
            }

            if ($filter = $field->getFilter($imports)) {
                $filters[] = $filter;
            }
        }

        foreach ($resource->belongs_to_many as $relation) {
            $select = $imports->add('Javaabu\\Filters\\SelectFilter');
            $label = Str::ucfirst(str_replace('_', ' ', Str::snake($relation->name)));
            $name_field = app(ResourceResolver::class)->nameFieldOf($relation->related_table);

            $filters[] = "{$select}::make('{$relation->name}')->label(__('{$label}'))->relationship('{$relation->name}', '{$name_field}')->multiple()";
        }

        if ($resource->hasTimestamps()) {
            $range = $imports->add('Javaabu\\Filters\\DateRangeFilter');

            $filters[] = "{$range}::make('created_at')->label(__('Created at'))";
        }

        if ($resource->hasSoftDeletes()) {
            $trashed = $imports->add('Javaabu\\Filters\\TrashedFilter');

            $filters[] = "{$trashed}::make()";
        }

        return $filters;
    }

    /**
     * @return array<int, string>
     */
    public function sorts(Imports $imports): array
    {
        $resource = $this->resource;
        $sort = $imports->add('Javaabu\\Filters\\Sort');

        $sorts = [];

        foreach ($resource->indexFields() as $column => $field) {
            if ($field->isSortable()) {
                $sorts[] = "{$sort}::make('{$column}')->label({$field->getLabelCall()})";
            }
        }

        if ($resource->hasTimestamps()) {
            $sorts[] = "{$sort}::make('created_at')->label(__('Created at'))";
            $sorts[] = "{$sort}::make('updated_at')->label(__('Updated at'))";
        }

        if (! $sorts) {
            $sorts[] = "{$sort}::make('{$resource->key_name}')->label(__('ID'))";
        }

        return $sorts;
    }

    /**
     * Columns the index table can sort by.
     *
     * @return array<int, string>
     */
    public function sortableColumns(): array
    {
        $columns = [];

        foreach ($this->resource->indexFields() as $column => $field) {
            if ($field->isSortable()) {
                $columns[] = $column;
            }
        }

        return $columns;
    }
}
