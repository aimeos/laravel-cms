@if($data['subtype'] ?? '' === 'ol')
    <ol class="cms-list" @if($data['reversed'] ?? false) reversed @endif @if($data['start'] ?? 1) start="{{ (int) $data['start'] ?? 1 }}" @endif>
@else
    <ul class="cms-list">
@endif
@foreach($data['contents'] ?? [] as $item)
    <li>@includeFirst([$item['type'] ?? '', 'cms::invalid'], $item)</li>
@endforeach
@if($data['subtype'] ?? '' === 'ol')
    </ol>
@else
    </ul>
@endif