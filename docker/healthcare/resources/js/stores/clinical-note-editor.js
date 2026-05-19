import api from '../services/api';

export default () => ({

    note: {
        subjective: '',
        objective: '',
        assessment: '',
        plan: '',
    },

    async save() {

        await api.post('/notes', this.note);

        alert('Clinical note saved');
    }
});