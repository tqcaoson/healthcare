export default () => ({

    notifications: [],

    init() {

        window.Echo.private('notifications')
            .listen('.notification.created',
                (event) => {

                    this.notifications.unshift(
                        event.notification
                    );
                });
    }
});