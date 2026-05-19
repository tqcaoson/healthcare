import api from '../services/api';

export default () => ({

    resourceType: 'Patient',

    resourceId: '',

    json: '',

    async load() {

        const response = await api.get(
            `/fhir/${this.resourceType}/${this.resourceId}`
        );

        this.json = JSON.stringify(
            response.data,
            null,
            2
        );
    }
});