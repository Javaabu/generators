<?php

namespace Javaabu\Generators\Generators;

use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\BooleanField;
use Javaabu\Generators\FieldTypes\EnumField;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\FieldTypes\JsonField;
use Javaabu\Generators\Support\StringCaser;

class TestGenerator extends BaseGenerator
{
    /**
     * Render the controller
     */
    public function render(): string
    {
        $stub = 'generators::tests/Model' . ($this->hasSoftDeletes() ? 'SoftDeletes' : '' )  . 'ControllerTest.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        $use_statements = [
            'use App\\Models\\' . $this->getModelClass() . ';'
        ];

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            if ($field instanceof ForeignKeyField) {
                $import = 'use App\\Models\\' . $field->getRelatedModelClass() . ';';

                if (! in_array($import, $use_statements)) {
                    $use_statements[] = $import;
                }
            }

            if ($field instanceof EnumField && $field->hasEnumClass()) {
                $enum_import = 'use ' . $field->getEnumClass() . ';';
                if (! in_array($enum_import, $use_statements)) {
                    $use_statements[] = $enum_import;
                }
            }
        }

        $template = $renderer->appendMultipleContent([
            [
                'search' => "// use statements\n",
                'keep_search' => false,
                'content' => $use_statements ? implode("\n", $use_statements) . "\n" : '',
            ],
            [
                'search' => $renderer->addIndentation("// errors\n", 4),
                'keep_search' => false,
                'content' => $this->renderErrors(),
            ],
            [
                'search' => $renderer->addIndentation("// factory inputs\n", 3),
                'keep_search' => false,
                'content' => $this->renderFactoryInputs(),
            ],
            [
                'search' => $renderer->addIndentation("// factory db values\n", 3),
                'keep_search' => false,
                'content' => $this->renderFactoryDbValues(),
            ],
            [
                'search' => $renderer->addIndentation("// correct inputs\n", 3),
                'keep_search' => false,
                'content' => $this->renderCorrectInputs(),
            ],
            [
                'search' => $renderer->addIndentation("// new foreign key models\n", 2),
                'keep_search' => false,
                'content' => $this->renderForeignKeyModels('new_'),
            ],
            [
                'search' => $renderer->addIndentation("// old foreign key models\n", 2),
                'keep_search' => false,
                'content' => $this->renderForeignKeyModels('old_'),
            ],
            [
                'search' => $renderer->addIndentation("// correct db values\n", 3),
                'keep_search' => false,
                'content' => $this->renderCorrectDbValues(),
            ],
            [
                'search' => $renderer->addIndentation("// correct factory values\n", 3),
                'keep_search' => false,
                'content' => $this->renderCorrectFactoryValues(),
            ],
            [
                'search' => $renderer->addIndentation("// different correct inputs\n", 3),
                'keep_search' => false,
                'content' => $this->renderDifferentCorrectInputs(),
            ],
            [
                'search' => $renderer->addIndentation("// different correct db values\n", 3),
                'keep_search' => false,
                'content' => $this->renderDifferentCorrectDbValues(),
            ],
            [
                'search' => $renderer->addIndentation("// wrong inputs\n", 3),
                'keep_search' => false,
                'content' => $this->renderWrongInputs(),
            ],
            [
                'search' => $renderer->addIndentation("// wrong db values\n", 3),
                'keep_search' => false,
                'content' => $this->renderWrongDbValues(),
            ],
            [
                'search' => '{{keyName}}',
                'keep_search' => false,
                'content' => $this->getKeyName(),
            ],
            [
                'search' => '{{tableName}}',
                'keep_search' => false,
                'content' => $this->getTable(),
            ],
            [
                'search' => '{{nameField}}',
                'keep_search' => false,
                'content' => $this->getNameField(),
            ],
        ], $template);

        return $template;
    }

    public function renderForeignKeyModels($prefix = ''): string
    {
        $models = '';
        $fields = $this->getFields();
        $renderer = $this->getRenderer();

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($fields as $column => $field) {
            if ($field instanceof ForeignKeyField) {
                $models .= $renderer->addIndentation($field->generateTestFactoryStatement($prefix) . "\n", 2);
            }
        }

        return $models ? $models . "\n" : '';
    }

    public function renderErrors(): string
    {
        $errors = '';
        $fields = $this->getFields();
        $renderer = $this->getRenderer();

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($fields as $column => $field) {
            $errors .= $renderer->addIndentation("'" . $field->getInputName() . "',\n", 4);
        }

        return $errors;
    }

    protected function renderValues(string $key_callback, string $value_callback, int $tabs = 3, string $value_prefix = ''): string
    {
        $values = '';
        $fields = $this->getFields();
        $renderer = $this->getRenderer();

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($fields as $column => $field) {
            $value = $field->{$value_callback}();
            $key = $field->{$key_callback}();

            $values .= $renderer->addIndentation("'$key' => $value_prefix$value,\n", $tabs);
        }

        return $values;
    }

    public function renderWrongInputs(): string
    {
        return $this->renderValues('getInputName', 'generateWrongValue');
    }

    public function renderWrongDbValues(): string
    {
        return $this->renderValues('getName', 'generateWrongDbValue');
    }

    public function renderCorrectInputs(): string
    {
        return $this->renderValues('getInputName', 'generateCorrectValue');
    }

    public function renderCorrectDbValues(): string
    {
        return $this->renderValues('getName', 'generateCorrectDbValue');
    }

    public function renderCorrectFactoryValues(): string
    {
        return $this->renderValues('getName', 'generateCorrectValue');
    }

    public function renderDifferentCorrectInputs(): string
    {
        return $this->renderValues('getInputName', 'generateDifferentCorrectValue');
    }

    public function renderDifferentCorrectDbValues(): string
    {
        return $this->renderValues('getName', 'generateDifferentCorrectDbValue');
    }

    public function renderFactoryInputs(): string
    {
        return $this->renderValues('getInputName', 'generateFactoryInput', value_prefix: '$' . $this->getMorph() . '->');
    }

    public function renderFactoryDbValues(): string
    {
        $values = '';
        $value_callback = 'generateFactoryDbValue';
        $key_callback = 'getName';
        $fields = $this->getFields();
        $renderer = $this->getRenderer();
        $value_prefix = '$' . $this->getMorph() . '->';
        $tabs = 3;

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($fields as $column => $field) {
            $value = $field->{$value_callback}();
            $key = $field->{$key_callback}();

            $generated_value = $value_prefix . $value;

            $generated_value = $field->formatFactoryDbValue($generated_value);

            $values .= $renderer->addIndentation("'$key' => $generated_value,\n", $tabs);
        }

        return $values;
    }

}
