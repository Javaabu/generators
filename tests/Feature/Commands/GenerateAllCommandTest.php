<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Javaabu\Generators\Commands\GenerateControllerCommand;
use Javaabu\Generators\Commands\GenerateExportCommand;
use Javaabu\Generators\Commands\GenerateFactoryCommand;
use Javaabu\Generators\Commands\GenerateModelCommand;
use Javaabu\Generators\Commands\GeneratePermissionsCommand;
use Javaabu\Generators\Commands\GeneratePolicyCommand;
use Javaabu\Generators\Commands\GenerateRequestCommand;
use Javaabu\Generators\Commands\GenerateRoutesCommand;
use Javaabu\Generators\Commands\GenerateTestCommand;
use Javaabu\Generators\Commands\GenerateViewsCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class GenerateAllCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function getCommands(): array
    {
        return [
            GenerateFactoryCommand::class,
            GeneratePermissionsCommand::class,
            GenerateModelCommand::class,
            GeneratePolicyCommand::class,
            GenerateRequestCommand::class,
            GenerateExportCommand::class,
            GenerateControllerCommand::class,
            GenerateRoutesCommand::class,
            GenerateViewsCommand::class,
            GenerateTestCommand::class,
        ];
    }

    public function test_it_can_skip_commands_for_all(): void
    {
        $commands = $this->getCommands();

        foreach ($commands as $command) {
            // skip test and permissions
            if ($command == GenerateTestCommand::class || $command == GeneratePermissionsCommand::class) {
                $fake_command = $this->mock($command . '[createOutput]', function ($mock) {
                    $mock->shouldAllowMockingProtectedMethods()
                        ->shouldNotHaveReceived('createOutput');
                });
            } else {
                $fake_command = $this->mock($command . '[createOutput]', function ($mock) {
                    $mock->shouldAllowMockingProtectedMethods()
                        ->shouldReceive('createOutput')
                        ->once()
                        ->with('categories', []);
                });
            }

            $this->app->instance($command, $fake_command);
        }

        $this->artisan('generate:all', ['table' => 'categories', '--except' => 'test,permissions'])
            ->assertSuccessful();
    }

    public function test_it_can_generate_all_output(): void
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

        $this->artisan('generate:all', ['table' => 'categories'])
             ->assertSuccessful();
    }

    public function test_it_can_create_all_files(): void
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

        $this->artisan('generate:all', ['table' => 'categories', '--create' => true])
            ->assertSuccessful();
    }
}
