<x-forms::card>
    <x-forms::text name="order_no" maxlength="4" required inline show-placeholder />

    <x-forms::select2 name="category" :options="\App\Models\Category::query()" relation required inline show-placeholder />

    <x-forms::select2 name="product_slug" :options="\App\Models\Product::query()" relation required inline show-placeholder />

    <x-forms::select2 name="status" :options="\Javaabu\Generators\Tests\TestSupport\Enums\OrderStatuses::getLabels()" required inline show-placeholder />

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
