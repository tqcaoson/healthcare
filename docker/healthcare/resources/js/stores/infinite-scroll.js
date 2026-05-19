export default () => ({

    page: 1,

    items: [],

    async init() {

        await this.loadMore();
    },

    async loadMore() {

        for (let i = 0; i < 20; i++) {

            this.items.push({
                title: `Item ${i + this.items.length}`,
                description: 'Generated item'
            });
        }

        this.page++;
    }
});