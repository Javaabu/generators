<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\Support\Imports;
use Javaabu\Generators\Support\Stub;

/**
 * A javaabu/exports v2 model export.
 */
class ExportGenerator extends Generator
{
    public function files(): array
    {
        $resource = $this->resource;

        return [new GeneratedFile($resource->classPath($resource->exportClass()), $this->renderExport(), description: 'export')];
    }

    public function renderExport(): string
    {
        $resource = $this->resource;
        $namespace = $resource->namespace('exports');
        $imports = new Imports(['Javaabu\\Exports\\ModelExport'], $namespace);

        $model = $imports->add($resource->modelClass());

        return Stub::make('export/Export.stub')->with([
            'namespace' => $namespace,
            'class' => class_basename($resource->exportClass()),
            'model' => $model,
            'imports' => $imports->render(),
        ])->render();
    }
}
