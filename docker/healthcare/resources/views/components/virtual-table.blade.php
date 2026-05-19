<div
    x-data="virtualTable"
    class="overflow-auto h-[700px]"
    @scroll="handleScroll"
>

    <table class="w-full">

        <tbody>

            <template x-for="row in visibleRows">

                <tr class="border-b">

                    <td class="px-4 py-3">
                        <span x-text="row.id"></span>
                    </td>

                    <td class="px-4 py-3">
                        <span x-text="row.name"></span>
                    </td>

                </tr>

            </template>

        </tbody>
    </table>
</div>