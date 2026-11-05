---
title: Blade and Inertia React
sidebar_position: 6
---

```bash
php artisan generate:all products --create                    # Blade (the default)
php artisan generate:all products --create --views=inertia    # Inertia React
```

Change the default with `GENERATORS_VIEWS=inertia` or `generators.views`. The model, filters, bulk actions, form and policy are the same for both; the controller, the pages and the tests differ.

# Blade

`resources/views/admin/products/` has `index`, `create`, `edit` and `show`. They extend `generators.blade.layout` (`layouts.admin`) and fill its `content` section.

```blade
{{-- index.blade.php --}}
<x-filters::bar :filters="$filters" />

<x-forms::table model="products">
    <x-slot:bulk-form :action="$bulk_actions->getUrl()">
        <x-bulk-actions::actions :actions="$bulk_actions" :paginator="$products" />
    </x-slot:bulk-form>

    <x-slot:headers>
        <x-filters::heading :filters="$filters" sort="name" :label="__('Name')" />
        <x-forms::table.heading :label="__('Category')" />
    </x-slot:headers>

    <x-slot:rows>
        @forelse($products as $product)
            <x-forms::table.row :model="$product" name="products">
                <x-forms::table.cell :label="__('Name')">
                    <a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a>
                </x-forms::table.cell>
                <x-forms::table.cell :label="__('Category')" :value="$product->category?->name" />
            </x-forms::table.row>
        @empty
            ...
        @endforelse
    </x-slot:rows>
</x-forms::table>
```

```blade
{{-- create.blade.php and edit.blade.php --}}
{{ $form }}
```

The show page is an `<x-forms::infolist>` with an entry per field, and buttons that check the policy with `@can`. The controller passes `filters`, `bulk_actions` and the paginator to the index, and `form` to the create and edit pages.

# Inertia React

`resources/js/pages/admin/products/` has `Index.tsx`, `Create.tsx`, `Edit.tsx`, `Show.tsx` and `types.ts`, in TypeScript. The controller renders them with `Inertia::render('admin/products/Index', [...])` and sends the records through `App\Http\Resources\Admin\ProductResource`. The resource holds the attributes, the loaded relations, the media URLs, the record's `urls` and what the user `can` do with it.

```tsx
// Index.tsx
import { BulkActionsTable, type BulkActionsSchema } from "@javaabu/bulk-actions"
import { FilterBar, SortableHeading, type FiltersData } from "@javaabu/filters"
import { type LaravelResourceCollection, Pagination, TableCell, TableRow } from "@javaabu/forms"
import { __ } from "@javaabu/i18n"
import type { Product } from "./types"

export default function Index({ products, filters, bulkActions, createUrl }: Props) {
  return (
    <>
      <FilterBar filters={filters} />

      <BulkActionsTable
        bulkActions={bulkActions}
        onRunFinished={() => router.reload()}
        headers={<SortableHeading filters={filters} sort="name" label={__("Name")} />}
        pagination={products.meta.last_page > 1 ? <Pagination paginator={products} /> : null}
      >
        {products.data.map((product) => (
          <TableRow key={product.id} model={product} name="products">
            <TableCell name="name" label={__("Name")} />
          </TableRow>
        ))}
      </BulkActionsTable>
    </>
  )
}
```

```tsx
// Create.tsx and Edit.tsx
<StencilForm schema={form} />
```

- `@javaabu/stencil`, `@javaabu/filters`, `@javaabu/bulk-actions` and `@javaabu/mediapicker` are the packages' own React code, aliased by javaabu/manifest's Vite plugin. `@javaabu/forms` and `@javaabu/i18n` are npm packages.
- The pages have no layout of their own: give them one with a persistent layout in `app.tsx`.
- `php artisan manifest:types` gives TypeScript the package paths.
- The generated pages are type-checked and formatted with Prettier in this package's CI (`semi: false`, `printWidth: 120`).

# Directions

Both kinds of pages use logical CSS (`ms-`, `ps-`, `gap-`), so they work in right-to-left layouts. See [Dhivehi and RTL](../advanced-usage/dhivehi-and-rtl.md).
