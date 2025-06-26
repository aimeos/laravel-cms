@if($file = $files[$data->file?->id] ?? null)
	@include('cms::pic', ['file' => $file, 'class' => 'image ' . ($data->position ?? 'auto')])
@endif

<div class="text">
	@markdown($data->text)
</div>
