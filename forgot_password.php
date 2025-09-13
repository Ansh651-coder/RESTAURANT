<?php
ob_start();
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ✅ Reset session if page is freshly opened (not submitted yet)
if (isset($_POST['backToEmail'])) {
    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_otp']);
    unset($_SESSION['otp_verified']);
}

$con = mysqli_connect("localhost", "root", "", "restaurant");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

$con = mysqli_connect("localhost", "root", "", "restaurant");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

$error_message = "";
$success_message = "";

// Step 1: Send OTP
if (isset($_POST['sendOtp'])) {
    $email = $_POST['email'];

    $query = "SELECT * FROM register WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) == 0) {
        $error_message = "This email is not registered.";
    } else {
        $otp = rand(100000, 999999);
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '23bmiit155@gmail.coom';
            $mail->Password = 'vwgl rrft tpxf tawc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('23bmiit155@gmail.com', 'Restaurant System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "<h3>Hello,</h3><p>Your OTP for resetting password is: <b>$otp</b></p>";

            $mail->send();
            $success_message = "OTP sent to $email. Please verify.";
        } catch (Exception $e) {
            $error_message = "Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

// Step 2: Verify OTP
if (isset($_POST['verifyOtp'])) {
    $enteredOtp = $_POST['otp'];

    if ($enteredOtp == $_SESSION['reset_otp']) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
        exit;
    } else {
        $error_message = "Invalid OTP. Please try again.";
    }
}

// Step 3: Resend OTP
if (isset($_POST['resendOtp'])) {
    if (isset($_SESSION['reset_email'])) {
        $email = $_SESSION['reset_email'];
        $otp = rand(100000, 999999);
        $_SESSION['reset_otp'] = $otp;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '23bmiit155@gmail.com';
            $mail->Password = 'vwgl rrft tpxf tawc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('23bmiit155@gmail.com', 'Restaurant System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Resent Password Reset OTP';
            $mail->Body = "<h3>Hello,</h3><p>Your new OTP is: <b>$otp</b></p>";

            $mail->send();
            $success_message = "New OTP sent to $email.";
        } catch (Exception $e) {
            $error_message = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error_message = "Session expired. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
            margin-bottom: 1rem;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
        }

        .send {
            background: #e63946;
            color: white;
        }

        .resend {
            background: #6c757d;
            color: white;
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
</head>

<body>
    <form method="POST">
        <h2>Forgot Password</h2>

        <?php if (!empty($error_message))
            echo "<div class='message error'>$error_message</div>"; ?>
        <?php if (!empty($success_message))
            echo "<div class='message success'>$success_message</div>"; ?>

        <?php if (!isset($_SESSION['reset_email'])): ?>
            <input type="email" name="email" placeholder="Enter your registered email" required>
            <button type="submit" name="sendOtp" class="send">Send OTP</button>
        <?php else: ?>
            <input type="text" name="otp" placeholder="Enter OTP" maxlength="6" required>
            <button type="submit" name="verifyOtp" class="send">Verify OTP</button>
            <button type="submit" name="resendOtp" class="resend">Resend OTP</button>
            <button type="submit" name="backToEmail" class="resend">Back to Email</button>

        <?php endif; ?>
    </form>
</body>

</html>
<?php ob_end_flush(); ?>
