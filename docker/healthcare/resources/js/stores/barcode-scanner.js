export default () => ({

    result: '',

    async init() {

        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'environment'
            }
        });

        this.$refs.video.srcObject = stream;
    }
});