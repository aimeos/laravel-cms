@once('caption')
	@push('js')
		<script defer src="{{ cmsasset('vendor/cms/caption.js') }}"></script>
	@endpush
@endonce

@if($file = $files[@$data->file?->id] ?? null)
	<video preload="metadata" controls playsinline
		title="{{ @cms($file, 'description')?->{cms($page, 'lang')} }}"
		src="{{ cmsurl(@$file->path) }}"
		@if($preview = @current($file->previews ?? []))
			poster="{{ cmsurl($preview) }}"
		@endif
	>
		{{ __('Download file') }}: <a href="{{ cmsurl(@$file->path) }}">{{ cmsurl(@$file->path) }}</a>
		<div class="transcription" lang="{{ cms($page, 'lang') }}">{{ @cms($file, 'transcription')?->{cms($page, 'lang')} }}</div>
	</video>
	<div class="caption"></div>
@else
	<!-- no video file -->
@endif
