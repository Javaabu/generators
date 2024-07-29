<?php

namespace Javaabu\Generators\Testing;

trait InteractsWithTestStubs
{
    protected string $test_stub_path = __DIR__ . '/stubs';

    protected function loadTestStubsFrom(string $path)
    {
        $this->test_stub_path = $path;
    }

    protected function getTestStubContents(string $stub): string
    {
        return file_get_contents($this->getTestStubPath($stub));
    }

    protected function getGeneratedFileContents(string $file): string
    {
        return file_get_contents($file);
    }

    protected function getTestStubPath(string $name): string
    {
        return rtrim($this->test_stub_path, '/') . '/' . $name;
    }
}
