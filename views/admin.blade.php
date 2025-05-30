<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) ? 'rtl' : 'ltr' }}">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="csrf-token" content="{{ csrf_token() }}">

		@if( !config('app.debug') )
			<meta http-equiv="Content-Security-Policy" content="base-uri 'self'; default-src 'self'; style-src 'unsafe-inline' 'self'; img-src 'self' data:; }}">
		@endif

    <title>Laravel CMS Admin</title>

    <script type="module" crossorigin src="{{ cmsasset('vendor/cms/admin/index.js') }}"></script>
    <link rel="stylesheet" crossorigin href="{{ cmsasset('vendor/cms/admin/index.css') }}">
  </head>
  <body>
    <div id="app"
      data-graphql="{{ route('graphql') }}"
      data-languages='{"en": "English"}'
      data-urlbase="{{ route('cms.admin', [], false) }}"
      data-urlpage="{{ route('cms.page', ['slug' => ':slug', 'lang' => 'xx-XX']) }}"
      data-urlfile="{{ \Illuminate\Support\Facades\Storage::disk( config( 'cms.disk', 'public' ) )->url( '' ) }}"
      data-permissions="{{ \Aimeos\Cms\Permission::get( \Illuminate\Support\Facades\Auth::user() ) }}"
    </div>
  </body>
</html>
