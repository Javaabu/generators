---
title: Dhivehi and RTL
sidebar_position: 4
---

# The generated code

- **Translatable strings.** Every string the generated pages and controllers show goes through `__()`: labels, headings, buttons, messages, options of database enums. Add their Dhivehi to `lang/dv.json`.
- **Generic phrases.** `generate:all` adds Dhivehi for the generic phrases it uses to your `lang/dv.json`, without changing the ones you have: Search, Edit, View, Delete, Restore, Created at and the rest. The phrases of each resource (its name, "New product", "No products yet.") are yours to translate.
- **Right to left.** The pages use logical CSS utilities (`ms-`, `ps-`, `gap-`) and no left or right, so they work right to left. The forms, filters and tables are javaabu/forms' and @javaabu/forms' components, which support RTL and Thaana input.
- **Thaana in the tests.** The generated tests save Thaana text (`'ރަސްމީ'`) to check that it round-trips.

# The commands

The generators' own messages and prompts are in `lang/en` and `lang/dv` of the package. Run the commands with `APP_LOCALE=dv` to see them in Dhivehi.

# To review

These translations were written without a native speaker's review. Please check them:

| English | Dhivehi |
| --- | --- |
| Search | ހޯދާ |
| Created at | އުފެއްދި ތާރީޚް |
| Updated at | ބަދަލުކުރި ތާރީޚް |
| Edit | ބަދަލުކުރޭ |
| View | ބަލާ |
| Delete | ފޮހެލާ |
| Restore | އަނބުރާ ގެނޭ |
| Delete permanently | ދާއިމީކޮށް ފޮހެލާ |
| Media | މީޑިއާ |
| Breadcrumb | ނެވިގޭޝަން |
| ID | އައިޑީ |
| The console messages | `lang/dv/generators.php` |
