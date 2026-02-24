<?php

namespace Javaabu\Generators\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\FieldTypes\BooleanField;
use Javaabu\Generators\FieldTypes\DateField;
use Javaabu\Generators\FieldTypes\DateTimeField;
use Javaabu\Generators\FieldTypes\DecimalField;
use Javaabu\Generators\FieldTypes\EnumField;
use Javaabu\Generators\FieldTypes\Field;
use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\FieldTypes\IntegerField;
use Javaabu\Generators\FieldTypes\JsonField;
use Javaabu\Generators\FieldTypes\StringField;
use Javaabu\Generators\FieldTypes\TextField;
use Javaabu\Generators\FieldTypes\TimeField;
use Javaabu\Generators\FieldTypes\YearField;
use Javaabu\Generators\Resolvers\SchemaResolverMySql;
use Javaabu\Generators\Tests\TestCase;

class SchemaResolverMySqlTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_resolve_the_key_name(): void
    {
        $resolver = new SchemaResolverMySql('products');
        $this->assertEquals('id', $resolver->resolve()->getKeyName());
    }

    public function test_it_can_resolve_soft_deletes(): void
    {
        $resolver = new SchemaResolverMySql('products');
        $this->assertTrue($resolver->resolve()->hasSoftDeletes());

        $resolver = new SchemaResolverMySql('categories');
        $this->assertFalse($resolver->resolve()->hasSoftDeletes());
    }

    public function test_it_can_resolve_timestamps(): void
    {
        $resolver = new SchemaResolverMySql('products');
        $this->assertTrue($resolver->resolve()->hasTimestamps());

        $resolver = new SchemaResolverMySql('payments');
        $this->assertFalse($resolver->resolve()->hasTimestamps());
    }

    public function test_it_can_resolve_a_subset_of_fields(): void
    {
        $resolver = new SchemaResolverMySql('orders', ['order_no', 'category_id']);
        $fields = $resolver->resolve()->getFields();

        $this->assertArrayHasKey('order_no', $fields);
        $this->assertArrayHasKey('category_id', $fields);
        $this->assertArrayNotHasKey('product_slug', $fields);
        $this->assertCount(2, $fields);
    }

    public function test_it_does_not_resolve_always_ignored_fields(): void
    {
        $resolver = new SchemaResolverMySql('products');
        $fields = $resolver->resolve()->getFields();

        $this->assertArrayNotHasKey('created_at', $fields);
        $this->assertArrayNotHasKey('updated_at', $fields);
        $this->assertArrayNotHasKey('deleted_at', $fields);
    }

    public function test_it_does_not_resolve_auto_increments(): void
    {
        $resolver = new SchemaResolverMySql('products');
        $fields = $resolver->resolve()->getFields();

        $this->assertArrayNotHasKey('id', $fields);
    }

    public function test_it_can_resolve_text_fields(): void
    {
        $resolver = new SchemaResolverMySql('posts');
        $fields = $resolver->resolve()->getFields();

        $this->assertArrayHasKey('excerpt', $fields);

        $field = $fields['excerpt'];
        $this->assertInstanceOf(TextField::class, $field);
        $this->assertEquals('excerpt', $field->getName());
        $this->assertEquals(65535, $field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
    }

    public function test_it_can_resolve_medium_text_fields(): void
    {
        $resolver = new SchemaResolverMySql('posts');
        $fields = $resolver->resolve()->getFields();

        $this->assertArrayHasKey('medium_content', $fields);

        $field = $fields['medium_content'];
        $this->assertInstanceOf(TextField::class, $field);
        $this->assertEquals('medium_content', $field->getName());
        $this->assertEquals(16777215, $field->getMax());
        $this->assertTrue($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
    }

    public function test_it_can_resolve_tiny_text_fields(): void
    {
        $resolver = new SchemaResolverMySql('posts');
        $fields = $resolver->resolve()->getFields();

        $this->assertArrayHasKey('tiny_content', $fields);

        $field = $fields['tiny_content'];
        $this->assertInstanceOf(TextField::class, $field);
        $this->assertEquals('tiny_content', $field->getName());
        $this->assertEquals(255, $field->getMax());
        $this->assertTrue($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
    }

    public function test_it_can_resolve_long_text_fields(): void
    {
        $resolver = new SchemaResolverMySql('posts');
        $fields = $resolver->resolve()->getFields();

        $this->assertArrayHasKey('content', $fields);

        $field = $fields['content'];
        $this->assertInstanceOf(TextField::class, $field);
        $this->assertEquals('content', $field->getName());
        $this->assertEquals(4294967295, $field->getMax());
        $this->assertTrue($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
    }

    public function test_it_can_resolve_foreign_key_fields(): void
    {
        $field = $this->resolveField('category_id');

        $this->assertInstanceOf(ForeignKeyField::class, $field);
        $this->assertEquals('category_id', $field->getName());
        $this->assertEquals('categories', $field->getRelatedTable());
        $this->assertEquals('id', $field->getRelatedKeyName());
        $this->assertEquals('Category', $field->getRelatedModelClass());
        $this->assertTrue($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertNull($field->generateCast());
    }

    public function test_it_can_resolve_enums_from_comments(): void
    {
        $field = $this->resolveField('status', 'payments');

        $this->assertInstanceOf(EnumField::class, $field);
        $this->assertEquals('status', $field->getName());
        $this->assertTrue($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('PaymentStatuses::class', $field->generateCast());
    }

    public function test_it_can_resolve_boolean_fields(): void
    {
        $field = $this->resolveField('on_sale');

        $this->assertInstanceOf(BooleanField::class, $field);
        $this->assertEquals('on_sale', $field->getName());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->getDefault());
        $this->assertTrue($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('boolean', $field->generateCast());
    }

    public function test_it_can_resolve_unique_fields(): void
    {
        $field = $this->resolveField('slug');

        $this->assertInstanceOf(StringField::class, $field);
        $this->assertEquals('slug', $field->getName());
        $this->assertEquals(255, $field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertTrue($field->isUnique());
    }

    public function test_it_can_resolve_string_fields(): void
    {
        $field = $this->resolveField('name');

        $this->assertInstanceOf(StringField::class, $field);
        $this->assertEquals('name', $field->getName());
        $this->assertEquals(255, $field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('string', $field->generateCast());
    }

    public function test_it_can_resolve_integer_fields(): void
    {
        $field = $this->resolveField('stock');

        $this->assertInstanceOf(IntegerField::class, $field);
        $this->assertEquals('stock', $field->getName());
        $this->assertTrue($field->isUnsigned());
        $this->assertEquals(0, $field->getMin());
        $this->assertEquals(4294967295, $field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('integer', $field->generateCast());
    }

    public function test_it_can_resolve_decimal_fields(): void
    {
        $field = $this->resolveField('price');

        $this->assertInstanceOf(DecimalField::class, $field);
        $this->assertEquals('price', $field->getName());
        $this->assertTrue($field->isUnsigned());
        $this->assertEquals(14, $field->getTotalDigits());
        $this->assertEquals(2, $field->getPlaces());
        $this->assertEquals(0, $field->getMin());
        $this->assertEquals(999999999999, $field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('decimal:2', $field->generateCast());
    }

    public function test_it_can_resolve_enum_fields(): void
    {
        $field = $this->resolveField('status');

        $this->assertInstanceOf(EnumField::class, $field);
        $this->assertEquals('status', $field->getName());
        $this->assertEquals(['draft', 'published'], $field->getOptions());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertNull($field->generateCast());
    }

    public function test_it_can_resolve_year_fields(): void
    {
        $field = $this->resolveField('manufactured_year');

        $this->assertInstanceOf(YearField::class, $field);
        $this->assertEquals('manufactured_year', $field->getName());
        $this->assertEquals(1900, $field->getMin());
        $this->assertEquals(2100, $field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('integer', $field->generateCast());
    }

    public function test_it_can_resolve_date_time_fields(): void
    {
        $field = $this->resolveField('published_at');

        $this->assertInstanceOf(DateTimeField::class, $field);
        $this->assertEquals('published_at', $field->getName());
        $this->assertNull($field->getMin());
        $this->assertNull($field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('datetime', $field->generateCast());

        $field = $this->resolveField('expire_at');

        $this->assertInstanceOf(DateTimeField::class, $field);
        $this->assertEquals('expire_at', $field->getName());
        $this->assertNull($field->getMin());
        $this->assertNull($field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('datetime', $field->generateCast());
    }

    public function test_it_can_resolve_date_fields(): void
    {
        $field = $this->resolveField('released_on');

        $this->assertInstanceOf(DateField::class, $field);
        $this->assertEquals('released_on', $field->getName());
        $this->assertNull($field->getMin());
        $this->assertNull($field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('date', $field->generateCast());
    }

    public function test_it_can_resolve_time_fields(): void
    {
        $field = $this->resolveField('sale_time');

        $this->assertInstanceOf(TimeField::class, $field);
        $this->assertEquals('sale_time', $field->getName());
        $this->assertNull($field->getMin());
        $this->assertNull($field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('datetime', $field->generateCast());
    }

    public function test_it_can_resolve_json_fields(): void
    {
        $field = $this->resolveField('features');

        $this->assertInstanceOf(JsonField::class, $field);
        $this->assertEquals('features', $field->getName());
        $this->assertNull($field->getMin());
        $this->assertNull($field->getMax());
        $this->assertFalse($field->isNullable());
        $this->assertFalse($field->hasDefault());
        $this->assertFalse($field->isUnique());
        $this->assertEquals('array', $field->generateCast());
    }

    protected function resolveField($field, $table = 'products'): Field
    {
        $resolver = new SchemaResolverMySql($table, [$field]);
        $fields = $resolver->resolve()->getFields();

        return $fields[$field];
    }
}
