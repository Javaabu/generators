<?php

namespace Javaabu\Generators\Generators\Auth;

use Javaabu\Generators\Generators\GeneratedFile;
use Javaabu\Generators\Generators\Generator;
use Javaabu\Generators\Support\PhpFile;

/**
 * The user type's session guard, user provider and password broker in
 * config/auth.php. Every broker shares the password_reset_tokens table.
 */
class AuthConfigGenerator extends Generator
{
    public function files(): array
    {
        return [new GeneratedFile(config_path('auth.php'), $this->renderConfig(), fn (string $code) => $this->mergeInto($code), 'auth config')];
    }

    protected function types(): UserTypeGenerator
    {
        return new UserTypeGenerator($this->resource);
    }

    public function mergeInto(string $code): string
    {
        $types = $this->types();
        $provider = $this->resource->table();

        $code = PhpFile::addImport($code, $this->resource->modelClass());
        $code = PhpFile::addArrayEntry($code, 'guards', $types->guard(), $this->guardEntry());
        $code = PhpFile::addArrayEntry($code, 'providers', $provider, $this->providerEntry());

        return PhpFile::addArrayEntry($code, 'passwords', $types->broker(), $this->brokerEntry());
    }

    public function renderConfig(): string
    {
        return "// 'guards'\n{$this->guardEntry()}\n\n// 'providers'\n{$this->providerEntry()}\n\n// 'passwords'\n{$this->brokerEntry()}";
    }

    protected function guardEntry(): string
    {
        return "'{$this->types()->guard()}' => [\n    'driver' => 'session',\n    'provider' => '{$this->resource->table()}',\n],";
    }

    protected function providerEntry(): string
    {
        return "'{$this->resource->table()}' => [\n    'driver' => 'eloquent',\n    'model' => {$this->resource->model()}::class,\n],";
    }

    protected function brokerEntry(): string
    {
        return "'{$this->types()->broker()}' => [\n    'provider' => '{$this->resource->table()}',\n    'table' => 'password_reset_tokens',\n    'expire' => 60,\n    'throttle' => 60,\n],";
    }
}
