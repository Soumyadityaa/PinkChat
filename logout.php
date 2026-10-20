<?php
session_start();
if (isset($_SESSION['user_id'])) {
    require_once 'db_connection.php';
    $stmt = $pdo->prepare("UPDATE users SET status = 'offline' WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
?>