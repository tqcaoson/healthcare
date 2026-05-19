export default () => ({

    scale: 1,

    invertEnabled: false,

    zoomIn() {
        this.scale += 0.1;
    },

    zoomOut() {
        this.scale -= 0.1;
    },

    invert() {
        this.invertEnabled = !this.invertEnabled;
    }
});