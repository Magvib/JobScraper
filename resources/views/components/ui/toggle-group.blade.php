{{-- Segmented control. Children: <x-ui.toggle-item :active="..."> --}}
<div role="group" {{ $attributes->merge(['class' => 'inline-flex items-center rounded-md shadow-xs']) }}>{{ $slot }}</div>
