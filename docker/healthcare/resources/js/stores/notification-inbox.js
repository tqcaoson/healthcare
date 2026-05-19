export default () => ({

    notifications: [],

    init() {

        window.Echo.private('users')
            .listen('.critical.lab', (event) => {

                this.notifications.unshift({
                    title: 'Critical Lab',
                    message: event.message,
                });
            });
    }
});