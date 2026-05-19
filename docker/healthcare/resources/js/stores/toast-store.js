export default () => ({

    toasts: [],

    push(toast) {

        this.toasts.push(toast);

        setTimeout(() => {
            this.toasts.shift();
        }, 4000);
    }
});