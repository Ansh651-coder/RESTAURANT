<?php
ob_start();
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$success_message = "";
$error_message = "";

// Database connection
$con = mysqli_connect("localhost", "root", "", "restaurant");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// OTP verification
if (isset($_POST['verifyOtp'])) {
    $enteredOtp = $_POST['otp'];

    if ($enteredOtp == $_SESSION['otp']) {
        $user = $_SESSION['pending_user'];
        $fullname = $user['fullName'];
        $email = $user['email'];
        $phone = $user['phone_number'];
        $passwordHash = $user['password'];

        $query = "INSERT INTO register (fullName, email, phone_number, password) 
              VALUES ('$fullname', '$email', '$phone', '$passwordHash')";

        if (mysqli_query($con, $query)) {
            $new_user_id = mysqli_insert_id($con);

            // ✅ Auto login after successful OTP verification
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['user_name'] = $fullname;
            $_SESSION['user_email'] = $email;

            // ✅ Cleanup
            unset($_SESSION['otp']);
            unset($_SESSION['pending_user']);

            // ✅ Redirect directly to home.php
            header("Location: home.php");
            exit();
        } else {
            $error_message = "Database error: " . mysqli_error($con);
        }
    }

}

// Resend OTP
if (isset($_POST['resendOtp'])) {
    if (isset($_SESSION['pending_user'])) {
        $user = $_SESSION['pending_user'];
        $email = $user['email'];
        $fullname = $user['fullName'];

        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;

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
            $mail->addAddress($email, $fullname);

            $mail->isHTML(true);
            $mail->Subject = 'Your New OTP Code';
            $mail->Body = "<h3>Hello $fullname,</h3><p>Your new OTP is: <b>$otp</b></p>";

            $mail->send();
            $success_message = "A new OTP has been sent to your email.";
        } catch (Exception $e) {
            $error_message = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error_message = "Session expired. Please register again.";
        echo "<script>setTimeout(function(){ window.location='Register.php'; }, 2000);</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management System - OTP Verification</title>
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

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
            margin-bottom: 1rem;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #e63946;
        }

        button {
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

        button:hover {
            background-color: #c1121f;
        }

        .resend-btn {
            background-color: #6c757d;
            margin-top: 0.5rem;
        }

        .resend-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <form method="POST">
        <h1>OTP Verification</h1>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= $error_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?= $success_message; ?></div>
        <?php endif; ?>

        <label for="otp">Enter OTP</label>
        <input type="text" id="otp" name="otp" placeholder="Enter OTP" required maxlength="6">

        <button type="submit" name="verifyOtp">Verify OTP</button>
        <button type="submit" name="resendOtp" class="resend-btn">Resend OTP</button>
    </form>

    <script>
        document.getElementById("otp").focus();
    </script>
</body>

</html>
<?php
ob_end_flush();
?>