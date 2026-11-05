<?php

namespace Javaabu\Generators\Workbench;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Javaabu\Generators\GeneratorsServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Generates the integration app's code, the way a developer would. The
 * generated tests then run with workbench/phpunit.xml.
 */
class GenerateAppTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        // the root package isn't discovered: it's this repository
        return [...parent::getPackageProviders($app), GeneratorsServiceProvider::class];
    }

    /**
     * Each command, as a developer runs it.
     *
     * @return array<int, array{0: string, 1: array<string, mixed>}>
     */
    public static function commands(): array
    {
        return [
            // Blade, plain
            ['generate:all', ['table' => 'categories']],
            ['generate:all', ['table' => 'tags']],
            // Inertia React
            ['generate:all', ['table' => 'brands', '--views' => 'inertia']],
            // Blade: soft deletes, enums, every column type, a many-to-many relation
            ['generate:all', ['table' => 'products']],
            // Inertia React, nested under products
            ['generate:all', ['table' => 'reviews', '--parent' => 'products', '--views' => 'inertia']],
            // Media collections (javaabu/mediapicker), Blade and Inertia React
            ['generate:all', ['table' => 'posts', '--media' => 'featured_image:image,documents:document:multiple']],
            // Translatable when javaabu/translatable is installed (the translations and lang columns)
            ['generate:all', ['table' => 'articles']],
        ];
    }

    #[Test]
    public function it_generates_the_app(): void
    {
        foreach (static::commands() as [$command, $arguments]) {
            $exit = Artisan::call($command, [...$arguments, '--create' => true, '--force' => true]);

            fwrite(STDOUT, "\n> {$command} " . json_encode($arguments, JSON_UNESCAPED_SLASHES) . "\n" . Artisan::output());

            $this->assertEquals(0, $exit, "{$command} failed");
        }
    }
}
