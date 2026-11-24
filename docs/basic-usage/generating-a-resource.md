---
title: Generating a resource
sidebar_position: 1
---

A resource is everything the admin area needs for one table: model, factory, policy, permissions, filters, bulk actions, form, controller, routes, pages and tests.

# 1. Write the migration

The generators read the table, so design it first and migrate:

```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('sku', 20)->unique();
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2)->unsigned();
    $table->enum('status', ['draft', 'published']);
    $table->boolean('is_featured')->default(false);
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    $table->softDeletes();
});
```

Everything in the generated code comes from this schema:

| From the schema | In the generated code |
| --- | --- |
| Column types | The casts, the form fields and their validation, the factory values and the filters |
| `nullable()` and defaults | Whether fields are required |
| `unique()` | `->unique()` rules and unique factory values |
| Foreign keys | Relations and selects |
| `softDeletes()` | Restore and permanently delete |

```bash
php artisan migrate
```

# 2. See the code

Without `--create`, a command prints the code instead of writing it:

```bash
php artisan generate:all products
```

# 3. Write the files

```bash
php artisan generate:all products --create
```

```
  app/Models/Product.php ...................................... created
  database/factories/ProductFactory.php ....................... created
  app/Policies/ProductPolicy.php .............................. created
  app/Providers/AppServiceProvider.php ........................ updated
  app/Filters/ProductFilters.php .............................. created
  app/BulkActions/ProductBulkActions.php ...................... created
  app/Stencil/ProductForm.php ................................. created
  app/Http/Controllers/Admin/ProductsController.php ........... created
  routes/admin.php ............................................ updated
  routes/web.php ............................................ unchanged
  resources/views/admin/products/index.blade.php .............. created
  resources/views/admin/products/create.blade.php ............. created
  resources/views/admin/products/edit.blade.php ............... created
  resources/views/admin/products/show.blade.php ............... created
  tests/Feature/Admin/ProductsControllerTest.php .............. created
  lang/dv.json ................................................ updated
```

Some files are added to rather than replaced: the service provider, the routes and `lang/dv.json`. The additions are made once, so running a command again doesn't repeat them. Files that exist are skipped unless you pass `--force`.

Generate the tables a resource relates to first (here, `categories`): its factory and tests use their factories.

# 4. Finish up

```bash
php artisan permissions:sync     # write the new permissions
php artisan test --filter=ProductsControllerTest
```

Give your admin role the permissions (a `super_admin` role with `'*'` has them already), then open `/admin/products`.

# Skipping parts

```bash
# everything but the tests and the Dhivehi phrases
php artisan generate:all products --create --except=test,translations
```

The parts are `model`, `factory`, `policy`, `permissions`, `filters`, `bulk-actions`, `form`, `controller`, `routes`, `views`, `test` and `translations`. Each also has its own command: see [Available generators](available-generators.md).

# The options

| Option | What |
| --- | --- |
| `--create`, `-c` | Write the files instead of printing the code |
| `--force`, `-f` | Overwrite files that exist |
| `--columns=name,price` | Only use these columns |
| `--index=`, `--form=`, `--show=` | Choose the fields of the index table, the form and the show page: [Choosing fields](choosing-fields.md) |
| `--interactive`, `-i` | Choose the fields, the parent, the media and the views with prompts |
| `--views=blade` or `inertia` | [Blade or Inertia React](blade-and-inertia.md) pages |
| `--parent=categories` | [Nest the resource](nested-resources.md) under a parent |
| `--media=featured_image:image` | [Media collections](media.md) |
| `--translatable[=title,body]` | [Translatable models](../advanced-usage/translatable.md) |
| `--except=test,views` | Skip parts (`generate:all` and `generate:auth`) |
