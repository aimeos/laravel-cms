<picture class="cms-image" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
	<meta itemprop="representativeOfPage" content="{{ ( $main ?? null ) ? 'true' : 'false' }}">
	@foreach( $image['previews'] ?? [] as $width => $path )
		<source srcset="<{{ cmsurl( $path ) }}"
			media="(min-width: {{ $width }}px)"
			width="{{ $width }}">
	@endforeach
	<img class="img-fluid" itemprop="contentUrl" {{ ( $main ?? null ) ? '' : 'loading="lazy"' }}
		src="{{ cmsurl( current( $image['previews'] ?? [] ) ?: '' ) }}"
		width="{{ key( $image['previews'] ?? [] ) }}"
		alt="{{ $image['name'] ?? '' }}">
</picture>
