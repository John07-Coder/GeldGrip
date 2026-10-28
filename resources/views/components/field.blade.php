@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'id' => null])
@php($id = $id ?? $name)
<div class="field">
    <label for="{{ $id }}">{{ $label }}</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
        value="{{ $type === 'password' ? '' : old($name, $value) }}"
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror {{ $attributes }}>
    @if ($hint)
        <small>{{ $hint }}</small>
    @endif
    @error($name)
        <span class="field-error" id="{{ $name }}-error">{{ $message }}</span>
    @enderror
</div>
