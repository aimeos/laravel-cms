<div class="cms-image {{ ( $main ?? false ) ? 'cms-image-main' : '' }}" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
	<meta itemprop="representativeOfPage" content="{{ ( $main ?? false ) ? 'true' : 'false' }}">
	<img class="img-fluid" itemprop="contentUrl" {{ ( $main ?? false ) ? '' : 'loading="lazy"' }}
		src="{{ cmsurl( current( $data['previews'] ?? [] ) ) }}"
		srcset="{{ cmsimage( $data['previews'] ?? [] ) }}"
		sizes="{{ $sizes ?? '100vw' }}"
		alt="{{ $data['name'] ?? '' }}">
</div>
