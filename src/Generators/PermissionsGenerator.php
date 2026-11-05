<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\Support\PhpFile;

/**
 * Registers the resource's permissions with javaabu/permissions in the
 * app's service provider. php artisan permissions:sync writes them.
 */
class PermissionsGenerator extends Generator
{
    public function files(): array
    {
        $path = app_path('Providers/AppServiceProvider.php');

        return [new GeneratedFile($path, $this->renderRegistration(), function (string $code) {
            $code = PhpFile::addImport($code, 'Javaabu\\Permissions\\Permissions');

            return PhpFile::appendToMethod($code, 'boot', $this->renderRegistration());
        }, 'permissions')];
    }

    public function renderRegistration(): string
    {
        $resource = $this->resource;

        $label = str_replace("'", "\\'", $resource->pluralLabel());

        $statement = "Permissions::resource('{$resource->permissionResource()}', label: '{$label}')";

        if ($guard = $resource->guard()) {
            $statement .= "->guards(['{$guard}'])";
        }

        return $statement . ';';
    }
}
