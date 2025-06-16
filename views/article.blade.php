@pushOnce('css')
<link type="text/css" rel="stylesheet" href="{{ cmsasset('vendor/cms/article.css') }}">
@endPushOnce

<div data-cid="{{ $cid ?? '' }}" class="cms-article">
	<h1>{{ $data['title'] }}</h1>

	<picture class="cms-image" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
@foreach( $files[$data['file']['id']]?->previews ?? [] as $width => $path )
		<source srcset="{{ cmsurl( $path ) }}"
			media="(min-width: {{ $width }}px)"
			width="{{ $width }}">
@endforeach
		<img class="img-fluid" itemprop="contentUrl" loading="lazy"
			src="{{ cmsurl( current( $files[$data['file']['id']]?->previews ?? [] ) ?: '' ) }}"
			width="{{ key( $files[$data['file']['id']]?->previews ?? [] ) }}"
			alt="{{ $files[$data['file']['id']]?->description?->{$page->lang} ?? '' }}">
	</picture>

	<div class="lead">
		<?= (new \League\CommonMark\GithubFlavoredMarkdownConverter([
				'html_input' => 'escape',
				'allow_unsafe_links' => false,
				'max_nesting_level' => 25
			]))->convert($data['text'] ?? '')
		?>
	</div>
</div>
