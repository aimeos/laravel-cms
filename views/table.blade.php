<table class="cms-table">
@if(($data['header'] ?? false) && is_array($data['contents']))
    <tr>
    @foreach((array) array_shift($data['contents']) as $item)
        <td>@includeFirst([$item['type'] ?? '', 'cms::invalid'], $item)</td>
    @endforeach
    </tr>
@endif
@foreach($data['contents'] ?? [] as $row)
    <tr>
    @foreach((array) $row as $item)
        <td>@includeFirst([$item['type'] ?? '', 'cms::invalid'], $item)</td>
    @endforeach
    </tr>
@endforeach
</table