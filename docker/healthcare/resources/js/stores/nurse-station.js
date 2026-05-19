import api from '../services/api';

export default () => ({

    metrics: {},

    patients: [],

    async init() {

        const response = await api.get('/dashboard/nurse-station');

        this.metrics = response.data.metrics;

        this.patients = response.data.patients;
    }
});