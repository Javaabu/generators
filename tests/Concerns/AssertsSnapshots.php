<?php

namespace Javaabu\Generators\Tests\Concerns;

/**
 * Compares generated code with a snapshot in tests/snapshots. Run with
 * GENERATORS_UPDATE_SNAPSHOTS=1 to write the snapshots, then review the diff.
 */
trait AssertsSnapshots
{
    protected function assertMatchesSnapshot(string $name, string $actual): void
    {
        $path = dirname(__DIR__) . '/snapshots/' . $name;

        if (getenv('GENERATORS_UPDATE_SNAPSHOTS')) {
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }

            file_put_contents($path, $actual);
        }

        if (! file_exists($path)) {
            $this->fail("The snapshot {$name} is missing: run the tests with GENERATORS_UPDATE_SNAPSHOTS=1.");
        }

        $this->assertEquals(file_get_contents($path), $actual, "The output differs from the snapshot {$name}.");
    }
}
