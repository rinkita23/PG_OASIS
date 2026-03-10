<?php
require_once "includes/db_connect.php";

if (isset($_COOKIE['pg_session'])) {
    $session_id = $_COOKIE['pg_session'];
    $stmt = $pdo->prepare("DELETE FROM sessions WHERE session_id = ?");
    $stmt->execute([$session_id]);
    setcookie("pg_session", "", time()-3600, "/");
}

header("Location: login.php");
exit;
?>
