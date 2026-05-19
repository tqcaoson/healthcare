import api from '../services/api';

export default () => ({

    devices: [],

    async init() {

        const response =
            await api.get('/devices');

        this.devices =
            response.data.data;
    }
});