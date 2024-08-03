<?php
session_start();
include 'config/db_connect.php';

function validateInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = validateInput($_POST['email']);
    $password = validateInput($_POST['password']);

    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id, username, password, role_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $hashed_password, $role_id);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Start the session and set session variables
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role_id'] = $role_id;

                // Redirect to the appropriate dashboard
                if ($role_id == 1) {
                    header("Location: admin_page/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }
        $stmt->close();
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styleRegester.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Login</title>
    <style>
        body {
            background: url('images/login_bg.png') no-repeat center center/cover;
            background-repeat: no-repeat;
            background-size: cover;
            color: hsl(0, 0%, 100%);
        }
        .login__register {
            justify-self: flex-end;
            margin-top: 10px;
            color: #f8fafc;
        }
        .signup_btn {
            color: #0f766e !important;
        }
    </style>
</head>
<body>
<div class="login" style="height: 100vh;">
    <form action="login.php" method="POST" class="login__form" onsubmit="return validateForm(event)">
        <h1 class="login__title">Login</h1>

        <div class="login__content">
            <div class="login__box">
                <i class="ri-mail-line login__icon"></i>
                <div class="login__box-input">
                    <input type="email" name="email" class="login__input" id="login-email" placeholder=" " required>
                    <label for="login-email" class="login__label">Email</label>
                </div>
            </div>
            <div id="errorEmail" class="error"></div>

            <div class="login__box">
                <i class="ri-lock-2-line login__icon"></i>
                <div class="login__box-input">
                    <input type="password" name="password" class="login__input" id="login-pass" placeholder=" " required>
                    <label for="login-pass" class="login__label">Password</label>
                    <i class="ri-eye-off-line login__eye" id="login-eye-pass" onclick="togglePassword('login-pass', 'login-eye-pass')"></i>
                </div>
            </div>
            <div id="errorPass" class="error"></div>
        </div>

        <div class="login__check">
            <div class="login__check-group">
                <input type="checkbox" class="login__check-input" id="login-check">
                <label for="login-check" class="login__check-label">Remember me</label>
            </div>

            <a href="#" class="login__forgot">Forgot Password?</a>
        </div>

        <button type="submit" class="login__button">Login</button>
        <button type="button" class="home__button" onclick="location.href='index.html'">Home</button>

        <p class="login__register">
            Don't have an account? <a href="/ecommerce/ecommerce_group4/register.php" class="signup_btn">Sign Up</a>
        </p>
    </form>
</div>

<script>
    function togglePassword(inputId, iconId) {
        var passwordInput = document.getElementById(inputId);
        var icon = document.getElementById(iconId);
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove("ri-eye-off-line");
            icon.classList.add("ri-eye-line");
        } else {
            passwordInput.type = "password";
            icon.classList.remove("ri-eye-line");
            icon.classList.add("ri-eye-off-line");
        }
    }
</script>
</body>
</html>