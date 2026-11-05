<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'dv' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>
</head>
<body>
    @if(session('status'))
        <x-forms::alert type="success">{{ session('status') }}</x-forms::alert>
    @endif

    <main class="mx-auto max-w-7xl p-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
