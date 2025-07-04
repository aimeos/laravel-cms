<div class="blog-list">
    @foreach(@$action ?? [] as $item)
        <div class="blog-item">
            <h2>{{ cms($item, 'title') }}</h2>
            @if($article = collect(cms($item, 'content'))->first(fn($el) => @$el->type === 'article')?->data)
                @if($file = $files[@$article->file?->id] ?? null)
                    @include('cms::pic', ['file' => $file])
                @endif
                <p>{{ @$article->lead }}</p>
            @endif
            <a href="{{ route('cms.page', ['path' => @$item->path]) }}">Read more</a>
        </div>
    @endforeach

    {{ @$action?->appends(request()->query())?->links() }}
</div>
