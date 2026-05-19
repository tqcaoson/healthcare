<div
    x-data="fileUploader"
    class="border-2 border-dashed border-slate-300 rounded-2xl p-10 text-center bg-slate-50"
    @dragover.prevent
    @drop.prevent="drop($event)"
>

    <div class="text-4xl mb-4">
        📄
    </div>

    <div class="font-semibold text-lg">
        Drop files here
    </div>

    <div class="text-sm text-slate-500 mt-2">
        or click to browse
    </div>

    <input
        type="file"
        class="hidden"
        x-ref="input"
        @change="upload"
        multiple
    >

    <button
        class="btn-primary mt-5"
        @click="$refs.input.click()"
    >
        Select Files
    </button>
</div>