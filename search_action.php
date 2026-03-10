<?php
require_once "includes/db_connect.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($q == '') {
    exit('<p>Start typing to search for PGs...</p>');
}

$stmt = $pdo->prepare("
    SELECT pg_name, city, address, rent, bed_available, sharing_type, furnish_type, image
    FROM pg_listings
    WHERE pg_name LIKE :q OR city LIKE :q OR address LIKE :q
    ORDER BY city ASC
");
$stmt->execute(['q' => "%$q%"]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    exit('<p>No PGs found matching "<strong>' . htmlspecialchars($q) . '</strong>".</p>');
}

foreach ($rows as $pg) {
    echo '<div class="pg-card">';
    echo '<img src="uploads/' . htmlspecialchars($pg['image']) . '" alt="PG Image">';
    echo '<div class="content">';
    echo '<h3>' . htmlspecialchars($pg['pg_name']) . '</h3>';
    echo '<p><strong>City:</strong> ' . htmlspecialchars($pg['city']) . '</p>';
    echo '<p><strong>Address:</strong> ' . htmlspecialchars($pg['address']) . '</p>';
    echo '<p><strong>Sharing Type:</strong> ' . htmlspecialchars($pg['sharing_type']) . '</p>';
    echo '<p><strong>Furnish:</strong> ' . htmlspecialchars($pg['furnish_type']) . '</p>';
    echo '<p><strong>Beds:</strong> ' . htmlspecialchars($pg['bed_available']) . '</p>';
    echo '<p><strong>Rent:</strong> ₹' . htmlspecialchars($pg['rent']) . '</p>';
    echo '</div></div>';
}
?>
