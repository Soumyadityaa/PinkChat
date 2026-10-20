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
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

if ($receiver_id <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id, sender_id, receiver_id, message_text, created_at 
        FROM messages 
        WHERE (sender_id = :u1 AND receiver_id = :r1) 
           OR (sender_id = :r2 AND receiver_id = :u2)
        ORDER BY created_at ASC, id ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':u1' => $current_user,
    ':r1' => $receiver_id,
    ':r2' => $receiver_id,
    ':u2' => $current_user
]);

$messages = $stmt->fetchAll();
echo json_encode($messages);
?>