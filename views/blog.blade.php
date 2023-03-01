@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/blog.css?v=1') }}">
@endPushOnce

<div class="cms-blog">
@foreach( $page->children as $blogPage )
	@if( $article = current( array_filter( $blogPage->content?->data ?? [], fn($item) => $item['type'] === 'cms::article' ) ) )
		@if($loop->first)

		<a class="cms-blog-item" href="{{ route('cms.page', ['slug' => $blogPage->slug, 'lang' => $blogPage->lang]) }}">

			@includeFirst([$article['image']['type'] ?? '', 'cms::invalid'], ['data' => $article['image'] ?? [], 'main' => true])

			<h2>@includeFirst([$article['title']['type'] ?? '', 'cms::invalid'], ['data' => $article['title'] ?? []])</h2>

			<div class="lead">@includeFirst([$article['intro']['type'] ?? '', 'cms::invalid'], ['data' => $article['intro'] ?? []])</div>
		</a>

		@else

		<a class="cms-blog-item" href="{{ route('cms.page', ['slug' => $blogPage->slug, 'lang' => $blogPage->lang]) }}">
			<div class="row">
				<div class="col-md-6">
					@includeFirst([$article['image']['type'] ?? '', 'cms::invalid'], ['data' => $article['image'] ?? [], 'main' => true])
				</div>
				<div class="col-md-6">
					<h2>@includeFirst([$article['title']['type'] ?? '', 'cms::invalid'], ['data' => $article['title'] ?? []])</h2>

					<div class="lead">@includeFirst([$article['intro']['type'] ?? '', 'cms::invalid'], ['data' => $article['intro'] ?? []])</div>
				</div>
			</div>
		</a>

		@endif
	@endif
@endforeach
</div>