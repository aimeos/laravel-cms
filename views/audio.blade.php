@once('caption')
	@push('js')
		<script defer src="{{ cmsasset('vendor/cms/caption.js') }}"></script>
	@endpush
@endonce

@if($file = $files[@$data->file?->id] ?? null)
	<audio preload="metadata" controls
		title="{{ @cms($file, 'description')?->{cms($page, 'lang')} }}"
		src="{{ cmsurl(@$file->path) }}">
		<div class="transcription" lang="{{ cms($page, 'lang') }}">{{ @cms($file, 'transcription')?->{cms($page, 'lang')} }}</div>
	</audio>
	<div class="caption"></div>
@else
	<!-- no audio file -->
@endif
