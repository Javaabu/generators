<?php

namespace Javaabu\Generators\Tests\Feature\Commands;

use Illuminate\Filesystem\Filesystem;
use Javaabu\Generators\Exceptions\ColumnDoesNotExistException;
use Javaabu\Generators\Exceptions\MultipleTablesSuppliedException;
use Javaabu\Generators\Exceptions\TableDoesNotExistException;
use Javaabu\Generators\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GenerateCommandsTest extends TestCase
{
    protected string $path;

    protected function setUp(): void
    {
        parent::setUp();

        // write into a scratch app directory
        $this->path = sys_get_temp_dir() . '/generators-' . uniqid();

        (new Filesystem)->ensureDirectoryExists("{$this->path}/app/Providers");
        (new Filesystem)->ensureDirectoryExists("{$this->path}/routes");

        file_put_contents("{$this->path}/app/Providers/AppServiceProvider.php", <<<'PHP'
        <?php

        namespace App\Providers;

        use Illuminate\Support\ServiceProvider;

        class AppServiceProvider extends ServiceProvider
        {
            public function boot(): void
            {
                //
            }
        }

        PHP);

        file_put_contents("{$this->path}/routes/web.php", "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n");

        $this->app->setBasePath($this->path);
        $this->app->useAppPath("{$this->path}/app");
        $this->app->useDatabasePath("{$this->path}/database");
        $this->app->useLangPath("{$this->path}/lang");
    }

    protected function tearDown(): void
    {
        $path = $this->path;

        parent::tearDown();

        (new Filesystem)->deleteDirectory($path);
    }

    #[Test]
    public function it_prints_the_code_without_create(): void
    {
        $this->artisan('generate:model', ['table' => 'categories'])
            ->expectsOutputToContain('// app/Models/Category.php')
            ->expectsOutputToContain('class Category extends Model')
            ->assertSuccessful();

        $this->assertFileDoesNotExist("{$this->path}/app/Models/Category.php");
    }

    #[Test]
    public function it_writes_the_files_with_create(): void
    {
        $this->artisan('generate:all', ['table' => 'products', '--create' => true])->assertSuccessful();

        foreach ([
            'app/Models/Product.php',
            'database/factories/ProductFactory.php',
            'app/Policies/ProductPolicy.php',
            'app/Filters/ProductFilters.php',
            'app/BulkActions/ProductBulkActions.php',
            'app/Stencil/ProductForm.php',
            'app/Http/Controllers/Admin/ProductsController.php',
            'routes/admin.php',
            'resources/views/admin/products/index.blade.php',
            'resources/views/admin/products/show.blade.php',
            'tests/Feature/Admin/ProductsControllerTest.php',
            'lang/dv.json',
        ] as $file) {
            $this->assertFileExists("{$this->path}/{$file}");
        }

        $provider = file_get_contents("{$this->path}/app/Providers/AppServiceProvider.php");

        $this->assertStringContainsString('use Javaabu\\Permissions\\Permissions;', $provider);
        $this->assertStringContainsString("Permissions::resource('products', label: 'Products');", $provider);

        $routes = file_get_contents("{$this->path}/routes/admin.php");

        $this->assertStringContainsString("Route::resource('products', ProductsController::class)->withTrashed(['show']);", $routes);
        $this->assertStringContainsString("Route::bulkActions('products', ProductBulkActions::class);", $routes);
        $this->assertStringContainsString("require __DIR__ . '/admin.php';", file_get_contents("{$this->path}/routes/web.php"));

        $this->assertEquals('ހޯދާ', json_decode(file_get_contents("{$this->path}/lang/dv.json"), true)['Search']);
    }

    #[Test]
    public function it_adds_to_merged_files_once_and_keeps_existing_files(): void
    {
        $this->artisan('generate:all', ['table' => 'categories', '--create' => true])->assertSuccessful();

        file_put_contents("{$this->path}/app/Models/Category.php", '<?php // edited');

        $this->artisan('generate:all', ['table' => 'categories', '--create' => true])
            ->expectsOutputToContain('exists')
            ->assertSuccessful();

        $this->assertEquals('<?php // edited', file_get_contents("{$this->path}/app/Models/Category.php"));
        $this->assertEquals(1, substr_count(file_get_contents("{$this->path}/routes/admin.php"), "Route::resource('categories'"));
        $this->assertEquals(1, substr_count(file_get_contents("{$this->path}/app/Providers/AppServiceProvider.php"), "Permissions::resource('categories'"));
    }

    #[Test]
    public function it_overwrites_with_force(): void
    {
        $this->artisan('generate:model', ['table' => 'categories', '--create' => true])->assertSuccessful();

        file_put_contents("{$this->path}/app/Models/Category.php", '<?php // edited');

        $this->artisan('generate:model', ['table' => 'categories', '--create' => true, '--force' => true])->assertSuccessful();

        $this->assertStringContainsString('class Category extends Model', file_get_contents("{$this->path}/app/Models/Category.php"));
    }

    #[Test]
    public function it_skips_parts_with_except(): void
    {
        $this->artisan('generate:all', ['table' => 'categories', '--create' => true, '--except' => 'views,test,routes'])->assertSuccessful();

        $this->assertFileExists("{$this->path}/app/Models/Category.php");
        $this->assertFileDoesNotExist("{$this->path}/resources/views/admin/categories/index.blade.php");
        $this->assertFileDoesNotExist("{$this->path}/tests/Feature/Admin/CategoriesControllerTest.php");
        $this->assertFileDoesNotExist("{$this->path}/routes/admin.php");
    }

    #[Test]
    public function it_generates_inertia_pages_with_the_views_option(): void
    {
        $this->artisan('generate:views', ['table' => 'categories', '--create' => true, '--views' => 'inertia'])->assertSuccessful();

        $this->assertFileExists("{$this->path}/resources/js/pages/admin/categories/Index.tsx");
        $this->assertFileExists("{$this->path}/resources/js/pages/admin/categories/types.ts");
        $this->assertFileExists("{$this->path}/app/Http/Resources/Admin/CategoryResource.php");
    }

    #[Test]
    public function it_chooses_the_fields_with_options(): void
    {
        $this->artisan('generate:form', ['table' => 'products', '--form' => 'name,price,tags'])
            ->expectsOutputToContain("TextInput::make('name')")
            ->expectsOutputToContain("Select::make('tags')")
            ->doesntExpectOutputToContain("TextInput::make('stock')")
            ->assertSuccessful();
    }

    #[Test]
    public function it_asks_for_the_fields_when_interactive(): void
    {
        $this->artisan('generate:form', ['table' => 'categories', '--interactive' => true])
            ->expectsQuestion('Which fields go in the index table?', ['name'])
            ->expectsQuestion('Which fields go in the form?', ['name'])
            ->expectsQuestion('Which fields go on the show page?', ['name', 'slug'])
            ->expectsQuestion('Media collections (leave empty for none)', '')
            ->expectsQuestion('Which views?', 'blade')
            ->expectsOutputToContain("TextInput::make('name')")
            ->doesntExpectOutputToContain("TextInput::make('slug')")
            ->assertSuccessful();
    }

    #[Test]
    public function it_writes_a_user_type_migration_when_the_table_does_not_exist(): void
    {
        $this->artisan('generate:auth', ['table' => 'vendors', '--migration' => true, '--create' => true])
            ->expectsOutputToContain('php artisan migrate')
            ->assertSuccessful();

        $migrations = glob("{$this->path}/database/migrations/*_create_vendors_table.php");

        $this->assertCount(1, $migrations);
        $this->assertStringContainsString('UserSchema::columns($table);', file_get_contents($migrations[0]));
    }

    #[Test]
    public function it_generates_a_user_type(): void
    {
        (new Filesystem)->ensureDirectoryExists("{$this->path}/config");
        copy(dirname(__DIR__, 3) . '/vendor/orchestra/testbench-core/laravel/config/auth.php', "{$this->path}/config/auth.php");
        $this->app->useConfigPath("{$this->path}/config");

        $this->artisan('generate:auth', ['table' => 'public_users', '--type' => 'member', '--guard' => 'web_member', '--create' => true])
            ->assertSuccessful();

        $model = file_get_contents("{$this->path}/app/Models/PublicUser.php");

        $this->assertStringContainsString('class PublicUser extends User', $model);
        $this->assertStringContainsString('use Javaabu\\Auth\\Models\\User;', $model);
        $this->assertStringNotContainsString('use SoftDeletes;', $model);

        $config = file_get_contents("{$this->path}/config/auth.php");

        $this->assertStringContainsString("'web_member' => [\n            'driver' => 'session',\n            'provider' => 'public_users',", $config);
        $this->assertStringContainsString("'model' => PublicUser::class,", $config);
        $this->assertStringContainsString("'members' => [\n            'provider' => 'public_users',", $config);

        $provider = file_get_contents("{$this->path}/app/Providers/AppServiceProvider.php");

        $this->assertStringContainsString("UserTypes::register('member', [", $provider);
        $this->assertStringContainsString("'guard' => 'web_member',", $provider);

        $this->assertStringContainsString("Route::authRoutes('member', ['prefix' => 'member']);", file_get_contents("{$this->path}/routes/web.php"));
        $this->assertStringContainsString("Route::userAccountActions('member', ['prefix' => 'public-users']);", file_get_contents("{$this->path}/routes/admin.php"));

        $form = file_get_contents("{$this->path}/app/Stencil/PublicUserForm.php");

        $this->assertStringContainsString('->password()', $form);
        $this->assertStringContainsString('->options(UserStatuses::class)', $form);
        $this->assertStringNotContainsString("'remember_token'", $form);
        $this->assertStringNotContainsString("'login_attempts'", $form);

        // running it again changes nothing
        $this->artisan('generate:auth', ['table' => 'public_users', '--type' => 'member', '--guard' => 'web_member', '--create' => true])->assertSuccessful();

        $this->assertEquals(1, substr_count(file_get_contents("{$this->path}/config/auth.php"), "'web_member' =>"));
        $this->assertEquals(1, substr_count(file_get_contents("{$this->path}/app/Providers/AppServiceProvider.php"), "UserTypes::register('member'"));
    }

    #[Test]
    public function it_prints_a_user_type_without_create(): void
    {
        $this->artisan('generate:auth', ['table' => 'customers', '--except' => 'views,test'])
            ->expectsOutputToContain("UserTypes::register('customer', [")
            ->expectsOutputToContain("Route::authRoutes('customer', ['prefix' => 'customer']);")
            ->assertSuccessful();
    }

    #[Test]
    public function it_rejects_several_tables(): void
    {
        $this->expectException(MultipleTablesSuppliedException::class);

        $this->artisan('generate:model', ['table' => 'products,categories']);
    }

    #[Test]
    public function it_rejects_a_missing_table(): void
    {
        $this->expectException(TableDoesNotExistException::class);

        $this->artisan('generate:model', ['table' => 'missing']);
    }

    #[Test]
    public function it_rejects_missing_columns(): void
    {
        $this->expectException(ColumnDoesNotExistException::class);

        $this->artisan('generate:model', ['table' => 'products', '--columns' => 'name,missing']);
    }

    #[Test]
    public function it_rejects_unknown_fields(): void
    {
        $this->expectException(ColumnDoesNotExistException::class);

        $this->artisan('generate:form', ['table' => 'products', '--form' => 'name,missing']);
    }
}
