<table class="cms-table">
	@if($title ?? null)
		<caption>
			{{ $title ?? '' }}
		</caption>
	@endif
	<tr>
		@foreach((array) str_getcsv($content ?? '') as $rowidx => $row)
			@foreach((array) $row as $colidx => $col)
				@if($rowidx === 0 && ($header ?? null) === 'row'
					|| $colidx === 0 && ($header ?? null) === 'col'
					|| ($rowidx === 0 || $colidx === 0) && ($header ?? null) === 'row+col')
					<th>
				@else
					<td>
				@endif
					<?= (new \League\CommonMark\GithubFlavoredMarkdownConverter([
							'html_input' => 'escape',
							'allow_unsafe_links' => false,
							'max_nesting_level' => 25
						]))->convert((string) $col)
					?>
				@if($rowidx === 0 && ($header ?? null) === 'row'
					|| $colidx === 0 && ($header ?? null) === 'col'
					|| ($rowidx === 0 || $colidx === 0) && ($header ?? null) === 'row+col')
					</th>
				@else
					<td>
				@endif
			@endforeach
		@endforeach
	</tr>
</table