<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Javaabu\Generators\Commands\GenerateApiControllerCommand;
use Javaabu\Generators\Commands\GenerateApiTestCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class GenerateApiCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function getCommands(): array
    {
        return [
            GenerateApiControllerCommand::class,
            GenerateApiTestCommand::class,
        ];
    }

    public function test_it_can_generate_all_api_output(): void
    {
        $commands = $this->getCommands();

        foreach ($commands as $command) {
            $fake_command = $this->mock($command . '[createOutput]', function ($mock) {
                $mock->shouldAllowMockingProtectedMethods()
                    ->shouldReceive('createOutput')
                    ->once()
                    ->with('categories', []);
            });

            $this->app->instance($command, $fake_command);
        }

        $this->artisan('generate:api', ['table' => 'categories'])
            ->assertSuccessful();
    }

    public function test_it_can_create_all_api_files(): void
    {
        $commands = $this->getCommands();

        foreach ($commands as $command) {
            $fake_command = $this->mock($command . '[createFiles]', function ($mock) {
                $mock->shouldAllowMockingProtectedMethods()
                    ->shouldReceive('createFiles')
                    ->once()
                    ->with('categories', [], false, '');
            });

            $this->app->instance($command, $fake_command);
        }

        $this->artisan('generate:api', ['table' => 'categories', '--create' => true])
            ->assertSuccessful();
    }
}
