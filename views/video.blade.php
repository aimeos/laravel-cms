<video class="cms-video" controls src="{{ $data['source'] ?? '' }}" poster="{{ $data['poster'] ?? '' }}">
  {{ __('Download') }}: <a href="{{ $data['source'] ?? '' }}">{{ $data['source'] ?? '' }}</a>
</video>