@props(['label', 'type' => 'text', 'layoutClass' => ''])

<div class="{{ $layoutClass ?? '' }}">
    @if ($label)
        <label class="block mb-2 font-medium"> {{ $label }} </label>
    @endif
    <input type="{{ $type }}"
        {{ $attributes->merge(['class' => 'w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500']) }} />
</div>
