@if($file = $files[@$data->file?->id] ?? null)
	<audio preload="metadata" controls src="{{ cmsurl(@$file->path) }}"></audio>
@else
	<!-- no audio file -->
@endif
