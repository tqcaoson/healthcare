export function initializeReconnect() {

    let reconnectAttempts = 0;

    function connect() {

        window.Echo.connector.pusher.connection.bind(
            'disconnected',
            () => {

                reconnectAttempts++;

                const timeout =
                    Math.min(
                        reconnectAttempts * 2000,
                        30000
                    );

                setTimeout(() => {
                    connect();
                }, timeout);
            }
        );
    }

    connect();
}