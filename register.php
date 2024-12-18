<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; 

    $password_error = '';
    $email_error = '';

   
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $email_error = 'Only Gmail addresses are allowed.';
    }

 
    if (strlen($password) < 6) {
        $password_error = 'Password must be at least 6 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $password_error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $password_error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $password_error = 'Password must contain at least one number.';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $password_error = 'Password must contain at least one special character.';
    } elseif ($password !== $confirm_password) {
        $password_error = 'Passwords do not match.';
    }

    if (empty($password_error) && empty($email_error)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (name, email, password, role) 
        VALUES ('$name', '$email', '$hashed_password', '$role')";

if ($conn->query($sql) === TRUE) {
    header("Location: login.php");
    exit;
} else {
    echo "Error: " . $conn->error;
}

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="CSS/register.css">
    <script>
    function validateForm() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        const emailError = document.getElementById('email_error');
        const passwordError = document.getElementById('password_error');

        emailError.textContent = '';
        passwordError.textContent = '';


        const emailPattern = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
        if (!emailPattern.test(email)) {
            emailError.textContent = 'Only Gmail addresses are allowed.';
            return false;
        }

        if (password.length < 6) {
            passwordError.textContent = 'Password must be at least 6 characters long.';
            return false;
        }

        if (!/[A-Z]/.test(password)) {
            passwordError.textContent = 'Password must contain at least one uppercase letter.';
            return false;
        }

        if (!/[a-z]/.test(password)) {
            passwordError.textContent = 'Password must contain at least one lowercase letter.';
            return false;
        }

        if (!/[0-9]/.test(password)) {
            passwordError.textContent = 'Password must contain at least one number.';
            return false;
        }

        if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            passwordError.textContent = 'Password must contain at least one special character.';
            return false;
        }

        if (password !== confirmPassword) {
            passwordError.textContent = 'Passwords do not match.';
            return false;
        }

        return true;
    }
    </script>
</head>

<body>
    <div class="container">
        <h2>Create an Account</h2>
        <form action="register.php" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" placeholder="Enter your full name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
            </div>
            <div id="email_error" style="color: red; font-size: 0.9em;"></div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Create a password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password"
                    required>
            </div>
            <div id="password_error" style="color: red; font-size: 0.9em;"></div>

            <div class="form-group">
                <label for="role">Select Role</label>
                <select name="role" id="role" required>
                    <option value="learner">Learner</option>
                    <option value="creator">Course Creator</option>
                </select>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>