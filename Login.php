<!--<!DOCTYPE html>
<html lang="en">-->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMS - Customer Login</title>
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
            margin-bottom: 1rem;
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
            transform: translateY(-90%);
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

        a.forgot-password {
            display: block;
            text-align: right;
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
            text-decoration: none;
        }

        a.forgot-password:hover {
            color: #e63946;
            text-decoration: underline;
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
            margin-top: 1.5rem;
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
    </style>
</head>

<body>

    <?php
    session_start();

    $servername = "localhost";
    $username = "root";
    $password = "";
    $databasename = "restaurant";

    $conn = new mysqli($servername, $username, $password, $databasename);

    if ($conn->connect_error) {
        die("connection failed: " . $conn->connect_error);
    }

    $error_message = "";
    $success_message = "";

    if (isset($_POST['btnsubmit'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // ✅ Admin Login Condition
        if ($email === "admin" && $password === "123") {
            $_SESSION['admin'] = true;
            header("Location: admin/admin_dashboard.php");
            exit();
        }

        // ✅ User Login Condition
        $stmt = $conn->prepare("SELECT * FROM register WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error_message = "Account not found. Please register first.";
        } else {
            $user = $result->fetch_assoc();

            if (!password_verify($password, $user['password'])) {
                $error_message = "Invalid password. Please try again.";
            } else {
                // ✅ Create session for logged-in user
                $_SESSION['user_id'] = $user['id'];         // assuming your table has `id` as PK
                $_SESSION['user_name'] = $user['fullName']; // adjust column name if different
                $_SESSION['user_email'] = $user['email'];

                $success_message = "Login successful!";
                header("Location: home.php");
                exit();
            }
        }
        $stmt->close();
    }
    $conn->close();
    ?>


    <form action="Login.php" method="POST">
        <h1>Login</h1>

        <!-- ✅ Messages shown directly under Login -->
        <?php if (!empty($error_message)): ?>
            <div class="error-message" id="messageBox"><?= $error_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-message" id="messageBox"><?= $success_message; ?></div>
        <?php endif; ?>

        <label for="email">Email</label>
        <input type="text" id="email" name="email" placeholder="Enter your email">

        <label for="password">Password</label>
        <div class="input-container">
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            <button type="button" class="toggle-password" id="togglePassword">Show</button>
        </div>

        <a href="forgot_password.php" class="forgot-password">Forgot password?</a>

        <button type="submit" name="btnsubmit">Sign In</button>

        <p>New customer? <a href="Register.php">Create account</a></p>
    </form>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Show' : 'Hide';
        });

        // ✅ Auto-hide message after 3 seconds
        const msg = document.getElementById("messageBox");
        if (msg) {
            setTimeout(() => {
                msg.style.display = "none";
            }, 3000);
        }
    </script>
</body>

</html>