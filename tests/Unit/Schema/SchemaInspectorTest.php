<?php

namespace Javaabu\Generators\Tests\Unit\Schema;

use Javaabu\Generators\Exceptions\TableDoesNotExistException;
use Javaabu\Generators\Schema\SchemaInspector;
use Javaabu\Generators\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SchemaInspectorTest extends TestCase
{
    protected function inspector(): SchemaInspector
    {
        return app(SchemaInspector::class);
    }

    #[Test]
    public function it_lists_the_tables(): void
    {
        $tables = $this->inspector()->tableNames();

        $this->assertContains('products', $tables);
        $this->assertContains('product_tag', $tables);
        $this->assertTrue($this->inspector()->hasTable('categories'));
        $this->assertFalse($this->inspector()->hasTable('missing'));
    }

    #[Test]
    public function it_throws_for_a_missing_table(): void
    {
        $this->expectException(TableDoesNotExistException::class);

        $this->inspector()->table('missing');
    }

    #[Test]
    public function it_normalizes_the_column_types(): void
    {
        $table = $this->inspector()->table('products');

        $types = array_map(fn ($column) => $column->type, $table->columns);

        $this->assertContains($types['id'], ['bigint', 'int']);
        $this->assertEquals('string', $types['name']);
        $this->assertEquals('text', $types['description']);
        $this->assertEquals('decimal', $types['price']);
        $this->assertEquals('int', $types['stock']);
        $this->assertEquals('boolean', $types['on_sale']);
        $this->assertEquals('date', $types['released_on']);
        $this->assertEquals('time', $types['sale_time']);
        $this->assertEquals('enum', $types['status']);
        $this->assertContains($types['published_at'], ['datetime', 'timestamp']);
        $this->assertContains($types['expire_at'], ['datetime', 'timestamp']);

        // SQLite stores JSON as text
        $this->assertContains($types['features'], ['json', 'text']);

        // MySQL keeps year columns, the others store integers
        $this->assertContains($types['manufactured_year'], ['year', 'int']);
    }

    #[Test]
    public function it_reads_enum_options_on_every_driver(): void
    {
        $status = $this->inspector()->table('products')->column('status');

        $this->assertEquals('enum', $status->type);
        $this->assertEquals(['draft', 'published'], $status->options);
    }

    #[Test]
    public function it_reads_nullability_uniqueness_and_defaults(): void
    {
        $table = $this->inspector()->table('products');

        $this->assertTrue($table->column('description')->nullable);
        $this->assertFalse($table->column('name')->nullable);
        $this->assertTrue($table->column('slug')->unique);
        $this->assertFalse($table->column('name')->unique);
        $this->assertNotNull($table->column('on_sale')->default);
        $this->assertTrue($table->column('id')->auto_increment);
    }

    #[Test]
    public function it_reads_the_decimal_precision(): void
    {
        $price = $this->inspector()->table('products')->column('price');

        // SQLite keeps no precision: 10,2 is assumed
        $sqlite = $this->inspector()->driver() === 'sqlite';

        $this->assertEquals($sqlite ? 10 : 14, $price->precision);
        $this->assertEquals(2, $price->scale);
    }

    #[Test]
    public function it_reads_string_lengths_where_the_driver_keeps_them(): void
    {
        $order_no = $this->inspector()->table('orders')->column('order_no');

        $expected = $this->inspector()->driver() === 'sqlite' ? 255 : 4;

        $this->assertEquals($expected, $order_no->length);
    }

    #[Test]
    public function it_reads_enum_classes_from_column_comments_where_the_driver_keeps_them(): void
    {
        if ($this->inspector()->driver() === 'sqlite') {
            $this->markTestSkipped('SQLite keeps no column comments.');
        }

        $status = $this->inspector()->table('payments')->column('status');

        $this->assertEquals('App\\Enums\\PaymentStatuses', $status->enumClassFromComment());
    }

    #[Test]
    public function it_reads_the_keys_timestamps_and_soft_deletes(): void
    {
        $products = $this->inspector()->table('products');
        $payments = $this->inspector()->table('payments');
        $pivot = $this->inspector()->table('product_tag');

        $this->assertEquals('id', $products->keyName());
        $this->assertTrue($products->hasTimestamps());
        $this->assertTrue($products->hasSoftDeletes());

        $this->assertFalse($payments->hasTimestamps());
        $this->assertTrue($payments->hasSoftDeletes());

        $this->assertNull($pivot->keyName());
        $this->assertEquals(['product_id', 'tag_id'], $pivot->primary_key);
    }

    #[Test]
    public function it_reads_foreign_keys(): void
    {
        $orders = $this->inspector()->table('orders');

        $category = $orders->foreignKeyFor('category_id');
        $product = $orders->foreignKeyFor('product_slug');

        $this->assertEquals('categories', $category->foreign_table);
        $this->assertEquals('id', $category->foreign_column);
        $this->assertEquals('products', $product->foreign_table);
        $this->assertEquals('slug', $product->foreign_column);
        $this->assertNull($orders->foreignKeyFor('order_no'));
    }
}
