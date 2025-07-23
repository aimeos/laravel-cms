@if($file = cms($files, @$data->file?->id))
	@include('cms::pic', ['file' => $file, 'main' => @$data->main])
@else
	<!-- no image file -->
@endif
