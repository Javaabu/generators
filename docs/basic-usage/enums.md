---
title: Enums
sidebar_position: 5
---

A column with a fixed set of values becomes a select in the form, a select filter and a random value in the factory.

# Database enums

```php
$table->enum('status', ['draft', 'published']);
```

The options come from the column. MySQL and MariaDB keep them in the type; PostgreSQL and SQLite keep them in a check constraint, which the generators read. The labels are translated:

```php
Select::make('status')
    ->label(__('Status'))
    ->required()
    ->options(['draft' => __('Draft'), 'published' => __('Published')]),
```

# Enum classes

To use a PHP enum, name it in the column's comment:

```php
$table->string('status')->comment('enum:App\Enums\OrderStatuses');
```

Or in `config/generators.php`, which works on every database, SQLite included:

```php
'enums' => [
    'orders.status' => App\Enums\OrderStatuses::class,
],
```

The model casts the column to the enum. The form and the filters take the options from the enum, labelled by its `labels()` or `getLabel()`. The factory picks a random case, and the tests use the cases:

```php
'status' => OrderStatuses::class,                                // casts()
Select::make('status')->options(OrderStatuses::class),           // the form
'status' => fake()->randomElement(OrderStatuses::cases()),       // the factory
```

The enum should be backed, and have at least two cases for the update test to change it.
