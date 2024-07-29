<?php

namespace Javaabu\Generators\Concerns;

use Illuminate\Filesystem\Filesystem;
use Javaabu\Generators\Support\StubRenderer;

trait GeneratesFiles
{
    protected Filesystem $files;
    protected StubRenderer $renderer;

    public function getFilesystem(): Filesystem
    {
        if (! isset($this->files)) {
            $this->files = app(Filesystem::class);
        }

        return $this->files;
    }

    public function getRenderer(): StubRenderer
    {
        if (! isset($this->renderer)) {
            $this->renderer = new StubRenderer($this->getFilesystem());
        }

        return $this->renderer;
    }

    protected function getFullFilePath(string $path, string $file_name): string
    {
        return rtrim($path, '/') . '/' . $file_name;
    }

    protected function getPath(string $default, string $path = ''): string
    {
        if (! $path) {
            return $default;
        }

        return base_path($path);
    }

    protected function appendContent(string $file_path, array $contents, string $stub = ''): bool
    {
        $template = '';

        if ($this->alreadyExists($file_path)) {
            $template = $this->getRenderer()->getFileContents($file_path);
        } elseif ($stub) {
            $template = $this->getRenderer()->loadStub($stub);
        }

        $template = $this->getRenderer()->appendMultipleContent($contents, $template, skip_existing: true);

        return $this->putContent($file_path, $template, true);
    }

    protected function putContent(string $file_path, string $content, bool $force = false): bool
    {
        if ($this->alreadyExists($file_path) && ! $force) {
            $this->error($file_path . ' already exists!');

            return false;
        }

        $this->makeDirectory($file_path);

        $this->getFilesystem()->put($file_path, $content);

        return true;
    }

    protected function alreadyExists(string $path): bool
    {
        return $this->getFilesystem()->exists($path);
    }

    protected function makeDirectory(string $path)
    {
        if (! $this->getFilesystem()->isDirectory(dirname($path))) {
            $this->getFilesystem()->makeDirectory(dirname($path), 0777, true, true);
        }
    }
}
