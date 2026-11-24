---
title: Requirements
sidebar_position: 1.1
---

- PHP 8.4 or 8.5
- Laravel 12 or 13
- MySQL 8, MariaDB 10.11+, PostgreSQL 14+ or SQLite 3.35+ (see [Databases and column types](advanced-usage/databases-and-column-types.md))

The generators themselves only need Laravel. The code they write uses these packages, which your app installs:

| Package | For |
| --- | --- |
| [javaabu/forms](https://github.com/Javaabu/forms) v2 | Blade pages |
| [javaabu/stencil](https://github.com/Javaabu/stencil) | Forms and their validation |
| [javaabu/filters](https://github.com/Javaabu/filters) | Index filters and sorts |
| [javaabu/bulk-actions](https://github.com/Javaabu/bulk-actions) | Bulk actions |
| [javaabu/permissions](https://github.com/Javaabu/permissions) v3 | Policies and permissions |
| [inertiajs/inertia-laravel](https://github.com/inertiajs/inertia-laravel), [@javaabu/forms](https://github.com/Javaabu/react-forms) and [@javaabu/i18n](https://github.com/Javaabu/i18n) | Inertia React pages (`--views=inertia`) |
| [javaabu/mediapicker](https://github.com/Javaabu/mediapicker) | Media collections (`--media`) |
| [javaabu/translatable](https://github.com/Javaabu/translatable) v3 | Translatable models (optional) |
| [javaabu/auth](https://github.com/Javaabu/auth) v3 | User types (`generate:auth`) |
| [javaabu/exports](https://github.com/Javaabu/exports) v2 | Exports (`generate:export`) |

The [Javaabu Laravel skeletons](https://github.com/Javaabu) come with all of them.
