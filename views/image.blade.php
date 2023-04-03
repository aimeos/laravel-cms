<picture class="cms-picture {{ ( $main ?? false ) ? 'cms-image-main' : '' }}" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
	<meta itemprop="representativeOfPage" content="{{ ( $main ?? false ) ? 'true' : 'false' }}">

@foreach( $data['previews'] ?? [] as $preview )
	<source srcset="<?= cmsurl( $preview['path'] ) ?>"
		media="(min-width: <?= round( $preview['width'] / 16 ) ?>em)"
		height="<?= $preview['height'] ?? '' ?>"
		width="<?= $preview['width'] ?? '' ?>">
@endforeach

	<img class="img-fluid" itemprop="contentUrl" {{ ( $main ?? false ) ? '' : 'loading="lazy"' }}
		src="{{ cmsurl( current( $data['previews'] ?? [] ) ?: '' ) }}"
		height="<?= $preview['height'] ?? '' ?>"
		width="<?= $preview['width'] ?? '' ?>"
		alt="{{ $data['name'] ?? '' }}">
</picture>
