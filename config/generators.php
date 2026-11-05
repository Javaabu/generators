<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | The views generated for a resource: 'blade' (javaabu/forms) or
    | 'inertia' (Inertia React pages with @javaabu/forms). Override it per
    | command with --views.
    |
    */

    'views' => env('GENERATORS_VIEWS', 'blade'),

    /*
    |--------------------------------------------------------------------------
    | Database connection
    |--------------------------------------------------------------------------
    |
    | The connection whose schema is read. Null for the default connection.
    |
    */

    'connection' => null,

    /*
    |--------------------------------------------------------------------------
    | The admin area
    |--------------------------------------------------------------------------
    |
    | Where generated controllers, routes, views and pages go, and who uses
    | them. Nothing assumes the web guard or App\Models\User: set the guard
    | and user model of your admins (a javaabu/auth user type, for example).
    |
    */

    'admin' => [
        // Controllers go in App\Http\Controllers\{namespace}, tests in Tests\Feature\{namespace}
        'namespace' => 'Admin',

        // URL prefix and route name prefix
        'route_prefix' => 'admin',
        'route_name' => 'admin.',

        // resources/views/{view_prefix}/products, resources/js/pages/{page_prefix}/products
        'view_prefix' => 'admin',
        'page_prefix' => 'admin',

        // The routes file the resource routes are added to, and its middleware
        'routes_file' => 'routes/admin.php',
        'middleware' => ['web', 'auth'],

        // The guard and model of the users of the admin area (null for the default guard)
        'guard' => null,
        'user_model' => 'App\\Models\\User',
    ],

    /*
    |--------------------------------------------------------------------------
    | Blade
    |--------------------------------------------------------------------------
    |
    | The layout generated Blade views extend, and the section they fill.
    |
    */

    'blade' => [
        'layout' => 'layouts.admin',
        'section' => 'content',
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    */

    'namespaces' => [
        'models' => 'App\\Models',
        'policies' => 'App\\Policies',
        'filters' => 'App\\Filters',
        'bulk_actions' => 'App\\BulkActions',
        'forms' => 'App\\Stencil',
        'controllers' => 'App\\Http\\Controllers',
        'resources' => 'App\\Http\\Resources',
        'exports' => 'App\\Exports',
        'factories' => 'Database\\Factories',
        'tests' => 'Tests\\Feature',
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema
    |--------------------------------------------------------------------------
    */

    // Whether tinyint(1) columns are booleans (MySQL)
    'tinyint1_to_bool' => env('GENERATORS_TINYINT1_TO_BOOL', true),

    // Columns that are never fields
    'skip_columns' => ['created_at', 'updated_at', 'deleted_at', 'translations', 'lang', 'remember_token'],

    // Enum classes of columns, for drivers that keep no column comments (SQLite):
    // 'orders.status' => App\Enums\OrderStatuses::class.
    // On MySQL and PostgreSQL a column comment "enum:App\Enums\OrderStatuses" works too.
    'enums' => [],

    // Columns that name a record, in order of preference
    'name_fields' => ['name', 'title'],

    // How many columns the index table shows by default
    'index_columns' => 5,

    // Tables that are never resources or relations
    'ignored_tables' => [
        'migrations', 'password_reset_tokens', 'password_resets', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'personal_access_tokens', 'oauth_access_tokens',
        'oauth_auth_codes', 'oauth_clients', 'oauth_refresh_tokens', 'oauth_device_codes',
        'permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions',
        'media', 'attachments', 'activity_log', 'bulk_action_runs', 'exports', 'imports',
        'languages', 'notifications', 'passkeys', 'telescope_entries', 'telescope_entries_tags',
        'telescope_monitoring', 'pulse_aggregates', 'pulse_entries', 'pulse_values',
    ],

    /*
    |--------------------------------------------------------------------------
    | Translatable
    |--------------------------------------------------------------------------
    |
    | 'auto' makes models translatable when javaabu/translatable is installed
    | and the table has its translations and lang columns. false never does.
    |
    */

    'translatable' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | User types (javaabu/auth)
    |--------------------------------------------------------------------------
    */

    'auth' => [
        // Columns of a user type that javaabu/auth manages, kept out of forms
        'skip_columns' => [
            'email_verified_at', 'password', 'remember_token', 'last_login_at', 'login_attempts',
            'require_password_update', 'status', 'new_email', 'two_factor_secret',
            'two_factor_recovery_codes', 'two_factor_confirmed_at',
        ],
    ],
];
