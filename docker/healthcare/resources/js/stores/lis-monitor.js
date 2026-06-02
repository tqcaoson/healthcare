import api from '../services/api';

export default () => ({

    stats: {
        pending: 0,
        processing: 0,
        completed: 0,
        critical: 0,
    },

    orders: [],
    loading: false,
    _poll: null,

    async init() {
        await this.load();
        this._poll = setInterval(() => this.load(), 30_000);
    },

    destroy() {
        clearInterval(this._poll);
    },

    async load() {
        this.loading = true;
        try {
            const response = await api.get('/lis/stats');
            const payload = response.data.data ?? {};
            this.stats = payload.stats ?? this.stats;
            this.orders = payload.orders ?? [];
        } catch {
            // keep last values on error
        } finally {
            this.loading = false;
        }
    },
});
