<?php

namespace Javaabu\Generators\Tests\Unit;

use Javaabu\Generators\Resolvers\BaseResolver;
use Javaabu\Generators\Tests\InteractsWithDatabase;
use Javaabu\Generators\Tests\TestCase;

class BaseResolverTest extends TestCase
{
    use InteractsWithDatabase;

    /** @test */
    public function it_can_generate_model_class_name_from_table_name(): void
    {
        $resolver = $this->getMockForAbstractClass(BaseResolver::class, ['test']);

        $this->assertEquals('Category', $resolver->getModelClassFromTableName('categories'));
        $this->assertEquals('PostType', $resolver->getModelClassFromTableName('post_types'));
        $this->assertEquals('FormInputCategory', $resolver->getModelClassFromTableName('form_input_categories'));
    }

    /** @test */
    public function it_can_get_rule_value_from_attribute(): void
    {
        $this->runMigrations();

        $resolver = $this->getMockForAbstractClass(BaseResolver::class, ['products']);

        $this->assertEquals('categories,id', $resolver->getAttributeRuleValue('category_id', 'exists'));
    }

    /** @test */
    public function it_can_get_the_foreign_key_table_from_attribute(): void
    {
        $this->runMigrations();

        $resolver = $this->getMockForAbstractClass(BaseResolver::class, ['products']);

        $this->assertEquals('categories', $resolver->getAttributeForeingKeyTable('category_id'));
    }

    /** @test */
    public function it_can_get_the_foreign_key_name_from_attribute(): void
    {
        $this->runMigrations();

        $resolver = $this->getMockForAbstractClass(BaseResolver::class, ['products']);

        $this->assertEquals('id', $resolver->getAttributeForeingKeyName('category_id'));
    }

    /** @test */
    public function it_can_get_the_foreign_key_model_class_from_attribute(): void
    {
        $this->runMigrations();

        $resolver = $this->getMockForAbstractClass(BaseResolver::class, ['products']);

        $this->assertEquals('Category', $resolver->getAttributeForeingKeyModelClass('category_id'));
    }
}