<!DOCTYPE html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) ? 'rtl' : 'ltr' }}">
    <head>
        <title>{{ $page->title }}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @if( in_array(app()->getLocale(), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) )
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.rtl.min.css" rel="stylesheet" crossorigin="anonymous">
		@else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
		@endif

		<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/cms.css?v=1') }}">
        @stack('css')

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="{{ asset('vendor/cms/cms.js?v=1') }}" crossorigin="anonymous"></script>
        @stack('js')

        @foreach($page->data ?? [] as $item)
            @includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
        @endforeach
    </head>
    <body>
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('cms.page', ['slug' => '', 'lang' => $page->ancestors?->last()?->lang]) }}">
                    {{ config('app.name') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbar">
                    <div class="navbar-nav">
                        @foreach(\Aimeos\Cms\Models\Page::nav('root')->children ?? [] as $item)
                            @if($item->status === 1 )
                                @if($page === $item)
                                    <a class="nav-link active" aria-current="page" href="{{ $item->to ?: route('cms.page', ['slug' => $item->slug, 'lang' => $item->lang]) }}">{{ $item->name }}</a>
                                @else
                                    <a class="nav-link" href="{{ $item->to ?: route('cms.page', ['slug' => $item->slug, 'lang' => $item->lang]) }}">{{ $item->name }}</a>
                                @endif
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </nav>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @foreach($page->ancestors ?? [] as $item)
                    @if($item->status === 1 )
                        <li class="breadcrumb-item">
                            <a href="{{ route('cms.page', ['slug' => $item->to ?: $item->slug]) }}">
                                {{ $item->name }}
                            </a>
                        </li>
                    @endif
                @endforeach
                <li class="breadcrumb-item active">
                    {{ $page->name }}
                </li>
            </ol>
        </nav>
        <div class="container">
            @foreach($page->content->data ?? [] as $item)
                @includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
            @endforeach
        </div>
    </body>
</html>
