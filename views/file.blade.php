@if($file = $files[$data['file']['id']] ?? null)
	<a href="{{ cmsurl($file->path ?? '') }}">
		{{ _('Download file') }}
	</a>
@else
	<!-- no file -->
@endif
