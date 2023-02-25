@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/blog.css?v=1') }}">
@endPushOnce

<div class="cms-blog">
    @foreach( $page->children as $blogPage )
        @if( $article = current( array_filter( $blogPage->content?->data ?? [], fn($item) => $item['type'] === 'cms::article' ) ) )
            @if($loop->first)
                <a class="cms-blog-item" href="{{ route('cms.page', ['slug' => $blogPage->slug, 'lang' => $blogPage->lang]) }}">
                    @includeIf('cms::image', ['data' => $article['image'], 'main' => true])
                    <h2>{{ $article['title'] ?? '' }}</h2>
                    <p class="lead">{{ $article['intro'] ?? '' }}</p>
                </a>
            @else
                <a class="cms-blog-item" href="{{ route('cms.page', ['slug' => $blogPage->slug, 'lang' => $blogPage->lang]) }}">
                    <div class="row">
                        <div class="col-md-6">
                            @includeIf('cms::image', ['data' => $article['image']])
                        </div>
                        <div class="col-md-6">
                            <h2>{{ $article['title'] ?? '' }}</h2>
                            <p class="lead">{{ $article['intro'] ?? '' }}</p>
                        </div>
                    </div>
                </a>
            @endif
        @endif
    @endforeach
</div>