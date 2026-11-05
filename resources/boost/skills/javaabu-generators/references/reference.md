# javaabu/generators reference

## Commands

Every command takes `{table}` and the options below. Without `--create` it prints the code.

| Command | Writes |
| --- | --- |
| `generate:all` | Every part below except the export |
| `generate:model` | `app/Models/{Model}.php` |
| `generate:factory` | `database/factories/{Model}Factory.php` |
| `generate:policy` | `app/Policies/{Model}Policy.php` (`ResourcePolicy`, `$resource = '{plural}'`, `$guard` when `generators.admin.guard` is set) |
| `generate:permissions` | `Permissions::resource('{plural}', label: '...')` in `AppServiceProvider::boot()` |
| `generate:filters` | `app/Filters/{Model}Filters.php` |
| `generate:bulk-actions` | `app/BulkActions/{Model}BulkActions.php` |
| `generate:form` | `app/Stencil/{Model}Form.php` |
| `generate:controller` | `app/Http/Controllers/Admin/{Models}Controller.php` |
| `generate:routes` | Routes in `routes/admin.php`, above `// generators: routes` |
| `generate:views` | Blade views, or (`--views=inertia`) pages, `types.ts` and `app/Http/Resources/Admin/{Model}Resource.php` |
| `generate:test` | `tests/Feature/Admin/{Models}ControllerTest.php` |
| `generate:export` | `app/Exports/{Models}Export.php` (javaabu/exports `ModelExport`) |
| `generate:auth` | A javaabu/auth user type: model, `config/auth.php`, `UserTypes::register()`, `Route::authRoutes()`, `Route::userAccountActions()`, and the admin pages. `--type`, `--guard`, `--migration` |
| `generate:rollback` | `git reset`, `git checkout .`, `git clean -fd` (every uncommitted change) |

## Options

| Option | What |
| --- | --- |
| `--create`, `-c` | Write files |
| `--force`, `-f` | Overwrite existing files |
| `--columns=a,b` | Only these columns, in every part |
| `--index=a,b`, `--form=a,b`, `--show=a,b` | Fields per page; many-to-many relations by name |
| `--interactive`, `-i` | Prompts for the fields, parent, media and views |
| `--views=blade\|inertia` | Default `generators.views` |
| `--parent=table\|foreign_key\|relation` | Nested resource: `Route::resource('parents.children')->scoped()` |
| `--media=name[:type][:multiple],...` | Types: image, video, audio, document, archive |
| `--translatable[=a,b]` | Detect or choose translatable columns (needs `translations` and `lang` columns and javaabu/translatable) |
| `--except=part,...` | `generate:all`/`generate:auth`: model, factory, policy, permissions, filters, bulk-actions, form, controller, routes, views, test, translations (and config for `generate:auth`) |

## Column types

- string/char/uuid: `TextInput` with `maxLength`; `email()`, `tel()`, `url()` by name. They're searched.
- text: `Textarea`, searched.
- integers: `TextInput::integer()` with min/max; cast `integer`; number range filter on the index.
- decimal: `TextInput::numeric()->decimals()`; cast `decimal:n`.
- boolean (MySQL `tinyint(1)`): `Checkbox`; cast `boolean`; `TernaryFilter`.
- enum (type or check constraint), or an enum class from a comment or `generators.enums`: `Select::options()`; `SelectFilter`.
- date, datetime/timestamp, time: `DatePicker`, `DateTimePicker`, `TimePicker`; date casts; `DateRangeFilter`.
- year: `TextInput::integer()` 1901 to 2155.
- json: `Select::multiple()->tags()`, cast `array`.
- foreign key: `Select::relationship('category', 'name')->searchable()`, a `belongsTo`, a `SelectFilter`, eager loaded.
- pivot table: `belongsToMany` on both models, `Select::make('tags')->multiple()->relationship('tags', 'name')`.
- SQLite keeps no lengths, precision, unsigned or comments.

## Config (`config/generators.php`)

`views`, `connection`, `admin.namespace`, `admin.route_prefix`, `admin.route_name`, `admin.view_prefix`, `admin.page_prefix`, `admin.routes_file`, `admin.middleware`, `admin.guard`, `admin.user_model`, `blade.layout`, `blade.section`, `namespaces.*`, `tinyint1_to_bool`, `skip_columns`, `enums`, `name_fields`, `index_columns`, `ignored_tables`, `translatable` (`'auto'`|`false`), `auth.skip_columns`.

## The generated tests need

- `Tests\TestCase`
- the admin model with a factory and `HasRoles`
- `permissions:sync` (in their `setUp()`)
- the related tables' factories
- for Inertia: `inertia.testing.page_paths` with `resources/js/pages`

## Extending

- `Javaabu\Generators\Commands\GenerateCommand`: implement `generators(ResourceDefinition $resource): array`.
- `Javaabu\Generators\Generators\Generator`: implement `files(): array` of `GeneratedFile`.
- Bind a subclass of `Javaabu\Generators\Resource\ResourceResolver` and override `makeField()` to map columns to your own `Javaabu\Generators\Fields\Field` types.
- Publish stubs: `php artisan vendor:publish --tag=generators-stubs` (`stubs/vendor/generators`).
