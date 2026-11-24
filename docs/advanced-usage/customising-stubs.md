---
title: Customising stubs
sidebar_position: 2
---

The skeleton of each generated file is a stub. Publish them to change what the generators write:

```bash
php artisan vendor:publish --tag=generators-stubs
```

They go to `stubs/vendor/generators/`. A published stub is used instead of the package's; delete it to go back.

| Stub | File |
| --- | --- |
| `model/Model.stub` | The model |
| `factory/Factory.stub` | The factory |
| `policy/Policy.stub` | The policy |
| `filters/Filters.stub` | The filters |
| `bulk-actions/BulkActions.stub` | The bulk actions |
| `form/Form.stub` | The stencil form |
| `controller/Controller.stub` | The controller (its methods are generated) |
| `resource/Resource.stub` | The JSON resource of Inertia pages |
| `routes/admin.stub` | A new admin routes file |
| `views/blade/index.blade.stub`, `create`, `edit`, `show` | The Blade pages |
| `test/ControllerTest.stub` | The tests (the test methods are generated) |
| `export/Export.stub` | The export |
| `auth/migration.stub` | A user type's migration |

# Placeholders

Stubs have two kinds of placeholders:

- **`{{ name }}`, with spaces:** a value or a block of code the generator gives, such as `{{ namespace }}`, `{{ imports }}`, `{{ fillable }}` or `{{ filters }}`. A block alone on its line is indented to that line, and the line disappears when the block is empty. Keep the placeholders you don't change.
- **`{{pluralStudly}}`, without spaces:** the cases of the table name, from javaabu/generator-helpers: `{{pluralStudly}}` (Products), `{{singularSnake}}` (product), `{{pluralKebab}}` (products), `{{pluralLower}}`, `{{singularTitle}}` and the rest.

```php
// stubs/vendor/generators/model/Model.stub
<?php

namespace {{ namespace }};

use App\Models\Concerns\HasAdminLinks;
{{ imports }}

class {{ class }} extends {{ base }}{{ implements }}
{
    {{ traits }}
    use HasAdminLinks;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        {{ fillable }}
    ];

    {{ body }}
}
```

The Inertia pages are written in code rather than from stubs. To change them, generate them and edit them, or [extend the generators](extending.md).
