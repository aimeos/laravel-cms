@extends('cms::layouts.main')

@section('header')
    <nav>
        <ul>
            <li>
                <a href="{{ cmsroute($page->ancestors?->first() ?? $page) }}" class="contrast">
                    <strong>{{ config('app.name') }}</strong>
                </a>
            </li>
        </ul>
        <ul>
            @foreach($page->nav() as $item)
                <li>
                    @if($item->children->count())
                        <details class="dropdown">
                            <summary role>{{ cms($item, 'name') }}</summary>
                            <ul>
                                @foreach($item->children as $subItem)
                                    <li>
                                        <a href="{{ cmsroute($subItem) }}" class="{{ !$page->isSelfOrDescendantOf($subItem) ?: 'active' }}">
                                            {{ cms($subItem, 'name') }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    @else
                        <a href="{{ cmsroute($item) }}" class="{{ !$page->isSelfOrDescendantOf($item) ?: 'active' }} contrast">
                            {{ cms($item, 'name') }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>

    @if($page->ancestors->count() > 1)
        <nav aria-label="breadcrumb">
            <ul>
                @foreach($page->ancestors ?? [] as $item)
                    @if(cms($item, 'status') === 1)
                        <li>
                            <a href="{{ cmsroute($item) }}">{{ cms($item, 'name') }}</a>
                        </li>
                    @endif
                @endforeach
                <li>{{ cms($page, 'name') }}</li>
            </ul>
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