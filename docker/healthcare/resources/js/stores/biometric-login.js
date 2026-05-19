import {
    authenticateBiometric
} from '../plugins/webauthn';

export default () => ({

    async login() {

        try {

            await authenticateBiometric({});

            window.location.href =
                '/dashboard';

        } catch (error) {

            alert('Authentication failed');
        }
    }
});