// Basic client-side form validation
function validateRegister() {
  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  const role = document.getElementById("role").value;

  if (!name || !email || !password) {
    alert("Please fill all fields.");
    return false;
  }
  if (!email.match(/^[^ ]+@[^ ]+\.[a-z]{2,3}$/)) {
    alert("Enter a valid email.");
    return false;
  }
  if (password.length < 6) {
    alert("Password must be at least 6 characters.");
    return false;
  }
  return true;
}

function validateLogin() {
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  if (!email || !password) {
    alert("Please enter both email and password.");
    return false;
  }
  return true;
}
