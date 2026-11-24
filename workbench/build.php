<?php

/*
 * Builds the integration app in build/app: Testbench's Laravel skeleton,
 * with the workbench overlay (models, migrations, layout, Tests\TestCase)
 * on top. The generators then write into it, and its tests run.
 *
 *     php workbench/build.php
 */

$root = dirname(__DIR__);
$app = "{$root}/build/app";
$skeleton = "{$root}/vendor/orchestra/testbench-core/laravel";

if (! is_dir($skeleton)) {
    fwrite(STDERR, "Run composer install first.\n");

    exit(1);
}

$remove = function (string $path) use (&$remove): void {
    if (is_link($path) || is_file($path)) {
        unlink($path);

        return;
    }

    if (! is_dir($path)) {
        return;
    }

    foreach (scandir($path) as $item) {
        if ($item !== '.' && $item !== '..') {
            $remove("{$path}/{$item}");
        }
    }

    rmdir($path);
};

$copy = function (string $from, string $to) use (&$copy): void {
    if (is_file($from)) {
        @mkdir(dirname($to), 0777, true);
        copy($from, $to);

        return;
    }

    @mkdir($to, 0777, true);

    foreach (scandir($from) as $item) {
        if ($item === '.' || $item === '..' || $item === 'vendor') {
            continue;
        }

        $copy("{$from}/{$item}", "{$to}/{$item}");
    }
};

$remove($app);

$copy($skeleton, $app);

foreach (['app', 'database', 'resources', 'routes', 'tests', 'lang'] as $directory) {
    if (is_dir("{$root}/workbench/{$directory}")) {
        $copy("{$root}/workbench/{$directory}", "{$app}/{$directory}");
    }
}

// the app shares the package's vendor directory, so packages are discovered
symlink("{$root}/vendor", "{$app}/vendor");

@mkdir("{$app}/bootstrap/cache", 0777, true);
@mkdir("{$app}/storage/framework/views", 0777, true);
@mkdir("{$app}/storage/framework/cache", 0777, true);

foreach (glob("{$app}/bootstrap/cache/*.php") as $cached) {
    unlink($cached);
}

echo "Built {$app}\n";
