<?php

namespace Javaabu\Generators\Generators\Auth;

use Javaabu\Generators\Generators\GeneratedFile;
use Javaabu\Generators\Support\Stub;

/**
 * A migration for a user type's table, with javaabu/auth's user columns.
 * It works before the table exists, so it has no resource.
 */
class AuthMigrationGenerator
{
    public function __construct(
        protected string $table,
    ) {}

    public function file(): GeneratedFile
    {
        $name = date('Y_m_d_His') . "_create_{$this->table}_table.php";

        return new GeneratedFile(database_path("migrations/{$name}"), $this->render(), description: 'migration');
    }

    public function render(): string
    {
        return Stub::make('auth/migration.stub')->with(['table' => $this->table])->render();
    }
}
