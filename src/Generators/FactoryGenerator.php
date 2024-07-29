<?php

namespace Javaabu\Generators\Generators;

use Faker\Generator;
use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\Generators\Concerns\GeneratesFactory;
use Javaabu\GeneratorHelpers\StringCaser;

class FactoryGenerator extends BaseGenerator
{
    use GeneratesFactory;

    protected string $factory_stub = 'generators::factories/ModelFactory.stub';

    /**
     * Constructor
     */
    public function __construct(string $table, array $columns = [])
    {
        parent::__construct($table, $columns);

        $this->faker = app(Generator::class);
    }

    public function render(): string
    {
        return $this->renderFactory();
    }
}
