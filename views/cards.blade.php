<div class="cms-cards">
	@foreach($cards ?? [] as $card)
		<div class="cms-card">
			<div class="cms-card-header">
				{{ $card['title'] ?? '' }}
			</div>
			<div class="cms-card-body">
				<picture class="cms-image" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
					@foreach( $card['image']['previews'] ?? [] as $width => $path )
						<source srcset="<{{ cmsurl( $path ) }}"
							media="(min-width: {{ $width }}px)"
							width="{{ $width }}">
					@endforeach
					<img class="img-fluid" itemprop="contentUrl" loading="lazy"
						src="{{ cmsurl( current( $card['image']['previews'] ?? [] ) ?: '' ) }}"
						width="{{ key( $card['image']['previews'] ?? [] ) }}"
						alt="{{ $card['image']['name'] ?? '' }}">
				</picture>
				<div class="cms-string">{{ $card['name'] ?? '' }}</div>
				<div class="cms-text">
					<?= (new \League\CommonMark\GithubFlavoredMarkdownConverter([
							'html_input' => 'escape',
							'allow_unsafe_links' => false,
							'max_nesting_level' => 25
						]))->convert($card['text'] ?? '')
					?>
				</div>
			</div>
		</div>
	@endforeach
</div>