<?php
require_once "includes/db_connect.php";

$user_name = "";

// Get current user name if logged in
if (isset($_COOKIE['pg_session'])) {
    $session_id = $_COOKIE['pg_session'];
    $stmt = $pdo->prepare("SELECT u.name FROM sessions s JOIN users u ON s.user_id = u.id WHERE s.session_id = ? AND s.expires_at > NOW()");
    $stmt->execute([$session_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $user_name = $user['name'];
    }
}

// Handle search + filters
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$where = [];
$params = [];

// Search
if ($search) {
    $where[] = "(pg.pg_name LIKE ? OR pg.city LIKE ? OR pg.address LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

// City filter
if (!empty($_GET['city'])) {
    $where[] = "pg.city = ?";
    $params[] = $_GET['city'];
}

// Gender filter
if (!empty($_GET['gender'])) {
    $where[] = "pg.gender = ?";
    $params[] = $_GET['gender'];
}

// Budget filter
if (!empty($_GET['budget'])) {
    if ($_GET['budget'] == '5000') {
        $where[] = "pg.rent <= 5000";
    } elseif ($_GET['budget'] == '10000') {
        $where[] = "pg.rent BETWEEN 5000 AND 10000";
    } elseif ($_GET['budget'] == '10001') {
        $where[] = "pg.rent > 10000";
    }
}

// Sharing filter
if (!empty($_GET['sharing'])) {
    if ($_GET['sharing'] === '4+') {
        $where[] = "pg.sharing_type >= 4";
    } else {
        $where[] = "pg.sharing_type = ?";
        $params[] = $_GET['sharing'];
    }
}

$sql = "SELECT pg.*, u.name AS owner_name 
        FROM pg_listings pg 
        JOIN users u ON pg.owner_id = u.id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY pg.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load all images for random fallback
$all_images = glob("uploads/sample/*.{jpg,png,jpeg,gif}", GLOB_BRACE);
if (!$all_images) {
    $all_images = ["uploads/sample/default.jpg"];
}
$all_images = array_map(function($img){ return str_replace("\\","/",$img); }, $all_images);
shuffle($all_images);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PG Listings | PG Oasis</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
body { font-family: Poppins, sans-serif; background-color: #e6f2ff; margin:0; padding:0; overflow-x:hidden; }

/* ===== Navbar ===== */
nav { display:flex; justify-content:space-between; align-items:center; background:#2c3e50; padding:15px 50px; color:white; position:sticky; top:0; z-index:1000; }
nav .logo { display:flex; align-items:center; font-size:24px; font-weight:600; color:#f1c40f; gap:10px; }
nav .logo img { height:50px; }
nav .nav-links a { color:white; text-decoration:none; margin:0 15px; font-weight:500; }
nav .nav-links a:hover { color:#f1c40f; }

/* ===== Container ===== */
.container { max-width:1200px; margin:40px auto; padding:20px; }
.container h1 { text-align:center; color:#004aad; font-size:2.5rem; margin-bottom:40px; }

/* ===== Search Bar + Filters ===== */
.search-bar { display:flex; flex-direction:column; align-items:center; margin-bottom:30px; gap:10px; }
.search-bar form { display:flex; flex-wrap:wrap; justify-content:center; gap:10px; }
.search-bar input, .search-bar select { padding:10px 15px; border-radius:6px; border:1px solid #ccc; font-size:16px; }
.search-bar button { background:#f1c40f; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; }
.search-bar button:hover { background:#d4ac0d; }

/* ===== PG List Layout ===== */
.pg-list { display: flex; flex-direction: column; gap:30px; }
.pg-card { display: flex; background: white; border-radius: 15px; height: 100%; overflow: hidden; box-shadow:0 6px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; }
.pg-card:hover { transform: translateY(-5px); box-shadow:0 10px 25px rgba(0,0,0,0.2); }
.pg-card img { width: 300px; height: 100%; object-fit:cover; }
.pg-details { padding: 20px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
.pg-details h3 { color:#004aad; font-size:1.5rem; margin-bottom:8px; }
.pg-details p { margin:5px 0; color:#003366; font-size:1rem; }
.pg-details .rent { font-weight:bold; font-size:1.2rem; margin-top:5px; }
.pg-details .owner { color:#888; font-size:0.9rem; margin-top:5px; }
.pg-details button { margin-top:15px; padding:10px 20px; background:#f1c40f; border:none; border-radius:6px; font-weight:600; cursor:pointer; transition: background 0.3s; }
.pg-details button:hover { background:#d4ac0d; }

/* ===== Modal ===== */
.modal { display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color: rgba(0,0,0,0.6); }
.modal-content { background:#fff; margin:10% auto; padding:20px; border-radius:10px; width:90%; max-width:600px; position:relative; }
.close { position:absolute; top:10px; right:15px; font-size:28px; font-weight:bold; color:#333; cursor:pointer; }
.modal-content img { width:100%; height:250px; object-fit:cover; border-radius:8px; margin-bottom:15px; }

/* ===== Footer ===== */
footer { background:rgba(128,128,128,0.15); padding:30px; text-align:center; font-size:1rem; color:#003366; margin-top:50px; }
</style>
</head>
<body>

<!-- Navbar -->
<nav>
  <div class="logo">
    <img src="uploads/logo/logo.png" alt="PG Oasis Logo">
    <span>PG Oasis</span>
  </div>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="add_pg.php">Add PG</a>
    <a href="feedback.php">Feedback</a>
    <?php if($user_name): ?>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">Login/Register</a>
    <?php endif; ?>
  </div>
</nav>

<div class="container">
  <h1>Find Your Perfect PG</h1>

  <!-- Search + Filters -->
  <div class="search-bar">
    <form method="GET" action="pg_listings.php">
      <input type="text" name="query" placeholder="Search PGs by city, area, or name..." value="<?= htmlspecialchars($search) ?>">
      <select name="city">
        <option value="">All Cities</option>
        <option value="Delhi">Delhi</option>
        <option value="Mumbai">Mumbai</option>
        <option value="Pune">Pune</option>
        <option value="Chennai">Chennai</option>
        <option value="Kolkata">Kolkata</option>
        <option value="Bengaluru">Bengaluru</option>
        <option value="Hyderabad">Hyderabad</option>
      </select>
      <select name="gender">
        <option value="">All</option>
        <option value="Boys">Boys</option>
        <option value="Girls">Girls</option>
        <option value="Any">Any</option>
      </select>
      <select name="sharing">
        <option value="">All Sharing</option>
        <option value="1">1 Sharing</option>
        <option value="2">2 Sharing</option>
        <option value="3">3 Sharing</option>
        <option value="4+">4+ Sharing</option>
      </select>
      <select name="budget">
        <option value="">Any Budget</option>
        <option value="5000">Under ₹5000</option>
        <option value="10000">₹5000 - ₹10000</option>
        <option value="10001">Above ₹10000</option>
      </select>
      <button type="submit">Apply Filters</button>
    </form>
  </div>

  <div class="pg-list">
    <?php if(count($pgs) > 0): ?>
      <?php foreach($pgs as $pg): ?>
        <?php
            $pg_image = !empty($pg['image1']) 
                ? str_replace("\\","/",$pg['image1']) 
                : $all_images[array_rand($all_images)];
        ?>
        <div class="pg-card">
            <img src="<?= htmlspecialchars($pg_image) ?>" alt="<?= htmlspecialchars($pg['pg_name']) ?>">
            <div class="pg-details">
                <h3><?= htmlspecialchars($pg['pg_name']) ?></h3>
                <p><strong>City:</strong> <?= htmlspecialchars($pg['city']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($pg['address']) ?></p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($pg['gender']) ?></p>
                <p class="rent">₹<?= htmlspecialchars($pg['rent']) ?>/month</p>
                <p class="security_deposit"><strong>Security Deposit:</strong> ₹<?= htmlspecialchars($pg['security_deposit']) ?></p>
                <p class="owner">Listed by <?= htmlspecialchars($pg['owner_name']) ?></p>
                <button onclick="openModal('modal-<?= $pg['id'] ?>')">Book Now</button>
            </div>
        </div>

        <!-- Modal -->
        <div id="modal-<?= $pg['id'] ?>" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('modal-<?= $pg['id'] ?>')">&times;</span>
                <img src="<?= htmlspecialchars($pg_image) ?>" alt="<?= htmlspecialchars($pg['pg_name']) ?>">
                <h2><?= htmlspecialchars($pg['pg_name']) ?></h2>
                <p><strong>City:</strong> <?= htmlspecialchars($pg['city']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($pg['address']) ?></p>
                <p><strong>Gender:</strong> <?= htmlspecialchars($pg['gender']) ?></p>
                <p><strong>Furnish Type:</strong> <?= htmlspecialchars($pg['furnish_type']) ?></p>
                <p><strong>Bed Available:</strong> <?= htmlspecialchars($pg['bed_available']) ?></p>
                <p><strong>Total Rooms:</strong> <?= htmlspecialchars($pg['total_rooms']) ?></p>
                <p><strong>Sharing Type:</strong> <?= htmlspecialchars($pg['sharing_type'] ?? $pg['sharing_non_sharing']) ?></p>
                <p class="rent"><strong>Rent:</strong> ₹<?= htmlspecialchars($pg['rent']) ?>/month</p>
                <p class="security_deposit"><strong>Security Deposit:</strong> ₹<?= htmlspecialchars($pg['security_deposit']) ?></p>
                <p class="owner"><strong>Owner:</strong> <?= htmlspecialchars($pg['owner_name']) ?></p>
                <a href="book_pg.php?pg_id=<?= $pg['id'] ?>" class="book-btn">Proceed to Book</a>

            </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center; color:#003366;">No PGs found for your search or filters.</p>
    <?php endif; ?>
  </div>
</div>

<footer>
  © 2025 PG Oasis | Designed for Easy Living
</footer>

<script>
function openModal(id) { document.getElementById(id).style.display = 'block'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }
window.onclick = function(event) {
  if(event.target.classList.contains('modal')) { event.target.style.display = 'none'; }
}
</script>
</body>
</html>
