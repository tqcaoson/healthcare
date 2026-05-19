import api from '../services/api';

export default () => ({

    settings: {},

    async init() {

        const response = await api.get('/settings');

        this.settings = response.data;
    },

    async save() {

        await api.put('/settings', this.settings);

        alert('Settings saved');
    }
});