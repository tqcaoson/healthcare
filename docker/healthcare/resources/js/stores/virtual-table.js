export default () => ({

    rows: [],

    visibleRows: [],

    chunkSize: 50,

    currentPage: 0,

    init() {
        this.render();
    },

    render() {

        this.visibleRows =
            this.rows.slice(
                0,
                this.chunkSize * (this.currentPage + 1)
            );
    },

    handleScroll(event) {

        const element = event.target;

        if (
            element.scrollTop
            + element.clientHeight
            >= element.scrollHeight - 100
        ) {

            this.currentPage++;

            this.render();
        }
    }
});