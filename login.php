<?php
require_once "includes/db_connect.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $session_id = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
            $insert = $pdo->prepare("INSERT INTO sessions (session_id, user_id, expires_at) VALUES (?, ?, ?)");
            $insert->execute([$session_id, $user['id'], $expiry]);
            setcookie("pg_session", $session_id, time()+60*60*24*7, "/", "", false, true);

            header("Location: home.php");
            exit;
        } else $message = "Invalid username or password.";
    } else $message = "Please enter username and password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login | PG Oasis</title>
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
    max-width:500px;
    width:100%;
    background: #fff url('https://images.unsplash.com/photo-1611078486431-0c1b40831487?auto=format&fit=crop&w=800&q=60') center/cover no-repeat;
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
    background: rgba(255,255,255,0.85); /* low opacity overlay */
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
    margin-bottom:10px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
}

.tagline{
    text-align:center;
    font-size:1.2rem;
    margin-bottom:25px;
    font-weight:600;
    color:#004aad;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    background: linear-gradient(to right, #f1c40f, #ffd347);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

form input, form button{
    width:100%;
    padding:15px 18px;
    margin:12px 0;
    border-radius:10px;
    border:1px solid #ccc;
    font-size:1.1rem;
    transition:0.3s;
    display:block;
    box-sizing:border-box;
}

form input:focus{
    border-color:#004aad;
    box-shadow:0 0 12px rgba(0,74,173,0.2);
    outline:none;
}

form button{
    border:none;
    background: linear-gradient(45deg,#f1c40f,#ffd347);
    color:#fff;
    font-weight:700;
    font-size:1.2rem;
    cursor:pointer;
    transition:0.4s;
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
    .tagline{font-size:1rem;}
}
</style>
<script>
function validateLogin(){
    const username=document.getElementById('username').value.trim();
    const password=document.getElementById('password').value.trim();
    if(!username||!password){
        alert("⚠️ Please enter username and password!");
        return false;
    }
    return true;
}
</script>
</head>
<body>
<div class="container">
<h2>Login</h2>
<div class="tagline">Access your account and manage your PG listings effortlessly</div>

<?php if (isset($_GET['registered'])): ?>
<div class="message">Registration successful! Please log in.</div>
<?php endif; ?>

<?php if ($message): ?>
<div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" onsubmit="return validateLogin();">
    <input type="text" id="username" name="username" placeholder="Username">
    <input type="password" id="password" name="password" placeholder="Password">
    <button type="submit">Login</button>
</form>

<div class="message">
    Don’t have an account? <a href="register.php">Register</a>
</div>
</div>
</body>
</html>
