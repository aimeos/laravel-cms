<!doctype html>
<html lang="en">
    <head>
        <title>{{ $page->title }}</title>
        @foreach($page->data ?? [] as $item)
            @includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
        @endforeach
    </head>
    <body>
        <nav>
            <ul>
                @foreach(\Aimeos\Cms\Models\Page::nav('root')->children ?? [] as $item)
                    @if($item->status === 1 )
                        <li><a href="{{ route('cms.page', ['slug' => $item->to ?: $item->slug]) }}">
                            {{ $item->name }}
                        </a></li>
                    @endif
                @endforeach
            </ul>
        </nav>
        <small>
            @foreach($page->ancestors ?? [] as $item)
                @if($item->status === 1 )
                    <a href="{{ route('cms.page', ['slug' => $item->to ?: $item->slug]) }}">
                        &gt; {{ $item->name }}
                    </a>
                @endif
            @endforeach
        </small>
        <div class="container-xxl">
            @foreach($page->content->data ?? [] as $item)
                @includeFirst([$item['type'] ?? '', 'cms::invalid'], ['data' => $item])
            @endforeach
        </div>
    </body>
</html>
