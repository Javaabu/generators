@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', __('Products'))

@section('top-search')
    <x-forms::search-form route="admin.products.index" :placeholder="__('Search for products...')" />
@endsection

@section('model-actions')
    @include('admin.products._actions')
@endsection
