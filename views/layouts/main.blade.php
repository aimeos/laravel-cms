<!DOCTYPE html>
<html class="no-js" lang="{{ cms($page, 'lang') }}" dir="{{ in_array(cms($page, 'lang'), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="Content-Security-Policy" content="
            base-uri 'self';
            default-src 'self';
            style-src 'self' https://hcaptcha.com, https://*.hcaptcha.com;
            script-src 'self' https://hcaptcha.com, https://*.hcaptcha.com;
            frame-src 'self' https://hcaptcha.com, https://*.hcaptcha.com;
            connect-src 'self' https://hcaptcha.com, https://*.hcaptcha.com
        ">

        <title>{{ cms($page, 'title') }}</title>

        @foreach(cms($page, 'meta') ?? [] as $item)
            @includeFirst(cmsviews($page, $item), cmsdata($page, $item))
        @endforeach

        @if(in_array(cms($page, 'lang'), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']))
            <link href="{{ cmsasset('vendor/cms/bootstrap.rtl.min.css') }}" rel="stylesheet">
        @else
            <link href="{{ cmsasset('vendor/cms/bootstrap.min.css') }}" rel="stylesheet">
        @endif
        <link href="{{ cmsasset('vendor/cms/cms.css') }}" rel="stylesheet">

        @stack('css')

        <script defer src="{{ cmsasset('vendor/cms/bootstrap.bundle.min.js') }}"></script>
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
