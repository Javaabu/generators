<?php

namespace Javaabu\Generators\Generators\Auth;

use Faker\Generator;
use Illuminate\Support\Str;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\Generators\BaseGenerator;
use Javaabu\Generators\Generators\Concerns\GeneratesFactory;
use Javaabu\GeneratorHelpers\StringCaser;

class AuthConfigGenerator extends BaseAuthGenerator
{
    public function stubsToRender(): array
    {
        $stubs = [
            'renderGuards' => 'guards',
            'renderProviders' => 'providers',
            'renderPasswords' => 'passwords',
            'renderPassportGuards' => 'passport-guards',
        ];

        if ($this->shouldRenderDefaultGuard()) {
            $stubs['renderDefaultGuard'] = 'default-guard';
            $stubs['renderDefaultPasswords'] = 'default-passwords';
        }

        return $stubs;
    }


    /**
     * Render the views
     */
    public function render(): string
    {

        $output = '';

        $views = $this->stubsToRender();

        foreach ($views as $method => $file_name) {
            $output .= "// $file_name\n";
            $output .= $this->{$method}();
        }

        return $output;
    }

    public function renderGuards(): string
    {
        $stub = 'generators::config/auth/_guards.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderProviders(): string
    {
        $stub = 'generators::config/auth/_providers.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderPasswords(): string
    {
        $stub = 'generators::config/auth/_passwords.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderPassportGuards(): string
    {
        $stub = 'generators::config/auth/_passport-guards.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderDefaultGuard(): string
    {
        $stub = 'generators::config/auth/_default-guard.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function renderDefaultPasswords(): string
    {
        $stub = 'generators::config/auth/_default-passwords.stub';

        $renderer = $this->getRenderer();

        return $renderer->replaceStubNames($stub, $this->getTable());
    }

    public function shouldRenderDefaultGuard(): bool
    {
        $default_guard = config('auth.defaults.guard');

        return is_null($default_guard) || in_array($default_guard, ['web', 'web_admin']);
    }
}
