<?php

namespace Javaabu\Generators\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Javaabu\Generators\Generators\Auth\AuthConfigGenerator;
use Javaabu\Generators\Generators\Auth\AuthMigrationGenerator;
use Javaabu\Generators\Generators\Auth\AuthRoutesGenerator;
use Javaabu\Generators\Generators\Auth\UserTypeGenerator;
use Javaabu\Generators\Resource\ResourceDefinition;
use Javaabu\Generators\Resource\ResourceOptions;
use Javaabu\Generators\Schema\SchemaInspector;
use Symfony\Component\Console\Input\InputOption;

/**
 * A new javaabu/auth user type: its model, guard, password broker and auth
 * routes (javaabu/auth ships the auth pages), and the admin pages that
 * manage its users.
 */
class GenerateAuthCommand extends GenerateAllCommand
{
    protected $name = 'generate:auth';

    protected $description = 'Generate a javaabu/auth user type from a table: model, guard, password broker, auth routes, permissions, and the admin pages of its users';

    protected function getOptions(): array
    {
        return [
            ...parent::getOptions(),
            ['type', null, InputOption::VALUE_REQUIRED, 'The user type name (default: the singular of the table, e.g. customer)'],
            ['guard', null, InputOption::VALUE_REQUIRED, 'The session guard (default: the type name)'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Write a migration for the table when it does not exist yet'],
        ];
    }

    public function handle(): int
    {
        $table = (string) $this->argument('table');

        $inspector = app(SchemaInspector::class);
        $inspector->flush();

        if (! $inspector->hasTable($table) && $this->option('migration')) {
            return $this->writeMigration($table);
        }

        return parent::handle();
    }

    protected function writeMigration(string $table): int
    {
        $file = (new AuthMigrationGenerator($table))->file();

        if (! $this->option('create')) {
            $this->line("// {$file->relativePath()}");
            $this->line($file->contents);

            return self::SUCCESS;
        }

        $filesystem = app(Filesystem::class);
        $filesystem->ensureDirectoryExists(dirname($file->path));
        $filesystem->put($file->path, $file->contents);

        $this->components->twoColumnDetail($file->relativePath(), __('generators::generators.created'));
        $this->components->info(__('generators::generators.auth_migrate', ['table' => $table]));

        return self::SUCCESS;
    }

    protected function resourceOptions(): ResourceOptions
    {
        $options = parent::resourceOptions();

        $type = $this->option('type') ?: Str::singular(Str::snake((string) $this->argument('table')));

        return new ResourceOptions(
            columns: $options->columns,
            index: $options->index,
            form: $options->form,
            show: $options->show,
            parent: $options->parent,
            media: $options->media,
            views: $options->views,
            translatable: false,
            user_type: Str::snake($type),
            user_guard: $this->option('guard') ?: null,
        );
    }

    protected function generators(ResourceDefinition $resource): array
    {
        $except = array_filter(array_map('trim', explode(',', (string) $this->option('except'))));

        $auth = [];

        if (! in_array('config', $except, true)) {
            $auth[] = AuthConfigGenerator::class;
            $auth[] = UserTypeGenerator::class;
        }

        if (! in_array('routes', $except, true)) {
            $auth[] = AuthRoutesGenerator::class;
        }

        return [...$auth, ...parent::generators($resource)];
    }
}
