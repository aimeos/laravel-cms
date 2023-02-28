<blockquote class="cms-quote">
    <p>@includeFirst([$data['type'] ?? '', 'cms::invalid'], $data['quote'] ?? '')</p>
@if($data['cite'] ?? null)
    <cite>@includeFirst([$data['type'] ?? '', 'cms::invalid'], $data['cite'])</cite>
@endif
</blockquote>