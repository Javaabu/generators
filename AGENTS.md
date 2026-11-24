# generators: notes for AI coding agents

`javaabu/generators` is a Laravel package (a dev tool): Artisan commands that read a database table and write an admin resource built on the Javaabu next-gen packages (stencil, filters, bulk-actions, permissions, forms, mediapicker, translatable, auth). The package itself depends only on Laravel; the packages the generated code uses are dev dependencies for the integration app.

## Layout

| Path | What |
| --- | --- |
| `src/Schema/` | `SchemaInspector` (Laravel's schema builder, normalized columns, enum options from types and check constraints), `Table`, `Column`, `ForeignKey`, `RelationResolver` and `Relations/` |
| `src/Fields/` | One class per column type: cast, factory, stencil field, filter, cells, entries, TS type, test values |
| `src/Resource/` | `ResourceResolver` builds a `ResourceDefinition` (fields, relations, choices, every name) from a table and `ResourceOptions` |
| `src/Generators/` | One generator per part; `files()` returns `GeneratedFile`s (with a merge callback for files that are added to). `Auth/` for generate:auth |
| `src/Support/` | `Stub` (rendering), `Imports` (use statements), `PhpFile` (careful edits of existing PHP), `Naming`, `Translations` |
| `src/Commands/` | `GenerateCommand` (options, prompts, printing, writing) and one command per part |
| `stubs/` | File skeletons (publishable) |
| `tests/` | PHPUnit: `Unit`, `Feature`, `Fixtures/migrations`, `snapshots/` (reviewed output) |
| `workbench/` | The integration app: an overlay on Testbench's skeleton, `generate/GenerateAppTest.php` (the commands), `tests/` (its TestCase and hand-written tests) |
| `docs`, `llms.txt`, `resources/boost` | Docs, the index for assistants, the Laravel Boost guideline and skill |

## Rules

- Follow the Javaabu PHP guidelines (`vendor/javaabu/boost-javaabu-guidelines`): snake_case variables and properties, camelCase methods, `#[Test]` methods named `it_…`, early returns, no `else`.
- Generated code must pass Pint with `pint.json`, use the next-gen packages' current APIs, put every user-facing string through `__()`, and use logical CSS only. Generated React pages must typecheck and pass Prettier (`workbench/.prettierrc.json`).
- Never require the packages the generated code uses at runtime: the generators detect them (`class_exists`) and they are `suggest`/`require-dev` only. javaabu/translatable is optional and CI runs without it.
- A change to generated output changes the snapshots: run `GENERATORS_UPDATE_SNAPSHOTS=1 vendor/bin/phpunit` and review the diff. Then run the integration app.
- Every change needs tests, docs (linked from `llms.txt`), the Boost skill if it changes usage, and a line in `CHANGELOG.md`. Strings in `lang/en` and `lang/dv`.

## Commands

```bash
composer install && pnpm install        # pnpm 10.28 (packageManager)
composer lint && composer test          # Pint and PHPUnit (SQLite; DB_CONNECTION=mysql|mariadb|pgsql for the others)
php workbench/build.php                 # the integration app in build/app
vendor/bin/phpunit -c workbench/phpunit.generate.xml   # generate into it
vendor/bin/phpunit -c workbench/phpunit.xml            # run the generated tests
vendor/bin/pint --test --config pint.json build/app/app build/app/database/factories build/app/tests/Feature build/app/routes
pnpm typecheck:app && pnpm format:app   # the generated React pages
```
