<?php

namespace Javaabu\Generators\Generators\Concerns;

use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\EnumField;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\GeneratorHelpers\StringCaser;

trait GeneratesRequest
{
    /**
     * Get the request rules body stub
     */
    protected function getRequestStub(): string
    {
        return property_exists($this, 'request_stub') ? $this->request_stub : '';
    }

    /**
     * Get the request rules body stub
     */
    protected function getRequestRulesBodyStub(): string
    {
        return 'generators::Requests/_rulesBody.stub';
    }

    /**
     * get the validation rules
     */
    public function getRequestValidationRules(string $column, bool $add_required = false, bool $add_unique = false): array
    {
        $field = $this->getField($column);

        if (! $field) {
            return [];
        }

        $rules = [];

        if ($add_required && $field->isRequired()) {
            $rules[] = 'required';
        }

        if ($field->isNullable()) {
            $rules[] = 'nullable';
        }

        if ($add_unique && $field->isUnique()) {
            $rules[] = $this->renderRequestColumnUniqueRule($column);
        }

        return array_merge($rules, $field->generateValidationRules());
    }

    public function renderRequestRulesBody(): string
    {
        $stub = $this->getRequestStub();

        $renderer = $this->getRenderer();

        $template = $renderer->loadStub($stub);

        $body_stub = $renderer->loadStub($this->getRequestRulesBodyStub());

        return  $renderer->appendMultipleContent([
            [
                'search' => $renderer->addIndentation("// rules body\n", 2),
                'keep_search' => false,
                'content' => $body_stub,
            ],
        ], $template);
    }

    /**
     * Whether to use inline unique rules
     */
    protected function useInlineUniqueRules(): bool
    {
        return false;
    }

    /**
     * Whether to use inline required rules
     */
    protected function useInlineRequiredRules(): bool
    {
        return false;
    }

    /**
     * Whether to render rules for only required columns
     */
    protected function renderOnlyRequiredColumnRules(): bool
    {
        return false;
    }

    public function renderRequestColumnUniqueRule(string $column): string
    {
        $input_name = $this->getField($column)->getInputName();

        return "Rule::unique('{$this->getTable()}', '$input_name')";
    }


    /**
     * Render the request
     */
    public function renderRequest(): string
    {
        $renderer = $this->getRenderer();

        $template = $renderer->replaceNames($this->getTable(), $this->renderRequestRulesBody());
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
            if ($this->renderOnlyRequiredColumnRules() && (! $field->isRequired())) {
                continue;
            }

            $input_name = $field->getInputName();

            if ($field->isRequired() && (! $this->useInlineRequiredRules())) {
                $required_statement = '$rules[\'' . $input_name . "'][] = 'required';\n";

                if ($requireds) {
                    $requireds .= $renderer->addIndentation($required_statement, 3);
                } else {
                    $requireds .= $required_statement;
                }
            }

            if ($field->isUnique() && (! $this->useInlineUniqueRules())) {
                $unique_rule = $this->renderRequestColumnUniqueRule($column);
                $unique_definitions_statement = '$unique_' . $input_name . " = $unique_rule;\n";
                $unique_ignore_statement = '$unique_' . $input_name . "->ignore($" . $singular_snake . "->getKey());\n";
                $unique_insertion_statement = '$rules[\'' . $input_name . "'][] = " . '$unique_' . $input_name . ";\n";

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

            $field_rules = collect($this->getRequestValidationRules($column, $this->useInlineRequiredRules(), $this->useInlineUniqueRules()))
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
                'search' => "// use statements\n",
                'keep_search' => false,
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
