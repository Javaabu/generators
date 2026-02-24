<?php

namespace Javaabu\Generators\Tests\Feature\Commands\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;
use Mockery\MockInterface;
use Illuminate\Filesystem\Filesystem;

class GenerateAuthFactoryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_generate_auth_factory_output(): void
    {
        $expected_content = $this->getTestStubContents('factories/CustomerFactory.php');

        $this->artisan('generate:auth_factory', ['table' => 'customers'])
             ->expectsOutput($expected_content);
    }

    public function test_it_can_generate_auth_factory_file(): void
    {
        $expected_path = $this->app->databasePath('factories/CustomerFactory.php');
        $expected_content = $this->getTestStubContents('factories/CustomerFactory.php');

        $this->partialMock(Filesystem::class, function (MockInterface $mock) use ($expected_path, $expected_content) {
            $mock->shouldReceive('makeDirectory')
                ->andReturnTrue();

            $mock->shouldReceive('put')
                ->once()
                ->with($expected_path, $expected_content)
                ->andReturnTrue();
        });

        $this->artisan('generate:auth_factory', ['table' => 'customers', '--create' => true])
             ->assertSuccessful();
    }
}
