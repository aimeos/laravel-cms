<picture class="{{ @$class }}" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
	<meta itemprop="representativeOfPage" content="{{ @$main ? 'true' : 'false' }}">
    @if($preview = current(array_reverse((array) $file?->previews ?? [])))
        <img itemprop="contentUrl" loading="{{ @$main ?: 'lazy' }}"
            srcset="{{ cmssrcset($file?->previews) }}"
            src="{{ cmsurl($preview) }}"
            alt="{{ @cms($file, 'description')?->{cms($page, 'lang')} }}">
    @endif
</picture>
