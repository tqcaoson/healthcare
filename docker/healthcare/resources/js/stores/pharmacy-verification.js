import api from '../services/api';

export default () => ({

    orders: [],
    loading: false,

    async init() {
        await this.load();
    },

    async load() {
        this.loading = true;
        try {
            const response = await api.get('/pharmacy/pending-orders');
            this.orders = response.data.data ?? [];
        } catch {
            this.orders = [];
        } finally {
            this.loading = false;
        }
    },

    async approve(order) {
        try {
            await api.post(`/pharmacy/orders/${order.id}/verify`);
            this.orders = this.orders.filter(o => o.id !== order.id);
        } catch {
            // error handled by global error handler
        }
    },

    async reject(order) {
        try {
            await api.post(`/pharmacy/orders/${order.id}/reject`);
            this.orders = this.orders.filter(o => o.id !== order.id);
        } catch {
            // error handled by global error handler
        }
    },
});
