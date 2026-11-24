---
title: Choosing fields
sidebar_position: 3
---

By default:

- **The form** has every column, except the key, timestamps, soft deletes, the translation columns and (for a nested resource) the parent's key. Many-to-many relations are included too.
- **The index table** has the name column (`name`, `title`, or the first string) and up to four more short columns. Long text and JSON are left out.
- **The show page** has every column and many-to-many relation.

# With options

```bash
php artisan generate:all products --create \
    --index=name,price,status,category_id \
    --form=name,sku,price,status,category_id,tags \
    --show=name,sku,description,price,status,category_id,tags,created_at
```

- Give column names, and many-to-many relations by their relation name (`tags`).
- The name column is always on the index table, as the link to the show page.
- An unknown name stops the command with a list of what it doesn't know.
- `generators.index_columns` sets how many columns the index table gets by default.

`--columns` restricts every part to those columns, model and factory included:

```bash
php artisan generate:form products --columns=name,price
```

# With prompts

```bash
php artisan generate:all products --create --interactive
```

The prompts show checklists of the fields with the defaults ticked, for the index table, the form and the show page. They then ask for the parent of a nested resource, media collections and the views. An option you pass on the command line isn't asked for.

```
 ┌ Which fields go in the form? ────────────────────────────────┐
 │ › ◼ Name (name)                                               │
 │   ◼ Sku (sku)                                                 │
 │   ◼ Description (description)                                 │
 │   ◻ Is featured (is_featured)                                 │
 │   ◼ Tags (many-to-many)                                       │
 └───────────────────────────────────────────────────────────────┘
```
