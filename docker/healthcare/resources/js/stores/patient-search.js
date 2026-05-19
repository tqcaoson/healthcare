import api from '../services/api';

export default () => ({

    query: '',
    mrn: '',
    patients: [],
    loading: false,

    async search() {

        this.loading = true;

        try {

            const response = await api.get('/patients', {
                params: {
                    query: this.query,
                    mrn: this.mrn,
                }
            });

            this.patients = response.data.data;

        } finally {
            this.loading = false;
        }
    }
});