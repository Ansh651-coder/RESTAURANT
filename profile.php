<?php
session_start();
include "DataBase.php"; // DB connection

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: Login.php");
    exit();
}

$email = $_SESSION['user_email'];

// Fetch user data
$sql = "SELECT * FROM register WHERE email = '$email' LIMIT 1";
$result = mysqli_query($con, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    die("User not found.");
}

$user = mysqli_fetch_assoc($result);

// Ensure phone_number exists
if (!isset($user['phone_number'])) {
    $user['phone_number'] = "";
}

// Handle update form
if (isset($_POST['update_profile'])) {
    $fullName = mysqli_real_escape_string($con, $_POST['fullName']);
    $phone_number = mysqli_real_escape_string($con, $_POST['phone_number']);

    // ✅ Validate phone number (only digits, exactly 10)
    if (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        $error = "Invalid phone number. Please enter a 10-digit number.";
    } else {
        $update = "UPDATE register SET fullName='$fullName', phone_number='$phone_number' WHERE email='$email'";
        if (mysqli_query($con, $update)) {
            $success = "Profile updated successfully!";
            $user['fullName'] = $fullName;
            $user['phone_number'] = $phone_number;
        } else {
            $error = "Error updating profile: " . mysqli_error($con);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Wok N Bowl</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h1 {
            text-align: center;
            color: #e63946;
        }

        form {
            margin-top: 20px;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            margin-top: 20px;
            width: 100%;
            background: #e63946;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #c1121f;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #495057;
        }

        /* ✅ Reduce gap between stacked buttons */
        .stacked-buttons form {
            margin-top: -20px;
        }

        .stacked-buttons form:first-child {
            margin-top: -17px;
            /* normal spacing for the first one */
        }

        .message {
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>My Profile</h1>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="message error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="fullName" value="<?= htmlspecialchars($user['fullName'] ?? '') ?>" required>

            <label>Email (cannot change)</label>
            <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>

            <label>Phone Number</label>
            <input type="text" name="phone_number" maxlength="10" pattern="[0-9]{10}"
                title="Please enter a valid 10-digit phone number"
                value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" required>

            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <!-- ✅ Extra Buttons (stacked with reduced gap) -->
        <div class="stacked-buttons">
            <form method="GET" action="home.php">
                <button type="submit" class="btn-secondary">Go to Home</button>
            </form>

            <form method="POST" action="Logout.php">
                <button type="submit" class="btn-secondary">Log Out</button>
            </form>
        </div>
    </div>
</body>

</html>