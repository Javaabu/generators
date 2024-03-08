<x-forms::card>
    <x-forms::text name="name" maxlength="255" required inline />

    <x-forms::text name="address" maxlength="255" required inline />

    <x-forms::text name="slug" maxlength="255" required inline />

    <x-forms::textarea name="description" inline />

    <x-forms::number name="price" min="0" max="999999999999" step="0.01" required inline />

    <x-forms::number name="stock" min="0" max="4294967295" step="1" required inline />

    <x-forms::checkbox name="on_sale" value="1" inline />

    <x-forms::select name="features[]" :options="['apple', 'orange']" multiple required inline />

    <x-forms::datetime name="published_at" required inline />

    <x-forms::datetime name="expire_at" required inline />

    <x-forms::date name="released_on" required inline />

    <x-forms::time name="sale_time" required inline />

    <x-forms::select name="status" :options="['draft', 'published']" required inline />

    <x-forms::select name="category" :options="\App\Models\Category::query()" inline />

    <x-forms::number name="manufactured_year" min="1900" max="2100" step="1" required inline />

    <x-forms::button-group inline>
        <x-forms::submit color="success" class="btn--icon-text btn--raised">
            <i class="zmdi zmdi-check"></i> {{ __('Save') }}
        </x-forms::submit>

        <x-forms::link-button color="light" class="btn--icon-text" :url="route('admin.products.index')">
            <i class="zmdi zmdi-close-circle"></i> {{ __('Cancel') }}
        </x-forms::link-button>
    </x-forms::button-group>
</x-forms::card>

@include('admin.components.floating-submit')
