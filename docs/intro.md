---
title: Introduction
sidebar_position: 1.0
---

# Generators

[javaabu/generators](https://github.com/Javaabu/generators) reads a database table and writes a complete, tested admin resource for it, built on the Javaabu next-gen packages. You design the migration; the generators write the rest.

```bash
php artisan generate:all products --create
```

For a `products` table with a category, tags (through a `product_tag` pivot table), an enum `status` and soft deletes, that writes:

| File | What it uses |
| --- | --- |
| `app/Models/Product.php` | Fillable fields, casts, `category()`, `tags()` and the other relations found in the schema, `SoftDeletes` |
| `database/factories/ProductFactory.php` | A value for every column, related records for foreign keys |
| `app/Policies/ProductPolicy.php` | [javaabu/permissions](https://github.com/Javaabu/permissions)' `ResourcePolicy` |
| `app/Providers/AppServiceProvider.php` | `Permissions::resource('products')`: `view_products`, `edit_products`, `delete_products`, `force_delete_products` |
| `app/Filters/ProductFilters.php` | [javaabu/filters](https://github.com/Javaabu/filters): a search over the text columns, selects for the status, category and tags, ranges, trashed records, sorts |
| `app/BulkActions/ProductBulkActions.php` | [javaabu/bulk-actions](https://github.com/Javaabu/bulk-actions): delete, restore and permanently delete the selected or filtered products |
| `app/Stencil/ProductForm.php` | A [javaabu/stencil](https://github.com/Javaabu/stencil) form: one field per column, and the validation rules |
| `app/Http/Controllers/Admin/ProductsController.php` | Index, create, show, edit, delete, restore and permanently delete, authorized by the policy |
| `routes/admin.php` | `Route::resource()`, the restore and delete routes, `Route::bulkActions()` |
| `resources/views/admin/products/*.blade.php` | Blade pages with [javaabu/forms](https://github.com/Javaabu/forms) |
| or `resources/js/pages/admin/products/*.tsx` | Inertia React pages with [@javaabu/forms](https://github.com/Javaabu/react-forms) and a JSON resource |
| `tests/Feature/Admin/ProductsControllerTest.php` | Feature tests of every page, validation, authorization and the bulk actions, which pass as they are |
| `lang/dv.json` | Dhivehi for the generic phrases of the pages |

The generators also:

- nest resources under a parent (`/admin/categories/{category}/products`)
- add [javaabu/mediapicker](https://github.com/Javaabu/mediapicker) media collections
- make models translatable with [javaabu/translatable](https://github.com/Javaabu/translatable)
- create [javaabu/auth](https://github.com/Javaabu/auth) user types with their own guard, routes and permissions
- let you choose which fields appear on the index table, the form and the show page

The generated code is yours: it is plain Laravel code that follows the Javaabu coding standards and passes Pint. Edit it as you like.

Every generated file is checked in this package's CI. A whole admin is generated into a Laravel app, its tests run, and the React pages are type-checked.

# What next

- [Requirements](requirements.md) and [installation](installation-and-setup.md)
- [Generating a resource](basic-usage/generating-a-resource.md): a walkthrough
- [Available generators](basic-usage/available-generators.md): every command
- [Upgrading from v1](upgrading.md)
