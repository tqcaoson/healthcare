export default () => ({

    hasError: false,

    init() {

        window.addEventListener(
            'error',
            () => {

                this.hasError = true;
            }
        );
    },

    reload() {

        window.location.reload();
    }
});