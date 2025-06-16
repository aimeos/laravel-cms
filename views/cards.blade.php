<div data-cid="{{ $cid ?? '' }}" class="cms-cards">
@foreach($data['cards'] ?? [] as $card)
	<div class="cms-card">
		<div class="cms-title">{{ $card['title'] ?? '' }}</div>
		<picture class="cms-image" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
	@foreach( $files[$card['file']['id']]?->previews ?? [] as $width => $path )
			<source srcset="{{ cmsurl( $path ) }}"
				media="(min-width: {{ $width }}px)"
				width="{{ $width }}">
	@endforeach
			<img class="img-fluid" itemprop="contentUrl" loading="lazy"
				src="{{ cmsurl( current( $files[$card['file']['id']]?->previews ?? [] ) ?: '' ) }}"
				width="{{ key( $files[$card['file']['id']]?->previews ?? [] ) }}"
				alt="{{ $files[$card['file']['id']]?->description?->{$page->lang} ?? '' }}">
		</picture>
		<div class="cms-text">
			<?= (new \League\CommonMark\GithubFlavoredMarkdownConverter([
					'html_input' => 'escape',
					'allow_unsafe_links' => false,
					'max_nesting_level' => 25
				]))->convert($card['text'] ?? '')
			?>
		</div>
	</div>
@endforeach
</div>
