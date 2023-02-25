<div class="cms-image {{ ( $main ?? null ) ? 'cms-image-main' : '' }}" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
    <meta itemprop="representativeOfPage" content="{{ ( $main ?? null ) ? 'true' : 'false' }}">
    <img class="img-fluid" itemprop="contentUrl" {{ ( $main ?? null ) ? '' : 'loading="lazy"' }}
        src="{{ cmsurl( current( $data['previews'] ?? [] ) ) }}"
        srcset="{{ cmsimage( $data['previews'] ?? [] ) }}"
        sizes="{{ $sizes ?? '100vw' }}"
        alt="{{ $data['name'] ?? '' }}">
</div>
