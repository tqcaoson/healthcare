<div
    x-data="{ visible: false }"
    class="relative"
>

    <input
        :type="visible ? 'text' : 'password'"
        {{ $attributes->merge([
            'class' => 'form-input pr-14'
        ]) }}
    >

    <button
        type="button"
        class="absolute right-4 top-3 text-sm text-slate-500"
        @click="visible = !visible"
    >
        <span x-text="visible ? 'Hide' : 'Show'"></span>
    </button>
</div>