<?php

namespace Javaabu\Generators\Tests\Unit\Generators;

use Javaabu\Generators\Generators\ViewsGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Generators\Tests\TestCase;

class ViewsGeneratorTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_generate_the_component_for_nullable_attributes(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::textarea name="description" inline />', $views_generator->getFormComponentBlade('description'));
    }

    /** @test */
    public function it_can_generate_the_component_for_decimals(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::number name="price" min="0" max="999999999999" step="0.01" required inline />', $views_generator->getFormComponentBlade('price'));
    }

    /** @test */
    public function it_can_generate_the_component_for_ints(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::number name="stock" min="0" max="4294967295" step="1" required inline />', $views_generator->getFormComponentBlade('stock'));
    }

    /** @test */
    public function it_can_generate_the_component_for_texts(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::textarea name="description" inline />', $views_generator->getFormComponentBlade('description'));
    }

    /** @test */
    public function it_can_generate_the_component_for_strings(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::text name="name" maxlength="255" required inline />', $views_generator->getFormComponentBlade('name'));
    }


    /** @test */
    public function it_can_generate_the_component_for_booleans(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::checkbox name="on_sale" value="1" inline />', $views_generator->getFormComponentBlade('on_sale'));
    }


    /** @test */
    public function it_can_generate_the_component_for_date_times(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::datetime name="published_at" required inline />', $views_generator->getFormComponentBlade('published_at'));
    }

    /** @test */
    public function it_can_generate_the_component_for_times(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::time name="sale_time" required inline />', $views_generator->getFormComponentBlade('sale_time'));
    }

    /** @test */
    public function it_can_generate_the_component_for_timestamps(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::datetime name="expire_at" required inline />', $views_generator->getFormComponentBlade('expire_at'));
    }

    /** @test */
    public function it_can_generate_the_component_for_dates(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::date name="released_on" required inline />', $views_generator->getFormComponentBlade('released_on'));
    }

    /** @test */
    public function it_can_generate_the_component_for_years(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::number name="manufactured_year" min="1900" max="2100" step="1" required inline />', $views_generator->getFormComponentBlade('manufactured_year'));
    }

    /** @test */
    public function it_can_generate_the_component_for_foreign_keys(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::select2 name="category" :options="\App\Models\Category::query()" relation inline />', $views_generator->getFormComponentBlade('category_id'));
    }

    /** @test */
    public function it_can_generate_the_component_for_json_fields(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::select2 name="features[]" :options="[\'apple\', \'orange\']" multiple required inline />', $views_generator->getFormComponentBlade('features'));
    }

    /** @test */
    public function it_can_generate_the_component_for_enum_fields(): void
    {
        $views_generator = new ViewsGenerator('products');

        $this->assertEquals('<x-forms::select2 name="status" :options="[\'draft\', \'published\']" required inline />', $views_generator->getFormComponentBlade('status'));
    }

    /** @test */
    public function it_can_generate_the_component_for_enum_fields_with_enum_class_that_dont_have_get_labels(): void
    {
        $views_generator = new ViewsGenerator('payments');

        $this->assertEquals('<x-forms::select2 name="status" :options="array_column(\\App\\Enums\\PaymentStatuses::cases(), \'name\', \'value\')" inline />', $views_generator->getFormComponentBlade('status'));
    }

    /** @test */
    public function it_can_generate_the_component_for_enum_fields_with_enum_class(): void
    {
        $views_generator = new ViewsGenerator('orders');

        $this->assertEquals('<x-forms::select2 name="status" :options="\\Javaabu\\Generators\\Tests\\Enums\\OrderStatuses::getLabels()" required inline />', $views_generator->getFormComponentBlade('status'));
    }

    /** @test */
    public function it_can_generate_an_infolist_with_foreign_keys(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/_details.blade.php');
        $actual_content = $views_generator->renderInfolist();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_an_infolist_with_multiple_foreign_keys(): void
    {
        $views_generator = new ViewsGenerator('orders');

        $expected_content = $this->getTestStubContents('views/orders/_details.blade.php');
        $actual_content = $views_generator->renderInfolist();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_form_with_foreign_keys(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/_form.blade.php');
        $actual_content = $views_generator->renderForm();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_form_without_foreign_keys(): void
    {
        $views_generator = new ViewsGenerator('categories');

        $expected_content = $this->getTestStubContents('views/categories/_form.blade.php');
        $actual_content = $views_generator->renderForm();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_a_form_with_multiple_foreign_keys(): void
    {
        $views_generator = new ViewsGenerator('orders');

        $expected_content = $this->getTestStubContents('views/orders/_form.blade.php');
        $actual_content = $views_generator->renderForm();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_model_layout(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/products.blade.php');
        $actual_content = $views_generator->renderLayout();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_model_create_view(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/create.blade.php');
        $actual_content = $views_generator->renderCreateView();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_model_edit_view(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/edit.blade.php');
        $actual_content = $views_generator->renderEditView();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_model_show_view(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/show.blade.php');
        $actual_content = $views_generator->renderShowView();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_actions_view_for_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/_actions.blade.php');
        $actual_content = $views_generator->renderActions();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_actions_view_for_none_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('categories');

        $expected_content = $this->getTestStubContents('views/categories/_actions.blade.php');
        $actual_content = $views_generator->renderActions();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_bulk_actions_view(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/_bulk.blade.php');
        $actual_content = $views_generator->renderBulkActions();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_filters_view_for_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/_filter.blade.php');
        $actual_content = $views_generator->renderFilters();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_filters_view_for_none_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('categories');

        $expected_content = $this->getTestStubContents('views/categories/_filter.blade.php');
        $actual_content = $views_generator->renderFilters();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_model_columns_and_skips_the_admin_link_name(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/_columns.blade.stub');
        $actual_content = $views_generator->renderTableColumns();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_list_view_for_multiple_foreign_keys_model(): void
    {
        $views_generator = new ViewsGenerator('orders');

        $expected_content = $this->getTestStubContents('views/orders/_list.blade.php');
        $actual_content = $views_generator->renderTableRows();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_list_view_for_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/_list.blade.php');
        $actual_content = $views_generator->renderTableRows();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_list_view_for_none_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('categories');

        $expected_content = $this->getTestStubContents('views/categories/_list.blade.php');
        $actual_content = $views_generator->renderTableRows();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_column_titles_and_skips_the_admin_link_name(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/_titles.blade.stub');
        $actual_content = $views_generator->renderTableTitles();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_table_view_for_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/_table.blade.php');
        $actual_content = $views_generator->renderTable();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_table_view_for_none_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('categories');

        $expected_content = $this->getTestStubContents('views/categories/_table.blade.php');
        $actual_content = $views_generator->renderTable();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_index_view_for_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('products');

        $expected_content = $this->getTestStubContents('views/products/index.blade.php');
        $actual_content = $views_generator->renderIndexView();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_generate_index_view_for_none_soft_delete_models(): void
    {
        $views_generator = new ViewsGenerator('categories');

        $expected_content = $this->getTestStubContents('views/categories/index.blade.php');
        $actual_content = $views_generator->renderIndexView();

        $this->assertEquals($expected_content, $actual_content);
    }

    /** @test */
    public function it_can_render_sidebar_links(): void
    {
        $views_generator = new ViewsGenerator('categories');

        $expected_content = $this->getTestStubContents('Menus/_categoriesSidebar.stub');
        $actual_content = $views_generator->renderSidebarLinks();

        $this->assertEquals($expected_content, $actual_content);
    }
}
