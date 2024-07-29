<?php

namespace Javaabu\Generators\Generators\Concerns;

use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\BooleanField;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\GeneratorHelpers\StringCaser;

trait GeneratesController
{

    public function getControllerName(): string
    {
        return StringCaser::pluralStudly($this->getTable()) . 'Controller';
    }

    public function getControllerStub(): string
    {
        return 'generators::Controllers/Model' . ($this->hasSoftDeletes() ? 'SoftDeletes' : '' )  . 'Controller.stub';
    }

    protected function getControllerFillStoreIndentation(): int
    {
        return 2;
    }

    protected function getControllerFillUpdateIndentation(): int
    {
        return 2;
    }

    protected function getAdditionalControllerEagerLoads(): array
    {
        return [];
    }

    protected function getAdditionalControllerOrderbys(): array
    {
        return [];
    }

    /**
     * Render the controller
     */
    public function renderController(): string
    {
        $stub = $this->getControllerStub();

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        $orderbys = '';
        $booleans = [];
        $foreign_keys = [];
        $eager_loads = $this->getAdditionalControllerEagerLoads();

        $order_columns = array_merge([
            $this->getKeyName(),
        ], $this->getAdditionalControllerOrderbys());

        if ($this->hasTimestamps()) {
            $order_columns[] = 'created_at';
            $order_columns[] = 'updated_at';
        }

        foreach ($order_columns as $column) {
            $orderbys .= $this->renderControllerOrderBy($column);
        }

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            if ($field->isSortable()) {
                $orderbys .= $this->renderControllerOrderBy($column);
            }

            if ($field instanceof BooleanField) {
                $booleans[] = $this->renderControllerBoolean($column, $field);
            } elseif ($field instanceof ForeignKeyField) {
                $foreign_keys[] = $this->renderControllerForeignKey($column, $field);
                $eager_loads[] = "'" . $field->getRelationName() . "'";
            }
        }

        $default_indent = 2;
        $store_indent = $this->getControllerFillStoreIndentation();
        $update_indent = $this->getControllerFillUpdateIndentation();

        $template = $renderer->appendMultipleContent([
            [
                'search' => "static::\$orderbys = [\n",
                'keep_search' => true,
                'content' => $orderbys,
            ],
            [
                'search' => '{{keyName}}',
                'keep_search' => false,
                'content' => $this->getKeyName(),
            ],
            [
                'search' => '{{requestFillable}}',
                'keep_search' => false,
                'content' => $this->hasAnyFillable() ? '$request->validated()' : '',
            ],
            [
                'search' => $renderer->addIndentation("// eager loads\n", 2),
                'keep_search' => false,
                'content' => $eager_loads ? $this->renderControllerEagerLoads($eager_loads) . "\n" : "\n",
            ],
            [
                'search' => $renderer->addIndentation("// fill update\n", $update_indent),
                'keep_search' => false,
                'content' => $this->hasAnyFillable() ? $renderer->addIndentation($this->renderControllerUpdateFill(), $update_indent - $default_indent) : "\n",
            ],
            [
                'search' => $renderer->addIndentation("// booleans\n", $store_indent),
                'keep_search' => false,
                'content' => $booleans ? "\n" . $renderer->addIndentation(implode("\n", $booleans), $store_indent - $default_indent) : '',
            ],
            [
                'search' => $renderer->addIndentation("// foreign keys\n", $store_indent),
                'keep_search' => false,
                'content' => $foreign_keys ? "\n" . $renderer->addIndentation(implode("\n", $foreign_keys), $store_indent - $default_indent) : '',
            ],
            [
                'search' => $renderer->addIndentation("// booleans update\n", $update_indent),
                'keep_search' => false,
                'content' => $booleans ? "\n" . $renderer->addIndentation(implode("\n", $booleans), $update_indent - $default_indent) : '',
            ],
            [
                'search' => $renderer->addIndentation("// foreign keys update\n", $update_indent),
                'keep_search' => false,
                'content' => $foreign_keys ? "\n" . $renderer->addIndentation(implode("\n", $foreign_keys), $update_indent - $default_indent) : '',
            ],
            [
                'search' => '{{appendSpaces}}',
                'keep_search' => false,
                'content' => Str::repeat(' ', (Str::length(StringCaser::pluralSnake($this->getTable())) * 2) + 2),
            ],
        ], $template);

        return $template;
    }

    /**
     * Render fill update
     */
    public function renderControllerUpdateFill(): string
    {
        $stub = 'generators::Controllers/_controllerUpdateFill.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    /**
     * Render eager loads
     */
    public function renderControllerEagerLoads(array $relations): string
    {
        if (! $relations) {
            return '';
        }

        $stub = 'generators::Controllers/_controllerEagerLoads.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        $template = $renderer->appendMultipleContent([
            [
                'search' => '->with(',
                'keep_search' => true,
                'content' => implode(', ', $relations),
            ],
        ], $template);

        return $template;
    }

    /**
     * Render a boolean
     */
    public function renderControllerBoolean(string $column, BooleanField $field): string
    {
        $stub = 'generators::Controllers/_controllerBoolean.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        $template = $renderer->appendMultipleContent([
            [
                'search' => '{{inputName}}',
                'keep_search' => false,
                'content' => $field->getInputName(),
            ],
            [
                'search' => '{{column}}',
                'keep_search' => false,
                'content' => $column,
            ],
        ], $template);

        return $template;
    }

    /**
     * Render a foreign key
     */
    public function renderControllerForeignKey(string $column, ForeignKeyField $field): string
    {
        $stub = 'generators::Controllers/_controllerForeignKey.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $field->getRelatedTable());
        $template = $renderer->replaceNames($this->getTable(), $template, 'Model');

        $template = $renderer->appendMultipleContent([
            [
                'search' => '{{attribute}}',
                'keep_search' => false,
                'content' => $field->getInputName(),
            ],
            [
                'search' => '{{relationName}}',
                'keep_search' => false,
                'content' => $field->getRelationName(),
            ],
        ], $template);

        return $template;
    }

    /**
     * Render a foreign key
     */
    public function renderControllerOrderBy(string $column): string
    {
        $stub = 'generators::Controllers/_orderBy.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $column);
    }
}
