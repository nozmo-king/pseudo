const EC = elliptic.ec;
const ec = new EC('secp256k1');

function generateKeyPair() {
    const keyPair = ec.genKeyPair();
    return {
        privateKey: keyPair.getPrivate('hex'),
        publicKey: keyPair.getPublic('hex')
    };
}

function signMessage(message, privateKey) {
    const keyPair = ec.keyFromPrivate(privateKey, 'hex');
    const hash = sha256(message);
    const signature = keyPair.sign(hash);
    return signature.toDER('hex');
}

function sha256(message) {
    const buffer = new TextEncoder().encode(message);
    return crypto.subtle.digest('SHA-256', buffer).then(hash => {
        return Array.from(new Uint8Array(hash))
            .map(b => b.toString(16).padStart(2, '0'))
            .join('');
    });
}

function loadOrCreateKeyPair() {
    let privateKey = localStorage.getItem('privateKey');
    if (!privateKey) {
        const keyPair = generateKeyPair();
        localStorage.setItem('privateKey', keyPair.privateKey);
        privateKey = keyPair.privateKey;
    }
    const keyPair = ec.keyFromPrivate(privateKey, 'hex');
    return {
        privateKey: privateKey,
        publicKey: keyPair.getPublic('hex')
    };
}
