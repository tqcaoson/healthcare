export default () => ({

    show: false,

    deferredPrompt: null,

    init() {

        window.addEventListener(
            'beforeinstallprompt',
            (event) => {

                event.preventDefault();

                this.deferredPrompt = event;

                this.show = true;
            }
        );
    },

    async install() {

        if (!this.deferredPrompt) {
            return;
        }

        this.deferredPrompt.prompt();

        this.show = false;
    }
});