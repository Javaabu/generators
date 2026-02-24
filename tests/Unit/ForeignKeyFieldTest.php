<?php

namespace Javaabu\Generators\Tests\Unit;

use Javaabu\Generators\FieldTypes\ForeignKeyField;
use Javaabu\Generators\Tests\TestCase;

class ForeignKeyFieldTest extends TestCase
{
    public function test_it_can_generate_model_class_name_from_table_name(): void
    {
        $this->assertEquals('Category', (new ForeignKeyField('category_id', 'categories', 'id'))->getRelatedModelClass());
        $this->assertEquals('PostType', (new ForeignKeyField('post_type_id', 'post_types', 'id'))->getRelatedModelClass());
        $this->assertEquals('FormInputCategory', (new ForeignKeyField('form_input_category_id', 'form_input_categories', 'id'))->getRelatedModelClass());
    }

    public function test_it_can_generate_foreign_key_field_assignment_statement(): void
    {
        $this->assertEquals('category()->associate($request->input(\'category\'))', (new ForeignKeyField('category_id', 'categories', 'id'))->renderAssignment());
    }

    public function test_it_can_generate_the_correct_relation_name(): void
    {
        $this->assertEquals('category', (new ForeignKeyField('category_id', 'categories', 'id'))->getRelationName());
        $this->assertEquals('postType', (new ForeignKeyField('post_type_id', 'post_types', 'id'))->getRelationName());
        $this->assertEquals('formInputCategory', (new ForeignKeyField('form_input_category_id', 'form_input_categories', 'id'))->getRelationName());
        $this->assertEquals('nationality', (new ForeignKeyField('nationality', 'countries', 'code'))->getRelationName());
        $this->assertEquals('productSlug', (new ForeignKeyField('product_slug', 'products', 'slug'))->getRelationName());
        $this->assertEquals('id', (new ForeignKeyField('_id', 'countries', 'code'))->getRelationName());
    }

    public function test_it_can_generate_the_correct_input_name(): void
    {
        $this->assertEquals('category', (new ForeignKeyField('category_id', 'categories', 'id'))->getInputName());
        $this->assertEquals('post_type', (new ForeignKeyField('post_type_id', 'post_types', 'id'))->getInputName());
        $this->assertEquals('form_input_category', (new ForeignKeyField('form_input_category_id', 'form_input_categories', 'id'))->getInputName());
        $this->assertEquals('nationality', (new ForeignKeyField('nationality', 'countries', 'code'))->getInputName());
        $this->assertEquals('_id', (new ForeignKeyField('_id', 'countries', 'code'))->getInputName());
    }

    public function test_it_can_generate_the_correct_model_creation_statement(): void
    {
        $this->assertEquals('$new_category = $this->getFactory(Category::class)->create();', (new ForeignKeyField('category_id', 'categories', 'id'))->generateTestFactoryStatement('new_'));
        $this->assertEquals('$old_nationality = $this->getFactory(Country::class)->create();', (new ForeignKeyField('nationality', 'countries', 'code'))->generateTestFactoryStatement('old_'));
    }

    public function test_it_can_generate_the_correct_relation_statement(): void
    {
        $this->assertEquals('belongsTo(Category::class)', (new ForeignKeyField('category_id', 'categories', 'id'))->generateRelationStatement());
        $this->assertEquals('belongsTo(PostType::class)', (new ForeignKeyField('post_type_id', 'post_types', 'id'))->generateRelationStatement());
        $this->assertEquals('belongsTo(FormInputCategory::class)', (new ForeignKeyField('form_input_category_id', 'form_input_categories', 'id'))->generateRelationStatement());
        $this->assertEquals('belongsTo(Country::class, \'nationality\', \'code\')', (new ForeignKeyField('nationality', 'countries', 'code'))->generateRelationStatement());
        $this->assertEquals('belongsTo(Country::class, \'nationality\')', (new ForeignKeyField('nationality', 'countries', 'id'))->generateRelationStatement());
    }
}
