import api from '../services/api';

export default () => ({

    patients: [],

    async init() {

        const response = await api.get('/dashboard/icu');

        this.patients = response.data.data;
    }
});