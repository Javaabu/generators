<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\Fields\Field;
use Javaabu\Generators\Fields\ForeignKeyField;
use Javaabu\Generators\Schema\Relations\BelongsTo;
use Javaabu\Generators\Schema\Relations\Relation;
use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

class ModelGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->modelClass()), $this->renderModel(), description: 'model')];
    }

    public function renderModel(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('models');
        $imports = new Imports(['Illuminate\\Database\\Eloquent\\Model', 'Illuminate\\Database\\Eloquent\\Factories\\HasFactory'], $namespace);

        $factory = $imports->add($resource->factoryClass());

        $traits = ["/** @use HasFactory<{$factory}> */", 'use HasFactory;'];
        $implements = [];

        if ($resource->hasSoftDeletes()) {
            $traits[] = 'use ' . $imports->add('Illuminate\\Database\\Eloquent\\SoftDeletes') . ';';
        }

        if ($resource->isTranslatable()) {
            $implements[] = $imports->add('Javaabu\\Translatable\\Contracts\\JsonTranslatable');
            $traits[] = 'use ' . $imports->add('Javaabu\\Translatable\\JsonTranslatable\\IsJsonTranslatable') . ';';
        }

        if ($resource->hasMedia()) {
            $implements[] = $imports->add('Javaabu\\Mediapicker\\Contracts\\HasAttachments');
            $traits[] = 'use ' . $imports->add('Javaabu\\Mediapicker\\Concerns\\InteractsWithAttachments') . ';';
        }

        $body = $this->blocks([
            $this->renderTranslatable(),
            $this->renderCasts($imports),
            $this->renderRelations($imports),
            $this->renderMedia($imports),
        ]);

        return Stub::make('model/Model.stub')->with([
            'namespace' => $namespace,
            'class' => $resource->model(),
            'implements' => $implements ? ' implements ' . implode(', ', $implements) : '',
            'traits' => implode("\n", array_slice($traits, 0, 2)) . (count($traits) > 2 ? "\n\n" . implode("\n", array_slice($traits, 2)) : ''),
            'fillable' => implode("\n", array_map(fn (string $column) => "'{$column}',", $this->fillable())),
            'body' => $body,
            'imports' => $imports->render(),
        ])->render();
    }

    /**
     * @return array<int, string>
     */
    public function fillable(): array
    {
        $fillable = [];

        /** @var Field $field */
        foreach ($this->resource->fields as $column => $field) {
            if ($field->isFillable()) {
                $fillable[] = $column;
            }
        }

        return $fillable;
    }

    protected function renderTranslatable(): ?string
    {
        if (! $this->resource->isTranslatable()) {
            return null;
        }

        $columns = implode(', ', array_map(fn (string $column) => "'{$column}'", $this->resource->translatable));

        return <<<PHP
        /**
         * The attributes that are translatable.
         *
         * @var array<int, string>
         */
        protected array \$translatable = [{$columns}];
        PHP;
    }

    protected function renderCasts(Imports $imports): ?string
    {
        $casts = [];

        foreach ($this->resource->fields as $column => $field) {
            if ($cast = $field->getCast($imports)) {
                $casts[] = "        '{$column}' => {$cast},";
            }
        }

        if (! $casts) {
            return null;
        }

        $casts = implode("\n", $casts);

        return <<<PHP
        /**
         * Get the attributes that should be cast.
         *
         * @return array<string, string>
         */
        protected function casts(): array
        {
            return [
        {$casts}
            ];
        }
        PHP;
    }

    protected function renderRelations(Imports $imports): ?string
    {
        $resource = $this->resource;

        /** @var array<int, Relation> $relations */
        $relations = [...$resource->belongs_to, ...$resource->has_many, ...$resource->belongs_to_many];

        $methods = [];
        $names = [];

        foreach ($relations as $relation) {
            if (in_array($relation->name, $names, true)) {
                continue;
            }

            // a foreign key left out with --columns has no relation
            if ($relation instanceof BelongsTo && ! ($resource->field($relation->foreign_key) instanceof ForeignKeyField)) {
                continue;
            }

            $names[] = $relation->name;

            $type = $imports->add("Illuminate\\Database\\Eloquent\\Relations\\{$relation->type()}");
            $related_class = $resource->namespace('models') . '\\' . $relation->relatedModel();
            $related = $imports->add($related_class);

            $definition = str_replace("{$relation->relatedModel()}::class", "{$related}::class", $relation->definition());
            $generic = "{$related}, \$this";

            $methods[] = <<<PHP
            /**
             * @return {$type}<{$generic}>
             */
            public function {$relation->name}(): {$type}
            {
                return \$this->{$definition};
            }
            PHP;
        }

        return $methods ? implode("\n\n", $methods) : null;
    }

    protected function renderMedia(Imports $imports): ?string
    {
        if (! $this->resource->hasMedia()) {
            return null;
        }

        $file_types = $imports->add('Javaabu\\Mediapicker\\Support\\FileTypes');
        $file = $imports->add('Spatie\\MediaLibrary\\MediaCollections\\File');

        $collections = [];

        foreach ($this->resource->media as $media) {
            $single = $media->multiple ? '' : "\n            ->singleFile()";

            $collections[] = <<<PHP
                \$this->addAttachmentCollection('{$media->name}'){$single}
                    ->acceptsFile(fn ({$file} \$file) => {$file_types}::isAllowed(\$file->mimeType, '{$media->type}'));
            PHP;
        }

        $collections = implode("\n\n", $collections);

        return <<<PHP
        /**
         * The media collections, picked from the media library.
         */
        public function registerAttachmentCollections(): void
        {
        {$collections}
        }
        PHP;
    }
}
