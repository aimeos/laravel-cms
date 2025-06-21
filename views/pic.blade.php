<picture class="{{ $class ?? '' }}" itemscope itemprop="image" itemtype="http://schema.org/ImageObject">
	<meta itemprop="representativeOfPage" content="{{ ( $main ?? null ) ? 'true' : 'false' }}">
	@foreach($file?->previews ?? [] as $width => $path)
		<source srcset="{{ cmsurl($path) }}"
			media="(min-width: {{ $width }}px)"
			width="{{ $width }}">
	@endforeach
    @if($preview = current($file?->previews ?? []))
        <img class="img-fluid" itemprop="contentUrl" {{ ( $main ?? null ) ? '' : 'loading="lazy"' }}
            src="{{ cmsurl($preview) }}"
            width="{{ key($file?->previews ?? []) }}"
            alt="{{ $file?->description?->{$page->lang ?? 'en'} ?? '' }}">
    @endif
</picture>
