<video class="cms-video" controls src="{{ cmsurl( $data['source'] ?? '' ) }}" poster="{{ cmsurl( $data['poster'] ?? '' ) }}">
	{{ __('Download') }}: <a href="{{ cmsurl( $data['source'] ?? '' ) }}">{{ cmsurl( $data['source'] ?? '' ) }}</a>
</video>