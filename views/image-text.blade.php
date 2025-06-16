<div data-cid="{{ $cid ?? '' }}" class=cms-image-text">
	<picture class="cms-image position-{{ $data['position'] ?? 'auto' }}" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
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
	<div class="cms-text">
		<?= (new \League\CommonMark\GithubFlavoredMarkdownConverter([
				'html_input' => 'escape',
				'allow_unsafe_links' => false,
				'max_nesting_level' => 25
			]))->convert($data['text'] ?? '')
		?>
	</div>
</div>
