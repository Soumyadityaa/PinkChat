<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$currentUser = htmlspecialchars($_SESSION['username']);
$currentUserId = (int)$_SESSION['user_id'];
$cacheBust = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, interactive-widget=resizes-content">
    <title>Messenger Workspace</title>
    <link rel="stylesheet" href="style.css?v=<?= $cacheBust ?>">
    <style>
        /* Hard override to prevent cached CSS from clipping top header or bottom input on mobile */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            overflow: hidden !important;
            position: fixed !important;
            inset: 0 !important;
        }
        #messengerLayout {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            height: 100dvh;
            overflow: hidden !important;
        }
        #sidebar, #chatArea {
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }
        #sidebarHeader, #chatHeader, .search-box {
            flex: 0 0 auto !important;
        }
        #usersList, #messagesContainer {
            flex: 1 1 0% !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        #messageForm {
            flex: 0 0 auto !important;
            width: 100% !important;
            padding: 10px 12px 16px 12px !important;
            box-sizing: border-box !important;
        }
    </style>
</head>
<body>
    <div class="messenger-layout" id="messengerLayout" data-current-user-id="<?= $currentUserId ?>">
        <!-- Left Sidebar -->
        <aside class="sidebar" id="sidebar">
            <header class="sidebar-header" id="sidebarHeader">
                <div class="user-profile">
                    <div class="avatar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="user-meta">
                        <span class="username"><?= $currentUser ?></span>
                        <span class="status-badge online">Online</span>
                    </div>
                </div>
                <div class="header-actions">
                    <button type="button" id="themeToggle" class="icon-btn" aria-label="Toggle theme" title="Toggle Theme">
                        <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"/>
                            <line x1="12" y1="1" x2="12" y2="3"/>
                            <line x1="12" y1="21" x2="12" y2="23"/>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                            <line x1="1" y1="12" x2="3" y2="12"/>
                            <line x1="21" y1="12" x2="23" y2="12"/>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                    <a href="logout.php" class="icon-btn" aria-label="Logout" title="Logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                    </a>
                </div>
            </header>

            <div class="search-box">
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="userSearch" placeholder="Search users..." autocomplete="off">
            </div>

            <div class="users-list" id="usersList"></div>
        </aside>

        <!-- Right Chat Area -->
        <main class="chat-area" id="chatArea">
            <header class="chat-header" id="chatHeader">
                <button type="button" class="icon-btn mobile-back-btn" id="backToUsersBtn" aria-label="Back to user list">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </button>
                <div class="chat-partner-info" id="chatPartnerInfo">
                    <div class="avatar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="activePartnerName">Select a conversation</h2>
                        <span id="activePartnerStatus" class="status-text">Choose a user from the left sidebar</span>
                    </div>
                </div>
            </header>

            <div class="messages-container" id="messagesContainer">
                <div class="empty-chat-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                    <p>No active chat selected. Click on a user to begin messaging.</p>
                </div>
            </div>

            <form class="message-form" id="messageForm">
                <input type="text" id="messageInput" placeholder="Write a message..." autocomplete="off" disabled required>
                <button type="submit" id="sendButton" class="send-btn" disabled aria-label="Send message">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </form>
        </main>
    </div>

    <script src="script.js?v=<?= $cacheBust ?>"></script>
</body>
</html>