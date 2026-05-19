export default () => ({

    json: '{}',

    init() {

        setInterval(() => {

            this.json = JSON.stringify({
                memory: performance.memory,
                online: navigator.onLine,
            }, null, 2);

        }, 2000);
    }
});