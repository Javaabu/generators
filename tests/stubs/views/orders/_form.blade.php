<x-forms::card>
    <x-forms::text name="order_no" :label="__('Order No')" maxlength="255" required inline />

    <x-forms::select name="category" :label="__('Category')" :options="\App\Models\Category::query()" required inline />

    <x-forms::select name="product_slug" :label="__('Product Slug')" :options="\App\Models\Product::query()" required inline />

    <x-forms::button-group inline>
        <x-forms::submit color="success" class="btn--icon-text btn--raised">
            <i class="zmdi zmdi-check"></i> {{ __('Save') }}
        </x-forms::submit>

        <x-forms::link-button color="light" class="btn--icon-text" :url="route('admin.orders.index')">
            <i class="zmdi zmdi-close-circle"></i> {{ __('Cancel') }}
        </x-forms::link-button>
    </x-forms::button-group>
</x-forms::card>

@include('admin.components.floating-submit')
