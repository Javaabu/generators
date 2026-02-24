<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Filesystem\Filesystem;
use Javaabu\Generators\Exceptions\ColumnDoesNotExistException;
use Javaabu\Generators\Exceptions\MultipleTablesSuppliedException;
use Javaabu\Generators\Exceptions\TableDoesNotExistException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Symfony\Component\Console\Exception\RuntimeException;

class BaseGenerateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_asks_for_the_table_name(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "table")');

        $this->artisan('generate:factory');
    }

    public function test_it_does_not_accept_multiple_table_names(): void
    {
        $this->expectException(MultipleTablesSuppliedException::class);

        $this->artisan('generate:factory', ['table' => 'categories,orders']);
    }

    public function test_it_checks_if_the_table_exists(): void
    {
        $this->expectException(TableDoesNotExistException::class);

        $this->artisan('generate:factory', ['table' => 'none_existing_table']);
    }

    public function test_it_checks_if_the_columns_exists(): void
    {
        $this->expectException(ColumnDoesNotExistException::class);

        $this->artisan('generate:factory', ['table' => 'products', '--columns' => 'name,none_existing_field']);
    }

    public function test_it_does_not_create_existing_files_without_the_force_option(): void
    {
        $expected_path = $this->app->databasePath('factories/CategoryFactory.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')
                ->once()
                ->andReturnTrue();

            $mock->shouldNotReceive('makeDirectory');

            $mock->shouldNotReceive('put');
        });

        $this->artisan('generate:factory', ['table' => 'categories', '--create' => true])
            ->expectsOutput($expected_path . ' already exists!');
    }

    public function test_it_over_writes_existing_files_with_the_force_option(): void
    {
        $this->partialMock(Filesystem::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')
                ->once()
                ->andReturnTrue();

            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->andReturnTrue();
        });

        $this->artisan('generate:factory', ['table' => 'categories', '--create' => true, '--force' => true])
            ->assertSuccessful();
    }
}
