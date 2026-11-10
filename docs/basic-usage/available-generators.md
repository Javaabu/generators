---
title: Available generators
sidebar_position: 2
---

Every command takes a table and [the same options](generating-a-resource.md#the-options). Without `--create`, it prints the code.

| Command | Writes |
| --- | --- |
| `generate:all {table}` | Everything below, except the export |
| `generate:model {table}` | `app/Models/Product.php`: fillable fields, casts, relations, soft deletes, media collections, translations |
| `generate:factory {table}` | `database/factories/ProductFactory.php` |
| `generate:policy {table}` | `app/Policies/ProductPolicy.php`, a javaabu/permissions `ResourcePolicy` |
| `generate:permissions {table}` | Adds `Permissions::resource('products', label: 'Products')` to `AppServiceProvider::boot()` |
| `generate:filters {table}` | `app/Filters/ProductFilters.php` (javaabu/filters) |
| `generate:bulk-actions {table}` | `app/BulkActions/ProductBulkActions.php` (javaabu/bulk-actions) |
| `generate:form {table}` | `app/Stencil/ProductForm.php` (javaabu/stencil) |
| `generate:controller {table}` | `app/Http/Controllers/Admin/ProductsController.php` |
| `generate:routes {table}` | Adds the routes to `routes/admin.php` |
| `generate:views {table}` | The Blade views, or with `--views=inertia` the React pages, their types and `app/Http/Resources/Admin/ProductResource.php` |
| `generate:test {table}` | `tests/Feature/Admin/ProductsControllerTest.php` |
| `generate:export {table}` | `app/Exports/ProductsExport.php` (javaabu/exports) |
| `generate:auth {table}` | A javaabu/auth user type and the admin pages of its users: [User types](user-types.md) |
| `generate:rollback` | Discards every uncommitted change: [Rolling back](rolling-back-changes.md) |

# What each part writes

**Model.**
- `$fillable` lists every column the form saves, foreign keys included.
- `casts()` covers integers, decimals, booleans, dates, JSON arrays and enum classes.
- Relations are found in the schema (see [Relations](relations.md)).
- `SoftDeletes` when the table has `deleted_at`.

**Factory.** A realistic value for every column. Foreign keys get a factory of the related model, unique columns unique values, and nullable columns sometimes `null`.

**Policy and permissions.** The `ResourcePolicy` maps the abilities to the resource's permissions:

| Ability | Permission |
| --- | --- |
| `viewAny`, `view` | `view_products` |
| `create`, `update` | `edit_products` |
| `delete` | `delete_products` |
| `restore` | `delete_products` or `force_delete_products` |
| `forceDelete` | `force_delete_products` |

Laravel finds the policy by its name. `php artisan permissions:sync` writes the permissions.

**Filters.**
- `search` over the text columns
- selects for enums, foreign keys and many-to-many relations
- yes/no filters for booleans
- ranges for the number and date columns on the index table
- `created_at`
- trashed records for soft-deleting tables
- a sort for each sortable column on the index table

**Bulk actions.** Delete, plus restore and permanently delete for soft-deleting tables, each behind its permission. They run on the selected rows or on every row matching the filters.

**Form.** One stencil field per form column, in a section. Stencil derives the validation rules from the fields: required, types, lengths, ranges, `in:` options, `unique`. So there is no FormRequest.

**Controller.**
- `index`, `create`, `store`, `show`, `edit`, `update` and `destroy`, plus `restore` and `forceDelete` for soft-deleting tables.
- It authorizes every action with the policy.
- `store` and `update` call `ProductForm::make()->model(...)->save()`: validation, filling, saving and syncing many-to-many relations and media.

**Routes.** In the admin group of `routes/admin.php`:

```php
// Products
Route::resource('products', ProductsController::class)->withTrashed(['show']);
Route::patch('products/{product}/restore', [ProductsController::class, 'restore'])->withTrashed()->name('products.restore');
Route::delete('products/{product}/force-delete', [ProductsController::class, 'forceDelete'])->withTrashed()->name('products.force-delete');
Route::bulkActions('products', ProductBulkActions::class);
```

**Views.** The index page has the filter bar, a sortable table with bulk actions and pagination. The create and edit pages render the form. The show page has an infolist and the edit, delete and restore buttons. See [Blade and Inertia React](blade-and-inertia.md).

**Tests.** The tests cover listing, searching, every form, creating, validation, showing, updating, deleting, restoring, bulk deleting and the permission checks. See [The generated tests](../advanced-usage/the-generated-tests.md).

**Export.**

```php
class ProductsExport extends ModelExport
{
    public function modelClass(): string
    {
        return Product::class;
    }
}
```

Return `ProductsExport::make($filters->apply(Product::query()))->download('products.xlsx')` from the index when `$request->filled('download')`, and add `export` to `<x-filters::bar>`.
