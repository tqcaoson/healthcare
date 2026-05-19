export async function registerBiometric(options) {

    return await navigator.credentials.create({
        publicKey: options
    });
}

export async function authenticateBiometric(options) {

    return await navigator.credentials.get({
        publicKey: options
    });
}