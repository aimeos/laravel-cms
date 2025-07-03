<picture class="{{ $class ?? '' }}" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
	<meta itemprop="representativeOfPage" content="{{ ( $main ?? null ) ? 'true' : 'false' }}">
    @if($preview = current($file?->previews ?? []))
        <img class="img-fluid" itemprop="contentUrl" {{ ( $main ?? null ) ? '' : 'loading="lazy"' }}
            srcset="{{ cmssrcset($file?->previews) }}"
            src="{{ cmsurl($preview) }}"
            alt="{{ $file?->description?->{$page->lang ?? 'en'} ?? '' }}">
    @endif
</picture>
