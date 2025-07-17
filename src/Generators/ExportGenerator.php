<?php

namespace Javaabu\Generators\Generators;

use Javaabu\Generators\Generators\Concerns\GeneratesExport;

class ExportGenerator extends BaseGenerator
{
    use GeneratesExport;

    public function render(): string
    {
        return $this->renderExport();
    }
}
