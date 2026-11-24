---
title: AI assistants
sidebar_position: 1.25
---

The docs are plain Markdown, written for people and AI coding assistants alike. The package ships them, so they always match the version you have installed.

| File | What |
| --- | --- |
| `vendor/javaabu/generators/llms.txt` | The rules for using the generators, and every page |
| `vendor/javaabu/generators/docs/` | The pages |
| `vendor/javaabu/generators/resources/boost/` | The Laravel Boost skill and guideline |

# Laravel Boost

[Laravel Boost](https://github.com/laravel/boost) finds the skill and guideline in the Composer package by itself. Run this after installing or updating:

```bash
php artisan boost:update
```

The guideline tells the assistant to generate admin resources with the generators rather than writing them by hand. The skill (`javaabu-generators`) has the commands, options, what each writes, and the common mistakes. It loads when the assistant is asked to scaffold CRUD pages, resources or user types.

# Other assistants

Point them at `vendor/javaabu/generators/llms.txt`.
