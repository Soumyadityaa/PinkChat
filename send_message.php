<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

require_once 'db_connection.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$sender_id = (int)$_SESSION['user_id'];
$receiver_id = isset($input['receiver_id']) ? (int)$input['receiver_id'] : 0;
$message_text = trim($input['message_text'] ?? '');

if ($receiver_id <= 0 || $message_text === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid recipient or empty message']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)");
$success = $stmt->execute([$sender_id, $receiver_id, $message_text]);

if ($success) {
    echo json_encode(['status' => 'success', 'message_id' => $pdo->lastInsertId()]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save message']);
}
?>