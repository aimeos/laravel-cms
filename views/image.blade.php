<picture data-cid="{{ $cid ?? '' }}" class="cms-image" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
	<meta itemprop="representativeOfPage" content="{{ ( $data['main'] ?? null ) ? 'true' : 'false' }}">
	@foreach( $files[$data['file']['id']]?->previews ?? [] as $width => $path )
		<source srcset="{{ cmsurl( $path ) }}"
			media="(min-width: {{ $width }}px)"
			width="{{ $width }}">
	@endforeach
	<img class="img-fluid" itemprop="contentUrl" {{ ( $data['main'] ?? null ) ? '' : 'loading="lazy"' }}
		src="{{ cmsurl( current( $files[$data['file']['id']]?->previews ?? [] ) ?: '' ) }}"
		width="{{ key( $files[$data['file']['id']]?->previews ?? [] ) }}"
		alt="{{ $files[$data['file']['id']]?->description?->{$page->lang} ?? '' }}">
</picture>
