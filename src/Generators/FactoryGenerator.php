<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

class FactoryGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->factoryClass()), $this->renderFactory(), description: 'factory')];
    }

    public function renderFactory(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('factories');
        $imports = new Imports(['Illuminate\\Database\\Eloquent\\Factories\\Factory'], $namespace);

        $model = $imports->add($resource->modelClass());

        $definition = [];

        foreach ($resource->fields as $column => $field) {
            $definition[] = "'{$column}' => {$field->getFactoryValue($imports)},";
        }

        return Stub::make('factory/Factory.stub')->with([
            'namespace' => $namespace,
            'class' => class_basename($resource->factoryClass()),
            'model' => $model,
            'definition' => implode("\n", $definition),
            'imports' => $imports->render(),
        ])->render();
    }
}
