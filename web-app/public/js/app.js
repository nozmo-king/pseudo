let currentBoard = null;
let currentThread = null;

function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
}

function hideAllViews() {
    document.getElementById('thread-list').style.display = 'none';
    document.getElementById('thread-view').style.display = 'none';
    document.getElementById('profile-view').style.display = 'none';
    document.getElementById('chat-view').style.display = 'none';
}

function goHome() {
    if (currentBoard) {
        selectBoard(currentBoard);
    } else {
        loadBoards();
    }
}

function showBoards() {
    hideAllViews();
    loadBoards();
}

function showChat() {
    hideAllViews();
    document.getElementById('chat-view').style.display = 'block';
    loadChat();
}

function showLeaderboard() {
    hideAllViews();
    loadLeaderboard();
}

function showOwnProfile() {
    if (currentUser) {
        showProfile(currentUser.id);
    }
}

async function loadBoards() {
    const response = await fetch('/api/boards');
    const boards = await response.json();

    const selector = document.getElementById('board-selector');
    selector.innerHTML = `
        <div class="card">
            <h3>boards</h3>
            ${boards.map(b => `
                <button class="btn" onclick="selectBoard('${b.slug}')">${b.name}</button>
            `).join(' ')}
        </div>
    `;

    if (boards.length > 0) {
        selectBoard(boards[0].slug);
    }
}

async function selectBoard(slug) {
    currentBoard = slug;
    const response = await fetch(`/api/threads?board=${slug}`);
    const threads = await response.json();

    const listEl = document.getElementById('thread-list');
    listEl.innerHTML = `
        <div class="card">
            <h2>/${currentBoard}/</h2>
            <button class="btn" onclick="showNewThread()">new thread</button>
        </div>
        ${threads.map(t => `
            <div class="thread-card" onclick="loadThread(${t.id})">
                <h3>${t.title}</h3>
                <div class="pow-indicator">${t.total_pow || 0} POW</div>
                <small>by ${t.user.display_name || t.user.pubkey.substring(0, 8)}</small>
            </div>
        `).join('')}
    `;

    document.getElementById('thread-view').style.display = 'none';
    document.getElementById('thread-list').style.display = 'block';
}

function showNewThread() {
    const listEl = document.getElementById('thread-list');
    listEl.innerHTML = `
        <div class="card">
            <h2>new thread</h2>
            <form onsubmit="createThread(event)">
                <input type="text" id="thread-title" class="input-field" placeholder="title" required style="margin-bottom: 10px;">
                <textarea id="thread-body" class="input-field" placeholder="body" required style="min-height: 100px; margin-bottom: 10px;"></textarea>
                <div id="thread-pow-status" class="pow-indicator">mining... 0 POW</div>
                <button type="submit" class="btn" style="margin-top: 10px;">post</button>
                <button type="button" class="btn" onclick="selectBoard('${currentBoard}')">cancel</button>
            </form>
        </div>
    `;

    const textarea = document.getElementById('thread-body');
    powMiner.startMouseoverMining(textarea, (hash, nonce, points) => {
        document.getElementById('thread-pow-status').textContent = `${points} POW (${hash.substring(0, 16)}...)`;
    });
}

async function createThread(event) {
    event.preventDefault();

    const title = document.getElementById('thread-title').value;
    const body = document.getElementById('thread-body').value;

    if (!powMiner.bestHash || powMiner.bestPoints < 15) {
        alert('mine a valid hash first (hover over text area)');
        return;
    }

    const response = await fetch('/api/threads', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
            board_slug: currentBoard,
            title: title,
            body: body,
            challenge: powMiner.challenge,
            nonce: powMiner.bestNonce
        })
    });

    if (response.ok) {
        const thread = await response.json();
        powMiner.bestHash = null;
        powMiner.bestPoints = 0;
        loadThread(thread.id);
    } else {
        const error = await response.json();
        alert('failed to create thread: ' + (error.error || 'unknown error'));
    }
}

async function loadThread(threadId) {
    currentThread = threadId;

    const threadResponse = await fetch(`/api/threads/${threadId}`);
    const thread = await threadResponse.json();

    const postsResponse = await fetch(`/api/threads/${threadId}/posts`);
    const posts = await postsResponse.json();

    const threadView = document.getElementById('thread-view');
    threadView.innerHTML = `
        <div class="card">
            <button class="btn" onclick="selectBoard('${currentBoard}')">back to board</button>
            <h2>${thread.title}</h2>
            <div class="pow-indicator">${thread.total_pow || 0} POW</div>
        </div>

        <div class="post-box">
            <div><strong>${thread.user.display_name || thread.user.pubkey.substring(0, 8)}</strong></div>
            <div>${thread.body}</div>
            <div class="pow-indicator">${thread.pow_points || 0} POW</div>
        </div>

        ${posts.map(p => renderPost(p)).join('')}

        <div class="card">
            <h3>reply</h3>
            <form onsubmit="createPost(event, ${threadId})">
                <textarea id="post-body" class="input-field" placeholder="reply" required style="min-height: 80px; margin-bottom: 10px;"></textarea>
                <div id="post-pow-status" class="pow-indicator">mining... 0 POW</div>
                <button type="submit" class="btn" style="margin-top: 10px;">post</button>
            </form>
        </div>
    `;

    document.getElementById('thread-list').style.display = 'none';
    threadView.style.display = 'block';

    const textarea = document.getElementById('post-body');
    powMiner.bestHash = null;
    powMiner.bestPoints = 0;
    powMiner.startMouseoverMining(textarea, (hash, nonce, points) => {
        document.getElementById('post-pow-status').textContent = `${points} POW (${hash.substring(0, 16)}...)`;
    });
}

function renderPost(post) {
    return `
        <div class="post-box" style="${post.parent_id ? 'margin-left: 40px;' : ''}">
            <div>
                <strong onclick="showProfile(${post.user.id})" style="cursor: pointer;">
                    ${post.user.display_name || post.user.pubkey.substring(0, 8)}
                </strong>
            </div>
            <div>${post.body}</div>
            <div class="pow-indicator">${post.pow_points || 0} POW</div>
        </div>
    `;
}

async function createPost(event, threadId) {
    event.preventDefault();

    const body = document.getElementById('post-body').value;

    if (!powMiner.bestHash || powMiner.bestPoints < 15) {
        alert('mine a valid hash first (hover over text area)');
        return;
    }

    const response = await fetch(`/api/threads/${threadId}/posts`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
            body: body,
            challenge: powMiner.challenge,
            nonce: powMiner.bestNonce
        })
    });

    if (response.ok) {
        powMiner.bestHash = null;
        powMiner.bestPoints = 0;
        loadThread(threadId);
    } else {
        const error = await response.json();
        alert('failed to post: ' + (error.error || 'unknown error'));
    }
}

async function showProfile(userId) {
    const response = await fetch(`/api/users/${userId}`);
    const user = await response.json();

    const blogResponse = await fetch(`/api/users/${userId}/blog`);
    const blogPosts = await blogResponse.json();

    const profileView = document.getElementById('profile-view');
    profileView.innerHTML = `
        <div class="card">
            <button class="btn" onclick="hideProfile()">back</button>
            <h2>${user.display_name || 'anonymous'}</h2>
            <div>pubkey: ${user.pubkey.substring(0, 32)}...</div>
            <div class="pow-indicator">${user.total_pow || 0} POW</div>
            <div>achievements: ${user.achievements || 0}/11</div>
        </div>

        <div class="card">
            <h3>blog</h3>
            ${blogPosts.map(p => `
                <div class="post-box">
                    <h4>${p.title}</h4>
                    <div>${p.body}</div>
                    <div class="pow-indicator">${p.pow_points} POW</div>
                    <small>${new Date(p.created_at).toLocaleString()}</small>
                </div>
            `).join('')}
        </div>
    `;

    document.getElementById('thread-list').style.display = 'none';
    document.getElementById('thread-view').style.display = 'none';
    profileView.style.display = 'block';
}

function hideProfile() {
    document.getElementById('profile-view').style.display = 'none';
    if (currentThread) {
        loadThread(currentThread);
    } else {
        document.getElementById('thread-list').style.display = 'block';
    }
}

async function loadChat() {
    const chatView = document.getElementById('chat-view');
    chatView.innerHTML = `
        <div class="card">
            <h2>chat</h2>
        </div>
        <div id="chat-messages" class="card" style="min-height: 400px; max-height: 500px; overflow-y: auto;"></div>
        <div class="card">
            <input type="text" id="chat-input" class="input-field" placeholder="type message or /command">
            <button class="btn" onclick="sendMessage()" style="margin-top: 10px;">send</button>
        </div>
    `;
    loadMessages();
}

async function loadMessages() {
    const response = await fetch('/api/messages?chatroom_id=1', { credentials: 'include' });
    const messages = await response.json();

    const container = document.getElementById('chat-messages');
    container.innerHTML = messages.map(m => `
        <div class="chat-message">
            <strong>${m.user.display_name || m.user.pubkey.substring(0, 8)}</strong>: ${m.body}
        </div>
    `).join('');
}

async function sendMessage() {
    const input = document.getElementById('chat-input');
    const body = input.value.trim();
    if (!body) return;

    await fetch('/api/messages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
            chatroom_id: 1,
            body: body
        })
    });

    input.value = '';
    loadMessages();
}

async function loadLeaderboard() {
    const response = await fetch('/api/pow/leaderboard');
    const leaders = await response.json();

    const listEl = document.getElementById('thread-list');
    listEl.style.display = 'block';
    listEl.innerHTML = `
        <div class="card">
            <h2>leaderboard</h2>
        </div>
        ${leaders.map((user, index) => `
            <div class="leaderboard-item">
                <span>#${index + 1}</span>
                <strong onclick="showProfile(${user.id})" style="cursor: pointer;">
                    ${user.display_name || user.pubkey.substring(0, 8)}
                </strong>
                <span class="pow-indicator">${user.total_pow} POW</span>
            </div>
        `).join('')}
    `;
}

window.updateMiningStats = function(points, attempts) {
    const statsEl = document.getElementById('mining-stats');
    if (statsEl) {
        statsEl.textContent = `${points} POW | ${attempts} attempts`;
    }
};

window.addEventListener('load', () => {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    checkAuth();
});
