<?php

namespace Javaabu\Generators\Generators\Auth;

use Faker\Generator;
use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\Generators\BaseGenerator;
use Javaabu\Generators\Generators\Concerns\GeneratesFactory;
use Javaabu\GeneratorHelpers\StringCaser;

class AuthFactoryGenerator extends BaseAuthGenerator
{
    use GeneratesFactory;

    protected string $factory_stub = 'generators::factories/AuthFactory.stub';

    /**
     * Constructor
     */
    public function __construct(string $table, array $columns = [], string $auth_name = '')
    {
        parent::__construct($table, $columns, $auth_name);

        $this->faker = app(Generator::class);
    }

    public function render(): string
    {
        $template = $this->renderFactory();

        $renderer = $this->getRenderer();

        return $renderer->appendMultipleContent([
            [
                'search' => '{{password}}',
                'keep_search' => false,
                'content' => $this->getDefaultPassword(),
            ],
        ], $template);
    }
}
