export default () => ({

    cpu: 0,

    memory: 0,

    socket: 'CONNECTED',

    workers: 0,

    init() {

        setInterval(() => {

            this.cpu =
                Math.floor(Math.random() * 100);

            this.memory =
                Math.floor(Math.random() * 100);

            this.workers =
                Math.floor(Math.random() * 20);

        }, 3000);
    }
});