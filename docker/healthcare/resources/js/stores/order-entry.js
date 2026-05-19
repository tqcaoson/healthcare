import api from '../services/api';

export default () => ({

    type: 'lab',

    query: '',

    results: [],

    selected: null,

    async search() {

        const response = await api.get('/orders/search', {
            params: {
                type: this.type,
                query: this.query,
            }
        });

        this.results = response.data;
    },

    select(item) {
        this.selected = item;
    },

    async submit() {

        await api.post('/orders', {
            type: this.type,
            item: this.selected,
        });

        alert('Order placed');
    }
});