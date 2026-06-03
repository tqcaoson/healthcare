export default () => ({
    filters: {
        user: '',
        resource: '',
        action: '',
    },
    logs: [],
    loading: false,
    error: null,

    async search() {
        this.loading = true;
        this.error = null;

        try {
            const params = new URLSearchParams(
                Object.fromEntries(
                    Object.entries(this.filters).filter(([, v]) => v !== '')
                )
            );

            const response = await window.axios.get(`/api/v1/audit-logs?${params}`);
            this.logs = response.data?.data ?? [];
        } catch (e) {
            this.error = 'Không thể tải audit log. Vui lòng thử lại.';
            this.logs = [];
        } finally {
            this.loading = false;
        }
    },

    init() {
        this.search();
    },
});
