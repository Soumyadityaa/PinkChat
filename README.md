# PinkChat — Private 1-on-1 Messenger Web Application

PinkChat is a lightweight, full-stack, 1-on-1 private messaging web application built with **HTML5**, **CSS3**, **Vanilla JavaScript (ES6+ Fetch API)**, **PHP**, and **MySQL (PDO)**. It features a custom pink aesthetic with persistent Light and Dark themes, pure inline SVG icons, real-time message polling, and a mobile-locked viewport layout that prevents address-bar and on-screen keyboard clipping.

---

## Features

- **1-on-1 Private Messaging:** Real-time two-way conversations between registered users powered by asynchronous Fetch API polling (every 2 seconds).
- **Dark & Light Theme Toggle:** Smooth CSS variable-based theme switching with state persistence via `localStorage`.
- **Mobile-First Viewport Lock:** Uses `100dvh` and the JavaScript `window.visualViewport` API to keep the conversation header and bottom message input bar visible at all times on mobile browsers.
- **Online / Offline Presence:** Automatically tracks and displays user online status upon login and logout.
- **Live User Search:** Instant client-side filtering of registered users in the sidebar.
- **Zero External UI Dependencies:** Built entirely with custom CSS Grid/Flexbox and clean inline SVG icons (no icon fonts, no heavy frameworks, and no emojis).
- **Secure Backend Architecture:**
  - Password hashing using PHP `password_hash()` (`PASSWORD_DEFAULT`) and `password_verify()`.
  - SQL injection protection using PHP Data Objects (`PDO`) with prepared statements and disabled emulated prepares.
  - Cross-Site Scripting (XSS) protection using `htmlspecialchars()` on server-rendered output and `.textContent` on client-rendered DOM nodes.

---

## Tech Stack

| Layer | Technology |
| :--- | :--- |
| **Frontend** | HTML5, CSS3 (Custom Properties, Grid, Flexbox), JavaScript (ES6+, Fetch API, Visual Viewport API) |
| **Backend** | PHP 7.4 / 8.x (Sessions, PDO) |
| **Database** | MySQL / MariaDB (`utf8mb4`) |
| **Environment** | Local XAMPP / Shared Cloud Hosting (InfinityFree, cPanel) |

---

## Project Structure

```text
├── db_connection.php   # PDO database connection configuration
├── index.php           # Protected main messenger workspace & UI layout
├── login.php           # User authentication & session initialization
├── register.php        # New user registration & password hashing
├── logout.php          # Session destruction & offline status update
├── fetch_users.php     # JSON endpoint returning all other registered users
├── fetch_messages.php  # JSON endpoint returning chronological 1-on-1 chat history
├── send_message.php    # POST JSON endpoint to validate and store new messages
├── script.js           # Client-side state, AJAX polling, theme toggle, & mobile viewport sync
└── style.css           # Light/Dark pink theme variables & responsive split-pane styling
```

---

## Database Schema

Run the following SQL script in **phpMyAdmin** to create the database and required tables:

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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (sender_id, receiver_id, created_at)
);
```

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
   Verify `db_connection.php` matches your local environment:
   ```php
   $host = '127.0.0.1';
   $dbname = 'private_messenger';
   $db_user = 'root';
   $db_pass = '';
   ```
6. **Launch the App:**
   Visit `http://localhost/messenger/register.php` in your browser to create two test accounts and begin chatting.

### Option B: Live Hosting Deployment (InfinityFree / cPanel)

1. Create a MySQL database in your hosting control panel and import the `users` and `messages` tables via phpMyAdmin.
2. Update `$host`, `$dbname`, `$db_user`, and `$db_pass` in `db_connection.php` with your hosting provider's MySQL credentials.
3. Upload all project files to your `htdocs` or `public_html` directory via File Manager or FTP.

---

## License

This project is open-source and available under the [MIT License](LICENSE).
