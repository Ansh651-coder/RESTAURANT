<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "restaurant");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

$error_message = "";
$success_message = "";

if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: forgot_password.php");
    exit;
}

if (isset($_POST['resetPassword'])) {
    $newPass = $_POST['password'];
    $confirmPass = $_POST['confirmPassword'];

    // Backend validation
    $passwordRegex = "/^(?=.*[0-9])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/";
    if (!preg_match($passwordRegex, $newPass)) {
        $error_message = "Password must be at least 8 characters long and include at least one number and one special character.";
    } elseif ($newPass !== $confirmPass) {
        $error_message = "Passwords do not match!";
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];

        $query = "UPDATE register SET password = '$hashed' WHERE email = '$email'";
        if (mysqli_query($con, $query)) {
            unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['otp_verified']);
            $success_message = "Password updated successfully!";
            echo "<script>setTimeout(()=>{ window.location='Login.php'; },2000);</script>";
        } else {
            $error_message = "Database error: " . mysqli_error($con);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        form {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            width: 350px;
        }

        h2 {
            text-align: center;
            color: #e63946;
            margin-bottom: 20px;
        }

        .input-group {
            position: relative;
            margin-bottom: 10px;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
            margin-bottom: 1rem;
            box-sizing: border-box;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-100%);
            cursor: pointer;
            font-size: 14px;
            color: #555;
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #e63946;
            color: white;
            cursor: pointer;
            margin-top: 10px;
        }

        .message {
            text-align: center;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .error {
            background: #ffeaea;
            color: #e63946;
            border: 1px solid #e63946;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #28a745;
        }
    </style>
    <script>
        function validateResetForm() {
            let pass = document.getElementById("password").value;
            let confirmPass = document.getElementById("confirmPassword").value;

            let passwordRegex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
            if (!passwordRegex.test(pass)) {
                alert("Password must be at least 8 characters long and include at least one number and one special character.");
                return false;
            }

            if (pass !== confirmPass) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }

        function togglePassword(id, toggleId) {
            let input = document.getElementById(id);
            let toggle = document.getElementById(toggleId);
            if (input.type === "password") {
                input.type = "text";
                toggle.innerText = "Hide";
            } else {
                input.type = "password";
                toggle.innerText = "Show";
            }
        }
    </script>
</head>

<body>
    <form method="POST" onsubmit="return validateResetForm()">
        <h2>Reset Password</h2>

        <?php if (!empty($error_message))
            echo "<div class='message error'>$error_message</div>"; ?>
        <?php if (!empty($success_message))
            echo "<div class='message success'>$success_message</div>"; ?>

        <div class="input-group">
            <input type="password" id="password" name="password" placeholder="Enter new password" required>
            <span class="toggle-password" id="togglePassword1"
                onclick="togglePassword('password','togglePassword1')">Show</span>
        </div>

        <div class="input-group">
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password"
                required>
            <span class="toggle-password" id="togglePassword2"
                onclick="togglePassword('confirmPassword','togglePassword2')">Show</span>
        </div>

        <button type="submit" name="resetPassword">Update Password</button>

        <a href="Login.php">
            <button type="button" style="
        width:100%;
        padding:10px;
        border:none;
        border-radius:5px;
        background:#6c757d;
        color:white;
        cursor:pointer;
        margin-top:10px;">
                Go to Login
            </button>
        </a>
    </form>
</body>

</html>