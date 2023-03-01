<blockquote class="cms-quote">
@includeFirst([$data['quote']['type'] ?? '', 'cms::invalid'], ['data' => $data['quote'] ?? []])

@if($data['cite'] ?? null)
	<cite>@includeFirst([$data['cite']['type'] ?? '', 'cms::invalid'], ['data' => $data['cite'] ?? []])</cite>
@endif
</blockquote>