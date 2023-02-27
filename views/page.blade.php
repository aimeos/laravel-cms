<!DOCTYPE html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @yield('cms-head')
        @stack('css')
        @stack('js')
    </head>
    <body>
        @yield('cms-navbar')
        @yield('cms-breadcrumb')
        @yield('cms-content')
    </body>
</html>
