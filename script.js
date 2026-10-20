document.addEventListener('DOMContentLoaded', () => {
    const layout = document.getElementById('messengerLayout');
    const sidebar = document.getElementById('sidebar');
    const chatArea = document.getElementById('chatArea');
    const currentUserId = parseInt(layout.dataset.currentUserId, 10);
    const themeToggle = document.getElementById('themeToggle');
    const userSearch = document.getElementById('userSearch');
    const usersList = document.getElementById('usersList');
    const messagesContainer = document.getElementById('messagesContainer');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const activePartnerName = document.getElementById('activePartnerName');
    const activePartnerStatus = document.getElementById('activePartnerStatus');
    const backToUsersBtn = document.getElementById('backToUsersBtn');

    let allUsers = [];
    let selectedUserId = null;
    let lastRenderedMessageCount = 0;
    let lastMessageId = 0;

    // 0. Lock exact visible mobile viewport height & prevent top header from scrolling under URL bar
    function updateViewportBounds() {
        window.scrollTo(0, 0);
        const vv = window.visualViewport;
        const visibleHeight = vv ? Math.round(vv.height) : window.innerHeight;
        const offsetTop = vv ? Math.round(vv.offsetTop) : 0;

        layout.style.top = `${offsetTop}px`;
        layout.style.height = `${visibleHeight}px`;
        sidebar.style.height = `${visibleHeight}px`;
        chatArea.style.height = `${visibleHeight}px`;
    }

    updateViewportBounds();
    window.addEventListener('resize', updateViewportBounds);
    window.addEventListener('scroll', () => {
        if (window.scrollY !== 0) window.scrollTo(0, 0);
    });

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', () => {
            updateViewportBounds();
            if (selectedUserId) scrollToBottom(false);
        });
        window.visualViewport.addEventListener('scroll', updateViewportBounds);
    }

    messageInput.addEventListener('focus', () => {
        setTimeout(() => {
            updateViewportBounds();
            scrollToBottom(false);
        }, 150);
    });

    // 1. Theme Persistence & Toggle
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);

    themeToggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
    });

    // 2. Mobile Navigation Toggle
    backToUsersBtn.addEventListener('click', () => {
        layout.classList.remove('chat-active');
        messageInput.blur();
        updateViewportBounds();
    });

    // 3. Fetch & Render User List
    async function loadUsers() {
        try {
            const response = await fetch('fetch_users.php');
            if (!response.ok) return;
            allUsers = await response.json();
            renderUsers(allUsers);
        } catch (err) {
            console.error('Error fetching users:', err);
        }
    }

    function renderUsers(usersArray) {
        const query = userSearch.value.trim().toLowerCase();
        const filtered = usersArray.filter(u => u.username.toLowerCase().includes(query));

        usersList.innerHTML = '';
        if (filtered.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'no-users';
            empty.textContent = 'No users found.';
            usersList.appendChild(empty);
            return;
        }

        filtered.forEach(user => {
            const item = document.createElement('div');
            item.className = `user-item ${Number(user.id) === selectedUserId ? 'active' : ''}`;
            item.dataset.id = user.id;

            const isOnline = user.status === 'online';

            item.innerHTML = `
                <div class="avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span class="status-dot ${isOnline ? 'online' : 'offline'}"></span>
                </div>
                <div class="user-info">
                    <span class="user-name"></span>
                    <span class="user-status-label">${isOnline ? 'Online' : 'Offline'}</span>
                </div>
            `;
            item.querySelector('.user-name').textContent = user.username;

            item.addEventListener('click', () => selectUser(user));
            usersList.appendChild(item);

            if (Number(user.id) === selectedUserId) {
                activePartnerStatus.textContent = isOnline ? 'Online' : 'Offline';
            }
        });
    }

    userSearch.addEventListener('input', () => renderUsers(allUsers));

    // 4. Select User & Open Conversation
    function selectUser(user) {
        selectedUserId = Number(user.id);
        lastRenderedMessageCount = 0;
        lastMessageId = 0;

        activePartnerName.textContent = user.username;
        activePartnerStatus.textContent = user.status === 'online' ? 'Online' : 'Offline';

        messageInput.disabled = false;
        sendButton.disabled = false;

        layout.classList.add('chat-active');
        updateViewportBounds();

        // Only auto-focus immediately on desktop so mobile doesn't jump on open
        if (window.innerWidth > 768) {
            messageInput.focus();
        }

        renderUsers(allUsers);
        fetchMessages(true);
    }

    // 5. Fetch & Render Messages
    async function fetchMessages(forceScroll = false) {
        if (!selectedUserId) return;

        try {
            const response = await fetch(`fetch_messages.php?receiver_id=${selectedUserId}`);
            if (!response.ok) return;
            const messages = await response.json();

            const latestId = messages.length > 0 ? Number(messages[messages.length - 1].id) : 0;
            if (!forceScroll && messages.length === lastRenderedMessageCount && latestId === lastMessageId) {
                return;
            }

            const isNearBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight < 100;

            messagesContainer.innerHTML = '';
            if (messages.length === 0) {
                messagesContainer.innerHTML = `
                    <div class="empty-chat-state">
                        <p>No messages yet. Say hello to start the conversation!</p>
                    </div>
                `;
            } else {
                messages.forEach(msg => {
                    const isSent = Number(msg.sender_id) === currentUserId;
                    const wrapper = document.createElement('div');
                    wrapper.className = `message-wrapper ${isSent ? 'sent' : 'received'}`;

                    const bubble = document.createElement('div');
                    bubble.className = 'message-bubble';

                    const textEl = document.createElement('p');
                    textEl.className = 'message-text';
                    textEl.textContent = msg.message_text;

                    const timeEl = document.createElement('span');
                    timeEl.className = 'message-time';
                    timeEl.textContent = formatTimestamp(msg.created_at);

                    bubble.appendChild(textEl);
                    bubble.appendChild(timeEl);
                    wrapper.appendChild(bubble);
                    messagesContainer.appendChild(wrapper);
                });
            }

            lastRenderedMessageCount = messages.length;
            lastMessageId = latestId;

            if (forceScroll || isNearBottom) {
                scrollToBottom(!forceScroll);
            }
        } catch (err) {
            console.error('Error loading messages:', err);
        }
    }

    function formatTimestamp(ts) {
        const date = new Date(ts.replace(' ', 'T'));
        if (isNaN(date.getTime())) return ts;
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function scrollToBottom(smooth = true) {
        messagesContainer.scrollTo({
            top: messagesContainer.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto'
        });
    }

    // 6. Send Message Handler
    messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = messageInput.value.trim();
        if (!text || !selectedUserId) return;

        messageInput.value = '';
        try {
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    receiver_id: selectedUserId,
                    message_text: text
                })
            });
            const result = await response.json();
            if (result.status === 'success') {
                fetchMessages(true);
            }
        } catch (err) {
            console.error('Failed to send message:', err);
        }
    });

    // 7. Polling Loop (Every 2 Seconds)
    loadUsers();
    setInterval(() => {
        loadUsers();
        if (selectedUserId) {
            fetchMessages(false);
        }
    }, 2000);
});