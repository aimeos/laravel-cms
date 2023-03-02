@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/blog.css?v=1') }}">
@endPushOnce

<div class="cms-blog">
@foreach( $page->children as $blogPage )

	@if( $article = $blogPage->content->filter( fn($item) => $item->data['type'] === 'cms::article' )->first()?->data )
		@if($loop->first)

		<a class="cms-blog-item" href="{{ $blogPage->to ?: route('cms.page', ['slug' => $blogPage->slug, 'lang' => $blogPage->lang]) }}">

			@includeFirst([$article['cover']['type'] ?? '', 'cms::invalid'], ['data' => $article['cover'] ?? [], 'main' => true])

			<h2>@includeIf('cms::string', ['data' => $article['title'] ?? null])</h2>

			<div class="lead">@includeIf('cms::text', ['data' => $article['intro'] ?? null])</div>
		</a>

		@else
		<?= 'FOLLOW ARTICLE' ?>

		<a class="cms-blog-item" href="{{ $blogPage->to ?: route('cms.page', ['slug' => $blogPage->slug, 'lang' => $blogPage->lang]) }}">
			<div class="row">
				<div class="col-md-6">
					@includeFirst([$article['cover']['type'] ?? '', 'cms::invalid'], ['data' => $article['cover'] ?? [], 'main' => true])
				</div>
				<div class="col-md-6">
					<h2>@includeIf('cms::string', ['data' => $article['title'] ?? null])</h2>

					<div class="lead">@includeIf('cms::text', ['data' => $article['intro'] ?? null])</div>
				</div>
			</div>
		</a>

		@endif
	@endif
@endforeach
</div>