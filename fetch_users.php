<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once 'db_connection.php';

$stmt = $pdo->prepare("SELECT id, username, status FROM users WHERE id != ? ORDER BY username ASC");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();

echo json_encode($users);
?>