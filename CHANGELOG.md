# Changelog

All notable changes to `javaabu/generators` are documented in this file.

## 2.0.0 - unreleased

A new major for the Javaabu next-gen packages. See [docs/upgrading.md](docs/upgrading.md).

- Generated code uses javaabu/stencil forms, javaabu/filters, javaabu/bulk-actions, javaabu/permissions policies and permissions, and Blade (javaabu/forms v2) or Inertia React (@javaabu/forms) pages.
- The schema is read through Laravel's schema builder: MySQL, MariaDB, PostgreSQL and SQLite.
- Relations from the schema: belongsTo, hasMany and many-to-many through pivot tables.
- `--index`, `--form`, `--show` and `--interactive` choose the fields of each page.
- `--parent` nests a resource under another.
- `--media` adds javaabu/mediapicker media collections.
- Optional javaabu/translatable support.
- `generate:auth` creates a javaabu/auth v3 user type and the admin pages of its users.
- New `generate:filters`, `generate:bulk-actions` and `generate:form`.
- The generated tests pass as generated; CI generates a whole admin into a Laravel app and runs them.
- Dhivehi translations of the commands and of the generic phrases of the pages.
- Removed: `generate:request`, the API generators and the per-controller auth generators.
- Requires PHP 8.4+ and Laravel 12+.
