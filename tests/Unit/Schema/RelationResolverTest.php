<?php

namespace Javaabu\Generators\Tests\Unit\Schema;

use Javaabu\Generators\Schema\RelationResolver;
use Javaabu\Generators\Schema\Relations\Relation;
use Javaabu\Generators\Schema\SchemaInspector;
use Javaabu\Generators\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RelationResolverTest extends TestCase
{
    /**
     * @param  array<int, Relation>  $relations
     * @return array<string, string>
     */
    protected function definitions(array $relations): array
    {
        $definitions = [];

        foreach ($relations as $relation) {
            $definitions[$relation->name] = $relation->definition();
        }

        return $definitions;
    }

    protected function table(string $name)
    {
        return app(SchemaInspector::class)->table($name);
    }

    #[Test]
    public function it_finds_belongs_to_relations(): void
    {
        $relations = app(RelationResolver::class)->belongsTo($this->table('orders'));

        $this->assertEquals([
            'category' => 'belongsTo(Category::class)',
            'product' => "belongsTo(Product::class, 'product_slug', 'slug')",
        ], $this->definitions($relations));
    }

    #[Test]
    public function it_finds_has_many_relations_from_other_tables(): void
    {
        $definitions = $this->definitions(app(RelationResolver::class)->hasMany($this->table('categories')));

        $this->assertEquals('hasMany(Product::class)', $definitions['products']);
        $this->assertEquals('hasMany(Order::class)', $definitions['orders']);
        $this->assertArrayNotHasKey('productTags', $definitions);
    }

    #[Test]
    public function it_finds_has_many_relations_on_other_keys(): void
    {
        $definitions = $this->definitions(app(RelationResolver::class)->hasMany($this->table('products')));

        $this->assertEquals("hasMany(Order::class, 'product_slug', 'slug')", $definitions['orders']);
    }

    #[Test]
    public function it_finds_many_to_many_relations_through_pivot_tables(): void
    {
        $resolver = app(RelationResolver::class);

        $this->assertEquals(['tags' => 'belongsToMany(Tag::class)'], $this->definitions($resolver->belongsToMany($this->table('products'))));
        $this->assertEquals(['products' => 'belongsToMany(Product::class)'], $this->definitions($resolver->belongsToMany($this->table('tags'))));
    }

    #[Test]
    public function it_tells_pivot_tables_apart(): void
    {
        $resolver = app(RelationResolver::class);

        $this->assertTrue($resolver->isPivotTable($this->table('product_tag')));

        // two keys, but one is to products.slug, not a key
        $this->assertFalse($resolver->isPivotTable($this->table('all_foreigns')));

        $this->assertFalse($resolver->isPivotTable($this->table('orders')));
        $this->assertFalse($resolver->isPivotTable($this->table('products')));
    }

    #[Test]
    public function it_skips_ignored_tables(): void
    {
        config()->set('generators.ignored_tables', ['orders']);

        $definitions = $this->definitions(app(RelationResolver::class)->hasMany($this->table('categories')));

        $this->assertArrayNotHasKey('orders', $definitions);
        $this->assertArrayHasKey('products', $definitions);
    }
}
