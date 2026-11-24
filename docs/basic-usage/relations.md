---
title: Relations, filters and bulk actions
sidebar_position: 4
---

The generators read every table's foreign keys to find the relations.

# belongsTo

Each foreign key of the table:

```php
$table->foreignId('category_id')->constrained();
$table->string('product_slug');
$table->foreign('product_slug')->references('slug')->on('products');
```

```php
public function category(): BelongsTo
{
    return $this->belongsTo(Category::class);
}

public function product(): BelongsTo
{
    return $this->belongsTo(Product::class, 'product_slug', 'slug');
}
```

The relation is named after the key without `_id`, or after the related table when the key starts with its name (`product_slug`).

In the form, a foreign key is a searchable select of the related records, labelled by their name column:

```php
Select::make('category_id')
    ->label(__('Category'))
    ->required()
    ->relationship('category', 'name')
    ->searchable(),
```

The index table shows `$product->category?->name`, and the controller eager loads it.

# hasMany

Foreign keys on other tables that point at this one: `products.category_id` gives `Category::products()`. When a table has two keys to the same table, each relation is named after its key (`author_id` gives `authorPosts`, `reviewer_id` gives `reviewerPosts`).

# Many-to-many

A pivot table joins two tables through their keys. It has two foreign keys and nothing else but an id and timestamps, or it is named after the two tables (`product_tag`) and may have more pivot columns.

```php
Schema::create('product_tag', function (Blueprint $table) {
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    $table->primary(['product_id', 'tag_id']);
});
```

Both models get the relation: `Product::tags()` and `Tag::products()`. Other names, keys, pivot columns and timestamps are written out:

```php
return $this->belongsToMany(Tag::class, 'labels', 'item_id', 'label_id')->withPivot('position')->withTimestamps();
```

In the form, the relation is a multiple select, which stencil syncs after saving:

```php
Select::make('tags')
    ->label(__('Tags'))
    ->multiple()
    ->relationship('tags', 'name')
    ->searchable(),
```

A pivot table isn't a resource of its own: `generate:all product_tag` says to generate `products` and `tags` instead.

# Filters

`ProductFilters` (javaabu/filters) gets:

| Column | Filter |
| --- | --- |
| The string and text columns | One `TextFilter::make('search')->columns([...])` |
| Enums | `SelectFilter` with the options or the enum class |
| Foreign keys | `SelectFilter::make('category_id')->relationship('category', 'name')->searchable()` |
| Many-to-many relations | `SelectFilter::make('tags')->relationship('tags', 'name')->multiple()` |
| Booleans | `TernaryFilter` (yes, no, any) |
| Numbers and dates on the index table | `NumberRangeFilter`, `DateRangeFilter` |
| `created_at` | `DateRangeFilter` |
| `deleted_at` | `TrashedFilter` |

The sorts are the sortable columns of the index table, and `created_at` and `updated_at`. The default sort is the newest first.

# Bulk actions

`ProductBulkActions` (javaabu/bulk-actions) runs on the selected rows, or on every row matching the filters:

```php
protected function actions(): array
{
    return [
        DeleteAction::make()->can('delete_products'),
        RestoreAction::make()->can('delete_products'),
        ForceDeleteAction::make()->can('force_delete_products'),
    ];
}
```

Restore and force delete are added only for soft-deleting tables. Add your own actions to the array: see [javaabu/bulk-actions](https://github.com/Javaabu/bulk-actions).

# Tables the generators ignore

Framework and package tables (`migrations`, `jobs`, `permissions`, `media` and others) are never relations. The list is `generators.ignored_tables`.
