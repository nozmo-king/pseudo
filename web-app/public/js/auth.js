let currentUser = null;
let authToken = null;

async function login() {
    try {
        const keyPair = loadOrCreateKeyPair();

        const challengeResponse = await fetch('/api/auth/challenge');
        const challengeData = await challengeResponse.json();

        const message = challengeData.message;
        const messageHash = await sha256(message);

        const signature = signMessage(messageHash, keyPair.privateKey);

        const verifyResponse = await fetch('/api/auth/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                pubkey: keyPair.publicKey,
                signature: signature
            })
        });

        const result = await verifyResponse.json();

        if (result.success) {
            currentUser = result.user;
            document.getElementById('login-screen').style.display = 'none';
            document.getElementById('main-content').style.display = 'block';
            updateUserStatus();
            loadBoards();
        } else {
            alert('authentication failed');
        }
    } catch (error) {
        console.error('login error:', error);
        alert('login failed: ' + error.message);
    }
}

function updateUserStatus() {
    const userInfo = document.getElementById('user-info');
    if (currentUser) {
        userInfo.innerHTML = `
            ${currentUser.display_name || currentUser.pubkey.substring(0, 8)}
        `;
    }
}

function checkAuth() {
    fetch('/api/pow/challenge', { credentials: 'include' })
        .then(response => {
            if (response.ok) {
                document.getElementById('main-content').style.display = 'block';
                loadBoards();
            } else {
                document.getElementById('login-screen').style.display = 'block';
            }
        })
        .catch(() => {
            document.getElementById('login-screen').style.display = 'block';
        });
}
