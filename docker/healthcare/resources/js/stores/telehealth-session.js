export default () => ({

    status: 'waiting',
    localStream: null,
    peerConnection: null,

    async init() {
        await this.startLocalVideo();
    },

    async startLocalVideo() {
        try {
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true,
            });

            const localVideo = document.getElementById('localVideo');
            if (localVideo) {
                localVideo.srcObject = this.localStream;
            }

            this.status = 'ready';
        } catch {
            this.status = 'error';
        }
    },

    end() {
        this.localStream?.getTracks().forEach(t => t.stop());
        this.peerConnection?.close();
        this.status = 'ended';
    },
});
