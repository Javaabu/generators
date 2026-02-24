<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class GenerateViewsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // delete all views
        $this->deleteDirectory($this->app->resourcePath('views/admin'));

        // setup skeleton menu
        $this->makeDirectory($this->app->path('Menus/AdminSidebar.php'));

        $this->copyFile(
            $this->getTestStubPath('Menus/AdminSidebar.php'),
            $this->app->path('Menus/AdminSidebar.php')
        );
    }

    protected function tearDown(): void
    {
        // delete all views
        $this->deleteDirectory($this->app->resourcePath('views/admin'));

        // delete menus
        $this->deleteDirectory($this->app->path('Menus'));

        parent::tearDown();
    }

    public function test_it_can_generate_views_output(): void
    {
        $expected_content = '';

        $expected_content .= "// products.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/products.blade.php');

        $expected_content .= "// _actions.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/_actions.blade.php');

        $expected_content .= "// _form.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/_form.blade.php');

        $expected_content .= "// create.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/create.blade.php');

        $expected_content .= "// edit.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/edit.blade.php');

        $expected_content .= "// _details.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/_details.blade.php');

        $expected_content .= "// show.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/show.blade.php');

        $expected_content .= "// _bulk.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/_bulk.blade.php');

        $expected_content .= "// _list.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/_list.blade.php');

        $expected_content .= "// _table.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/_table.blade.php');

        $expected_content .= "// _filter.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/_filter.blade.php');

        $expected_content .= "// index.blade.php\n";
        $expected_content .= $this->getTestStubContents('views/products/index.blade.php');

        $this->artisan('generate:views', ['table' => 'products'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_new_view_files(): void
    {
        $views = [
            'products.blade.php',
            '_actions.blade.php',
            '_form.blade.php',
            'create.blade.php',
            'edit.blade.php',
            '_details.blade.php',
            'show.blade.php',
            '_bulk.blade.php',
            '_list.blade.php',
            '_table.blade.php',
            'index.blade.php',
        ];

        $content = [];

        // load expected path and content
        foreach ($views as $view) {
            $content[$view] = [
                'expected_path' => $this->app->resourcePath('views/admin/products/' . $view),
                'expected_content' => $this->getTestStubContents('views/products/' . $view),
            ];
        }

        $this->artisan('generate:views', ['table' => 'products', '--create' => true])
            ->assertSuccessful();

        foreach ($content as $view => $test_data) {
            $this->assertFileExists($test_data['expected_path'], "$view not generated");

            $actual_content = $this->getGeneratedFileContents($test_data['expected_path']);
            $this->assertEquals($test_data['expected_content'], $actual_content, "$view content wrong");
        }
    }

    public function test_it_adds_sidebar_links(): void
    {
        $expected_path = $this->app->path('Menus/AdminSidebar.php');
        $expected_content = $this->getTestStubContents('Menus/ModelsAdminSidebar.php');

        $this->artisan('generate:views', ['table' => 'categories', '--create' => true])
            ->assertSuccessful();

        $this->artisan('generate:views', ['table' => 'products', '--create' => true])
            ->assertSuccessful();

        $this->assertFileExists($expected_path);

        $actual_content = $this->getGeneratedFileContents($expected_path);
        $this->assertEquals($expected_content, $actual_content);
    }
}
