<?php
include 'db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

  
    $sql = "SELECT password FROM users WHERE user_id = $user_id";
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        $hashed_password = $row['password'];
    } else {
        echo "Error or no results found: " . $conn->error;
    }
    

 
    if (!password_verify($old_password, $hashed_password)) {
        $error_message = "Old password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirm password do not match.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{6,}$/', $new_password)) {
        $error_message = "New password must contain at least one uppercase letter, one lowercase letter, one number, one symbol, and be at least 6 characters long.";
    } else {
      
        $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_hashed_password, $user_id);

        if ($stmt->execute()) {
            $success_message = "Password changed successfully.";
        } else {
            $error_message = "Failed to update the password. Please try again later.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .container {
        background-color: #ffffff;
        padding: 20px 30px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
    }

    h2 {
        margin-bottom: 20px;
        font-size: 24px;
        color: #333333;
        text-align: center;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #555555;
    }

    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #dddddd;
        border-radius: 4px;
        font-size: 14px;
    }

    input:focus {
        border-color: #4a90e2;
        outline: none;
    }

    button {
        width: 100%;
        padding: 10px;
        background-color: #4a90e2;
        color: #ffffff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer;
    }

    button:hover {
        background-color: #4a90e2;
    }

    #error_message {
        color: #e74c3c;
        font-size: 0.9em;
        margin-top: 10px;
    }

    #success_message {
        color: #2ecc71;
        font-size: 0.9em;
        margin-top: 10px;
    }
    </style>
    <script>
    function validateForm() {
        const oldPassword = document.getElementById('old_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const errorContainer = document.getElementById('error_message');

        errorContainer.textContent = '';

        if (oldPassword.trim() === '') {
            errorContainer.textContent = 'Old password is required.';
            return false;
        }

        if (newPassword.trim() === '') {
            errorContainer.textContent = 'New password is required.';
            return false;
        }

        if (confirmPassword.trim() === '') {
            errorContainer.textContent = 'Confirm password is required.';
            return false;
        }

        if (newPassword !== confirmPassword) {
            errorContainer.textContent = 'New password and confirm password do not match.';
            return false;
        }

        const passwordPattern = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{6,}$/;
        if (!passwordPattern.test(newPassword)) {
            errorContainer.textContent =
                'New password must contain at least one uppercase letter, one lowercase letter, one number, one symbol, and be at least 6 characters long.';
            return false;
        }

        return true;
    }
    </script>
</head>

<body>
    <div class="container">
        <h2>Change Password</h2>
        <form action="changepassword.php" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="old_password">Old Password</label>
                <input type="password" name="old_password" id="old_password" placeholder="Enter your old password"
                    required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" placeholder="Enter your new password"
                    required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password"
                    placeholder="Confirm your new password" required>
            </div>
            <button type="submit">Change Password</button>
        </form>
        <div id="error_message">
            <?php if (!empty($error_message)): ?>
            <?php echo $error_message; ?>
            <?php endif; ?>
        </div>
        <div id="success_message">
            <?php if (!empty($success_message)): ?>
            <?php echo $success_message; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>