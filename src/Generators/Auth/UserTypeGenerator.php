<?php

namespace Javaabu\Generators\Generators\Auth;

use Javaabu\GeneratorHelpers\StringCaser;
use Javaabu\Generators\Generators\GeneratedFile;
use Javaabu\Generators\Generators\Generator;
use Javaabu\Generators\Support\PhpFile;

/**
 * Registers the javaabu/auth user type in the app's service provider: its
 * model, guard and password broker.
 */
class UserTypeGenerator extends Generator
{
    public function files(): array
    {
        $path = app_path('Providers/AppServiceProvider.php');

        return [new GeneratedFile($path, $this->renderRegistration(), function (string $code) {
            $code = PhpFile::addImport($code, 'Javaabu\\Auth\\UserTypes');
            $code = PhpFile::addImport($code, $this->resource->modelClass());

            return PhpFile::appendToMethod($code, 'boot', $this->renderRegistration());
        }, 'user type')];
    }

    public function type(): string
    {
        return (string) $this->resource->user_type;
    }

    public function guard(): string
    {
        return (string) ($this->resource->user_guard ?: $this->type());
    }

    public function broker(): string
    {
        return StringCaser::pluralSnake($this->type());
    }

    public function renderRegistration(): string
    {
        $resource = $this->resource;

        return "// The {$this->type()} user type: login, registration, password resets and account pages (javaabu/auth)\n"
            . "UserTypes::register('{$this->type()}', [\n"
            . "    'model' => {$resource->model()}::class,\n"
            . "    'guard' => '{$this->guard()}',\n"
            . "    'passwords' => '{$this->broker()}',\n"
            . ']);';
    }
}
