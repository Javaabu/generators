<?php

namespace Javaabu\Generators\Testing;

use Illuminate\Filesystem\Filesystem;

trait InteractsWithTestFiles
{
    protected function makeDirectory(string $path)
    {
        /** @var Filesystem $files */
        $files = $this->app->make(Filesystem::class);

        if (! $files->isDirectory(dirname($path))) {
            $files->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    /**
     * Clear directory
     */
    protected function deleteDirectory(string $path)
    {
        /** @var Filesystem $files */
        $files = $this->app->make(Filesystem::class);
        $files->deleteDirectory($path);
    }

    /**
     * Delete files
     */
    protected function deleteFile(string $path)
    {
        /** @var Filesystem $files */
        $files = $this->app->make(Filesystem::class);
        $files->delete($path);
    }

    /**
     * Clear directory
     */
    protected function copyFile(string $from, string $to)
    {
        /** @var Filesystem $files */
        $files = $this->app->make(Filesystem::class);
        $files->copy($from, $to);
    }
}
