<div class="cms-blog">
    @foreach( $page->children as $blogPage )
        @if( $article = current( array_filter( $blogPage->content?->data ?? [], fn($item) => $item['type'] === 'cms::article' ) ) )
            <a class="cms-blog-item" href="{{ route('cms.page', ['slug' => $blogPage->slug, 'lang' => $blogPage->lang]) }}">
                <div class="row">
                    <div class="col-md-6">
                        @includeIf('cms::image', ['data' => $article['image']])
                    </div>
                    <div class="col-md-6">
                        <h2>{{ $article['title'] ?? '' }}</h2>
                        <p>{{ $article['intro'] ?? '' }}</p>
                    </div>
                </div>
            </a>
        @endif
    @endforeach
</div>