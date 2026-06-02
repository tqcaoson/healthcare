import api from '../services/api';

export default () => ({

    days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    columns: [[], [], [], [], [], [], []],
    loading: false,

    async init() {
        await this.load();
    },

    async load() {
        this.loading = true;
        try {
            const response = await api.get('/appointments', {
                params: { view: 'week' },
            });

            const appointments = response.data.data ?? [];
            const grouped = [[], [], [], [], [], [], []];

            appointments.forEach(appt => {
                const day = new Date(appt.start_at).getDay();
                const index = day === 0 ? 6 : day - 1;
                grouped[index].push(appt);
            });

            this.columns = grouped;
        } catch {
            this.columns = [[], [], [], [], [], [], []];
        } finally {
            this.loading = false;
        }
    },
});
