import api from '../services/api';

export default () => ({

    range: 7,

    results: [],

    async init() {
        await this.load();
    },

    async load() {

        const response = await api.get('/results', {
            params: {
                days: this.range,
            }
        });

        this.results = response.data.data;
    }
});