<?php

namespace Javaabu\Generators\Generators;

use Illuminate\Support\Str;
use Javaabu\Generators\Resource\ResourceResolver;
use Javaabu\Generators\Schema\Relations\BelongsToMany;
use Javaabu\Generators\Support\Stub;

/**
 * Blade views with javaabu/forms v2: index (filters, bulk actions, sortable
 * table), create and edit (the stencil form), and show (an infolist).
 */
class BladeViewsGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [
            new GeneratedFile($resource->viewPath('index'), $this->renderIndex(), description: 'index view'),
            new GeneratedFile($resource->viewPath('create'), $this->renderCreate(), description: 'create view'),
            new GeneratedFile($resource->viewPath('edit'), $this->renderEdit(), description: 'edit view'),
            new GeneratedFile($resource->viewPath('show'), $this->renderShow(), description: 'show view'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function common(): array
    {
        $resource = $this->resource;

        return [
            'layout' => (string) config('generators.blade.layout', 'layouts.admin'),
            'section' => (string) config('generators.blade.section', 'content'),
            'modelClass' => '\\' . $resource->modelClass(),
            'plural' => $resource->pluralVariable(),
            'variable' => $resource->variable(),
            'pluralLower' => $this->escape($resource->pluralLower()),
            'singularLabel' => $this->escape($resource->singularLabel()),
            'nameField' => $resource->nameField(),
        ];
    }

    public function renderIndex(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();
        $sortable = (new FiltersGenerator($resource))->sortableColumns();

        $headers = [];
        $cells = [];

        foreach ($resource->indexFields() as $column => $field) {
            $label = $field->getLabelCall();

            $headers[] = in_array($column, $sortable, true)
                ? "<x-filters::heading :filters=\"\$filters\" sort=\"{$column}\" :label=\"{$label}\" />"
                : "<x-forms::table.heading :label=\"{$label}\" />";

            if ($column === $resource->nameField()) {
                $cells[] = "<x-forms::table.cell :label=\"{$label}\">\n"
                    . "    @can('view', {$variable})\n"
                    . "        <a href=\"{{ {$resource->route('show', $variable)} }}\" class=\"font-medium hover:underline\">{{ {$variable}->{$column} }}</a>\n"
                    . "    @else\n"
                    . "        {{ {$variable}->{$column} }}\n"
                    . "    @endcan\n"
                    . '</x-forms::table.cell>';

                continue;
            }

            $cells[] = $field->getBladeCell($variable);
        }

        foreach ($resource->indexRelations() as $relation) {
            $label = $this->relationLabel($relation);

            $headers[] = "<x-forms::table.heading :label=\"{$label}\" />";
            $cells[] = "<x-forms::table.cell :label=\"{$label}\" :value=\"{$variable}->{$relation->name}->pluck('{$this->relationNameField($relation)}')->all()\" />";
        }

        return Stub::make('views/blade/index.blade.stub')->with([
            ...$this->common(),
            'title' => $this->escape($resource->pluralLabel()),
            'newLabel' => $this->escape('New ' . $resource->singularLower()),
            'createRoute' => $resource->route('create'),
            'breadcrumbs' => $this->breadcrumbs(false),
            'headers' => implode("\n", $headers),
            'cells' => implode("\n", $cells),
            'columns' => (string) (count($headers) + 1),
        ])->render();
    }

    public function renderCreate(): string
    {
        $resource = $this->resource;

        return Stub::make('views/blade/create.blade.stub')->with([
            ...$this->common(),
            'title' => $this->escape('New ' . $resource->singularLower()),
            'breadcrumbs' => $this->breadcrumbs(),
        ])->render();
    }

    public function renderEdit(): string
    {
        $resource = $this->resource;

        return Stub::make('views/blade/edit.blade.stub')->with([
            ...$this->common(),
            'title' => $this->escape('Edit ' . $resource->singularLower()),
            'showRoute' => $resource->route('show', '$' . $resource->variable()),
            'breadcrumbs' => $this->breadcrumbs(),
        ])->render();
    }

    public function renderShow(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $entries = [];

        foreach ($resource->showFields() as $field) {
            $entries[] = $field->getBladeEntry($variable);
        }

        foreach ($resource->showRelations() as $relation) {
            $entries[] = "<x-forms::text-entry :label=\"{$this->relationLabel($relation)}\" :value=\"{$variable}->{$relation->name}->pluck('{$this->relationNameField($relation)}')->implode(', ')\" />";
        }

        return Stub::make('views/blade/show.blade.stub')->with([
            ...$this->common(),
            'breadcrumbs' => $this->breadcrumbs(),
            'actions' => $this->showActions(),
            'trashedAlert' => $resource->hasSoftDeletes()
                ? "@if({$variable}->trashed())\n    <x-forms::alert type=\"warning\" class=\"mb-6\">\n        {{ __('This {$this->escape($resource->singularLower())} is deleted.') }}\n    </x-forms::alert>\n@endif"
                : '',
            'entries' => implode("\n", $entries),
            'media' => $this->showMedia(),
        ])->render();
    }

    protected function showActions(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();
        $label = $this->escape($resource->singularLower());

        $edit = "@can('update', {$variable})\n"
            . "    <x-forms::link-button :url=\"{$resource->route('edit', $variable)}\" start-icon=\"pencil\">\n"
            . "        {{ __('Edit') }}\n"
            . "    </x-forms::link-button>\n"
            . '@endcan';

        $delete = $this->buttonForm('delete', 'DELETE', $resource->route('destroy', $variable), 'danger', 'trash-2', 'Delete', "Delete this {$label}?");

        if (! $resource->hasSoftDeletes()) {
            return "{$edit}\n\n{$delete}";
        }

        $restore = $this->buttonForm('restore', 'PATCH', $resource->route('restore', $variable), 'success', '', 'Restore');
        $force = $this->buttonForm('forceDelete', 'DELETE', $resource->route('force-delete', $variable), 'danger', 'trash-2', 'Delete permanently', "Permanently delete this {$label}? This cannot be undone.");

        return "@if({$variable}->trashed())\n"
            . Stub::indent("{$restore}\n\n{$force}") . "\n"
            . "@else\n"
            . Stub::indent("{$edit}\n\n{$delete}") . "\n"
            . '@endif';
    }

    protected function buttonForm(string $ability, string $method, string $route, string $color, string $icon, string $label, ?string $confirm = null): string
    {
        $variable = '$' . $this->resource->variable();
        $icon = $icon !== '' ? " start-icon=\"{$icon}\"" : '';
        $onsubmit = $confirm ? ' onsubmit="return confirm(@js(__(\'' . $this->escape($confirm) . '\')))"' : '';

        return "@can('{$ability}', {$variable})\n"
            . "    <form method=\"POST\" action=\"{{ {$route} }}\"{$onsubmit}>\n"
            . "        @csrf\n"
            . "        @method('{$method}')\n\n"
            . "        <x-forms::submit color=\"{$color}\"{$icon}>\n"
            . "            {{ __('{$this->escape($label)}') }}\n"
            . "        </x-forms::submit>\n"
            . "    </form>\n"
            . '@endcan';
    }

    protected function showMedia(): string
    {
        $resource = $this->resource;
        $variable = '$' . $resource->variable();

        $cards = [];

        foreach ($resource->media as $media) {
            $label = $this->escape($media->label());

            if ($media->type === 'image' && ! $media->multiple) {
                $content = "<x-mediapicker::image :model=\"{$variable}\" collection=\"{$media->name}\" />";
            } elseif ($media->type === 'image') {
                $content = "<div class=\"grid grid-cols-2 gap-4 md:grid-cols-4\">\n"
                    . "    @foreach({$variable}->getAttachments('{$media->name}') as \$attachment)\n"
                    . "        <x-mediapicker::image :attachment=\"\$attachment\" />\n"
                    . "    @endforeach\n"
                    . '</div>';
            } else {
                $content = "<ul class=\"list-disc ps-6\">\n"
                    . "    @foreach({$variable}->getAttachmentMedia('{$media->name}') as \$media)\n"
                    . "        <li><a href=\"{{ \$media->getUrl() }}\" class=\"hover:underline\">{{ \$media->name }}</a></li>\n"
                    . "    @endforeach\n"
                    . '</ul>';
            }

            $cards[] = "<x-forms::card :title=\"__('{$label}')\" class=\"mt-6\">\n" . Stub::indent($content) . "\n</x-forms::card>";
        }

        return $cards ? "\n" . implode("\n\n", $cards) : '';
    }

    /**
     * A link back to the list (and the parent of a nested resource).
     */
    protected function breadcrumbs(bool $to_index = true): string
    {
        $resource = $this->resource;
        $links = [];

        if ($resource->isNested()) {
            $parent = '$' . $resource->parentVariable();
            $links[] = "<span>{{ {$parent}->{$resource->parent_name_field} }}</span>";
        }

        if ($to_index) {
            $links[] = "<a href=\"{{ {$resource->route('index')} }}\" class=\"hover:underline\">{{ __('{$this->escape($resource->pluralLabel())}') }}</a>";
        }

        if (! $links) {
            return '';
        }

        return "<nav class=\"mb-1 flex gap-2 text-sm text-muted-foreground\" aria-label=\"{{ __('Breadcrumb') }}\">\n"
            . Stub::indent(implode("\n<span aria-hidden=\"true\">/</span>\n", $links)) . "\n"
            . '</nav>';
    }

    protected function relationLabel(BelongsToMany $relation): string
    {
        return "__('" . $this->escape(Str::ucfirst(str_replace('_', ' ', Str::snake($relation->name)))) . "')";
    }

    protected function relationNameField(BelongsToMany $relation): string
    {
        return app(ResourceResolver::class)->nameFieldOf($relation->related_table);
    }

    protected function escape(string $value): string
    {
        return str_replace("'", "\\'", $value);
    }
}
