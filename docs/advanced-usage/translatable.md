---
title: Translatable models
sidebar_position: 3
---

[javaabu/translatable](https://github.com/Javaabu/translatable) is optional. When it is installed, a table with its two columns gets a translatable model:

```php
$table->json('translations')->nullable();
$table->string('lang')->nullable()->index();
// or $table->jsonTranslatable();
```

```bash
php artisan generate:all articles --create
```

```php
// app/Models/Article.php
class Article extends Model implements JsonTranslatable
{
    use HasFactory;

    use IsJsonTranslatable;

    protected array $translatable = ['title', 'body'];
}
```

```php
// app/Stencil/ArticleForm.php
TextInput::make('title')
    ->label(__('Title'))
    ->required()
    ->maxLength(255)
    ->translatable(),
```

- **Which columns:** the string and text columns, except unique ones (slugs, codes) and emails, phones and URLs. Choose others with `--translatable=title,body`.
- **The form:** one input per active language, named `title_en`, `title_dv` and so on. The primary language is required, the others optional (javaabu/translatable's rules).
- **The tests:** they set up English and Dhivehi with translatable's `InteractsWithLanguages`, save a Dhivehi translation and check it.
- **Without the package:** the same table gives a plain model, and the translation columns are skipped. The generators only check `class_exists()` for translatable, so they don't need it installed.
- **Turning it off:** `'translatable' => false` in the config makes plain models even when the package is there.

`--translatable=title` on a table without the columns stops with the migration lines to add.
