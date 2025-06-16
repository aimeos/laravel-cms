<video data-cid="{{ $cid ?? '' }}" class="cms-video" preload="metadata" controls
	src="{{ cmsurl( $files[$data['file']['id']]->path ?? '' ) }}"
@if( current( $files[$data['file']['id']]->previews ) ?? '' ) )
	poster="{{ cmsurl( current( $files[$data['file']['id']]->previews ) ?? '' ) }}">
@endif
	{{ __('Download') }}: <a href="{{ cmsurl( $files[$data['file']['id']]->path ?? '' ) }}">{{ cmsurl( $files[$data['file']['id']]->path ?? '' ) }}</a>
</video>