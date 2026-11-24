---
name: javaabu-generators
description: Scaffold Laravel admin resources from database tables with javaabu/generators (php artisan generate:all, generate:model, generate:form, generate:filters, generate:bulk-actions, generate:controller, generate:views, generate:test, generate:auth). Use when creating CRUD or admin pages for a table, a model with its factory, policy, permissions, stencil form, filters, bulk actions, controller, routes, Blade or Inertia React pages and tests; when choosing which fields appear on index, form and show pages; nesting a resource under a parent; adding mediapicker media collections; making a model translatable; or creating a javaabu/auth user type (customers, vendors, admins) with its guard and routes.
license: MIT
metadata:
  author: Javaabu
---

# Admin resources with javaabu/generators

The docs ship with the package: `vendor/javaabu/generators/llms.txt` lists every page in `vendor/javaabu/generators/docs/`.

| Task | Read |
| --- | --- |
| Generate a resource, choose fields, nest, media, user types | This file |
| Every command, option, config key and what each part writes | [references/reference.md](references/reference.md) |

## The workflow

1. Write the migration. Everything comes from it: types, nullability, defaults, `unique()`, foreign keys, `softDeletes()`, enum columns. Run `php artisan migrate`.
2. Generate the tables it refers to first (`categories` before `products`).
3. Look at the code, then write it:

```bash
php artisan generate:all products                 # prints the code
php artisan generate:all products --create        # writes the files
php artisan permissions:sync                      # writes the new permissions
php artisan test --filter=ProductsControllerTest  # the generated tests pass as generated
```

`generate:all` writes:

| File | Built on |
| --- | --- |
| `app/Models/Product.php` | Casts, relations (belongsTo, hasMany, belongsToMany through pivots), `SoftDeletes` |
| `database/factories/ProductFactory.php` | |
| `app/Policies/ProductPolicy.php` | `extends Javaabu\Permissions\Policies\ResourcePolicy` |
| `AppServiceProvider::boot()` | `Permissions::resource('products', label: 'Products')` |
| `app/Filters/ProductFilters.php` | javaabu/filters |
| `app/BulkActions/ProductBulkActions.php` | javaabu/bulk-actions |
| `app/Stencil/ProductForm.php` | javaabu/stencil, which also validates (no FormRequest) |
| `app/Http/Controllers/Admin/ProductsController.php` | `Gate::authorize()` with the policy; `ProductForm::make()->model(...)->save()` |
| `routes/admin.php` | `Route::resource()`, restore and force delete routes, `Route::bulkActions()` |
| `resources/views/admin/products/*` or `resources/js/pages/admin/products/*.tsx` | Blade (javaabu/forms v2) or Inertia React (@javaabu/forms) |
| `tests/Feature/Admin/ProductsControllerTest.php` | Feature tests of every page |
| `lang/dv.json` | Dhivehi for the generic phrases |

## Choosing what is generated

```bash
php artisan generate:all products --create --index=name,price,status --form=name,price,status,category_id,tags
php artisan generate:all products --create --interactive        # checklists for each page
php artisan generate:all products --create --views=inertia      # Inertia React pages
php artisan generate:all reviews --create --parent=products     # /admin/products/{product}/reviews
php artisan generate:all posts --create --media=featured_image:image,gallery:image:multiple
php artisan generate:all products --create --except=test,views  # skip parts
php artisan generate:form products                              # one part
```

- Many-to-many relations are chosen by their relation name (`tags`).
- `--except` takes `model`, `factory`, `policy`, `permissions`, `filters`, `bulk-actions`, `form`, `controller`, `routes`, `views`, `test` and `translations`.

## User types

```bash
php artisan generate:auth customers --migration --create   # the migration, with UserSchema::columns()
php artisan migrate
php artisan generate:auth customers --create               # --type=customer --guard=customer by default
```

This writes:

- the model, which extends `Javaabu\Auth\Models\User`
- the guard, provider and password broker in `config/auth.php`
- `UserTypes::register('customer', [...])` in `AppServiceProvider::boot()`
- `Route::authRoutes('customer', ['prefix' => 'customer'])` in `routes/web.php`
- the admin pages of customers, with a password and status field, and `Route::userAccountActions()`

javaabu/auth ships the login and account controllers and pages: don't write them. Protect the type's own routes with `auth.type:customer`.

## Enums and translations

- **Database enums** (`$table->enum()`) become selects with the options.
- **PHP enums:** name the class in a column comment (`->comment('enum:App\Enums\OrderStatuses')`, MySQL/PostgreSQL) or in `config('generators.enums')` (`'orders.status' => OrderStatuses::class`, every driver). The enum must be backed.
- **Translatable models:** tables with `translations` (json) and `lang` columns get `JsonTranslatable` models and `->translatable()` fields when javaabu/translatable is installed (`--translatable=title` to choose the columns).

## Common mistakes

- Generating before migrating, or from a table that changed since: generate again with `--force` only for files nobody edited.
- Generating `products` before `categories`: the factory and tests use `Category::factory()`.
- Writing a FormRequest for a generated resource: the stencil form's fields are the rules. Change the rules on the fields.
- Checking permission names in the controller: authorize with the policy (`Gate::authorize('update', $product)`).
- Expecting comments to name enum classes on SQLite: use `generators.enums`.
- Editing `routes/admin.php` below or around the `// generators: routes` marker: new resources go above it, inside the admin group.
- Forgetting `php artisan permissions:sync` after generating.
- For Inertia pages: the app needs `@javaabu/forms`, `@javaabu/i18n`, javaabu/manifest's Vite plugin, and `resolvePage(name, import.meta.glob("./pages/**/*.tsx"), { base: "./pages/" })`.
