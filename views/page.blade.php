<!DOCTYPE html>
<html class="no-js" lang="{{ cms($page, 'lang') }}" dir="{{ in_array(cms($page, 'lang'), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']) ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ cms($page, 'title') }}</title>

        @if(in_array(app()->getLocale(), ['ar', 'az', 'dv', 'fa', 'he', 'ku', 'ur']))
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.rtl.min.css" rel="stylesheet" crossorigin="anonymous">
        @else
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        @endif
        <link href="{{ cmsasset('vendor/cms/cms.css') }}" rel="stylesheet">

        <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script defer src="{{ cmsasset('vendor/cms/cms.js') }}"></script>

        @if(\Aimeos\Cms\Permission::can('page:save', auth()->user()))
            <script defer src="{{ cmsasset('vendor/cms/admin.js') }}"></script>
        @endif

        @foreach(cms($page, 'meta') ?? [] as $item)
            @includeFirst(cmsview($page, $item), ['files' => cms($page, 'files')] + (array) $item)
        @endforeach
    </head>
    <body class="theme-{{ cms($page, 'theme') }} type-{{ cms($page, 'type') }}">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="{{ cmsroute($page->ancestors?->first() ?? $page) }}">
                    {{ config('app.name') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        @foreach($page->nav() as $item)
                            <li class="nav-item">
                                <a class="nav-link {{ $item->children->count() ? 'dropdown-toggle' : '' }} {{ $page->isSelfOrDescendantOf($item) ? 'active' : '' }}"
                                    href="{{ cmsroute($item) }}"
                                    @if($item->children->count()) role="button" aria-expanded="false" data-bs-toggle="dropdown" @endif
                                    @if($page->is($item)) aria-current="page" @endif>
                                    {{ cms($item, 'name') }}
                                </a>
                                @if($item->children->count())
                                    <ul class="dropdown-menu">
                                        @foreach($item->children as $subItem)
                                            <li>
                                                <a class="dropdown-item {{ $page->isSelfOrDescendantOf($subItem) ? 'active' : '' }}"
                                                    href="{{ cmsroute($subItem) }}"
                                                    @if($page->is($subItem)) aria-current="page" @endif>
                                                    {{ cms($subItem, 'name') }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </nav>

        @if($page->ancestors->count() > 1)
            <nav class="container" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @foreach($page->ancestors ?? [] as $item)
                        @if(cms($item, 'status') === 1)
                            <li class="breadcrumb-item">
                                <a href="{{ cmsroute($item) }}">{{ cms($item, 'name') }}</a>
                            </li>
                        @endif
                    @endforeach
                    <li class="breadcrumb-item active" aria-current="page">{{ cms($page, 'name') }}</li>
                </ol>
            </nav>
        @endif

        <div class="cms-content">
            @foreach($content['main'] ?? [] as $item)
                @if(@$item->type === 'reference' && ($refid = @$item->refid) && ($element = @cms($page,'elements')?->{$refid}))
                    <div id="{{ @$item->id }}" class="{{ str_replace('::', '-', @$element->type) }}">
                        <div class="container">
                            @includeFirst(cmsview($page, $element), ['files' => cms($page, 'files')] + ['data' => (array) @$element->data])
                        </div>
                    </div>
                @else
                    <div id="{{ @$item->id }}" class="{{ str_replace('::', '-', @$item->type) }}">
                        <div class="container">
                            @includeFirst(cmsview($page, $item), ['files' => cms($page, 'files')] + (array) $item)
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        @stack('css')
        @stack('js')
    </body>
</html>
