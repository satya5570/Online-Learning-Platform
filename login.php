<?php

require_once 'vendor/autoload.php';
include 'config.php';
include 'db.php';
session_start();


$clientID = $cid;
$clientSecret =$csc;
$redirectUri = 'http://localhost/OnlineLearningPlatform/dashboard.php';


$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");



$error_message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT user_id, password, role, name FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['user_name'] = $row['name'];

            if ($row['role'] == 'creator') {
                header("Location: create_course.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "No user found with this email.";
    }

    $result->free();  // Free the result set
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="CSS/login.css">
    <script>
    function validateForm() {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const errorContainer = document.getElementById('error_message');

        errorContainer.textContent = '';

        if (email.trim() === '') {
            errorContainer.textContent = 'Email is required.';
            return false;
        }

        if (password.trim() === '') {
            errorContainer.textContent = 'Password is required.';
            return false;
        }

        return true;
    }
    </script>
</head>

<body>
    <div class="container">
        <h2>Login</h2>
        <form action="login.php" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <img src="imgs/profile-user.png"
                    style="background-size:contain;background-postion:center;width:30px; height:30px" alt="">
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <img src="imgs/passowrd.png"
                    style="background-size:contain;background-postion:center;width:30px; height:30px" alt="">
                <input type="password" name="password" id="password" placeholder="Enter your password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div id="error_message" style="color: red; font-size: 0.9em;">
            <?php if (!empty($error_message)): ?>
            <?php echo $error_message; ?>
            <?php endif; ?>
        </div>
        <div style="text-align:center; margin-top: 10px;">


            <?php echo "<a href='" . $client->createAuthUrl() . "'>Google Login</a>"; ?>
        </div>

    </div>
</body>

</html>