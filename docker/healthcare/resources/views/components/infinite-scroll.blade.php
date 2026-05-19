<div
    x-data="infiniteScroll"
    class="space-y-4"
>

    <template x-for="item in items">

        <div class="bg-white rounded-2xl shadow-sm p-5">

            <div class="font-semibold">
                <span x-text="item.title"></span>
            </div>

            <div class="text-sm text-slate-500 mt-2">
                <span x-text="item.description"></span>
            </div>

        </div>

    </template>

    <div
        x-intersect="loadMore"
        class="h-10"
    ></div>
</div>