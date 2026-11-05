<?php

namespace Javaabu\Generators\Generators;

use Illuminate\Support\Str;
use Javaabu\Generators\Fields\ForeignKeyField;
use Javaabu\Generators\Resource\ResourceResolver;
use Javaabu\Generators\Schema\Relations\BelongsToMany;

/**
 * Inertia React pages in TypeScript, with @javaabu/forms, @javaabu/stencil's
 * StencilForm, @javaabu/filters and @javaabu/bulk-actions.
 */
class InertiaPagesGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [
            new GeneratedFile($this->typesPath(), $this->renderTypes(), description: 'page types'),
            new GeneratedFile($resource->pagePath('Index'), $this->renderIndex(), description: 'index page'),
            new GeneratedFile($resource->pagePath('Create'), $this->renderCreate(), description: 'create page'),
            new GeneratedFile($resource->pagePath('Edit'), $this->renderEdit(), description: 'edit page'),
            new GeneratedFile($resource->pagePath('Show'), $this->renderShow(), description: 'show page'),
        ];
    }

    public function typesPath(): string
    {
        return dirname($this->resource->pagePath('Index')) . '/types.ts';
    }

    /** Product */
    protected function type(): string
    {
        return $this->resource->model();
    }

    /** product */
    protected function variable(): string
    {
        return Str::camel($this->resource->variable());
    }

    /** products */
    protected function pluralVariable(): string
    {
        return Str::camel($this->resource->pluralVariable());
    }

    public function renderTypes(): string
    {
        $resource = $this->resource;
        $lines = [];

        $lines[] = "{$resource->key_name}: number";

        foreach ($resource->fields as $column => $field) {
            $type = $field->getTypeScriptType();

            $lines[] = "{$column}: {$type}" . ($field->isNullable() ? ' | null' : '');

            if ($field instanceof ForeignKeyField) {
                $key_type = $field->getRelatedKey() === 'id' ? 'number' : 'string';

                $lines[] = "{$field->getDisplayName()}?: { {$field->getRelatedKey()}: {$key_type}; {$field->getRelatedNameField()}: string } | null";
            }
        }

        foreach ($resource->belongs_to_many as $relation) {
            $lines[] = "{$relation->name}?: { id: number; {$this->relationNameField($relation)}: string }[]";
        }

        foreach ($resource->media as $media) {
            $lines[] = $media->multiple ? "{$media->name}: string[]" : "{$media->name}: string | null";
        }

        if ($resource->hasTimestamps()) {
            $lines[] = 'created_at: string | null';
            $lines[] = 'updated_at: string | null';
        }

        if ($resource->hasSoftDeletes()) {
            $lines[] = 'deleted_at: string | null';
        }

        $urls = ['show: string', 'edit: string', 'destroy: string'];
        $can = ['view: boolean', 'update: boolean', 'delete: boolean'];

        if ($resource->hasSoftDeletes()) {
            $urls[] = 'restore: string';
            $urls[] = 'force_delete: string';
            $can[] = 'restore: boolean';
            $can[] = 'forceDelete: boolean';
        }

        $lines[] = 'urls: { ' . implode('; ', $urls) . ' }';
        $lines[] = 'can: { ' . implode('; ', $can) . ' }';

        $parent = '';

        if ($resource->isNested()) {
            $parent_type = $resource->parentModel();
            $key = $resource->parentKey();
            $key_type = $key === 'id' ? 'number' : 'string';

            $parent = "\n\n/** The {$resource->parentVariable()} the {$resource->pluralLower()} belong to. */\nexport interface {$parent_type} {\n  {$key}: {$key_type}\n  {$resource->parent_name_field}: string\n}";
        }

        $body = implode("\n", array_map(fn (string $line) => "  {$line}", $lines));

        return "/** A {$resource->singularLower()}, as {$this->type()}Resource sends it. */\nexport interface {$this->type()} {\n{$body}\n}{$parent}\n";
    }

    public function renderIndex(): string
    {
        $resource = $this->resource;
        $type = $this->type();
        $item = $this->variable();
        $items = $this->pluralVariable();
        $sortable = (new FiltersGenerator($resource))->sortableColumns();

        $components = ['Pagination', 'TableCell', 'TableEmptyRow', 'TableHeading', 'TableRow', 'LinkButton'];
        $headers = [];
        $cells = [];
        $uses_sortable = false;

        foreach ($resource->indexFields() as $column => $field) {
            $label = $this->label($field->getLabel());

            if (in_array($column, $sortable, true)) {
                $uses_sortable = true;
                $headers[] = "<SortableHeading filters={filters} sort=\"{$column}\" label={{$label}} />";
            } else {
                $headers[] = "<TableHeading label={{$label}} />";
            }

            if ($column === $resource->nameField()) {
                $cells[] = "<TableCell label={{$label}}>\n"
                    . "  {{$item}.can.view ? (\n"
                    . "    <Link href={{$item}.urls.show} className=\"font-medium hover:underline\">\n"
                    . "      {{$item}.{$column}}\n"
                    . "    </Link>\n"
                    . "  ) : (\n"
                    . "    {$item}.{$column}\n"
                    . "  )}\n"
                    . '</TableCell>';

                continue;
            }

            $cells[] = $field->getReactCell($item);
        }

        foreach ($resource->indexRelations() as $relation) {
            $label = $this->label($this->relationLabel($relation));

            $headers[] = "<TableHeading label={{$label}} />";
            $cells[] = "<TableCell label={{$label}} value={({$item}.{$relation->name} ?? []).map((related) => related.{$this->relationNameField($relation)}).join(\", \")} />";
        }

        $filters_import = $uses_sortable ? 'FilterBar, SortableHeading, type FiltersData' : 'FilterBar, type FiltersData';
        $columns = count($headers) + 1;

        $parent_type = $resource->isNested() ? "\n  {$this->parentProp()}: {$resource->parentModel()}" : '';
        $parent_param = $resource->isNested() ? "{$this->parentProp()}, " : '';
        $types_import = $resource->isNested() ? "{$type}, {$resource->parentModel()}" : $type;

        $heading = $this->heading($resource->pluralLabel(), 8);

        $headers = $this->indent(implode("\n", $headers), 12);
        $cells = $this->indent(implode("\n", $cells), 14);

        $forms = implode(', ', $this->sortImports([...$components, 'type LaravelResourceCollection']));

        return <<<TSX
        import { Head, Link, router } from "@inertiajs/react"
        import { BulkActionsTable, type BulkActionsSchema } from "@javaabu/bulk-actions"
        import { {$filters_import} } from "@javaabu/filters"
        import { {$forms} } from "@javaabu/forms"
        import { __ } from "@javaabu/i18n"
        import type { {$types_import} } from "./types"

        interface Props {{$parent_type}
          {$items}: LaravelResourceCollection & { data: {$type}[] }
          filters: FiltersData
          bulkActions: BulkActionsSchema
          createUrl: string | null
        }

        export default function Index({ {$parent_param}{$items}, filters, bulkActions, createUrl }: Props) {
          return (
            <>
              <Head title={{$this->label($resource->pluralLabel())}} />

              <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
        {$heading}

                {createUrl && (
                  <LinkButton url={createUrl} startIcon="plus">
                    {{$this->label('New ' . $resource->singularLower())}}
                  </LinkButton>
                )}
              </div>

              <FilterBar filters={filters} />

              <BulkActionsTable
                bulkActions={bulkActions}
                onRunFinished={() => router.reload()}
                headers={
                  <>
        {$headers}
                  </>
                }
                pagination={{$items}.meta.last_page > 1 ? <Pagination paginator={{$items}} /> : null}
              >
                {{$items}.data.length > 0 ? (
                  {$items}.data.map(({$item}) => (
                    <TableRow key={{$item}.{$resource->key_name}} model={{$item}} name="{$resource->pluralVariable()}">
        {$cells}
                    </TableRow>
                  ))
                ) : (
                  <TableEmptyRow columns={{$columns}}>
                    {filters.active > 0
                      ? {$this->label("No {$resource->pluralLower()} match these filters.")}
                      : {$this->label("No {$resource->pluralLower()} yet.")}}
                  </TableEmptyRow>
                )}
              </BulkActionsTable>
            </>
          )
        }

        TSX;
    }

    public function renderCreate(): string
    {
        return $this->renderFormPage('Create', 'New ' . $this->resource->singularLower(), false);
    }

    public function renderEdit(): string
    {
        return $this->renderFormPage('Edit', 'Edit ' . $this->resource->singularLower(), true);
    }

    protected function renderFormPage(string $page, string $title, bool $editing): string
    {
        $resource = $this->resource;
        $type = $this->type();
        $item = $this->variable();

        $media = $resource->hasMedia();

        $imports = ['import { Head } from "@inertiajs/react"'];

        if ($editing) {
            $imports[0] = 'import { Head } from "@inertiajs/react"';
            $imports[] = 'import { LinkButton } from "@javaabu/forms"';
        }

        $imports[] = 'import { __ } from "@javaabu/i18n"';

        if ($media) {
            $imports[] = 'import { StencilMediaPicker } from "@javaabu/mediapicker"';
        }

        $imports[] = 'import { StencilForm, type StencilSchema } from "@javaabu/stencil"';

        $types = [];

        if ($editing) {
            $types[] = $type;
        }

        if ($resource->isNested()) {
            $types[] = $resource->parentModel();
        }

        if ($types) {
            $imports[] = 'import type { ' . implode(', ', $types) . ' } from "./types"';
        }

        $props = [];
        $params = [];

        if ($resource->isNested()) {
            $props[] = "{$this->parentProp()}: {$resource->parentModel()}";
            $params[] = $this->parentProp();
        }

        if ($editing) {
            $props[] = "{$item}: {$type}";
            $params[] = $item;
        }

        $props[] = 'form: StencilSchema';
        $params[] = 'form';

        $props = implode("\n", array_map(fn (string $prop) => "  {$prop}", $props));
        $params = implode(', ', $params);

        $components = $media ? ' components={{ mediapicker: StencilMediaPicker }}' : '';

        if ($editing) {
            $heading = "      <div className=\"mb-6 flex flex-wrap items-center justify-between gap-4\">\n"
                . $this->heading($title, 8) . "\n\n"
                . "        <LinkButton url={{$item}.urls.show} color=\"light\" startIcon=\"eye\">\n"
                . "          {{$this->label('View')}}\n"
                . "        </LinkButton>\n"
                . '      </div>';
        } else {
            $heading = "      <div className=\"mb-6\">\n" . $this->heading($title, 8) . "\n      </div>";
        }

        $imports = implode("\n", $imports);

        return <<<TSX
        {$imports}

        interface Props {
        {$props}
        }

        export default function {$page}({ {$params} }: Props) {
          return (
            <>
              <Head title={{$this->label($title)}} />

        {$heading}

              <StencilForm schema={form}{$components} />
            </>
          )
        }

        TSX;
    }

    public function renderShow(): string
    {
        $resource = $this->resource;
        $type = $this->type();
        $item = $this->variable();

        $components = ['Button', 'Infolist', 'LinkButton'];
        $entries = [];

        foreach ($resource->showFields() as $field) {
            $entries[] = $field->getReactEntry($item);
            $components = [...$components, ...array_filter($field->getReactComponents(), fn (string $component) => $component !== 'TableCell')];
        }

        foreach ($resource->showRelations() as $relation) {
            $components[] = 'TextEntry';
            $entries[] = "<TextEntry label={{$this->label($this->relationLabel($relation))}} value={({$item}.{$relation->name} ?? []).map((related) => related.{$this->relationNameField($relation)}).join(\", \")} />";
        }

        $media = [];

        foreach ($resource->media as $collection) {
            $components[] = 'Card';
            $label = $this->label($collection->label());

            if ($collection->multiple) {
                $content = $collection->type === 'image'
                    ? "<div className=\"grid grid-cols-2 gap-4 md:grid-cols-4\">\n  {{$item}.{$collection->name}.map((url) => (\n    <img key={url} src={url} alt=\"\" className=\"rounded-lg\" />\n  ))}\n</div>"
                    : "<ul className=\"list-disc ps-6\">\n  {{$item}.{$collection->name}.map((url) => (\n    <li key={url}>\n      <a href={url} className=\"hover:underline\">\n        {url.split(\"/\").pop()}\n      </a>\n    </li>\n  ))}\n</ul>";
            } else {
                $content = $collection->type === 'image'
                    ? "{{$item}.{$collection->name} && <img src={{$item}.{$collection->name}} alt=\"\" className=\"rounded-lg\" />}"
                    : "{{$item}.{$collection->name} && (\n  <a href={{$item}.{$collection->name}} className=\"hover:underline\">\n    {{$item}.{$collection->name}.split(\"/\").pop()}\n  </a>\n)}";
            }

            $media[] = "<Card title={{$label}} className=\"mt-6\">\n" . $this->indent($content, 2) . "\n</Card>";
        }

        $components = $this->sortImports(array_values(array_unique($components)));

        $types = $resource->isNested() ? "{$type}, {$resource->parentModel()}" : $type;

        $parent_prop = $resource->isNested() ? "\n  {$this->parentProp()}: {$resource->parentModel()}" : '';
        $parent_param = $resource->isNested() ? "{$this->parentProp()}, " : '';

        $name = "{$item}.{$resource->nameField()}";

        $heading = $this->heading("{{$name}}", 8, raw: true);

        $entries = $this->indent(implode("\n", $entries), 8);
        $media = $media ? "\n\n" . $this->indent(implode("\n\n", $media), 6) : '';

        $label = $resource->singularLower();
        $actions = $this->indent($this->showActions($item, $label), 10);

        $trashed = $resource->hasSoftDeletes()
            ? "\n\n      {{$item}.deleted_at && (\n        <Alert type=\"warning\" className=\"mb-6\">\n          {{$this->label("This {$label} is deleted.")}}\n        </Alert>\n      )}"
            : '';

        if ($resource->hasSoftDeletes()) {
            $components = $this->sortImports([...$components, 'Alert']);
        }

        $components = implode(', ', $components);

        return <<<TSX
        import { Head, router } from "@inertiajs/react"
        import { {$components} } from "@javaabu/forms"
        import { __ } from "@javaabu/i18n"
        import type { {$types} } from "./types"

        interface Props {{$parent_prop}
          {$item}: {$type}
        }

        export default function Show({ {$parent_param}{$item} }: Props) {
          return (
            <>
              <Head title={String({$name})} />

              <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
        {$heading}

                <div className="flex flex-wrap items-center gap-2">
        {$actions}
                </div>
              </div>{$trashed}

              <Infolist title={{$this->label($resource->singularLabel())}} model={{$item}}>
        {$entries}
              </Infolist>{$media}
            </>
          )
        }

        TSX;
    }

    protected function showActions(string $item, string $label): string
    {
        $resource = $this->resource;

        $confirm = $this->label("Delete this {$label}?");

        $edit = "{{$item}.can.update && (\n"
            . "  <LinkButton url={{$item}.urls.edit} startIcon=\"pencil\">\n"
            . "    {{$this->label('Edit')}}\n"
            . "  </LinkButton>\n"
            . ')}';

        $delete = $this->actionButton($item, 'delete', 'destroy', 'delete', 'danger', 'trash-2', 'Delete', $confirm);

        if (! $resource->hasSoftDeletes()) {
            return "{$edit}\n{$delete}";
        }

        $restore = $this->actionButton($item, 'restore', 'restore', 'patch', 'success', null, 'Restore');
        $force = $this->actionButton($item, 'forceDelete', 'force_delete', 'delete', 'danger', 'trash-2', 'Delete permanently', $this->label("Permanently delete this {$label}? This cannot be undone."));

        return "{{$item}.deleted_at ? (\n"
            . "  <>\n"
            . $this->indent("{$restore}\n{$force}", 4) . "\n"
            . "  </>\n"
            . ") : (\n"
            . "  <>\n"
            . $this->indent("{$edit}\n{$delete}", 4) . "\n"
            . "  </>\n"
            . ')}';
    }

    protected function actionButton(string $item, string $ability, string $url, string $method, string $color, ?string $icon, string $label, ?string $confirm = null): string
    {
        $icon = $icon ? "\n    startIcon=\"{$icon}\"" : '';

        $click = $confirm
            ? "() => {\n      if (window.confirm({$confirm})) router.{$method}({$item}.urls.{$url})\n    }"
            : "() => router.{$method}({$item}.urls.{$url})";

        return "{{$item}.can.{$ability} && (\n"
            . "  <Button\n"
            . "    type=\"button\"\n"
            . "    color=\"{$color}\"{$icon}\n"
            . "    onClick={{$click}}\n"
            . "  >\n"
            . "    {{$this->label($label)}}\n"
            . "  </Button>\n"
            . ')}';
    }

    /**
     * The page heading, with the breadcrumbs of a nested resource.
     */
    protected function heading(string $title, int $spaces, bool $raw = false): string
    {
        $resource = $this->resource;
        $title = $raw ? $title : "{{$this->label($title)}}";

        $lines = ['<div>'];

        if ($resource->isNested()) {
            $lines[] = "  <nav className=\"mb-1 flex gap-2 text-sm text-muted-foreground\" aria-label={{$this->label('Breadcrumb')}}>";
            $lines[] = "    <span>{{$this->parentProp()}.{$resource->parent_name_field}}</span>";
            $lines[] = '  </nav>';
        }

        $lines[] = "  <h1 className=\"text-2xl font-semibold\">{$title}</h1>";
        $lines[] = '</div>';

        return $this->indent(implode("\n", $lines), $spaces);
    }

    /**
     * Indent TSX by a number of spaces.
     */
    protected function indent(string $code, int $spaces): string
    {
        $prefix = str_repeat(' ', $spaces);

        return implode("\n", array_map(fn (string $line) => $line === '' ? '' : $prefix . $line, explode("\n", $code)));
    }

    protected function parentProp(): string
    {
        return Str::camel($this->resource->parentVariable());
    }

    protected function label(string $text): string
    {
        return '__("' . str_replace('"', '\\"', $text) . '")';
    }

    protected function relationLabel(BelongsToMany $relation): string
    {
        return Str::ucfirst(str_replace('_', ' ', Str::snake($relation->name)));
    }

    protected function relationNameField(BelongsToMany $relation): string
    {
        return app(ResourceResolver::class)->nameFieldOf($relation->related_table);
    }

    /**
     * @param  array<int, string>  $imports
     * @return array<int, string>
     */
    protected function sortImports(array $imports): array
    {
        $imports = array_values(array_unique($imports));

        usort($imports, fn (string $a, string $b) => strcasecmp(str_replace('type ', '', $a), str_replace('type ', '', $b)));

        return $imports;
    }
}
