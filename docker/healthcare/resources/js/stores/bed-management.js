import api from '../services/api';

export default () => ({

    beds: [],

    async init() {

        const response = await api.get('/beds');

        this.beds = response.data.data;
    }
});