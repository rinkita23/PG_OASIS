<?php
require_once "includes/db_connect.php";

$user_name = ""; // Default empty

if (isset($_COOKIE['pg_session'])) {
    $session_id = $_COOKIE['pg_session'];
    $stmt = $pdo->prepare("SELECT u.name FROM sessions s JOIN users u ON s.user_id = u.id WHERE s.session_id = ? AND s.expires_at > NOW()");
    $stmt->execute([$session_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_name = $user['name'];
    }
}
// Fetch all PG listings
$stmt = $pdo->prepare("SELECT pg.*, u.name AS owner_name FROM pg_listings pg JOIN users u ON pg.owner_id = u.id ORDER BY pg.created_at DESC");
$stmt->execute();
$pg_listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PG Oasis | Home</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
/* ===== Global ===== */
html, body {
  overflow-x: hidden;
  margin:0;
  padding:0;
  font-family:Poppins, sans-serif;
  background-color:#e6f2ff;
  scroll-behavior:smooth;
}
*, *::before, *::after { box-sizing: border-box; }

/* ===== Navbar ===== */
nav {
  display:flex;
  justify-content:space-between;
  align-items:center;
  background:#2c3e50;
  padding:15px 50px;
  color:white;
  position:sticky;
  top:0;
  z-index:1000;
}
nav .logo {
    display: flex;
    align-items: center;
    font-size: 24px;
    font-weight: 600;
    color: #f1c40f;
    gap: 10px; /* space between logo and text */
}

nav .logo img {
    height: 50px;  /* adjust as needed */
    width: auto;
}

nav .nav-links a { color:white; text-decoration:none; margin:0 15px; font-weight:500; }
nav .nav-links a:hover { color:#f1c40f; }

/* ===== Hero ===== */
.hero {
  width:100%;
  background: url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
  height:55vh;
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  text-align:center;
  color:white;
  position:relative;
}
.hero::after {
  content:"";
  position:absolute;
  top:0; left:0; right:0; bottom:0;
  background: rgba(0,0,0,0.4);
}
.hero-content { position:relative; z-index:1; }
.hero h1 { font-size:42px; margin-bottom:10px; }
.hero p { font-size:18px; color:#eee; }
.search-bar { margin-top:20px; display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }
.search-bar input { padding:10px 15px; width:280px; border-radius:6px; border:1px solid #ccc; font-size:16px; }
.search-bar button { background:#f1c40f; border:none; padding:10px 20px; border-radius:6px; font-weight:600; cursor:pointer; }
.search-bar button:hover { background:#d4ac0d; }

/* ===== Popular Cities ===== */
.cities {
  width:100vw;
  margin-left:calc(-50vw + 50%);
  background:linear-gradient(135deg, rgba(230,242,255,0.8), rgba(200,230,255,0.9));
  padding:60px 0;
}
.cities h2 {
  font-size:2rem;
  margin-bottom:40px;
  color:#004aad;
  text-align:center;
  text-shadow:1px 1px 2px rgba(0,0,0,0.2);
}
.city-grid {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
  gap:20px;
  max-width:1200px;
  margin:auto;
}
.city-card {
  position:relative;
  border-radius:15px;
  overflow:hidden;
  cursor:pointer;
  transition:transform 0.3s, box-shadow 0.3s;
}
.city-card img { width:100%; height:120px; object-fit:cover; display:block; }
.city-card h3 {
  position:absolute;
  bottom:10px;
  left:0;
  width:100%;
  text-align:center;
  color:white;
  font-size:1rem;
  background:rgba(0,0,0,0.35);
  margin:0;
  padding:5px 0;
}
.city-card:hover { transform:scale(1.05); box-shadow:0 8px 20px rgba(0,0,0,0.2); }

/* ===== 3 Steps Booking ===== */
.steps {
  width:100vw;
  margin-left:calc(-50vw + 50%);
  padding:80px 0;
  text-align:center;
  background:linear-gradient(135deg, rgba(144,238,144,0.3), rgba(144,238,144,0.6));
}
.steps h2 {
  font-size:2.5rem;
  color:#004aad;
  text-shadow:1px 1px 3px rgba(0,0,0,0.2);
  margin-bottom:50px;
}
.steps-container {
  display:flex;
  justify-content:center;
  gap:40px;
  flex-wrap:wrap;
  max-width:1200px;
  margin:auto;
}
.step-card {
  background: linear-gradient(145deg, #bfffc2, #a0f7a0);
  border-radius:15px;
  padding:25px;
  width:250px;
  box-shadow:0 8px 20px rgba(0,0,0,0.15);
  transition: all 0.3s ease;
  text-align:center;
  animation: pop 1s ease-in-out infinite alternate;
}
@keyframes pop {
  from { transform:translateY(0px); box-shadow:0 6px 15px rgba(0,0,0,0.15); }
  to { transform:translateY(-8px); box-shadow:0 10px 25px rgba(0,0,0,0.25); }
}
.step-card h3 { color:#004aad; text-shadow:0 1px 2px rgba(0,0,0,0.2); }
.step-card p { color:#003366; font-weight:500; line-height:1.4; }

/* ===== List Your PG ===== */
.list-section {
  background:rgba(210,180,140,0.3);
  border-radius:15px;
  display:flex;
  flex-wrap:wrap;
  justify-content:center;
  gap:30px;
  padding:40px 20px;
  max-width:1200px;
  margin:40px auto;
}
.list-card { display:flex; flex-wrap:wrap; align-items:center; gap:20px; max-width:1000px; }
.list-card img { width:100%; max-width:300px; border-radius:12px; object-fit:cover; }
.list-text { max-width:600px; }
.list-text h2 {
  font-size:2rem;
  background:linear-gradient(to right, #004aad, #00aaff);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  text-shadow:1px 1px 2px rgba(0,0,0,0.1);
  margin-bottom:20px;
}
.list-text p {
  font-size:1rem;
  color:#003366;
  font-weight:500;
  text-shadow:0 1px 1px rgba(0,0,0,0.05);
}
.list-text a {
  padding:12px 25px;
  font-weight:600;
  font-size:1rem;
  background:linear-gradient(45deg, #f1c40f, #ffd347);
  border-radius:8px;
  color:#fff;
  text-decoration:none;
  transition:0.3s;
}
.list-text a:hover { background:linear-gradient(45deg, #d4ac0d, #ffcc00); }

/* ===== Footer ===== */
footer {
  background:rgba(128,128,128,0.15);
  padding:40px 20px;
  font-size:1rem;
  color:#003366;
  text-align:center;
}
.footer-container {
  display:flex;
  flex-wrap:wrap;
  justify-content:center;
  gap:40px;
  margin-bottom:20px;
}
.footer-col { min-width:150px; }
.footer-col h3 { font-size:1.2rem; margin-bottom:10px; }
.footer-col a { display:block; font-size:1rem; margin-bottom:6px; color:#003366; text-decoration:none; }
.footer-col a:hover { color:#007bff; }
.footer-follow h3 { font-size:1.2rem; margin-bottom:10px; }
.footer-follow .social-icons { display:flex; justify-content:center; gap:15px; }
.footer-follow .social-icons a { font-size:1.5rem; color:#003366; transition:color 0.3s; }
.footer-follow .social-icons a:hover { color:#007bff; }
footer .copyright { font-size:0.95rem; margin-top:20px; }

/* ===== Responsive ===== */
@media(max-width:768px){
  .steps-container, .list-card, .city-grid { grid-template-columns:1fr; }
  .list-card img { width:100%; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<!-- NAVBAR -->
<nav>
  <div class="logo">
    <img src="uploads/logo/logo.png" alt="PG Oasis Logo" id="logo">
  </div>
  <div class="nav-links">
    <a href="home.php">Home</a>
    <a href="pg_listings.php">PGs</a>
    <a href="add_pg.php">Add PG</a>
    <a href="feedback.php">Feedback</a>

    <?php if($user_name): ?>
        <a href="booking.php">My Bookings</a>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login/Register</a>
    <?php endif; ?>
  </div>
</nav>



<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <h1>
      <?php 
        if($user_name) {
            echo "Hello, " . htmlspecialchars($user_name) . "! Welcome to PG Oasis";
        } else {
            echo "Welcome to PG Oasis";
        }
      ?>
    </h1>
    <p>Find your perfect PG or Hostel in any city effortlessly.</p>
    <div class="search-bar">
  <form method="GET" action="pg_listings.php">
    <input type="text" name="query" placeholder="Search PGs by city, address, or name..." required>
    <button type="submit">Search</button>
  </form>
</div>
      </section>


<!-- Popular Cities -->
<section class="cities">
  <h2>Find the Best PGs in Your City</h2>
  <div class="city-grid">
    <div class="city-card"><img src="uploads/cities/ahmedabad.jpg"><h3>Ahmedabad</h3></div>
    <div class="city-card"><img src="uploads/cities/banglore.jpg"><h3>Bengaluru</h3></div>
    <div class="city-card"><img src="uploads/cities/mumbai.jpg"><h3>Mumbai</h3></div>
    <div class="city-card"><img src="uploads/cities/chennai.jpg"><h3>Chennai</h3></div>
    <div class="city-card"><img src="uploads/cities/delhi.jpg"><h3>Delhi</h3></div>
  </div>
</section>

<!-- 3 Steps Booking -->
<section class="steps">
  <h2>Book Your PG in 3 Easy Steps</h2>
  <div class="steps-container">
    <div class="step-card">
      <div class="step-icon"><i class="fas fa-search"></i></div>
      <h3>Search PG</h3>
      <p>Find PGs based on city, rent, and amenities easily.</p>
    </div>
    <div class="step-card">
      <div class="step-icon"><i class="fas fa-map-marker-alt"></i></div>
      <h3>Choose Location</h3>
      <p>Select the PG that fits your preferred location and budget.</p>
    </div>
    <div class="step-card">
      <div class="step-icon"><i class="fas fa-door-open"></i></div>
      <h3>Book & Move In</h3>
      <p>Finalize your booking and move into your new PG hassle-free.</p>
    </div>
  </div>
</section>

<!-- List Your PG -->
<section class="list-section">
  <div class="list-card">
    <img src="uploads/sample.jpg" alt="List Your PG">
    <div class="list-text">
      <h2>List Your PG With Us</h2>
      <p>Join PG Oasis and reach thousands of students and professionals looking for PGs.</p>
      <a href="add_pg.php">Add Your PG</a>
    </div>
  </div>
</section>

<!-- Footer -->
<footer>
  <div class="footer-container">
    <div class="footer-col">
      <h3>Communities</h3>
      <a href="#">Students</a>
      <a href="#">Working Professionals</a>
      <a href="#">Families</a>
      <a href="#">PG Owners</a>
      <a href="#">Hostel Managers</a>
      <a href="#">Real Estate Agents</a>
    </div>
    <div class="footer-col">
      <h3>Cities</h3>
      <a href="#">Ahmedabad</a>
      <a href="#">Bengaluru</a>
      <a href="#">Mumbai</a>
      <a href="#">Chennai</a>
      <a href="#">Delhi</a>
      <a href="#">Hyderabad</a>
      <a href="#">Gurgaon</a>
      <a href="#">Noida</a>
      <a href="#">Pune</a>
      <a href="#">Kolkata</a>
    </div>
    <div class="footer-col">
      <h3>Services</h3>
      <a href="#">PG Booking</a>
      <a href="#">Add PG</a>
      <a href="#">Feedback</a>
      <a href="#">Contact Us</a>
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Service</a>
    </div>
  </div>
  <div class="footer-follow">
    <h3>Follow Us</h3>
    <div class="social-icons">
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-whatsapp"></i></a>
      <a href="#"><i class="fab fa-facebook"></i></a>
    </div>
  </div>
  <div class="copyright">© 2025 PG Oasis | Designed for Easy Living</div>
</footer>

<script>
function filterPGs() {
  const input = document.getElementById('searchCity').value.toLowerCase();
  const cards = document.querySelectorAll('.pg-card');
  cards.forEach(card => {
    const city = card.getAttribute('data-city');
    if(city.includes(input)) card.style.display = 'block';
    else card.style.display = 'none';
  });
}
</script>
</body>
</html>
