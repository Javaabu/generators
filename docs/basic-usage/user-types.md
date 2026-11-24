---
title: User types
sidebar_position: 9
---

`generate:auth` creates a new kind of user with [javaabu/auth](https://github.com/Javaabu/auth) v3: customers, vendors, staff. Each has its own model, guard, password broker, login and account pages, and permissions. You also get the admin pages that manage its users.

# 1. The table

Write a migration with javaabu/auth's user columns, or let the command write it:

```bash
php artisan generate:auth customers --migration --create
```

```php
Schema::create('customers', function (Blueprint $table) {
    // name, email, password, account status, two-factor and soft deletes
    UserSchema::columns($table);

    // the columns of your user type
    $table->string('phone')->nullable();
});
```

Add your columns and migrate.

# 2. The user type

```bash
php artisan generate:auth customers --create
```

| Option | Default |
| --- | --- |
| `--type` | The singular of the table: `customer` |
| `--guard` | The type's name: `customer` |

All the options of `generate:all` work too: `--views`, `--index`, `--form`, `--except` and so on. Skip the auth parts with `--except=config,routes`.

What's generated:

- **`app/Models/Customer.php`** extends `Javaabu\Auth\Models\User`. That gives account status, login lockout, email verification, two-factor authentication, passkeys and soft deletes.
- **`config/auth.php`** gets the `customer` session guard, the `customers` user provider and the `customers` password broker (on the `password_reset_tokens` table).
- **`AppServiceProvider::boot()`** registers the type:

  ```php
  UserTypes::register('customer', [
      'model' => Customer::class,
      'guard' => 'customer',
      'passwords' => 'customers',
  ]);
  ```

  Add its other options there: `home`, `features`, `two_factor`, `views`, `layouts`. See [javaabu/auth's user types](https://github.com/Javaabu/auth).
- **`routes/web.php`** gets the login, registration, password reset, email verification and account pages:

  ```php
  Route::authRoutes('customer', ['prefix' => 'customer']);   // customer.login, customer.register, ...
  ```

  javaabu/auth ships the controllers and the Blade and Inertia pages, so none are generated.
- **The admin pages of customers**, like `generate:all`:
  - The form has the name, email, password (required for new customers, kept when left empty), account status and your columns.
  - The columns javaabu/auth manages (login attempts, two-factor secrets, tokens) are left out (`generators.auth.skip_columns`).
  - The admins' account actions are added to the admin routes: `Route::userAccountActions('customer', ['prefix' => 'customers'])` (unlock, reset two-factor, require a new password, log out).
- **Permissions:**
  - `view_customers`, `edit_customers`, `delete_customers` and `force_delete_customers`.
  - javaabu/auth adds `unlock_customers` and the other account permissions.

Protect the customers' own pages with the type's middleware, not `auth:customer`:

```php
Route::middleware('auth.type:customer')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/', CustomerHomeController::class)->name('home');
});
```

# Admins as a user type

The admin area itself can be a user type. Point the generators at it first, so every resource, the admins' own pages included, uses its guard:

```php
// config/generators.php
'admin' => [
    'guard' => 'web_admin',
    'user_model' => App\Models\Admin::class,
    'middleware' => ['web', 'auth.type:admin'],
],
```

Then generate it:

```bash
php artisan generate:auth admins --type=admin --guard=web_admin --migration --create   # the migration
php artisan migrate
php artisan generate:auth admins --type=admin --guard=web_admin --create
```

As `App\Models\Admin` is the admins' model, it gets javaabu/permissions' `HasRoles` trait, and its factory makes approved, verified admins.
