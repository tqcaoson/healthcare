<div
    x-data="barcodeScanner"
    class="bg-black rounded-2xl overflow-hidden relative"
>

    <video
        x-ref="video"
        autoplay
        playsinline
        class="w-full h-[400px] object-cover"
    ></video>

    <div
        class="absolute inset-0 border-[3px] border-red-500 m-20 rounded-2xl"
    ></div>

    <div class="absolute bottom-5 left-5 right-5">

        <div class="bg-white rounded-xl px-4 py-3 text-center font-semibold">

            <span x-text="result || 'Waiting for scan...'"></span>

        </div>

    </div>
</div>