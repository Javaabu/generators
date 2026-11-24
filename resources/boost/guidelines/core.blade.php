{{--
    javaabu/generators guidelines for AI code assistants

    Maintained by: Javaabu (https://javaabu.com)
--}}

## javaabu/generators

- Scaffold admin CRUD with the generators instead of writing it by hand: write and run the migration, then `php artisan generate:all {table} --create`. It writes the model, factory, policy, permissions, filters, bulk actions, stencil form, controller, routes, Blade or Inertia React pages and passing feature tests. Run `php artisan permissions:sync` after.
- Generate a table's related tables first (its foreign keys' tables), so their models and factories exist. Pivot tables (`product_tag`) aren't resources: generate the two tables they join.
- Choose fields with `--index=`, `--form=`, `--show=` (many-to-many relations by name), pages with `--views=blade|inertia`, nesting with `--parent=`, media with `--media=name:type[:multiple]`. Use `generate:auth {table}` for a new javaabu/auth user type, never hand-written auth controllers.
- Without `--create` a command prints the code; existing files are skipped unless `--force`. Don't overwrite files the developer has edited.
- After generating, edit the generated code like any app code; keep the Javaabu codestyle and run the generated tests (`php artisan test --filter={Resource}ControllerTest`).
- Always activate the `javaabu-generators` skill when scaffolding resources, admin pages, CRUD, nested resources or user types, or when changing what the generators write.
