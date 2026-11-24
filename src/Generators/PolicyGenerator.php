<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

class PolicyGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->policyClass()), $this->renderPolicy(), description: 'policy')];
    }

    public function renderPolicy(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('policies');
        $imports = new Imports(['Javaabu\\Permissions\\Policies\\ResourcePolicy'], $namespace);

        $actions = ['view', 'edit', 'delete', 'force_delete'];

        $guard = $resource->guard()
            ? "\n/**\n * Check the permissions of this guard.\n */\nprotected ?string \$guard = '{$resource->guard()}';"
            : '';

        return Stub::make('policy/Policy.stub')->names($resource->table())->with([
            'namespace' => $namespace,
            'class' => class_basename($resource->policyClass()),
            'resource' => $resource->permissionResource(),
            'permissions' => implode(', ', array_map(fn (string $action) => $resource->permission($action), $actions)),
            'guard' => $guard,
            'imports' => $imports->render(),
        ])->render();
    }
}
