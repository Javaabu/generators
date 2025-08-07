<?php

namespace Javaabu\Generators\Generators\Concerns;

use Faker\Generator;
use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\GeneratorHelpers\StringCaser;

trait GeneratesFactory
{
    protected Generator $faker;

    /**
     * get the factory stub
     */
    protected function getFactoryStub(): string
    {
        return property_exists($this, 'factory_stub') ? $this->factory_stub : '';
    }

    /**
     * Determine fake method attribute
     */
    public function getFakerMethodFromColumnName(string $column): ?string
    {
        // don't want to use the name() method
        if ($column == 'name') {
            return null;
        }

        $method = Str::camel($column);

        try {
            if (method_exists($this->faker, $method) || $this->faker->getFormatter($method)) {
                return $method;
            }
        } catch (\InvalidArgumentException $e) {}

        return null;
    }

    /**
     * Determine fake method attribute
     */
    public function getFakerStatement(string $column, bool $use_faker_method = true, bool $required = false): ?string
    {
        $field = $this->getField($column);

        if (! $field) {
            return null;
        }

        $statement = 'fake()';

        if ($field->isNullable() && (! $required)) {
            $statement .= '->optional()';
        }

        if ($field->isUnique()) {
            $statement .= '->unique()';

            if ($field->isNullable() && (! $required)) {
                $statement .= '?';
            }
        }

        $faker_method = null;

        if ($use_faker_method) {
            $faker_method = $this->getFakerMethodFromColumnName($column);
        }

        if ($faker_method) {
            $statement .= '->' . $faker_method . '()';
        } else {
            $statement .= '->' . $field->generateFactoryStatement();
        }

        return $statement;
    }

    /**
     * Render the factory
     */
    public function renderFactory(): string
    {
        $stub = $this->getFactoryStub();

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $this->getTable());

        $definitions = '';
        $foreign_keys = [];
        $required_foreign_keys = [];
        $use_statements = [];

        /**
         * @var string $column
         * @var Field $field
         */
        foreach ($this->getFields() as $column => $field) {
            if ($field instanceof ForeignKeyField) {
                $foreign_keys[] = $this->renderForeignKey($column, $field);

                if ($field->isRequired()) {
                    $required_foreign_keys[$column] = $field;
                }

            } else {
                $statement = "'$column' => {$this->getFakerStatement($column)},\n";

                if (Str::contains($statement, 'Str::')) {
                    $str_import = 'use Illuminate\\Support\\Str;';
                    if (! in_array($str_import, $use_statements)) {
                        $use_statements[] = $str_import;
                    }
                }

                if ($definitions) {
                    $definitions .= $renderer->addIndentation($statement, 3);
                } else {
                    $definitions = $statement;
                }
            }
        }

        $foreign_keys = implode("\n", $foreign_keys);

        $foreign_key_search = "// foreign keys\n";
        $required_relations = $this->renderRequiredRelations($required_foreign_keys);

        $template = $renderer->appendMultipleContent([
            [
                'search' => "use Illuminate\\Database\\Eloquent\\Factories\\Factory;\n",
                'keep_search' => true,
                'content' => $use_statements ? implode("\n", $use_statements) . "\n" : '',
            ],
            [
                'search' => "// definition\n",
                'keep_search' => false,
                'content' => $definitions,
            ],
            [
                'search' => "// foreign keys\n",
                'keep_search' => true,
                'content' => $required_relations ? "\n" . $required_relations : '',
            ],
            [
                'search' => $foreign_keys ? $foreign_key_search : $renderer->addIndentation($foreign_key_search, 1),
                'keep_search' => false,
                'content' => Str::replaceFirst('    ', '', $foreign_keys),
            ]
        ], $template);

        return $template;
    }

    public function getFactoryName(): string
    {
        return StringCaser::singularStudly($this->getTable()) . 'Factory';
    }

    /**
     * Render a foreign key
     */
    public function renderForeignKey(string $column, ForeignKeyField $field): string
    {
        $stub = 'generators::factories/_factoryForeignKey.stub';

        $renderer = $this->getRenderer();

        $template = $renderer->replaceStubNames($stub, $field->getRelatedTable());
        $statement = "'$column' => {$this->getFakerStatement($column, use_faker_method: false, required: true)},\n";

        return $renderer->appendMultipleContent([
            [
                'search' => "// statement\n",
                'keep_search' => false,
                'content' => $statement,
            ],
            [
                'search' => "{{factoryName}}",
                'keep_search' => false,
                'content' => $this->getFactoryName(),
            ],
            [
                'search' => "{{relationName}}",
                'keep_search' => false,
                'content' => Str::ucfirst($field->getRelationName()),
            ]
        ], $template);
    }

    /**
     * Render a foreign key
     */
    public function renderRequiredRelations(array $required_relations): string
    {
        if (! $required_relations) {
            return '';
        }

        $stub = 'generators::factories/_factoryRequiredRelations.stub';
        $renderer = $this->getRenderer();

        $code = '';

        $i = 0;
        $count = count($required_relations);

        /* @var ForeignKeyField $field */
        foreach ($required_relations as $column => $field) {
            $is_first = $i == 0;
            $is_last = $i == ($count - 1);

            $new_line = ! $is_last ? "\n" : '';
            $statement = '->with' . StringCaser::singularStudly($field->getRelatedTable()) . "()$new_line";

            $code .= ! $is_first ? $renderer->addIndentation($statement, 2, match_length: 'return $this') : $statement;

            $i++;
        }



        $template = $renderer->loadStub($stub);

        return $renderer->appendMultipleContent([
            [
                'search' => "\$this",
                'keep_search' => true,
                'content' => $code,
            ],
            [
                'search' => "{{factoryName}}",
                'keep_search' => false,
                'content' => $this->getFactoryName(),
            ]
        ], $template);
    }
}
