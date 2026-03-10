<?php
require_once "includes/auth.php"; // makes $user available
require_once "includes/db_connect.php";

// Redirect if not logged in
if (!$user) {
    header("Location: login.php");
    exit;
}

// Ensure PG ID is passed
if (!isset($_GET['pg_id'])) {
    header("Location: pg_listings.php");
    exit;
}

$pg_id = intval($_GET['pg_id']);

try {
    // Insert booking with confirmed status
    $stmt = $pdo->prepare("
        INSERT INTO bookings (pg_id, user_id, booking_date, status)
        VALUES (?, ?, NOW(), 'confirmed')
    ");
    $stmt->execute([$pg_id, $user['id']]);

    // Redirect to bookings page with success flag
    header("Location: booking.php?booked=1");
    exit;

} catch (PDOException $e) {
    die("Booking failed: " . $e->getMessage());
}
?>
