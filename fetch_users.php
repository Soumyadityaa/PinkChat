<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'db_connection.php';

$current_user = (int)$_SESSION['user_id'];

$sql = "SELECT u.id, u.username, u.status, COUNT(m.id) AS unread_count
        FROM users u
        LEFT JOIN messages m 
            ON m.sender_id = u.id 
           AND m.receiver_id = :current_user 
           AND m.is_read = 0
        WHERE u.id != :exclude_user
        GROUP BY u.id, u.username, u.status
        ORDER BY u.username ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':current_user' => $current_user,
        ':exclude_user' => $current_user
    ]);
} catch (PDOException $e) {
    // Automatically add the is_read column if it hasn't been created yet
    $pdo->exec("ALTER TABLE messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0");
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':current_user' => $current_user,
        ':exclude_user' => $current_user
    ]);
}

$users = $stmt->fetchAll();
echo json_encode($users);
?>
