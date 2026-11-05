---
title: Configuration
sidebar_position: 1
---

Publish the config with `php artisan vendor:publish --tag=generators-config`. Every option has a default, so you only change what differs in your app.

| Option | Default | What |
| --- | --- | --- |
| `views` | `env('GENERATORS_VIEWS', 'blade')` | `blade` or `inertia`, for every command without `--views` |
| `connection` | `null` | The database connection whose schema is read; null for the default |
| `admin.namespace` | `Admin` | Controllers go in `App\Http\Controllers\Admin`, JSON resources in `App\Http\Resources\Admin`, tests in `Tests\Feature\Admin` |
| `admin.route_prefix` | `admin` | The URL prefix of the admin routes |
| `admin.route_name` | `admin.` | The route name prefix: `admin.products.index` |
| `admin.view_prefix` | `admin` | `resources/views/admin/products/` |
| `admin.page_prefix` | `admin` | `resources/js/pages/admin/products/`, `Inertia::render('admin/products/Index')` |
| `admin.routes_file` | `routes/admin.php` | Where resource routes go. Created the first time, and loaded from `routes/web.php` |
| `admin.middleware` | `['web', 'auth']` | The middleware of the admin group, when the routes file is created |
| `admin.guard` | `null` | The admins' guard: policies check it (`$guard`), permissions are made for it (`->guards()`), bulk actions use it, tests log in with it. Null for the default guard |
| `admin.user_model` | `App\Models\User` | The admins' model, which the tests create with its factory |
| `blade.layout` | `layouts.admin` | The layout the Blade pages extend |
| `blade.section` | `content` | The section they fill |
| `namespaces.*` | `App\Models`, `App\Policies`, `App\Filters`, `App\BulkActions`, `App\Stencil`, `App\Http\Controllers`, `App\Http\Resources`, `App\Exports`, `Database\Factories`, `Tests\Feature` | Where each kind of class goes |
| `tinyint1_to_bool` | `true` | Whether MySQL `tinyint(1)` columns are booleans |
| `skip_columns` | `created_at`, `updated_at`, `deleted_at`, `translations`, `lang`, `remember_token` | Columns that are never fields |
| `enums` | `[]` | Enum classes of columns: `'orders.status' => OrderStatuses::class`. See [Enums](../basic-usage/enums.md) |
| `name_fields` | `['name', 'title']` | The columns that name a record, in order: links, selects, page titles. Otherwise the first string column |
| `index_columns` | `5` | How many columns the index table gets by default |
| `ignored_tables` | Framework and package tables | Tables that are never relations |
| `translatable` | `'auto'` | `'auto'` makes models with the translation columns translatable when javaabu/translatable is installed; `false` never does. See [Translatable models](translatable.md) |
| `auth.skip_columns` | The columns javaabu/auth manages | Left out of the admin pages of [user types](../basic-usage/user-types.md) |

# An admin area on its own guard

```php
'admin' => [
    'namespace' => 'Admin',
    'route_prefix' => 'admin',
    'route_name' => 'admin.',
    'view_prefix' => 'admin',
    'page_prefix' => 'admin',
    'routes_file' => 'routes/admin.php',
    'middleware' => ['web', 'auth.type:admin'],
    'guard' => 'web_admin',
    'user_model' => App\Models\Admin::class,
],
```
