<?php

namespace Javaabu\Generators\Tests\Unit\Generators\Auth;

use Illuminate\Support\Facades\Config;
use Javaabu\Generators\FieldTypes\BooleanField;
use Javaabu\Generators\FieldTypes\DateField;
use Javaabu\Generators\FieldTypes\DateTimeField;
use Javaabu\Generators\FieldTypes\DecimalField;
use Javaabu\Generators\FieldTypes\EnumField;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\FieldTypes\IntegerField;
use Javaabu\Generators\FieldTypes\JsonField;
use Javaabu\Generators\FieldTypes\StringField;
use Javaabu\Generators\FieldTypes\TextField;
use Javaabu\Generators\FieldTypes\TimeField;
use Javaabu\Generators\FieldTypes\YearField;
use Javaabu\Generators\Generators\Auth\BaseAuthGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class MockAuthBaseGenerator extends BaseAuthGenerator
{

    public function render(): string
    {
        return '';
    }
}

class BaseAuthGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->deleteFile($this->app->databasePath('seeders/DefaultUsersSeeder.php'));
    }

    protected function tearDown(): void
    {
        $this->deleteFile($this->app->databasePath('seeders/DefaultUsersSeeder.php'));

        parent::tearDown();
    }

    public function test_it_can_determine_if_the_table_has_additional_attributes(): void
    {
        $generator = new MockAuthBaseGenerator('customers');

        $this->assertTrue($generator->hasAdditionalAttributes());

        $generator = new MockAuthBaseGenerator('public_users');

        $this->assertFalse($generator->hasAdditionalAttributes());
    }

    public function test_it_can_determine_if_email_is_required(): void
    {
        $generator = new MockAuthBaseGenerator('customers');

        $this->assertTrue($generator->requiresEmail());

        $generator = new MockAuthBaseGenerator('public_users');

        $this->assertFalse($generator->requiresEmail());
    }

    public function test_it_can_determine_if_password_is_required(): void
    {
        $generator = new MockAuthBaseGenerator('customers');

        $this->assertTrue($generator->requiresPassword());

        $generator = new MockAuthBaseGenerator('public_users');

        $this->assertFalse($generator->requiresPassword());
    }

    public function test_it_can_extract_default_password_from_default_users_seeder(): void
    {
        $seeder_path = $this->app->databasePath('seeders/DefaultUsersSeeder.php');

        $this->copyFile($this->getTestStubPath('seeders/DefaultUsersSeeder.php'), $seeder_path);

        $generator = new MockAuthBaseGenerator('customers');

        $this->assertEquals('RandomPassword12345', $generator->getDefaultPassword());
    }

    public function test_it_can_generate_default_password_from_table_name(): void
    {
        $generator = new MockAuthBaseGenerator('customers');

        $this->assertEquals('Customer@123456', $generator->getDefaultPassword());

    }

    public function test_it_can_generate_auth_name_from_table_name(): void
    {
        $generator = new MockAuthBaseGenerator('customers');

        $this->assertEquals('customer', $generator->getAuthName());
        $this->assertEquals('Customer', $generator->getNamespace());
        $this->assertEquals('web_customer', $generator->getWebGuardName());
        $this->assertEquals('api_customer', $generator->getApiGuardName());

    }

    public function test_it_can_generate_custom_auth_name(): void
    {
        $generator = new MockAuthBaseGenerator('customers', auth_name: 'Portal');

        $this->assertEquals('portal', $generator->getAuthName());
        $this->assertEquals('Portal', $generator->getNamespace());
        $this->assertEquals('web_customer', $generator->getWebGuardName());
        $this->assertEquals('api_customer', $generator->getApiGuardName());

    }

    public function test_it_can_remove_auth_columns_even_if_no_columns_are_provided(): void
    {
        $generator = new MockAuthBaseGenerator('customers');

        $skip_columns = [
            'name',
            'email',
            'email_verified_at',
            'password',
            'remember_token',
            'last_login_at',
            'login_attempts',
            'require_password_update',
            'status',
            'new_email',
            'created_at',
            'updated_at',
            'deleted_at'
        ];

        foreach ($skip_columns as $key) {
            $this->assertArrayNotHasKey($key, $generator->getFields());
        }

        $extra_columns = [
            'designation',
            'address',
            'on_sale',
            'expire_at',
        ];

        foreach ($extra_columns as $key) {
            $this->assertArrayHasKey($key, $generator->getFields());
        }
    }

    public function test_it_can_remove_auth_columns_from_the_columns_that_are_provided(): void
    {
        $extra_columns = [
            'address',
            'on_sale',
        ];

        $generator = new MockAuthBaseGenerator('customers', $extra_columns);

        $skip_columns = [
            'designation',
            'expire_at',
        ];

        foreach ($skip_columns as $key) {
            $this->assertArrayNotHasKey($key, $generator->getFields());
        }

        foreach ($extra_columns as $key) {
            $this->assertArrayHasKey($key, $generator->getFields());
        }
    }

    public function test_it_can_determine_what_is_an_auth_column(): void
    {
        $generator = new MockAuthBaseGenerator('customers');

        $this->assertTrue($generator->isAuthColumn('name'));
        $this->assertFalse($generator->isAuthColumn('designation'));

        Config::set('generators.auth_skip_columns', ['email']);

        $this->assertFalse($generator->isAuthColumn('name'));
    }


}
