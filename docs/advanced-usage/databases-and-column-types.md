---
title: Databases and column types
sidebar_position: 5
---

The generators read the schema with Laravel's schema builder (`Schema::getColumns()`, `getForeignKeys()`, `getIndexes()`), so they work on MySQL, MariaDB, PostgreSQL and SQLite. CI runs the schema tests on all four.

# Column types

| Column | Cast | Form field | Filter | Factory |
| --- | --- | --- | --- | --- |
| `string`, `char`, `uuid` | | `TextInput` with `maxLength()`; `email()`, `tel()` or `url()` for columns named so | in the search | `text()`, `safeEmail()`, ... |
| `text`, `mediumText`, `longText` | | `Textarea` | in the search | `paragraph()` |
| `integer` types | `integer` | `TextInput::integer()` with the type's range | range (on the index) | `numberBetween()` |
| `decimal`, `float`, `double` | `decimal:2` | `TextInput::numeric()->decimals()` | range (on the index) | `randomFloat()` |
| `boolean`, MySQL `tinyint(1)` | `boolean` | `Checkbox` | yes / no | `boolean()` |
| `enum`, or a string with an enum class | the enum class | `Select` with options | select | a random option |
| `date` | `date` | `DatePicker` | date range (on the index) | `date()` |
| `dateTime`, `timestamp` | `datetime` | `DateTimePicker` | date range (on the index) | `dateTimeBetween()` |
| `time` | | `TimePicker` | | `time('H:i')` |
| `year` | `integer` | `TextInput::integer()`, 1901 to 2155 | range (on the index) | `numberBetween()` |
| `json` | `array` | `Select::multiple()->tags()` | | `words()` |
| foreign key | | `Select::relationship()->searchable()` | select | the related factory |

Binary and other columns are skipped.

# What each database keeps

SQLite stores less about a column than the others:

| | MySQL, MariaDB | PostgreSQL | SQLite |
| --- | --- | --- | --- |
| String lengths | ✓ | ✓ | No: 255 is assumed |
| Decimal precision | ✓ | ✓ | No: 10,2 is assumed |
| Unsigned | ✓ | No | No |
| Enum options | ✓ (type) | ✓ (check constraint) | ✓ (check constraint) |
| Column comments (enum classes) | ✓ | ✓ | No: use `generators.enums` |
| `year` | ✓ | as integer | as integer |
| `json` | ✓ (MariaDB: as text) | ✓ | ✓ |

Generate from the database you deploy to, or from a MySQL or PostgreSQL copy, for the most exact code.
