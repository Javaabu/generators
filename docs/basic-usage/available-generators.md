---
title: Available generators
sidebar_position: 2
---

The available generators and what they generate are given below:

```bash
# creates database/factories/ProductFactory.php
php artisan generate:factory products --create

# adds permissions to database/seeders/PermissionsSeeder.php
php artisan generate:permissions products --create

# creates app/Models/Product.php
# adds subject type and morph map to app/Providers/AppServiceProvider.php
php artisan generate:model products --create

# creates app/Policies/ProductPolicy.php
php artisan generate:policy products --create

# creates app/Http/Requests/ProductsRequest.php
php artisan generate:request products --create

# creates app/Exports/ProductsExport.php
php artisan generate:export products --create

# creates app/Http/Controllers/Admin/ProductsController.php
php artisan generate:controller products --create

# adds routes to routes/admin.php
php artisan generate:routes products --create

# creates app/Http/Controllers/Api/ProductsController.php
# adds api routes to api.php routes file
php artisan generate:api_controller products --create

# creates tests/Feature/Controllers/Api/ProductsControllerTest.php
php artisan generate:api_test products --create

# creates app/Http/Controllers/Api/ProductsController.php
# adds api routes to api.php routes file
# creates tests/Feature/Controllers/Api/ProductsControllerTest.php
php artisan generate:api products --create {--except=<commands to skip>}

# creates tests/Feature/Controllers/Admin/ProductsControllerTest.php
php artisan generate:test products --create

# creates resources/views/admin/products/products.blade.php
# creates resources/views/admin/products/_actions.blade.php
# creates resources/views/admin/products/_form.blade.php
# creates resources/views/admin/products/create.blade.php
# creates resources/views/admin/products/edit.blade.php
# creates resources/views/admin/products/_details.blade.php
# creates resources/views/admin/products/show.blade.php
# creates resources/views/admin/products/_bulk.blade.php
# creates resources/views/admin/products/_list.blade.php
# creates resources/views/admin/products/_table.blade.php
# creates resources/views/admin/products/index.blade.php
# adds links to app/Menus/AdminSidebar.php
php artisan generate:views products --create

# creates all files specified above except API controller
# To skip a command, give comma separated list of commands
php artisan generate:all products --create {--except=<commands to skip>}

# To generate all except controllers and tests
php artisan generate:all products --create --except=controller,test
```
