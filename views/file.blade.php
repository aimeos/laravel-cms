<div data-cid="{{ $cid ?? '' }}">
	<a class="cms-file" href="{{ cmsurl( $files[$data['file']['id']]->path ?? '' ) }}">
		{{ _('Download file') }}
	</a>
</div>
