@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ asset('vendor/cms/article.css') }}">
@endPushOnce

<div class="cms-article">
	<h1>{{ $title }}</h1>

	@includeIf('cms::image', ['main' => true] + ($intro ?? []) ))

	<div class="lead">
		<?= (new \League\CommonMark\GithubFlavoredMarkdownConverter([
				'html_input' => 'escape',
				'allow_unsafe_links' => false,
				'max_nesting_level' => 25
			]))->convert($text ?? '')
		?>
	</div>
</div>