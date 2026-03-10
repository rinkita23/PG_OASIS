<?php
require_once "includes/auth.php"; // ensures $user is available
require_once "includes/db_connect.php";

// Redirect if not logged in
if (!$user) {
    header("Location: login.php");
    exit;
}

// Fetch bookings for logged-in user (with full PG details)

$stmt = $pdo->prepare("
    SELECT b.id, b.booking_date, b.status,
           p.pg_name, p.city, p.address, p.gender,
           p.total_rooms, p.sharing_type, p.furnish_type,
           p.rent, p.security_deposit, p.image
    FROM bookings b 
    JOIN pg_listings p ON b.pg_id = p.id 
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");

$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Bookings | PG Oasis</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

body{
  font-family:'Poppins',sans-serif;
  background: linear-gradient(135deg,#d9f0ff,#f0f9ff);
  margin:0;
  min-height:100vh;
  display:flex;
  flex-direction:column;
  align-items:center;
  padding:40px 20px;
}

h2{
  text-align:center;
  color:#004aad;
  font-size:2.8rem;
  margin-bottom:40px;
}

.booking-card{
  width:90%;
  max-width:700px;
  background:#fff;
  border-radius:20px;
  box-shadow:0 10px 25px rgba(0,0,0,0.15);
  padding:25px 30px;
  margin-bottom:25px;
  transition:transform 0.3s, box-shadow 0.3s;
}
.booking-card:hover{
  transform:translateY(-5px);
  box-shadow:0 20px 35px rgba(0,0,0,0.2);
}
.booking-card img{
  width:100%;
  height:230px;
  object-fit:cover;
  border-radius:15px;
  margin-bottom:15px;
}
.booking-card h3{
  color:#004aad;
  margin-bottom:10px;
  font-size:1.6rem;
}
.booking-info p{
  font-size:1.1rem;
  color:#333;
  margin:5px 0;
}
.status{
  font-weight:700;
  text-transform:capitalize;
  margin-top:10px;
}
.status.pending{color:#e67e22;}
.status.confirmed{color:#27ae60;}
.status.cancelled{color:#c0392b;}

.no-booking{
  text-align:center;
  font-size:1.3rem;
  color:#555;
  background:#fff;
  padding:40px;
  border-radius:20px;
  box-shadow:0 10px 25px rgba(0,0,0,0.1);
}
.no-booking button{
  margin-top:20px;
  background:linear-gradient(45deg,#004aad,#0066cc);
  color:#fff;
  border:none;
  padding:12px 25px;
  border-radius:10px;
  font-size:1rem;
  cursor:pointer;
  transition:0.3s;
}
.no-booking button:hover{
  transform:scale(1.05);
  background:linear-gradient(45deg,#003580,#0055aa);

  
}

.back-btn{
  position:fixed;
  top:20px;
  left:20px;
  background:#004aad;
  color:#fff;
  padding:10px 18px;
  border-radius:8px;
  text-decoration:none;
  font-weight:600;
  transition:0.3s;
}
.back-btn:hover{ background:#00347a; }

.success-message{
  background:#d4f8d4;
  color:#157a32;
  padding:12px 18px;
  border-radius:8px;
  margin-bottom:20px;
  font-size:1.1rem;
  font-weight:600;
  text-align:center;
}

</style>
</head>
<body>

<a href="home.php" class="back-btn">← Back to Home</a>
<h2>My Bookings</h2>

<?php if (isset($_GET['booked'])): ?>
<div class="success-message">✅ Booking Confirmed Successfully!</div>
<?php endif; ?>


<?php if (count($bookings) > 0): ?>
    <?php foreach ($bookings as $b): ?>
        <div class="booking-card">

            <?php 
                $img = !empty($b['image']) ? $b['image'] : "uploads/sample/default.jpg";

            ?>
            <img src="<?= htmlspecialchars($img) ?>" alt="PG Image">

            <h3><?= htmlspecialchars($b['pg_name']) ?></h3>

            <div class="booking-info">
                <p><strong>City:</strong> <?= htmlspecialchars($b['city']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($b['address']) ?></p>
                <p><strong>Gender Allowed:</strong> <?= htmlspecialchars($b['gender']) ?></p>
                <p><strong>Sharing Type:</strong> <?= htmlspecialchars($b['sharing_type']) ?> Sharing</p>
                <p><strong>Furnish Type:</strong> <?= htmlspecialchars($b['furnish_type']) ?></p>

                <p><strong>Rent:</strong> ₹<?= htmlspecialchars($b['rent']) ?>/month</p>
                <p><strong>Security Deposit:</strong> ₹<?= htmlspecialchars($b['security_deposit']) ?></p>
                <p><strong>Total Cost:</strong> ₹<?= number_format((float)$b['rent'] + (float)$b['security_deposit']) ?></p>

                <p><strong>Booking Date:</strong> <?= htmlspecialchars($b['booking_date']) ?></p>

                <p class="status <?= strtolower($b['status']) ?>">
                    <strong>Status:</strong> <?= htmlspecialchars($b['status']) ?>
                </p>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="no-booking">
        <p>😕 You haven’t booked any PG yet.</p>
        <form action="pg_listings.php" method="get">
            <button type="submit">Book Now</button>
        </form>
    </div>
<?php endif; ?>

</body>
</html>
