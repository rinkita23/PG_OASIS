<?php
require_once "includes/db_connect.php";

if (isset($_COOKIE['pg_session'])) {
    $session_id = $_COOKIE['pg_session'];
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE session_id = ? AND expires_at > NOW()");
    $stmt->execute([$session_id]);
    $session = $stmt->fetch();

    if ($session) {
        header("Location: home.php");
        exit;
    }
}

header("Location: login.php");
exit;
?>
