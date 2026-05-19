export default () => ({

    open: false,

    toggleContrast() {

        document.documentElement
            .classList.toggle('high-contrast');
    },

    increaseFont() {

        document.documentElement
            .classList.toggle('large-font');
    },

    reduceMotion() {

        document.documentElement
            .classList.toggle('reduce-motion');
    }
});