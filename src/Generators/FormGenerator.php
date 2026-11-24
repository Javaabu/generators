<?php

namespace Javaabu\Generators\Generators;

use Illuminate\Support\Str;
use Javaabu\Generators\Resource\ResourceResolver;
use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

/**
 * The javaabu/stencil form of the resource. It renders in Blade and React,
 * and its fields give the validation rules.
 */
class FormGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->formClass()), $this->renderForm(), description: 'form')];
    }

    public function renderForm(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('forms');
        $imports = new Imports(['Javaabu\\Stencil\\Form'], $namespace);

        $section = $imports->add('Javaabu\\Stencil\\Components\\Section');

        $fields = [];

        foreach ($resource->formFields() as $column => $field) {
            $extra = in_array($column, $resource->translatable, true) ? ['translatable()'] : [];

            $fields[] = $field->getStencilField($imports, $extra) . ',';
        }

        foreach ($resource->formRelations() as $relation) {
            $select = $imports->add('Javaabu\\Stencil\\Fields\\Select');
            $label = Str::ucfirst(str_replace('_', ' ', Str::snake($relation->name)));
            $name_field = app(ResourceResolver::class)->nameFieldOf($relation->related_table);

            $fields[] = "{$select}::make('{$relation->name}')\n    ->label(__('{$label}'))\n    ->multiple()\n    ->relationship('{$relation->name}', '{$name_field}')\n    ->searchable(),";
        }

        $title = str_replace("'", "\\'", $resource->singularLabel());

        $sections = [];

        $sections[] = "{$section}::make(__('{$title}'))\n    ->columns(2)\n    ->schema([\n" . $this->indentLines(implode("\n", $fields), 2) . "\n    ]),";

        if ($resource->hasMedia()) {
            $picker = $imports->add('Javaabu\\Mediapicker\\Stencil\\MediaPickerField');

            $media = [];

            foreach ($resource->media as $collection) {
                $label = str_replace("'", "\\'", $collection->label());
                $multiple = $collection->multiple ? "\n    ->multiple()" : '';

                $media[] = "{$picker}::make('{$collection->name}')\n    ->label(__('{$label}'))\n    ->mediaType('{$collection->type}'){$multiple},";
            }

            $sections[] = "{$section}::make(__('Media'))\n    ->schema([\n" . $this->indentLines(implode("\n", $media), 2) . "\n    ]),";
        }

        return Stub::make('form/Form.stub')->with([
            'namespace' => $namespace,
            'class' => class_basename($resource->formClass()),
            'schema' => implode("\n", $sections),
            'imports' => $imports->render(),
        ])->render();
    }

    protected function indentLines(string $code, int $levels): string
    {
        return Stub::indent($code, $levels);
    }
}
