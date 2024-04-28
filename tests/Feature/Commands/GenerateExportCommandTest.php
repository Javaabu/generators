<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateExportCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_generate_export_output(): void
    {
        $expected_content = $this->getTestStubContents('Exports/CategoriesExport.php');

        $this->artisan('generate:export', ['table' => 'categories'])
             ->expectsOutput($expected_content);
    }

    /** @test */
    public function it_can_generate_export_file(): void
    {
        $expected_path = $this->app->path('Exports/CategoriesExport.php');
        $expected_content = $this->getTestStubContents('Exports/CategoriesExport.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:export', ['table' => 'categories', '--create' => true])
             ->assertSuccessful();
    }
}
