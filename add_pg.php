<?php
require_once "includes/db_connect.php";

$message = "";

// --- Get logged-in user ---
$user = null;
if (isset($_COOKIE['pg_session'])) {
    $session_id = $_COOKIE['pg_session'];
    $stmt = $pdo->prepare("SELECT u.* FROM users u 
                           JOIN sessions s ON u.id = s.user_id 
                           WHERE s.session_id = ? AND s.expires_at > NOW()");
    $stmt->execute([$session_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $new_expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
        $update = $pdo->prepare("UPDATE sessions SET expires_at=? WHERE session_id=?");
        $update->execute([$new_expiry, $session_id]);
        setcookie("pg_session", $session_id, time()+60*60*24*7, "/", "", false, true);
    }
}

if (!$user) {
    header("Location: login.php");
    exit;
}

// --- Handle PG Add Form ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pg_name = trim($_POST['pg_name']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);
    $rent = $_POST['rent'];
    $security_deposit = $_POST['security_deposit'];
    $amenities = trim($_POST['amenities']);
    $total_rooms = $_POST['total_rooms'];
    $bed_available = $_POST['bed_available'];
    $sharing_type = $_POST['sharing_type'];
    $furnish_type = $_POST['furnish_type'];
    $gender = $_POST['gender'];

    if (isset($_FILES['image']) && $_FILES['image']['error']==0) {
        $allowed = ['jpg','jpeg','png','webp'];
        $fileName = $_FILES['image']['name'];
        $fileTmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext,$allowed)) $message = "⚠️ Invalid image type.";
        else {
            $newName = uniqid("pg_", true).".".$ext;
            $uploadPath = "uploads/".$newName;
            if (move_uploaded_file($fileTmp,$uploadPath)) {
                $stmt = $pdo->prepare("INSERT INTO pg_listings (owner_id, pg_name, city, address, rent, security_deposit, amenities, image, total_rooms, bed_available, sharing_type, furnish_type, gender) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$user['id'],$pg_name,$city,$address,$rent,$security_deposit,$amenities,$newName,$total_rooms,$bed_available,$sharing_type,$furnish_type,$gender]);
                $message = "✅ PG Added Successfully!";
            } else $message="⚠️ Failed to upload image.";
        }
    } else $message="⚠️ Please upload an image.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add PG | PG Oasis</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

body{
    font-family:'Poppins',sans-serif;
    background: linear-gradient(135deg,#d9f0ff,#f0f9ff);
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:flex-start; 
    padding:40px 20px 100px 20px; /* added extra bottom padding */
    position:relative;
    overflow:auto; /* allows scrolling if content is tall */
}

body::before {
    content:"";
    position:absolute;
    top:0; left:0; right:0; bottom:0;
    background: radial-gradient(circle at top left, rgba(241,196,15,0.15), transparent 70%), 
                radial-gradient(circle at bottom right, rgba(52,152,219,0.1), transparent 70%);
    z-index:0;
}

/* Floating PG Icons */
.floating-icon {
    position:absolute;
    font-size:2rem;
    color:rgba(241,196,15,0.35); /* increased opacity */
    animation: floatUp 8s linear infinite;
    pointer-events:none;
}

@keyframes floatUp {
    0% { transform: translateY(100vh) rotate(0deg); opacity:0; }
    10% { opacity:1; }
    50% { transform: translateY(50vh) rotate(180deg); opacity:0.5; }
    100% { transform: translateY(-20vh) rotate(360deg); opacity:0; }
}

.container{
    max-width:550px;  /* reduced width */
    width:100%;
    background: linear-gradient(145deg,#ffffff,#e6f7ff);
    padding:30px 20px; /* reduced padding */
    border-radius:20px;
    box-shadow:0 20px 50px rgba(0,0,0,0.1), 0 0 30px rgba(241,196,15,0.1);
    transition:0.3s;
    position:relative;
    z-index:1; 
}
.container:hover{
    transform: translateY(-3px);
    box-shadow:0 25px 60px rgba(0,0,0,0.15), 0 0 35px rgba(241,196,15,0.15);
}

h2{
    text-align:center;
    color:#004aad;
    font-size:2rem; 
    margin-bottom:25px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

/* Inputs and textarea with equal left/right spacing */
form input, form textarea, form button, form select{
    width:100%;
    box-sizing:border-box;
    padding:10px 12px; 
    margin:8px 0;       
    border-radius:10px;
    border:1px solid #ccc;
    font-size:0.95rem;  
    transition:0.3s;
}

form input:focus, form textarea:focus, form select:focus{
    border-color:#004aad;
    box-shadow:0 0 12px rgba(0,74,173,0.25);
    outline:none;
}

textarea{resize:none;}

form button{
    border:none;
    background: linear-gradient(45deg,#f1c40f,#ffd347);
    color:#fff;
    font-weight:700;
    font-size:1rem;     
    padding:10px 12px;
    cursor:pointer;
    transition:0.3s;
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
}
form button:hover{
    background: linear-gradient(45deg,#d4ac0d,#ffcc00);
    transform: scale(1.05);
    box-shadow:0 12px 25px rgba(0,0,0,0.25);
}

.message{
    text-align:center;
    font-weight:600;
    margin-bottom:15px;
    color:green;
    font-size:1rem;
}

.message a{
    color:#004aad;
    text-decoration:none;
    font-weight:500;
}

.message a:hover{
    text-decoration:underline;
}

input[type=file]{
    padding:8px;
}

@media(max-width:768px){
    .container{padding:25px 15px;}
    h2{font-size:1.6rem;}
}
</style>
<script>
function validatePGForm(){
    const pg_name=document.getElementById('pg_name').value.trim();
    const city=document.getElementById('city').value.trim();
    const address=document.getElementById('address').value.trim();
    const rent=document.getElementById('rent').value;
    const security=document.getElementById('security_deposit').value;
    const image=document.getElementById('image').value;
    const total_rooms=document.getElementById('total_rooms').value;
    const bed_available=document.getElementById('bed_available').value;
    const sharing_type=document.getElementById('sharing_type').value;
    const furnish_type=document.getElementById('furnish_type').value;
    const gender=document.getElementById('gender').value;

    if(!pg_name||!city||!address||!rent||!security||!image||!total_rooms||!bed_available||!sharing_type||!furnish_type||!gender){
        alert("⚠️ All fields are required!");
        return false;
    }
    if(rent<0||security<0){
        alert("⚠️ Rent and Security Deposit must be positive numbers!");
        return false;
    }
    return true;
}
</script>
</head>
<body>

<!-- Floating PG Icons -->
<i class="fas fa-home floating-icon" style="left:10%; animation-delay:0s;"></i>
<i class="fas fa-home floating-icon" style="left:30%; animation-delay:2s;"></i>
<i class="fas fa-home floating-icon" style="left:50%; animation-delay:4s;"></i>
<i class="fas fa-home floating-icon" style="left:70%; animation-delay:1s;"></i>
<i class="fas fa-home floating-icon" style="left:90%; animation-delay:3s;"></i>

<div class="container">
<h2>Add Your PG Listing</h2>
<?php if($message): ?>
<div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" onsubmit="return validatePGForm();">
    <input type="text" id="pg_name" name="pg_name" placeholder="PG Name">
    <input type="text" id="city" name="city" placeholder="City">
    <textarea id="address" name="address" placeholder="Address" rows="3"></textarea>
    <input type="number" id="rent" name="rent" placeholder="Monthly Rent (₹)" min="0">
    <input type="number" id="security_deposit" name="security_deposit" placeholder="Security Deposit (₹)" min="0">
    <textarea id="amenities" name="amenities" placeholder="Amenities (e.g., WiFi, AC, Meals)" rows="2"></textarea>

    <!-- New fields -->
    <input type="number" id="total_rooms" name="total_rooms" placeholder="Total Rooms" min="1">
    <input type="number" id="bed_available" name="bed_available" placeholder="Beds Available" min="1">
    <select id="sharing_type" name="sharing_type">
        <option value="">Select Sharing Type</option>
        <option value="Sharing">Sharing</option>
        <option value="Non-Sharing">Non-Sharing</option>
    </select>
    <select id="furnish_type" name="furnish_type">
        <option value="">Select Furnishing Type</option>
        <option value="Furnished">Furnished</option>
        <option value="Semi-Furnished">Semi-Furnished</option>
        <option value="Unfurnished">Unfurnished</option>
    </select>
    <select id="gender" name="gender">
        <option value="">Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Any">Any</option>
    </select>

    <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
    <button type="submit">Add PG</button>
</form>

<div class="message"><a href="home.php">← Back to Home</a></div>
</div>
</body>
</html>
