---
title: Nested resources
sidebar_position: 7
---

A resource that belongs to another can be managed under it: a product's reviews at `/admin/products/{product}/reviews`.

```bash
php artisan generate:all reviews --create --parent=products
```

`--parent` takes the parent table (`products`), the foreign key (`product_id`) or the relation (`product`). It must be a foreign key of the table.

What changes:

- **Routes.** `Route::resource('products.reviews', ...)->scoped()` gives `admin.products.reviews.index` and the rest, with scoped bindings: a review from another product is a 404. The restore, permanently delete and bulk action routes are nested the same way.

  ```php
  Route::resource('products.reviews', ReviewsController::class)->scoped();
  Route::bulkActions('products/{product}/reviews', ReviewBulkActions::class, ['name' => 'products.reviews.bulk']);
  ```

- **Controller.** Every action takes the parent. The index lists only its records (`Review::query()->whereBelongsTo($product, 'product')`), and new records belong to it (`(new Review)->product()->associate($product)`).
- **Form.** The parent's foreign key isn't a field: the URL gives it.
- **Bulk actions.** They select only the parent's records, and post to the nested route.
- **Pages.** They show the parent's name, and get the parent as `$product` (Blade) or `product` (Inertia).
- **Tests.** Each test creates a parent and the records under it.

`scoped()` finds the record through the parent's relation named after the child (`Product::reviews()`). Generate the parent first, so its model has the relation.

The policy is the resource's own. To also check the parent (for example, only editors of a product may see its reviews), add `Gate::authorize('view', $product)` to the controller's actions.
