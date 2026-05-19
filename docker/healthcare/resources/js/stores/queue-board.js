import api from '../services/api';

export default () => ({

    stats: {},

    queue: [],

    async init() {

        const response = await api.get('/queue');

        this.stats = response.data.stats;

        this.queue = response.data.queue;
    },

    async call(item) {

        await api.post(`/queue/${item.id}/call`);

        item.status = 'IN_PROGRESS';
    }
});