@if($file = $files[$data->file?->id] ?? null)
	@include('cms::pic', ['file' => $file, 'main' => $data->main ?? false])
@else
	<!-- no image file -->
@endif
