@if($file = cms($files, @$data->file?->id))
	@include('cms::pic', ['file' => $file, 'class' => 'image ' . (@$data->position ?? 'auto')])
@endif

<div class="text">
	@markdown(@$data->text)
</div>
