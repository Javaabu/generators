<?php

namespace Javaabu\Generators\Tests\Unit\Support;

use InvalidArgumentException;
use Javaabu\Generators\Resource\MediaCollection;
use Javaabu\Generators\Resource\ResourceOptions;
use Javaabu\Generators\Support\Naming;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NamingTest extends TestCase
{
    #[Test]
    public function it_names_belongs_to_relations(): void
    {
        $this->assertEquals('category', Naming::belongsTo('category_id', 'categories'));
        $this->assertEquals('blogPost', Naming::belongsTo('blog_post_id', 'blog_posts'));
        $this->assertEquals('product', Naming::belongsTo('product_slug', 'products'));
        $this->assertEquals('owner', Naming::belongsTo('owner', 'users'));
    }

    #[Test]
    public function it_parses_media_collections(): void
    {
        $collections = MediaCollection::parse('featured_image, gallery:image:multiple,documents:document:multiple,video:video');

        $this->assertCount(4, $collections);
        $this->assertEquals(['featured_image', 'image', false], [$collections[0]->name, $collections[0]->type, $collections[0]->multiple]);
        $this->assertEquals(['gallery', 'image', true], [$collections[1]->name, $collections[1]->type, $collections[1]->multiple]);
        $this->assertEquals(['documents', 'document', true], [$collections[2]->name, $collections[2]->type, $collections[2]->multiple]);
        $this->assertEquals('Featured image', $collections[0]->label());
    }

    #[Test]
    public function it_rejects_unknown_media_types(): void
    {
        $this->expectException(InvalidArgumentException::class);

        MediaCollection::parse('cover:picture');
    }

    #[Test]
    public function it_reads_resource_options(): void
    {
        $options = ResourceOptions::fromArray([
            'columns' => 'name, price',
            'index' => 'name',
            'form' => null,
            'media' => 'cover',
            'translatable' => 'title,body',
        ]);

        $this->assertEquals(['name', 'price'], $options->columns);
        $this->assertEquals(['name'], $options->index);
        $this->assertNull($options->form);
        $this->assertCount(1, $options->media);
        $this->assertEquals(['title', 'body'], $options->translatable);
        $this->assertTrue(ResourceOptions::fromArray(['translatable' => 'auto'])->translatable);
    }
}
