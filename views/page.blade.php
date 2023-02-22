<!doctype html>
<html lang="en">
    <head>
        <title>{{ $page->title }}</title>
        @foreach($page->data ?? [] as $item)
            @include($item['type'], ['data' => $item])
        @endforeach
    </head>
    <body>
        <div>
            <ul>
                @foreach(\Aimeos\Cms\Models\Page::nav('root')->children ?? [] as $item)
                    <li><a href="{{ route('cms.page', ['slug' => $item->to ?: $item->slug]) }}">
                        {{ $item->title }}
                    </a></li>
                @endforeach
            </ul>
        </div>
        <h1>Slug: {{ $page->slug }}</h1>
        <p>{{ cmsurl( 'path/to/file.jpg' ) }}</p>
        <div>
            @foreach($page->content->data ?? [] as $item)
                @include($item['type'] ?? 'cms::text', ['data' => $item])
            @endforeach
        </div>
    </body>
</html>
