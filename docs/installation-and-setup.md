---
title: Installation & Setup
sidebar_position: 1.2
---

# Installing

The generators are a development tool:

```bash
composer require javaabu/generators --dev
```

Until the next-gen packages are on Packagist, add their repositories to the app's `composer.json`. Then require `"javaabu/generators": "^2.0@dev"`. The package's service provider is discovered automatically.

# Getting the app ready

The generated code expects the packages it uses (see [Requirements](requirements.md)) and these few things in the app:

1. **A model for the admins** with javaabu/permissions' `HasRoles` trait and a factory. The default is `App\Models\User` on the default guard; set [`generators.admin`](advanced-usage/configuration.md) for another model or guard, such as a javaabu/auth `admin` user type.
2. **A layout** for the Blade pages: `resources/views/layouts/admin.blade.php` with a `content` section, or set `generators.blade.layout` and `generators.blade.section`. It shows `session('status')`, where the controllers put their messages.
3. **For Inertia React pages**:
   - inertia-laravel, javaabu/manifest's Vite plugin, and the `FormsProvider` in `resources/js/app.tsx`, as in [@javaabu/forms' setup](https://github.com/Javaabu/react-forms)
   - `__()` from `@javaabu/i18n`, through [javaabu/js-localization](https://github.com/Javaabu/js-localization)
   - pages resolved from `resources/js/pages` with `resolvePage(name, import.meta.glob("./pages/**/*.tsx"), { base: "./pages/" })`
4. **The routes file**: the generators write `routes/admin.php` the first time, and load it from `routes/web.php`.
5. **Permissions**: run `php artisan permissions:sync` after generating, and on every deploy.

# Publishing

```bash
# the config: views, the admin area, namespaces, schema options
php artisan vendor:publish --tag=generators-config

# the stubs, to change what the generators write
php artisan vendor:publish --tag=generators-stubs
```

See [Configuration](advanced-usage/configuration.md) and [Customising stubs](advanced-usage/customising-stubs.md).
