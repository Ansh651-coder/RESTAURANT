<?php
ob_start();
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management System - Customer Registration</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        form {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            box-sizing: border-box;
        }

        h1 {
            text-align: center;
            color: #e63946;
            margin-bottom: 1rem;
        }

        .error-message {
            color: #e63946;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            border: 1px solid #e63946;
            border-radius: 5px;
            background-color: #ffeaea;
            font-weight: 500;
        }

        .success-message {
            color: #155724;
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.75rem;
            border: 1px solid #28a745;
            border-radius: 5px;
            background-color: #d4edda;
            font-weight: 500;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }

        .input-container {
            position: relative;
            margin-bottom: 1rem;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #e63946;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .toggle-password:hover {
            color: #e63946;
        }

        button[type="submit"] {
            background-color: #e63946;
            color: white;
            border: none;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-top: 0.5rem;
        }

        button[type="submit"]:hover {
            background-color: #c1121f;
        }

        p {
            text-align: center;
            margin-top: 0.5rem;
            color: #6c757d;
        }

        p a {
            color: #e63946;
            text-decoration: none;
            font-weight: 500;
        }

        p a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            form {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>

<body>

    <form method="POST" onsubmit="return checkPasswordMatch()">
        <h1>Register Your Account</h1>

        <?php if (!empty($GLOBALS['error_message'])): ?>
            <div class="error-message"><?= $GLOBALS['error_message']; ?></div>
        <?php endif; ?>

        <?php if (!empty($GLOBALS['success_message'])): ?>
            <div class="success-message"><?= $GLOBALS['success_message']; ?></div>
        <?php endif; ?>

        <label for="fullName">Full Name</label>
        <input type="text" id="fullName" name="fullName" placeholder="Full Name" required>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Email Address" required>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="Phone No." required maxlength="10">

        <label for="password">Password</label>
        <div class="input-container">
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <button type="button" class="toggle-password" id="togglePassword">Show</button>
        </div>

        <label for="confirmPassword">Confirm Password</label>
        <div class="input-container">
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>
            <button type="button" class="toggle-password" id="toggleConfirmPassword">Show</button>
        </div>

        <button type="submit" name="btnsubmit">Create Account</button>

        <p>Already have an account? <a href="Login.php">Login here</a></p>
    </form>

    <script>
        function checkPasswordMatch() {
            let pass = document.getElementById("password").value;
            let confirmPass = document.getElementById("confirmPassword").value;
            let email = document.getElementById("email").value;
            let phone = document.getElementById("phone").value;

            if (email.indexOf("@") === -1 || email.indexOf(".") === -1) {
                alert("Please enter a valid email address.");
                return false;
            }

            if (phone.length !== 10 || isNaN(phone)) {
                alert("Phone number must be 10 digits.");
                return false;
            }

            // Password validation
            let passwordRegex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
            if (!passwordRegex.test(pass)) {
                alert("Password must be at least 8 characters long and include at least one number and one special character.");
                return false;
            }

            if (pass !== confirmPass) {
                alert("Password doesn't match with confirm password.");
                return false;
            }

            return true;
        }

        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Show' : 'Hide';
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
            const confirmInput = document.getElementById('confirmPassword');
            const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmInput.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Show' : 'Hide';
        });
    </script>

    <?php   
    $servername = "localhost";
    $username = "root";
    $password = "";
    $databasename = "restaurant";

    $con = mysqli_connect($servername, $username, $password, $databasename);

    if (!$con) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    if (isset($_POST['btnsubmit'])) {
        $Fullname = $_POST['fullName'];
        $email = $_POST['email'];
        $phone_number = $_POST['phone'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];

        if ($password !== $confirmPassword) {
            $GLOBALS['error_message'] = "Password and Confirm Password do not match.";
        } else {
            $check_email = "SELECT * FROM register WHERE email = '$email' LIMIT 1";
            $result = mysqli_query($con, $check_email);

            if (mysqli_num_rows($result) > 0) {
                $GLOBALS['error_message'] = "This email is already registered. Please use a different email.";
            } else {
                $_SESSION['pending_user'] = [
                    'fullName' => $Fullname,
                    'email' => $email,
                    'phone_number' => $phone_number,
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ];

                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'your_email@gmail.com';
                    $mail->Password   = 'your_app_password';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('your_email@gmail.com', 'Restaurant System');
                    $mail->addAddress($email, $Fullname);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP Code';
                    $mail->Body    = "<h3>Hello $Fullname,</h3><p>Your OTP is: <b>$otp</b></p>";

                    $mail->send();
                    $GLOBALS['success_message'] = "OTP sent to $email. Please verify.";
                    echo "<script>setTimeout(function(){ window.location='otp_verify.php'; }, 2000);</script>";
                } catch (Exception $e) {
                    $GLOBALS['error_message'] = "Mailer Error: {$mail->ErrorInfo}";
                }
            }
        }
    }
    ?>
</body>
</html>
<?php
ob_end_flush();
?>
