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
    document.getElementById('blogs-view').style.display = 'none';
    document.getElementById('chat-view').style.display = 'none';
    document.getElementById('mining-view').style.display = 'none';
    document.getElementById('images-view').style.display = 'none';
}

function goHome() {
    if (currentBoard) {
        selectBoard(currentBoard);
    } else {
        loadBoards();
    }
}

async function showBoards() {
    hideAllViews();
    const response = await fetch('/api/boards');
    const boards = await response.json();

    const selector = document.getElementById('board-selector');
    selector.innerHTML = `
        <div class="card" style="margin-bottom: 20px;">
            <h3>boards</h3>
            ${boards.map(b => `
                <a onclick="selectBoard('${b.slug}')" style="margin-right: 15px; cursor: pointer;">/${b.slug}/ - ${b.name}</a>
            `).join(' ')}
        </div>
    `;

    document.getElementById('updates-box').style.display = 'none';
}

function showBlogs() {
    hideAllViews();
    document.getElementById('updates-box').style.display = 'none';
    document.getElementById('blogs-view').style.display = 'block';
    loadBlogs();
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

function showMining() {
    hideAllViews();
    document.getElementById('mining-view').style.display = 'block';
    loadMiningPage();
}

function showImages() {
    hideAllViews();
    document.getElementById('images-view').style.display = 'block';
    loadImages();
}

async function loadBoards() {
    const response = await fetch('/api/boards');
    const boards = await response.json();

    const selector = document.getElementById('board-selector');
    selector.innerHTML = `
        <div class="card" style="margin-bottom: 20px;">
            <h3>boards</h3>
            ${boards.map(b => `
                <a onclick="selectBoard('${b.slug}')" style="margin-right: 15px; cursor: pointer;">/${b.slug}/ - ${b.name}</a>
            `).join(' ')}
        </div>
    `;

    document.getElementById('updates-box').style.display = 'none';
}

async function selectBoard(slug) {
    currentBoard = slug;
    hideAllViews();
    document.getElementById('updates-box').style.display = 'none';

    const boardResponse = await fetch(`/api/boards/${slug}`);
    const board = await boardResponse.json();

    const threadsResponse = await fetch(`/api/threads?board=${slug}`);
    const threads = await threadsResponse.json();

    const selector = document.getElementById('board-selector');
    const response = await fetch('/api/boards');
    const boards = await response.json();
    selector.innerHTML = `
        <div class="card" style="margin-bottom: 20px;">
            <h3>boards</h3>
            ${boards.map(b => `
                <a onclick="selectBoard('${b.slug}')" style="margin-right: 15px; cursor: pointer;">/${b.slug}/ - ${b.name}</a>
            `).join(' ')}
        </div>
    `;

    const listEl = document.getElementById('thread-list');
    listEl.innerHTML = `
        <div class="card">
            <h2>/${currentBoard}/ - ${board.name}</h2>
            <p>${board.description}</p>
            <button class="btn" onclick="showNewThread()">new thread</button>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; margin-top: 20px;">
            ${threads.map(t => `
                <div class="thread-card" onclick="loadThread(${t.id})" style="cursor: pointer;">
                    <h3 style="margin: 0 0 10px 0;">${t.title}</h3>
                    <div style="font-size: 14px; color: var(--text-secondary); margin-bottom: 10px;">
                        ${(t.body || '').substring(0, 150)}${(t.body || '').length > 150 ? '...' : ''}
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <small>by ${t.user.display_name || t.user.pubkey.substring(0, 8)}</small>
                        <div class="pow-indicator">${t.total_pow || 0} POW</div>
                    </div>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 5px;">
                        ${t.reply_count || 0} replies
                    </div>
                </div>
            `).join('')}
        </div>
    `;

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

                <div style="margin: 20px 0;">
                    <h3 style="font-size: 16px; margin-bottom: 10px;">doodle to mine</h3>
                    <canvas id="doodle-canvas" width="600" height="400" style="border: 1px solid var(--border-color); cursor: crosshair; background: var(--bg-secondary);"></canvas>
                    <div style="margin-top: 10px;">
                        <button type="button" class="btn" onclick="clearDoodle()">clear</button>
                        <span style="margin-left: 10px; font-size: 14px; color: var(--text-secondary);">draw to mine better hashes</span>
                    </div>
                </div>

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

    initDoodleMining();
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
        <div style="display: flex; gap: 0;">
            <div id="chat-messages" class="card" style="min-height: 400px; max-height: 500px; overflow-y: auto; flex: 1; margin-bottom: 0;"></div>
            <div id="chat-users" style="width: 150px; border: 1px solid #000000; padding: 10px; background: #ffffff; min-height: 400px; max-height: 500px; overflow-y: auto;">
                <h3 style="font-size: 12px; margin-bottom: 10px;">users</h3>
                <div id="user-list"></div>
            </div>
        </div>
        <div class="card" style="margin-top: 0;">
            <input type="text" id="chat-input" class="input-field" placeholder="type message" onkeypress="if(event.key==='Enter')sendMessage()">
            <button class="btn" onclick="sendMessage()" style="margin-top: 10px;">send</button>
        </div>
    `;
    loadMessages();

    // Auto-refresh messages every 2 seconds
    if (window.chatInterval) clearInterval(window.chatInterval);
    window.chatInterval = setInterval(() => {
        if (document.getElementById('chat-view').style.display === 'block') {
            loadMessages();
        } else {
            clearInterval(window.chatInterval);
        }
    }, 2000);
}

async function loadMessages() {
    const response = await fetch('/api/messages?chatroom_id=1', { credentials: 'include' });
    const messages = await response.json();

    const container = document.getElementById('chat-messages');
    const prevScrollTop = container.scrollTop;
    const prevScrollHeight = container.scrollHeight;
    const wasAtBottom = prevScrollHeight - prevScrollTop - container.clientHeight < 50;

    container.innerHTML = messages.map(m => {
        let displayName;
        if (m.anonymous_name) {
            displayName = m.anonymous_name;
        } else if (m.user) {
            displayName = m.user.display_name || m.user.pubkey.substring(0, 8);
        } else {
            displayName = '🍕';
        }
        return `
            <div class="chat-message">
                <strong>${displayName}</strong>: ${m.body}
            </div>
        `;
    }).join('');

    // Auto-scroll to bottom only if user was already at bottom
    if (wasAtBottom || prevScrollHeight === 0) {
        container.scrollTop = container.scrollHeight;
    }

    // Update user list
    updateUserList(messages);
}

function updateUserList(messages) {
    const userList = document.getElementById('user-list');
    if (!userList) return;

    const now = new Date();
    const userActivity = {};

    // Track last activity for each user
    messages.forEach(m => {
        let displayName;
        if (m.anonymous_name) {
            displayName = m.anonymous_name;
        } else if (m.user) {
            displayName = m.user.display_name || m.user.pubkey.substring(0, 8);
        } else {
            displayName = '🍕';
        }

        const messageTime = new Date(m.created_at);
        if (!userActivity[displayName] || messageTime > userActivity[displayName]) {
            userActivity[displayName] = messageTime;
        }
    });

    // Generate user list with idle status
    const userEntries = Object.entries(userActivity)
        .sort((a, b) => b[1] - a[1]) // Sort by most recent activity
        .map(([name, lastActivity]) => {
            const minutesAgo = (now - lastActivity) / 1000 / 60;
            const isIdle = minutesAgo > 30;
            const style = isIdle ? 'color: #666666;' : '';
            const idleText = isIdle ? ' (idle)' : '';
            return `<div style="${style} font-size: 11px; margin-bottom: 5px;">${name}${idleText}</div>`;
        })
        .join('');

    userList.innerHTML = userEntries || '<div style="color: #666666; font-size: 11px;">no users</div>';
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

let continuousMiner = null;

async function loadMiningPage() {
    const miningView = document.getElementById('mining-view');
    miningView.innerHTML = `
        <div class="card">
            <h2>continuous mining</h2>
            <p>mine proof-of-work hashes continuously. better hashes increase your rank.</p>
        </div>

        <div class="card">
            <div id="mining-display">
                <div class="pow-indicator" style="font-size: 24px; margin: 20px 0;">
                    <div id="current-hash">waiting to start...</div>
                    <div id="current-points" style="margin-top: 10px;">0 POW</div>
                </div>
                <div style="margin: 20px 0;">
                    <div>attempts: <span id="mining-attempts">0</span></div>
                    <div>best this session: <span id="session-best">0</span></div>
                    <div>total mined: <span id="total-mined">0</span></div>
                </div>
            </div>

            <button id="mining-toggle" class="btn" onclick="toggleContinuousMining()">start mining</button>
            <button class="btn" onclick="submitMinedHash()" id="submit-hash" disabled>submit hash</button>
        </div>
    `;

    if (continuousMiner && continuousMiner.mining) {
        document.getElementById('mining-toggle').textContent = 'stop mining';
    }

    loadTotalMined();
}

async function loadTotalMined() {
    if (!currentUser) return;
    const response = await fetch(`/api/users/${currentUser.id}`);
    const user = await response.json();
    document.getElementById('total-mined').textContent = user.total_pow || 0;
}

async function toggleContinuousMining() {
    const btn = document.getElementById('mining-toggle');

    if (continuousMiner && continuousMiner.mining) {
        continuousMiner.stopMining();
        continuousMiner = null;
        btn.textContent = 'start mining';
        document.getElementById('submit-hash').disabled = true;
    } else {
        btn.textContent = 'stop mining';
        continuousMiner = new ProofOfWorkMiner();
        await continuousMiner.getChallenge();

        continuousMiner.mine(999999999, (hash, nonce, points, attempts) => {
            document.getElementById('current-hash').textContent = hash.substring(0, 32) + '...';
            document.getElementById('current-points').textContent = `${points} POW`;
            document.getElementById('mining-attempts').textContent = attempts;

            const sessionBest = document.getElementById('session-best');
            const currentBest = parseInt(sessionBest.textContent) || 0;
            if (points > currentBest) {
                sessionBest.textContent = points;
            }

            if (points >= 15) {
                document.getElementById('submit-hash').disabled = false;
            }

            window.updateMiningStats(points, attempts);
        });
    }
}

async function submitMinedHash() {
    if (!continuousMiner || !continuousMiner.bestHash) {
        alert('no hash to submit');
        return;
    }

    const response = await fetch('/api/pow/submit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
            challenge: continuousMiner.challenge,
            nonce: continuousMiner.bestNonce
        })
    });

    if (response.ok) {
        const result = await response.json();
        alert(`submitted ${result.points} POW!` + (result.achievement_unlocked ? ` achievement unlocked!` : ''));

        continuousMiner.bestHash = null;
        continuousMiner.bestPoints = 0;
        await continuousMiner.getChallenge();

        document.getElementById('submit-hash').disabled = true;
        loadTotalMined();
    } else {
        alert('failed to submit hash');
    }
}

async function loadImages() {
    const imagesView = document.getElementById('images-view');

    const response = await fetch('/api/admin/files', { credentials: 'include' });

    if (response.ok) {
        const files = await response.json();

        imagesView.innerHTML = `
            <div class="card">
                <h2>image library</h2>
                ${currentUser && currentUser.is_admin ? '<button class="btn" onclick="showUploadForm()">upload</button>' : ''}
            </div>

            <div id="upload-form" style="display: none;" class="card">
                <h3>upload image</h3>
                <form onsubmit="uploadImage(event)">
                    <input type="file" id="image-file" accept="image/*" required style="margin-bottom: 10px;">
                    <textarea id="image-prompt" class="input-field" placeholder="prompt for claude (optional)" style="min-height: 60px; margin-bottom: 10px;"></textarea>
                    <button type="submit" class="btn">upload</button>
                    <button type="button" class="btn" onclick="hideUploadForm()">cancel</button>
                </form>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
                ${files.map(f => `
                    <div class="card" style="padding: 10px;">
                        <img src="${f.path}" style="width: 100%; height: auto; border: 1px solid var(--border-color);">
                        <div style="font-size: 12px; margin-top: 5px;">${f.filename}</div>
                        ${f.prompt ? `<div style="font-size: 11px; color: var(--text-secondary); margin-top: 5px;">${f.prompt}</div>` : ''}
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        imagesView.innerHTML = `
            <div class="card">
                <h2>image library</h2>
                <p>admin access required</p>
            </div>
        `;
    }
}

function showUploadForm() {
    document.getElementById('upload-form').style.display = 'block';
}

function hideUploadForm() {
    document.getElementById('upload-form').style.display = 'none';
}

async function uploadImage(event) {
    event.preventDefault();

    const fileInput = document.getElementById('image-file');
    const prompt = document.getElementById('image-prompt').value;

    const formData = new FormData();
    formData.append('file', fileInput.files[0]);
    if (prompt) formData.append('prompt', prompt);

    const response = await fetch('/api/admin/files', {
        method: 'POST',
        credentials: 'include',
        body: formData
    });

    if (response.ok) {
        hideUploadForm();
        loadImages();
    } else {
        alert('upload failed');
    }
}

let doodleCanvas = null;
let doodleCtx = null;
let doodleDrawing = false;
let doodleMiner = null;

function initDoodleMining() {
    doodleCanvas = document.getElementById('doodle-canvas');
    if (!doodleCanvas) return;

    doodleCtx = doodleCanvas.getContext('2d');
    doodleCtx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--text-primary');
    doodleCtx.lineWidth = 2;
    doodleCtx.lineCap = 'round';

    doodleMiner = new ProofOfWorkMiner();
    doodleMiner.getChallenge();

    doodleCanvas.addEventListener('mousedown', startDoodle);
    doodleCanvas.addEventListener('mousemove', doodle);
    doodleCanvas.addEventListener('mouseup', stopDoodle);
    doodleCanvas.addEventListener('mouseleave', stopDoodle);
}

function startDoodle(e) {
    doodleDrawing = true;
    const rect = doodleCanvas.getBoundingClientRect();
    doodleCtx.beginPath();
    doodleCtx.moveTo(e.clientX - rect.left, e.clientY - rect.top);

    if (doodleMiner && !doodleMiner.mining) {
        doodleMiner.mine(999999999, (hash, nonce, points) => {
            const statusEl = document.getElementById('thread-pow-status');
            if (statusEl) {
                statusEl.textContent = `${points} POW (${hash.substring(0, 16)}...) via doodle`;
            }
            window.updateMiningStats(points, doodleMiner.attempts);
        });
    }
}

function doodle(e) {
    if (!doodleDrawing) return;

    const rect = doodleCanvas.getBoundingClientRect();
    doodleCtx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
    doodleCtx.stroke();

    if (doodleMiner && Math.random() < 0.1) {
        doodleMiner.attempts += 100;
    }
}

function stopDoodle() {
    doodleDrawing = false;
}

function clearDoodle() {
    if (doodleCtx && doodleCanvas) {
        doodleCtx.clearRect(0, 0, doodleCanvas.width, doodleCanvas.height);
    }
}

window.updateMiningStats = function(points, attempts) {
    const statsEl = document.getElementById('mining-stats');
    if (statsEl) {
        statsEl.textContent = `${points} POW | ${attempts} attempts`;
    }
};

async function loadBlogs() {
    const blogsView = document.getElementById('blogs-view');

    blogsView.innerHTML = `
        <div class="card">
            <h2>public blogs</h2>
            <p>user blogs and posts from across haichan</p>
        </div>
        <div id="blogs-list"></div>
    `;

    try {
        const usersResponse = await fetch('/api/pow/leaderboard');
        const users = await usersResponse.json();

        let allBlogPosts = [];

        for (const user of users.slice(0, 20)) {
            try {
                const blogResponse = await fetch(`/api/users/${user.id}/blog`);
                if (blogResponse.ok) {
                    const posts = await blogResponse.json();
                    posts.forEach(post => {
                        post.user = user;
                    });
                    allBlogPosts = allBlogPosts.concat(posts);
                }
            } catch (e) {
                console.log(`Failed to load blog for user ${user.id}`);
            }
        }

        allBlogPosts.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

        const blogsList = document.getElementById('blogs-list');
        if (allBlogPosts.length > 0) {
            blogsList.innerHTML = allBlogPosts.map(post => `
                <div class="card" style="margin-bottom: 15px;">
                    <h3 style="margin-bottom: 5px;">${post.title}</h3>
                    <div style="font-size: 11px; margin-bottom: 10px;">
                        by <strong onclick="showProfile(${post.user.id})" style="cursor: pointer;">
                            ${post.user.display_name || post.user.pubkey.substring(0, 8)}
                        </strong>
                        | <span class="pow-indicator">${post.pow_points || 0} POW</span>
                        | ${new Date(post.created_at).toLocaleString()}
                    </div>
                    <div style="white-space: pre-wrap;">${post.body}</div>
                </div>
            `).join('');
        } else {
            blogsList.innerHTML = `
                <div class="card">
                    <p>no blog posts yet</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Failed to load blogs:', error);
        document.getElementById('blogs-list').innerHTML = `
            <div class="card">
                <p>failed to load blogs</p>
            </div>
        `;
    }
}

window.addEventListener('load', () => {
    document.documentElement.setAttribute('data-theme', 'light');
    checkAuth();
});
