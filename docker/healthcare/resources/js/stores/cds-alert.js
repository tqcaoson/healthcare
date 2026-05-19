export default () => ({

    open: false,

    message: '',

    show(message) {

        this.message = message;

        this.open = true;
    },

    override() {

        this.open = false;

        this.$dispatch('cds-overridden');
    },

    cancel() {

        this.open = false;
    }
});