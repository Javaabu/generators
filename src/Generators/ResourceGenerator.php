<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\Fields\ForeignKeyField;
use Javaabu\Generators\Resource\ResourceResolver;
use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

/**
 * The JSON resource the Inertia pages receive: attributes, loaded relations,
 * media URLs, the record's URLs and what the user can do with it.
 */
class ResourceGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->resourceClass()), $this->renderResource(), description: 'JSON resource')];
    }

    public function renderResource(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('resources');
        $imports = new Imports(['Illuminate\\Http\\Request', 'Illuminate\\Http\\Resources\\Json\\JsonResource'], $namespace);

        $model = $imports->add($resource->modelClass());

        $attributes = ["'{$resource->key_name}' => \$this->{$resource->key_name},"];

        foreach ($resource->fields as $column => $field) {
            $attributes[] = "'{$column}' => {$field->getResourceValue()},";

            if ($field instanceof ForeignKeyField) {
                $relation = $field->getRelationName();
                $key = $field->getRelatedKey();
                $name = $field->getRelatedNameField();
                $attribute = $field->getDisplayName();

                $attributes[] = "'{$attribute}' => \$this->whenLoaded('{$relation}', fn () => [\n    '{$key}' => \$this->{$relation}->{$key},\n    '{$name}' => \$this->{$relation}->{$name},\n]),";
            }
        }

        foreach ($resource->belongs_to_many as $relation) {
            $related = $imports->add($resource->namespace('models') . '\\' . $relation->relatedModel());
            $name = app(ResourceResolver::class)->nameFieldOf($relation->related_table);
            $item = lcfirst($relation->relatedModel());

            $attributes[] = "'{$relation->name}' => \$this->whenLoaded('{$relation->name}', fn () => \$this->{$relation->name}->map(fn ({$related} \${$item}) => [\n    'id' => \${$item}->getKey(),\n    '{$name}' => \${$item}->{$name},\n])->all()),";
        }

        foreach ($resource->media as $media) {
            $attributes[] = $media->multiple
                ? "'{$media->name}' => \$this->getAttachmentMedia('{$media->name}')->map(fn (\$media) => \$media->getUrl())->values()->all(),"
                : "'{$media->name}' => \$this->getFirstAttachmentUrl('{$media->name}') ?: null,";
        }

        if ($resource->hasTimestamps()) {
            $attributes[] = "'created_at' => \$this->created_at?->format('Y-m-d H:i'),";
            $attributes[] = "'updated_at' => \$this->updated_at?->format('Y-m-d H:i'),";
        }

        if ($resource->hasSoftDeletes()) {
            $attributes[] = "'deleted_at' => \$this->deleted_at?->format('Y-m-d H:i'),";
        }

        $record = $resource->isNested()
            ? "[\$this->{$resource->parent->getName()}, \$this->resource]"
            : '$this->resource';

        $urls = [
            "'show' => route('{$resource->routeName('show')}', {$record}),",
            "'edit' => route('{$resource->routeName('edit')}', {$record}),",
            "'destroy' => route('{$resource->routeName('destroy')}', {$record}),",
        ];

        $abilities = ['view', 'update', 'delete'];

        if ($resource->hasSoftDeletes()) {
            $urls[] = "'restore' => route('{$resource->routeName('restore')}', {$record}),";
            $urls[] = "'force_delete' => route('{$resource->routeName('force-delete')}', {$record}),";
            $abilities[] = 'restore';
            $abilities[] = 'forceDelete';
        }

        $attributes[] = "'urls' => [\n" . Stub::indent(implode("\n", $urls)) . "\n],";

        $can = array_map(fn (string $ability) => "'{$ability}' => (bool) \$request->user()?->can('{$ability}', \$this->resource),", $abilities);

        $attributes[] = "'can' => [\n" . Stub::indent(implode("\n", $can)) . "\n],";

        return Stub::make('resource/Resource.stub')->with([
            'namespace' => $namespace,
            'class' => class_basename($resource->resourceClass()),
            'model' => $model,
            'attributes' => implode("\n", $attributes),
            'imports' => $imports->render(),
        ])->render();
    }
}
