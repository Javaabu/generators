<?php

namespace Javaabu\Generators\Generators;

use Faker\Generator;
use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\EnumField;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\Support\StringCaser;

class RequestGenerator extends BaseGenerator
{
    /**
     * get the validation rules
     */
    public function getValidationRules(string $column): array
    {
        $field = $this->getField($column);

        if (! $field) {
            return [];
        }

        $rules = [];

        if ($field->isNullable()) {
            $rules[] = 'nullable';
        }

        return array_merge($rules, $field->generateValidationRules());
    }

    /**
     * Render the request
     */
    public function render(): string
    {
        $stub = 'generators::Requests/ModelRequest.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());
        $singular_snake = StringCaser::singularSnake($this->getTable());

        $use_statements = [];
        $rules = '';
        $unique_definitions = '';
        $unique_ignores = '';
        $unique_insertions = '';
        $requireds = '';

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            $input_name = $field->getInputName();

            if ($field->isUnique()) {
                $unique_definitions_statement = '$unique_' . $input_name . " = Rule::unique('{$this->getTable()}', '$input_name');\n";
                $unique_ignore_statement =  '$unique_' . $input_name . "->ignore($" . $singular_snake . "->getKey());\n";
                $unique_insertion_statement = '$rules[\'' . $input_name . "'][] = ".'$unique_' . $input_name.";\n";

                if ($unique_definitions) {
                    $unique_definitions .= $renderer->addIndentation($unique_definitions_statement, 2);
                    $unique_ignores .= $renderer->addIndentation($unique_ignore_statement, 3);
                    $unique_insertions .= $renderer->addIndentation($unique_insertion_statement, 2);
                } else {
                    $unique_definitions .= $unique_definitions_statement;
                    $unique_ignores .= $unique_ignore_statement;
                    $unique_insertions .= $unique_insertion_statement;
                }
            }

            if ($field->isRequired()) {
                $required_statement = '$rules[\'' . $input_name . "'][] = 'required';\n";

                if ($requireds) {
                    $requireds .= $renderer->addIndentation($required_statement, 3);
                } else {
                    $requireds .= $required_statement;
                }
            }

            $field_rules = collect($this->getValidationRules($column))
                ->transform(function ($value) {
                    return Str::startsWith($value, 'Rule::') ? $value : "'" . $value. "'";
                })->implode(', ');

            if (Str::contains($field_rules, 'Rule::') || $field->isUnique()) {
                $rule_import = 'use Illuminate\\Validation\\Rule;';
                if (! in_array($rule_import, $use_statements)) {
                    $use_statements[] = $rule_import;
                }
            }

            if ($field instanceof EnumField && $field->hasEnumClass()) {
                $enum_import = 'use ' . $field->getEnumClass() . ';';
                if (! in_array($enum_import, $use_statements)) {
                    $use_statements[] = $enum_import;
                }
            }

            $statement = "'$input_name' => [$field_rules],\n";

            if ($rules) {
                $rules .= $renderer->addIndentation($statement, 3);
            } else {
                $rules = $statement;
            }
        }

        $unique_ignores_search = "// unique ignores\n";
        $unique_definitions_search = "// unique definitions\n";
        $unique_insertions_search = "// unique rule insertions\n";
        $requireds_search = "// requireds\n";


        $template = $renderer->appendMultipleContent([
            [
                'search' => "use Illuminate\\Foundation\\Http\\FormRequest;\n",
                'keep_search' => true,
                'content' => $use_statements ? implode("\n", $use_statements) . "\n" : '',
            ],
            [
                'search' => "// rules\n",
                'keep_search' => false,
                'content' => $rules,
            ],
            [
                'search' => $unique_definitions ? $unique_definitions_search : $renderer->addIndentation($unique_definitions_search, 2),
                'keep_search' => false,
                'content' => $unique_definitions ? $unique_definitions . "\n" : '',
            ],
            [
                'search' => $unique_ignores ? $unique_ignores_search : $renderer->addIndentation($unique_ignores_search, 1),
                'keep_search' => false,
                'content' => $unique_ignores ? $unique_ignores : $renderer->addIndentation('//' . "\n", 1),
            ],
            [
                'search' => $requireds ? $requireds_search : $renderer->addIndentation($requireds_search, 1),
                'keep_search' => false,
                'content' => $requireds,
            ],
            [
                'search' => $unique_insertions ? $unique_insertions_search : $renderer->addIndentation($unique_insertions_search, 2),
                'keep_search' => false,
                'content' => $unique_insertions ? $unique_insertions . "\n" : '',
            ],
        ], $template);

        return $template;
    }
}
