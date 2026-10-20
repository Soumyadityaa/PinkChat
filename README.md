# PinkChat — Private 1-on-1 Messenger PWA

PinkChat is a lightweight, installable **Progressive Web App (PWA)** for 1-on-1 private messaging built with **HTML5**, **CSS3**, **Vanilla JavaScript (ES6+ Fetch API)**, **PHP**, and **MySQL (PDO)**[cite: 6, 9, 13, 15]. It features a custom pink aesthetic with persistent Light and Dark themes, pure inline SVG icons, real-time message polling, unread message indicators, and a mobile-locked viewport layout that prevents address-bar and on-screen keyboard clipping[cite: 9, 13, 15].

---

## Features

- **1-on-1 Private Messaging:** Real-time two-way conversations between registered users powered by asynchronous Fetch API polling (every 2 seconds)[cite: 7, 13, 14].
- **Unread Message Indicator:** Displays a glowing pink unread dot on the user sidebar when new messages arrive, automatically marking messages as read when the conversation is opened.
- **Installable PWA (Progressive Web App):** Includes a Web App Manifest (`manifest.json`), Service Worker (`sw.js`) with network-first dynamic routing, and scalable vector app icon (`icon.svg`) for standalone installation on Android, iOS, and Desktop.
- **Dark & Light Theme Toggle:** Smooth CSS variable-based theme switching with state persistence via `localStorage`[cite: 13, 15].
- **Mobile-First Viewport Lock:** Uses `100dvh` and the JavaScript `window.visualViewport` API to keep the conversation header and bottom message input bar visible at all times on mobile browsers[cite: 9, 13, 15].
- **Online / Offline Presence:** Automatically tracks and displays user online status upon login and logout[cite: 8, 10, 11, 13].
- **Live User Search:** Instant client-side filtering of registered users in the sidebar[cite: 9, 13].
- **Zero External UI Dependencies:** Built entirely with custom CSS Grid/Flexbox and clean inline SVG icons (no icon fonts, no heavy frameworks, and no emojis)[cite: 9, 15].
- **Secure Backend Architecture:**
  - Password hashing using PHP `password_hash()` (`PASSWORD_DEFAULT`) and `password_verify()`[cite: 10, 12].
  - SQL injection protection using PHP Data Objects (`PDO`) with prepared statements and disabled emulated prepares[cite: 6, 7, 8, 14].
  - Cross-Site Scripting (XSS) protection using `htmlspecialchars()` on server-rendered output and `.textContent` on client-rendered DOM nodes[cite: 9, 10, 12, 13].

---

## Tech Stack

| Layer | Technology |
| :--- | :--- |
| **Frontend** | HTML5, CSS3 (Custom Properties, Grid, Flexbox), JavaScript (ES6+, Fetch API, Visual Viewport API)[cite: 9, 13, 15] |
| **PWA** | Web App Manifest (`manifest.json`), Service Worker API (`sw.js`), SVG App Icon (`icon.svg`) |
| **Backend** | PHP 7.4 / 8.x (Sessions, PDO)[cite: 6, 7, 8, 14] |
| **Database** | MySQL / MariaDB (`utf8mb4`)[cite: 6] |
| **Environment** | Local XAMPP / Shared Cloud Hosting (InfinityFree, cPanel)[cite: 6] |

---

## Project Structure

```text
├── db_connection.php   # PDO database connection configuration
├── index.php           # Protected main messenger workspace & UI layout
├── login.php           # User authentication & session initialization
├── register.php        # New user registration & password hashing
├── logout.php          # Session destruction & offline status update
├── fetch_users.php     # JSON endpoint returning users & unread message counts
├── fetch_messages.php  # JSON endpoint returning 1-on-1 chat history & marking messages read
├── send_message.php    # POST JSON endpoint to validate and store new messages
├── script.js           # Client-side state, AJAX polling, unread dots, theme toggle, & PWA registration
├── style.css           # Light/Dark pink theme variables, unread dot badge, & responsive styling
├── manifest.json       # PWA Web App Manifest for standalone home-screen installation
├── sw.js               # PWA Service Worker (network-first PHP & static asset caching)
└── icon.svg            # Scalable vector app icon for mobile & desktop PWA installation
```

---

## Database Schema

Run the following SQL script in **phpMyAdmin** to create the database and required tables (including the `is_read` column for unread message indicators):

```sql
CREATE DATABASE IF NOT EXISTS private_messenger CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE private_messenger;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(20) DEFAULT 'offline'
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_text TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (sender_id, receiver_id, created_at)
);
```

*(Note: If you are upgrading an existing database, run `ALTER TABLE messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0;` in phpMyAdmin).*

---

## Installation & Setup

### Option A: Local Setup (XAMPP)

1. **Clone the Repository:**
   ```bash
   git clone [https://github.com/your-username/pinkchat-private-messenger.git](https://github.com/your-username/pinkchat-private-messenger.git)
   ```
2. **Move to Web Root:**
   Copy the project folder into `C:\xampp\htdocs\messenger`.
3. **Start Services:**
   Open the XAMPP Control Panel and start **Apache** and **MySQL**.
4. **Import Database:**
   Open `http://localhost/phpmyadmin`, go to the **SQL** tab, and execute the SQL schema above.
5. **Configure Database Credentials:**
   Verify `db_connection.php` matches your local environment[cite: 6]:
   ```php
   $host = '127.0.0.1';
   $dbname = 'private_messenger';
   $db_user = 'root';
   $db_pass = '';
   ```
6. **Launch the App:**
   Visit `http://localhost/messenger/register.php` in your browser to create two test accounts and begin chatting[cite: 12].

### Option B: Live Hosting Deployment (InfinityFree / cPanel)

1. Create a MySQL database in your hosting control panel and import the `users` and `messages` tables via phpMyAdmin.
2. Update `$host`, `$dbname`, `$db_user`, and `$db_pass` in `db_connection.php` with your hosting provider's MySQL credentials[cite: 6].
3. Upload all project files (including `manifest.json`, `sw.js`, and `icon.svg`) to your `htdocs` or `public_html` directory via File Manager or FTP.
4. Open your live HTTPS domain on a mobile or desktop browser and select **"Add to Home Screen"** or **"Install App"** to run PinkChat as a standalone application.

---

## License

This project is open-source and available under the [MIT License](LICENSE).
