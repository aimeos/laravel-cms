<!DOCTYPE html>
<html class="no-js" lang="{{ cms($page, 'lang') }}" dir="{{ in_array(cms($page, 'lang'), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ cms($page, 'title') }}</title>

        @foreach(cms($page, 'meta') ?? [] as $item)
            @includeFirst(cmsviews($page, $item), ['files' => cms($page, 'files')] + (array) $item)
        @endforeach

        @if(in_array(cms($page, 'lang'), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']))
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.rtl.min.css" rel="stylesheet" crossorigin="anonymous">
        @else
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        @endif
        <link href="{{ cmsasset('vendor/cms/cms.css') }}" rel="stylesheet">

        @stack('css')

        <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script defer src="{{ cmsasset('vendor/cms/cms.js') }}"></script>

        @stack('js')

        @if(\Aimeos\Cms\Permission::can('page:save', auth()->user()))
            <script defer src="{{ cmsasset('vendor/cms/admin.js') }}"></script>
        @endif
    </head>
    <body class="theme-{{ cms($page, 'theme') }} type-{{ cms($page, 'type') }}">
        @yield('header')
        @yield('main')
        @yield('footer')
    </body>
</html>
