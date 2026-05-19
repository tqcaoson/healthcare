export default () => ({

    document: {
        name: '',
        url: '',
    },

    print() {
        window.print();
    },

    download() {
        window.open(this.document.url);
    }
});