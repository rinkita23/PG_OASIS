<?php
require_once "includes/db_connect.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($name && $username && $email && $password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$name, $username, $email, $hashed, $role]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                $message = "Email or username already registered.";
            } else {
                $message = "Error: " . $e->getMessage();
            }
        }
    } else {
        $message = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | PG Oasis</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #d9f0ff, #f0f9ff);
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}

.container {
    max-width:600px;
    width:100%;
    background:#fff;
    padding:50px 40px;
    border-radius:20px;
    box-shadow:0 20px 40px rgba(0,0,0,0.15);
    transition:0.3s;
}
.container:hover{
    transform: translateY(-5px);
    box-shadow:0 25px 50px rgba(0,0,0,0.2);
}

h2{
    text-align:center;
    color:#004aad;
    font-size:2.5rem;
    margin-bottom:20px;
    text-shadow:1px 1px 3px rgba(0,0,0,0.1);
}

form input, form textarea, form select {
    width:100%;
    padding:12px 15px; /* Equal left & right spacing */
    margin:10px 0;
    border-radius:10px;
    border:1px solid #ccc;
    font-size:1rem;
    transition:0.3s;
    box-sizing:border-box;
}

form input:focus, form textarea:focus, form select:focus {
    border-color:#004aad;
    box-shadow:0 0 12px rgba(0,74,173,0.2);
    outline:none;
}

textarea{ resize:none; }

form button {
    width:100%;
    padding:15px;
    border:none;
    border-radius:10px;
    background: linear-gradient(45deg,#f1c40f,#ffd347);
    color:#fff;
    font-weight:700;
    font-size:1.2rem;
    cursor:pointer;
    transition:0.4s;
    box-shadow:0 8px 20px rgba(0,0,0,0.15);
    margin-top:10px;
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
    h2{font-size:2rem;}
}
</style>
<script>
function validateRegister() {
    const name = document.getElementById('name').value.trim();
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;

    if(!name || !username || !email || !password || !confirm){
        alert("⚠️ All fields are required!");
        return false;
    }
    if(password !== confirm){
        alert("⚠️ Passwords do not match!");
        return false;
    }
    return true;
}
</script>
</head>
<body>
<div class="container">
<h2>Create Your Account</h2>
<?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<form method="POST" onsubmit="return validateRegister();">
    <input type="text" id="name" name="name" placeholder="Full Name" required>
    <input type="text" id="username" name="username" placeholder="Username" required>
    <input type="email" id="email" name="email" placeholder="Email" required>
    <input type="password" id="password" name="password" placeholder="Password" required>
    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
    <select name="role" id="role">
        <option value="user">User</option>
        <option value="owner">Owner</option>
    </select>
    <button type="submit">Register</button>
</form>
<div class="message">
    Already have an account? <a href="login.php">Login here</a>
</div>
</div>
</body>
</html>
