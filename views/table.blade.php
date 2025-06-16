<table data-cid="{{ $cid ?? '' }}" class="cms-table">
@foreach(\League\Csv\Reader::createFromString( $data['table'] )->setDelimiter( ';' )->getRecords() as $rowidx => $row)
	<tr>
	@foreach((array) $row as $colidx => $col)
		@if($rowidx === 0 && ($data['header'] ?? null) === 'row'
			|| $colidx === 0 && ($data['header'] ?? null) === 'col'
			|| ($rowidx === 0 || $colidx === 0) && ($data['header'] ?? null) === 'row+col')

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
		@if($rowidx === 0 && ($data['header'] ?? null) === 'row'
			|| $colidx === 0 && ($data['header'] ?? null) === 'col'
			|| ($rowidx === 0 || $colidx === 0) && ($data['header'] ?? null) === 'row+col')
		</th>
		@else
		</td>
		@endif
	@endforeach
	</tr>
@endforeach
@if( $data['title'] ?? null )
	<caption>{{ $data['title'] ?? '' }}</caption>
@endif
</table>
