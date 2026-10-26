@props(['name', 'label', 'type' => 'text'])
<div class="master-field">
    <label for="master-{{ $name }}">{{ $label }}</label>
    <input id="master-{{ $name }}" name="{{ $name }}" type="{{ $type }}" x-model="form.{{ $name }}" x-bind:aria-invalid="Boolean(errors['{{ $name }}'])" aria-describedby="master-{{ $name }}-error" {{ $attributes }}>
    <p id="master-{{ $name }}-error" class="master-field-error" x-show="errors['{{ $name }}']" x-text="errors['{{ $name }}']?.[0]"></p>
</div>
