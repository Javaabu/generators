<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\FactoryGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class FactoryGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_determine_the_faker_method_from_attribute_name(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertNull($factory_generator->getFakerMethodFromColumnName('colorful'));
        $this->assertEquals('email', $factory_generator->getFakerMethodFromColumnName('email'));
        $this->assertEquals('firstName', $factory_generator->getFakerMethodFromColumnName('first_name'));
    }

    public function test_it_can_determine_the_faker_statement_for_faker_attributes(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->address()', $factory_generator->getFakerStatement('address'));
    }

    public function test_it_can_determine_the_faker_statement_for_nullable_attributes(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->optional()->sentences(3, true)', $factory_generator->getFakerStatement('description'));
    }

    public function test_it_can_determine_the_faker_statement_for_decimals(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->randomFloat(2, 0, 999999999999)', $factory_generator->getFakerStatement('price'));
    }

    public function test_it_can_determine_the_faker_statement_for_ints(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->numberBetween(0, 4294967295)', $factory_generator->getFakerStatement('stock'));
    }

    public function test_it_can_determine_the_faker_statement_for_texts(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->optional()->sentences(3, true)', $factory_generator->getFakerStatement('description'));
    }

    public function test_it_can_determine_the_faker_statement_for_unique_fields(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->unique()->slug()', $factory_generator->getFakerStatement('slug'));
    }

    public function test_it_can_determine_the_faker_statement_for_strings_shorter_than_5_characters(): void
    {
        $factory_generator = new FactoryGenerator('orders');

        $this->assertEquals('fake()->regexify(\'[a-z]{4}\')', $factory_generator->getFakerStatement('order_no'));
    }

    public function test_it_can_determine_the_faker_statement_for_strings(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->text(fake()->numberBetween(5, 255))', $factory_generator->getFakerStatement('name'));
    }

    public function test_it_can_determine_the_faker_statement_for_booleans(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->boolean()', $factory_generator->getFakerStatement('on_sale'));
    }


    public function test_it_can_determine_the_faker_statement_for_date_times(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->dateTime()?->format(\'Y-m-d H:i\')', $factory_generator->getFakerStatement('published_at'));
    }

    public function test_it_can_determine_the_faker_statement_for_times(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->time()', $factory_generator->getFakerStatement('sale_time'));
    }

    public function test_it_can_determine_the_faker_statement_for_timestamps(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->dateTime()?->format(\'Y-m-d H:i\')', $factory_generator->getFakerStatement('expire_at'));
    }

    public function test_it_can_determine_the_faker_statement_for_dates(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->date()', $factory_generator->getFakerStatement('released_on'));
    }

    public function test_it_can_determine_the_faker_statement_for_years(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->year(2100)', $factory_generator->getFakerStatement('manufactured_year'));
    }

    public function test_it_can_determine_the_faker_statement_for_foreign_keys(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()'."->optional()->passThrough(random_id_or_generate(\App\Models\Category::class, 'id', generate: true))", $factory_generator->getFakerStatement('category_id'));
    }

    public function test_it_can_determine_the_faker_statement_for_unique_foreign_keys(): void
    {
        $factory_generator = new FactoryGenerator('payments');

        $this->assertEquals('fake()'."->unique()->passThrough(random_id_or_generate(\App\Models\Order::class, 'id', generate: true, unique: true))", $factory_generator->getFakerStatement('order_id'));
    }

    public function test_it_can_determine_the_faker_statement_for_json_fields(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->words()', $factory_generator->getFakerStatement('features'));
    }

    public function test_it_can_determine_the_faker_statement_for_enum_fields(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $this->assertEquals('fake()->randomElement('."['draft', 'published']".')', $factory_generator->getFakerStatement('status'));
    }

    public function test_it_can_determine_the_faker_statement_for_enum_fields_with_enum_class(): void
    {
        $factory_generator = new FactoryGenerator('orders');

        $this->assertEquals('fake()->randomElement(array_column(\\Javaabu\\Generators\\Tests\\TestSupport\\Enums\\OrderStatuses::cases(), \'value\'))', $factory_generator->getFakerStatement('status'));
    }

    public function test_it_can_generate_a_factory_with_foreign_keys(): void
    {
        $factory_generator = new FactoryGenerator('products');

        $expected_content = $this->getTestStubContents('factories/ProductFactory.php');
        $actual_content = $factory_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_a_factory_without_foreign_keys(): void
    {
        $factory_generator = new FactoryGenerator('categories');

        $expected_content = $this->getTestStubContents('factories/CategoryFactory.php');
        $actual_content = $factory_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_a_factory_with_multiple_foreign_keys(): void
    {
        $factory_generator = new FactoryGenerator('orders');

        $expected_content = $this->getTestStubContents('factories/OrderFactory.php');
        $actual_content = $factory_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_a_factory_with_unique_foreign_keys(): void
    {
        $factory_generator = new FactoryGenerator('payments');

        $expected_content = $this->getTestStubContents('factories/PaymentFactory.php');
        $actual_content = $factory_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }

    public function test_it_can_generate_a_factory_with_optional_unique_values(): void
    {
        $factory_generator = new FactoryGenerator('countries');

        $expected_content = $this->getTestStubContents('factories/CountryFactory.php');
        $actual_content = $factory_generator->render();

        $this->assertEquals($expected_content, $actual_content);
    }
}
