<?php

namespace Javaabu\Generators\Generators\Concerns;

use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;

trait GeneratesExport
{
    public function getExportStub(): string
    {
        return 'generators::Exports/ModelExport.stub';
    }

    /**
     * Render the policy
     */
    public function renderExport(): string
    {
        $stub = $this->getExportStub();

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        $relations = [];

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            if ($field instanceof ForeignKeyField) {
                $relations[] = "'" . $field->getRelationName() . "'";
            }
        }

        $template = $renderer->appendMultipleContent([
            [
                'search' => $renderer->addIndentation("// relations\n", 1),
                'keep_search' => false,
                'content' => $relations ? $this->renderExportRelations($relations) : '',
            ],
        ], $template);

        return $template;
    }

    /**
     * Render relations
     */
    public function renderExportRelations(array $relations): string
    {
        if (! $relations) {
            return '';
        }

        $stub = 'generators::Exports/_exportRelations.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        foreach ($relations as $i => $relation) {
            $relations[$i] = $renderer->addIndentation($relation . ',', 3);
        }

        $template = $renderer->appendMultipleContent([
            [
                'search' => $renderer->addIndentation('// relations values', 3),
                'keep_search' => false,
                'content' => implode("\n", $relations),
            ],
        ], $template);

        return $template;
    }
}
