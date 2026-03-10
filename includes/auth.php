<?php
require_once "db_connect.php";

if (!isset($_COOKIE['pg_session'])) {
    header("Location: login.php");
    exit;
}

$session_id = $_COOKIE['pg_session'];

$stmt = $pdo->prepare("SELECT s.*, u.* FROM sessions s JOIN users u ON s.user_id = u.id WHERE s.session_id = ? AND s.expires_at > NOW()");
$stmt->execute([$session_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    setcookie("pg_session", "", time()-3600, "/");
    header("Location: login.php");
    exit;
}
?>
