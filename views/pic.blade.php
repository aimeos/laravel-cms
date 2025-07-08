<picture class="{{ @$class }}" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
	<meta itemprop="representativeOfPage" content="{{ @$main ? 'true' : 'false' }}">
    @if($preview = current($file?->previews ?? []))
        <img itemprop="contentUrl" loading="{{ @$main ?: 'lazy' }}"
            srcset="{{ cmssrcset($file?->previews) }}"
            src="{{ cmsurl($preview) }}"
            alt="{{ @$file?->description?->{$page->lang ?? 'en'} }}">
    @endif
</picture>
