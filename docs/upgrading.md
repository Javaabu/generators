---
title: Upgrading from v1
sidebar_position: 1.65
---

v2 writes different code, for the Javaabu next-gen packages, so read this before regenerating anything. Code that v1 generated keeps working: v2 doesn't change files that exist unless you pass `--force`.

# Requirements

- PHP 8.4+ and Laravel 12+ (v1: PHP 8.1+, Laravel 9+).
- `doctrine/dbal` is no longer needed.
- The schema is read with Laravel's schema builder, so MySQL, MariaDB, PostgreSQL and SQLite all work (v1: MySQL only).

# The generated code

| v1 | v2 |
| --- | --- |
| Models implement `AdminModel` with `IsAdminModel`, `LogsActivity` and `$searchable` (javaabu/helpers, javaabu/activitylog) | Plain models: fillable, casts, the relations found in the schema |
| Policies check `view_x`, `edit_x`, `delete_x` | Policies extend javaabu/permissions' `ResourcePolicy` |
| Permissions in `database/seeders/PermissionsSeeder.php` | `Permissions::resource('products')` in `AppServiceProvider::boot()`, written by `php artisan permissions:sync` |
| `ProductsRequest` with the rules | A javaabu/stencil form, `App\Stencil\ProductForm`, whose fields give the rules |
| `HasOrderbys`, `search`, `date_field`/`date_from`/`date_to` in the controller | `App\Filters\ProductFilters` (javaabu/filters) |
| A `bulk()` action in the controller | `App\BulkActions\ProductBulkActions` (javaabu/bulk-actions) and `Route::bulkActions()` |
| Material Admin Blade views with javaabu/forms v1 | javaabu/forms v2 Blade views, or Inertia React pages (`--views=inertia`) |
| `ProductsExport` with maatwebsite/excel | `generate:export` with javaabu/exports v2 (not part of `generate:all`) |
| Tests extend the skeleton's `AdminTestCase` (`actingAsAdmin`, `adminGet`) | Tests extend `Tests\TestCase`, log in with their own `actingAsAdmin()`, and pass as generated |
| Sidebar menu links, icons | Not generated |

# Commands

| v1 | v2 |
| --- | --- |
| `generate:all`, `model`, `factory`, `policy`, `permissions`, `controller`, `routes`, `views`, `test`, `export`, `rollback` | The same names |
| `generate:request` | Removed: the stencil form validates. Use `generate:form` |
| `generate:api`, `generate:api_controller`, `generate:api_test` | Removed |
| `generate:auth_model`, `auth_factory`, `auth_config`, `auth_policy`, `auth_permissions`, `auth_request`, `auth_password_resets`, `auth_controller` and the `auth_*_controller` commands | One `generate:auth {table}`. javaabu/auth v3 ships the login, registration, password and account controllers and pages, so they aren't generated. See [User types](basic-usage/user-types.md) |
| | New: `generate:filters`, `generate:bulk-actions`, `generate:form` |

# Options

| v1 | v2 |
| --- | --- |
| `--columns`, `--create`, `--force` | The same |
| `--except` (`generate:all`) | The same; the parts are `model`, `factory`, `policy`, `permissions`, `filters`, `bulk-actions`, `form`, `controller`, `routes`, `views`, `test`, `translations` |
| `--path` | Removed: set `generators.namespaces` |
| `--auth_name` | `--type` (`generate:auth`) |
| | New: `--index`, `--form`, `--show`, `--interactive`, `--views`, `--parent`, `--media`, `--translatable`; `--guard` and `--migration` for `generate:auth` |

# Config

Publish the new config (`php artisan vendor:publish --tag=generators-config --force`) and copy over what you changed:

| v1 | v2 |
| --- | --- |
| `tinyint1_to_bool`, `skip_columns` | The same |
| `auth_skip_columns` | `auth.skip_columns` |
| `icon_provider`, `sidebar_icon_prefix` | Removed |
| | New: `views`, `connection`, `admin.*`, `blade.*`, `namespaces.*`, `enums`, `name_fields`, `index_columns`, `ignored_tables`, `translatable` |

# Stubs

Every stub is new. Delete `stubs/vendor/generators` and publish again (`php artisan vendor:publish --tag=generators-stubs`) before customising.

# The routes file

v1 added routes above `// Generator Routes - DONOT REMOVE` in `routes/admin.php`. v2 adds them above `// generators: routes`, and creates `routes/admin.php` (loaded from `routes/web.php`) when it doesn't exist. In a v1 app, put the new marker where the old one is, inside your admin route group. Without a marker, v2 adds the routes at the end of the file.

# Enum classes

- **Comments:** `->comment('enum:App\Enums\Statuses')` still works on MySQL and PostgreSQL.
- **javaabu/schema:** its `nativeEnum()` isn't needed.
- **SQLite**, which keeps no comments: name the classes in `generators.enums`. See [Enums](basic-usage/enums.md).
