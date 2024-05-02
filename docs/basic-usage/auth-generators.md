---
title: Auth generators
sidebar_position: 3
---

This package allows you to scaffold authentication for new user types according to [javaabu/auth](https://github.com/Javaabu/auth) package.

# Setting up the auth migration

To use an auth generator, first create the new user type migration with the `\Javaabu\Auth\UserSchema::columns($table);` columns and run the migration. In this example, we are creating a new user type called `customers`.

```php
// create_customers_table.php
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            \Javaabu\Auth\UserSchema::columns($table);
        });     
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
```

For the default `UserSchema` columns, the auth generators will generate code based on predefined stubs instead of how code is generated by regular generators. You can customise which columns will be considered as `UserSchema` columns by editing the `auth_skip_columns` in the `config/generators.php` config file after you have published the package config. The default auth columns are given below:

```php
    /*
    |--------------------------------------------------------------------------
    | Which columns to always skip for auth
    |--------------------------------------------------------------------------
    |
    */
    'auth_skip_columns' => [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'last_login_at',
        'login_attempts',
        'require_password_update',
        'status',
        'new_email'
    ],
```

If required, you can also include additional columns in your migration apart from the default columns from the `UserSchema`. These additional columns will be handled by the auth generators as how regular generators handle columns.

```php
// create_customers_table.php
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            \Javaabu\Auth\UserSchema::columns($table);
            
            $table->text('address')->nullable();
            $table->datetime('dob')->index();
        });     
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
```

# Using auth generators

Auth generate commands function similar to the regular generate commands, apart from the `--auth_name` option. This option allows you to customize the name used for the auth routes and the namespace. By default, the `auth_name` will be generated using the singular name of the given table.

For example, running the following command:

```bash
php artisan generate:auth_routes customers --create
```

will generate a route file called `customer.php`. If you wanted to instead have the route file named `portal.php`, you could use the `--auth_name` option like so:

```bash
php artisan generate:auth_routes customers --auth_name=portal --create
```

# Available auth generators

The available auth generators and what they generate are given below:

```bash
# creates database/migrations/customer_password_reset_tokens.php
php artisan generate:auth_password_resets customers --create

# creates database/factories/CustomerFactory.php
php artisan generate:auth_factory customers --create

# adds auth permissions to database/seeders/PermissionsSeeder.php
php artisan generate:auth_permissions customers --create

# adds auth config to config/auth.php config file
php artisan generate:auth_config customers --create
```