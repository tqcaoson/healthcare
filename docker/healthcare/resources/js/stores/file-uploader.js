import api from '../services/api';

export default () => ({

    async upload(event) {

        const files = event.target.files;

        await this.send(files);
    },

    async drop(event) {

        const files = event.dataTransfer.files;

        await this.send(files);
    },

    async send(files) {

        const formData = new FormData();

        for (const file of files) {
            formData.append('files[]', file);
        }

        await api.post(
            '/documents/upload',
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        );

        alert('Upload completed');
    }
});