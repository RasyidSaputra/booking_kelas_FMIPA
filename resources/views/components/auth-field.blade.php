@props(['name', 'label', 'type' => 'text', 'hint' => null])

<div class="auth-field" @if ($type === 'password') x-data="{ visible: false }" @endif>
    <label for="{{ $name }}">{{ $label }}</label>
    <div class="auth-input-wrap">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($type === 'password') x-bind:type="visible ? 'text' : 'password'" @else value="{{ old($name) }}" @endif
            aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            @if ($errors->has($name) || $hint) aria-describedby="{{ $errors->has($name) ? $name.'-error' : $name.'-hint' }}" @endif
            {{ $attributes->class(['auth-input', 'auth-input-password' => $type === 'password']) }}
        >
        @if ($type === 'password')
            <button type="button" class="auth-password-toggle" x-on:click="visible = !visible" x-bind:aria-label="visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" aria-label="Tampilkan kata sandi" x-bind:aria-pressed="visible.toString()" aria-controls="{{ $name }}" x-cloak>
                <svg x-show="!visible" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.5"/></svg>
                <svg x-show="visible" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m3 3 18 18M9.8 5.7A11 11 0 0 1 12 5.5c6 0 9.5 6.5 9.5 6.5a19 19 0 0 1-3.1 3.9M6.1 6.1A20 20 0 0 0 2.5 12s3.5 6.5 9.5 6.5a11 11 0 0 0 4.3-.9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </button>
        @endif
    </div>
    @if ($hint)
        <p id="{{ $name }}-hint" class="auth-field-hint">{{ $hint }}</p>
    @endif
    @if ($errors->has($name))
        <div id="{{ $name }}-error" class="auth-field-error" role="alert">
            <x-input-error :messages="$errors->get($name)" />
        </div>
    @endif
</div>
