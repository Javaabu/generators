---
title: The generated tests
sidebar_position: 6
---

`tests/Feature/Admin/ProductsControllerTest.php` tests the generated resource from the outside, through HTTP, and passes as it is generated.

| Test | Checks |
| --- | --- |
| `it_lists_the_products` | The index shows a product |
| `it_does_not_list_the_products_without_permission` | 403 without `view_products` |
| `it_searches_the_products` | The search finds a product by its name (Thaana) and not another |
| `it_shows_the_new_product_form` | The create page |
| `it_creates_a_product` | A valid post saves every field, relations included |
| `it_validates_new_products` | Invalid values are rejected, nothing is saved |
| `it_does_not_create_products_without_permission` | 403 without `edit_products` |
| `it_shows_a_product` | The show page |
| `it_shows_the_edit_product_form` | The edit page |
| `it_updates_a_product` | A valid patch saves other values |
| `it_does_not_update_products_without_permission` | 403 without `edit_products` |
| `it_attaches_media_to_a_product` | Media from the user's library is attached (with media and a `MediaOwner` admin model) |
| `it_deletes_a_product` | Deleted, or soft deleted |
| `it_restores_a_deleted_product`, `it_permanently_deletes_a_product` | For soft-deleting tables |
| `it_deletes_products_in_bulk` | The bulk delete action |

For Inertia pages, the tests check the page component and its props with `assertInertia()` instead of the HTML.

# What they need from the app

- `Tests\TestCase`, the app's base test case.
- The admins' model (`generators.admin.user_model`) with a factory and javaabu/permissions' `HasRoles`. The tests log in with `actingAsAdmin('view_products')`, which creates a user with those permissions, on `generators.admin.guard`.
- `php artisan permissions:sync` in `setUp()`, which writes the registered permissions.
- The factories of the related tables. Generate those first.
- For Inertia pages, inertia-laravel's testing config: `inertia.testing.page_paths` must include `resource_path('js/pages')` when it checks that pages exist.
- For translatable models, javaabu/translatable's `InteractsWithLanguages`. For media, a fake disk (the tests call `Storage::fake()`).

If your admins need more to log in (an approved account, a verified email, a role), change `actingAsAdmin()` in the stub: `test/ControllerTest.stub`.

# How the package tests them

This package's CI generates a whole admin into a Laravel app (`workbench/`) and runs the generated tests there:

- every column type, a pivot and soft deletes
- Blade and Inertia pages
- a nested resource
- media
- a translatable model
- a user type

It also checks the generated PHP with Pint, and type-checks and formats the React pages:

```bash
php workbench/build.php                              # build/app: Testbench's skeleton with workbench/ on top
vendor/bin/phpunit -c workbench/phpunit.generate.xml # generate the admin into it
vendor/bin/phpunit -c workbench/phpunit.xml          # run the generated tests
pnpm typecheck:app && pnpm format:app                # the React pages
```
