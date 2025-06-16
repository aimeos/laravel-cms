<audio data-cid="{{ $cid ?? '' }}" class="cms-audio" preload="metadata" controls
	src="{{ cmsurl( $files[$data['file']['id']]->path ?? '' ) }}"
></audio>
