<?php

namespace Javaabu\Generators\Generators\Auth;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Javaabu\Generators\Generators\BaseGenerator;
use Javaabu\GeneratorHelpers\StringCaser;

abstract class BaseAuthGenerator extends BaseGenerator
{
    protected string $auth_name;
    protected string $default_password;
    protected bool $password_required;
    protected bool $email_required;

    /**
     * Constructor
     */
    public function __construct(string $table, array $columns = [], string $auth_name = '')
    {
        $this->auth_name = StringCaser::snake($auth_name) ?: StringCaser::singularSnake($table);

        parent::__construct($table);

        $this->password_required = (bool) $this->getField('password')?->isRequired();
        $this->email_required = (bool) $this->getField('email')?->isRequired();

        // remove auth columns
        $this->removeAuthColumns($table, $columns);

        $this->default_password = $this->generateDefaultPassword();
    }

    /**
     * Get the default password
     */
    public function getDefaultPassword(): string
    {
        return $this->default_password;
    }

    /**
     * Get the default password
     */
    public function generateDefaultPassword(): string
    {
        $file_path = database_path('seeders/DefaultUsersSeeder.php');
        $default_password = '';

        if (file_exists($file_path)) {
            $default_password = $this->extractDefaultPassword($file_path);
        }

        return $default_password ?: StringCaser::singularStudly($this->getTable()) . '@123456';
    }

    /**
     * Extract the default password
     */
    public function extractDefaultPassword(string $file): string
    {
        $contents = file_get_contents($file);

        $matches = [];

        if (preg_match('/\'password\' => \'(.+)\'/', $contents, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Check if is an auth column
     */
    public function isAuthColumn(string $column): bool
    {
        return in_array($column, config('generators.auth_skip_columns'));
    }

    /**
     * Check if has additional columns
     */
    public function hasAdditionalAttributes(): bool
    {
        return ! empty($this->getFields());
    }

    public function requiresPassword(): bool
    {
        return $this->password_required;
    }

    public function requiresEmail(): bool
    {
        return $this->email_required;
    }

    /**
     * Remove auth columns from
     */
    protected function removeAuthColumns(string $table, array $columns): void
    {
        $this->columns = array_diff($columns, config('generators.auth_skip_columns'));

        // get all columns if columns not provided
        if (! $columns) {
            $columns = array_keys($this->getFields());
        }

        $fields_to_keep = array_diff($columns, config('generators.auth_skip_columns'));

        $this->fields = Arr::only($this->getFields(), $fields_to_keep);
    }

    public function getAuthName(): string
    {
        return $this->auth_name;
    }

    public function getWebGuardName(): string
    {
        return 'web_' . $this->getMorph();
    }

    public function getApiGuardName(): string
    {
        return 'api_' . $this->getMorph();
    }

    public function getNamespace(): string
    {
        return StringCaser::studly($this->getAuthName());
    }
}
