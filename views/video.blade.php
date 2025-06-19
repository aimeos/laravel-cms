@if($file = $files[$data['file']['id']] ?? null)
	<video preload="metadata" controls playsinline
		src="{{ cmsurl($file->path ?? '') }}"
		@if($preview = current($file->previews))
			poster="{{ cmsurl($preview) }}">
		@endif
		{{ __('Download') }}: <a href="{{ cmsurl($file->path ?? '') }}">{{ cmsurl($file->path ?? '') }}</a>
	</video>
@else
	<!-- no video file -->
@endif
