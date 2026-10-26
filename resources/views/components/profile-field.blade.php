@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'errorBag' => 'default', 'id' => null])
@php
    $fieldId = $id ?? $name;
    $messages = $errors->getBag($errorBag)->get($name);
@endphp
<div class="profile-field" @if($type === 'password') x-data="{ visible: false }" @endif>
    <label for="{{ $fieldId }}">{{ $label }}</label>
    <div class="profile-input-wrap">
        <input
            id="{{ $fieldId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if($type === 'password') x-bind:type="visible ? 'text' : 'password'" @else value="{{ old($name, $value) }}" @endif
            aria-invalid="{{ $messages ? 'true' : 'false' }}"
            @if($messages || $hint) aria-describedby="{{ $messages ? $fieldId.'-error' : $fieldId.'-hint' }}" @endif
            {{ $attributes->class(['profile-input', 'is-password' => $type === 'password']) }}
        >
        @if($type === 'password')
            <button class="profile-password-toggle" type="button" x-on:click="visible = !visible" aria-label="Tampilkan kata sandi" x-bind:aria-label="visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" x-bind:aria-pressed="visible.toString()" aria-controls="{{ $fieldId }}" x-cloak>
                <span x-show="!visible"><x-dashboard-icon name="eye" /></span>
                <span x-show="visible"><x-dashboard-icon name="eye-off" /></span>
            </button>
        @endif
    </div>
    @if($hint)<p id="{{ $fieldId }}-hint" class="profile-field-hint">{{ $hint }}</p>@endif
    @if($messages)<div id="{{ $fieldId }}-error" class="profile-field-error" role="alert"><x-input-error :messages="$messages" /></div>@endif
</div>
