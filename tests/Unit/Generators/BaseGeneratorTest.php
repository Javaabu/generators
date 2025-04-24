<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Illuminate\Support\Facades\Artisan;
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
use Javaabu\Generators\Generators\BaseGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Generators\ExportGenerator;
use Javaabu\Generators\Tests\TestCase;

class MockBaseGenerator extends BaseGenerator
{
    public function render(): string
    {
        return '';
    }
}

class BaseGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('vendor:publish', [
            '--provider' => 'Javaabu\\Generators\\GeneratorsServiceProvider',
            '--tag' => 'generators-stubs',
        ]);

        // setup skeleton service provider
        $this->copyFile(
            $this->getTestStubPath('Exports/ModelExport.stub'),
            $this->app->basePath('stubs/vendor/generators/Exports/ModelExport.stub')
        );
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->app->basePath('stubs'));

        parent::tearDown();
    }

    /** @test */
    public function it_can_generate_from_custom_stubs(): void
    {
        $export_generator = new ExportGenerator('products');

        $expected_content = $this->getTestStubContents('Exports/ProductsExport.php');
        $actual_content = $export_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_determine_if_the_model_has_any_fillable_fields(): void
    {
        $generator = new MockBaseGenerator('products');
        $this->assertTrue($generator->hasAnyFillable());

        $generator = new MockBaseGenerator('all_foreigns');
        $this->assertFalse($generator->hasAnyFillable());
    }

    /** @test */
    public function it_can_determine_the_name_field(): void
    {
        $generator = new MockBaseGenerator('products');
        $this->assertEquals('name', $generator->getNameField());

        $generator = new MockBaseGenerator('orders');
        $this->assertEquals('order_no', $generator->getNameField());

        $generator = new MockBaseGenerator('posts');
        $this->assertEquals('title', $generator->getNameField());

        $generator = new MockBaseGenerator('payments');
        $this->assertEquals('id', $generator->getNameField());

        $generator = new MockBaseGenerator('certificates');
        $this->assertEquals('title', $generator->getNameField());
    }

    /** @test */
    public function it_can_determine_the_type_from_attribute(): void
    {
        $generator = new MockBaseGenerator('products');

        $this->assertInstanceOf(StringField::class, $generator->getField('name'));
        $this->assertInstanceOf(StringField::class, $generator->getField('address'));
        $this->assertInstanceOf(TextField::class, $generator->getField('description'));
        $this->assertInstanceOf(DecimalField::class, $generator->getField('price'));
        $this->assertInstanceOf(IntegerField::class, $generator->getField('stock'));
        $this->assertInstanceOf(BooleanField::class, $generator->getField('on_sale'));
        $this->assertInstanceOf(JsonField::class, $generator->getField('features'));
        $this->assertInstanceOf(EnumField::class, $generator->getField('status'));
        $this->assertInstanceOf(ForeignKeyField::class, $generator->getField('category_id'));
        $this->assertInstanceOf(DateTimeField::class, $generator->getField('published_at'));
        $this->assertInstanceOf(DateField::class, $generator->getField('released_on'));
        $this->assertInstanceOf(TimeField::class, $generator->getField('sale_time'));
        $this->assertInstanceOf(YearField::class, $generator->getField('manufactured_year'));
    }

    /** @test */
    public function it_can_determine_the_label_from_the_attribute(): void
    {
        $generator = new MockBaseGenerator('products');

        $this->assertEquals('Name', $generator->getField('name')->getLabel());
        $this->assertEquals('Address', $generator->getField('address')->getLabel());
        $this->assertEquals('Description', $generator->getField('description')->getLabel());
        $this->assertEquals('Price', $generator->getField('price')->getLabel());
        $this->assertEquals('Stock', $generator->getField('stock')->getLabel());
        $this->assertEquals('On Sale', $generator->getField('on_sale')->getLabel());
        $this->assertEquals('Features', $generator->getField('features')->getLabel());
        $this->assertEquals('Status', $generator->getField('status')->getLabel());
        $this->assertEquals('Category', $generator->getField('category_id')->getLabel());
        $this->assertEquals('Published At', $generator->getField('published_at')->getLabel());
        $this->assertEquals('Released On', $generator->getField('released_on')->getLabel());
        $this->assertEquals('Sale Time', $generator->getField('sale_time')->getLabel());
        $this->assertEquals('Manufactured Year', $generator->getField('manufactured_year')->getLabel());
    }

    /** @test */
    public function it_can_identify_the_fillable_attributes(): void
    {
        $generator = new MockBaseGenerator('products');

        $this->assertEquals([
            'name',
            'address',
            'slug',
            'description',
            'price',
            'stock',
            'on_sale',
            'features',
            'published_at',
            'expire_at',
            'released_on',
            'sale_time',
            'status',
            'manufactured_year',
        ], $generator->getFillableAttributes());
    }

    /** @test */
    public function it_can_identify_the_searchable_attributes(): void
    {
        $generator = new MockBaseGenerator('products');

        $this->assertEquals([
            'name',
            'address',
            'slug',
        ], $generator->getSearchableAttributes());
    }

    /** @test */
    public function it_can_identify_the_foreign_key_attributes(): void
    {
        $generator = new MockBaseGenerator('products');

        $this->assertEquals([
            'category_id',
        ], $generator->getForeignKeyAttributes());
    }
}
