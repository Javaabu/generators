# Generators

[![Tests](https://github.com/Javaabu/generators/actions/workflows/tests.yml/badge.svg?branch=next-gen)](https://github.com/Javaabu/generators/actions/workflows/tests.yml?query=branch%3Anext-gen)
[![codecov](https://codecov.io/gh/Javaabu/generators/branch/next-gen/graph/badge.svg)](https://codecov.io/gh/Javaabu/generators/tree/next-gen)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/generators.svg)](https://packagist.org/packages/javaabu/generators)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)

Generate a complete, tested Laravel admin resource from a database table, built on the Javaabu next-gen packages. You design the migration; the generators write the rest.

```bash
php artisan generate:all products --create
```

| Part | What's written |
| --- | --- |
| Model | Casts, and the relations found in the schema: belongsTo, hasMany, many-to-many through pivot tables |
| Factory, policy and permissions | A [javaabu/permissions](https://github.com/Javaabu/permissions) policy and permissions |
| Filters and sorts | [javaabu/filters](https://github.com/Javaabu/filters) |
| Bulk actions | [javaabu/bulk-actions](https://github.com/Javaabu/bulk-actions) |
| Form | A [javaabu/stencil](https://github.com/Javaabu/stencil) form, which also validates |
| Controller and routes | |
| Pages | Blade ([javaabu/forms](https://github.com/Javaabu/forms)) or Inertia React ([@javaabu/forms](https://github.com/Javaabu/react-forms)) |
| Feature tests | They pass as generated |

```bash
php artisan generate:all products --create --index=name,price --form=name,price,category_id,tags
php artisan generate:all reviews --create --parent=products --views=inertia
php artisan generate:all posts --create --media=featured_image:image,gallery:image:multiple
php artisan generate:auth customers --create        # a javaabu/auth user type and its admin pages
```

It reads MySQL, MariaDB, PostgreSQL and SQLite schemas. Translatable models with [javaabu/translatable](https://github.com/Javaabu/translatable) are supported but optional. Dhivehi and RTL are built in.

## Documentation

You'll find the documentation on [https://docs.javaabu.com/generators](https://docs.javaabu.com/generators), and in the [`docs`](docs) folder. Upgrading from v1? Read [`docs/upgrading.md`](docs/upgrading.md).

AI coding assistants can start from [`llms.txt`](llms.txt). The package ships a [Laravel Boost](https://github.com/laravel/boost) skill and guideline, which Boost finds by itself. See [AI assistants](docs/ai-assistants.md).

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving this package? Feel free to create an [issue](../../issues) on GitHub, we'll try to address it as soon as possible.

If you've found a bug regarding security please mail [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.

## Testing

```bash
composer test
composer lint

# generate a whole admin into a Laravel app and run its tests
php workbench/build.php
vendor/bin/phpunit -c workbench/phpunit.generate.xml
vendor/bin/phpunit -c workbench/phpunit.xml
pnpm install && pnpm typecheck:app && pnpm format:app
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email [info@javaabu.com](mailto:info@javaabu.com) instead of using the issue tracker.

## Credits

- [Javaabu Pvt. Ltd.](https://github.com/javaabu)
- [Arushad Ahmed (@dash8x)](http://arushad.org)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
