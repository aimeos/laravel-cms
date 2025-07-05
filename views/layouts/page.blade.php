@extends('cms::layouts.main')

@section('header')
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
@endsection


@section('main')
    <div class="cms-content">
        @foreach($content['main'] ?? [] as $item)
            @if($el = cmsref($page, $item))
                <div id="{{ cmsid(@$item->id) }}" class="{{ cmsid(@$el->type) }}">
                    <div class="container">
                        @includeFirst(cmsviews($page, $el), cmsdata($page, $el))
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endsection


@section('footer')
    <footer class="cms-content">
        @foreach($content['footer'] ?? [] as $item)
            @if($el = cmsref($page, $item))
                <div id="{{ cmsid(@$item->id) }}" class="{{ cmsid(@$el->type) }}">
                    <div class="container">
                        @includeFirst(cmsviews($page, $el), cmsdata($page, $el))
                    </div>
                </div>
            @endif
        @endforeach
    </footer>
@endsection