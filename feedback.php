<?php
require_once "includes/auth.php"; // ensures $user is available
require_once "includes/db_connect.php";

$message = "";

// Ensure user is logged in
if (!$user) {
    header("Location: login.php");
    exit;
}

// Handle feedback submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback = trim($_POST['feedback']);
    $rating = intval($_POST['rating']); // get numeric rating

    if ($feedback != "" && $rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id, message, ratings) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $feedback, $rating]);
        $message = "✅ Thank you for your feedback!";
    } else {
        $message = "⚠️ Please provide feedback and select a rating!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Feedback | PG Oasis</title>
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
    align-items:center;
    padding:20px;
}

.container{
    max-width:600px;
    width:100%;
    background: #fff url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=60') center/cover no-repeat;
    background-size: cover;
    position: relative;
    padding:50px 40px;
    border-radius:20px;
    box-shadow:0 20px 40px rgba(0,0,0,0.15);
    transition:0.3s;
    overflow:hidden;
}
.container::before{
    content:"";
    position:absolute;
    top:0; left:0; right:0; bottom:0;
    background: rgba(255,255,255,0.5);
    border-radius:20px;
    z-index:0;
}
.container *{ position: relative; z-index:1; }

.container:hover{
    transform: translateY(-5px);
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
}

h2{
    text-align:center;
    color:#004aad;
    font-size:2.8rem;
    margin-bottom:30px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

form textarea, form select{
    width:100%;
    padding:15px 18px;
    margin:12px 0;
    border-radius:10px;
    border:1px solid #ccc;
    font-size:1.1rem;
    font-family:'Poppins',sans-serif;
    transition:0.3s;
    box-sizing:border-box;
}

form textarea:focus, form select:focus{
    border-color:#004aad;
    box-shadow:0 0 12px rgba(0,74,173,0.2);
    outline:none;
}

form button{
    width:100%;
    padding:15px 18px;
    border:none;
    background: linear-gradient(45deg,#f1c40f,#ffd347);
    color:#fff;
    font-weight:700;
    font-size:1.2rem;
    cursor:pointer;
    transition:0.4s;
    border-radius:10px;
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
    font-size:1.1rem;
}

.message a{
    color:#004aad;
    text-decoration:none;
    font-weight:500;
}

.message a:hover{
    text-decoration:underline;
}

@media(max-width:768px){
    .container{padding:40px 20px;}
    h2{font-size:2.2rem;}
}
</style>
</head>
<body>
<div class="container">
    <h2>Feedback</h2>
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
      <textarea name="feedback" rows="5" placeholder="Share your experience..."></textarea>
      <select name="rating" required>
          <option value="">Select Rating</option>
          <option value="5">⭐⭐⭐⭐⭐ 5</option>
          <option value="4">⭐⭐⭐⭐ 4</option>
          <option value="3">⭐⭐⭐ 3</option>
          <option value="2">⭐⭐ 2</option>
          <option value="1">⭐ 1</option>
      </select>
      <button type="submit">Submit Feedback</button>
    </form>
    <div class="message">
      <a href="home.php">← Back to Home</a>
    </div>
</div>
</body>
</html>
