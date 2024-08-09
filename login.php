<?php
session_start();
include 'config/db_connect.php';

function validateInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = validateInput($_POST['email']);
    $password = validateInput($_POST['password']);

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
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role_id'] = $role_id;

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

    $_SESSION['errors'] = $errors;
    $conn->close();
    header("Location: login.php");
    exit();
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
    <title>Login</title>
    <style>
      html, body {
        margin: 0;
        padding: 0;
        height: 100%;
      }

      body {
        background: url('images/login_bg.png') no-repeat center center fixed;
        background-size: cover; 
        color: hsl(0, 0%, 100%);
      }

      .container {
        min-height: 100vh; 
      }
      .login_btn {
        color: white !important;
        font-size : large;
        font-weight: 600;
      }
       .login_btn
       {
        font-weight: 600;
       }
       .alert {
        margin-top: 10px;
        background-color : hsla(0, 0%, 10%, 0.1);
        border-color : hsla(0, 0%, 10%, 0.1);
        color: red; 
        padding: 0.75rem 2.25rem; 
        margin-left: auto;
        margin-right: auto;
        width: 80%;
        text-align: center;
        position: relative;
        font-weight: 600;
        font-size : x-large;

      }
      .login__form {
  background-color: hsl(0deg 0% 10% / 66%);
 border: 2px solid hsl(0, 0%, 100%);
  margin-inline: 1.5rem;
  padding: 2.5rem 1.5rem;
  border-radius: 1rem;
  backdrop-filter: blur(7px);
  
}
.login__title
{
   color : white;
}
.home__button{
   font-size : large;
}
    </style>
</head>
<body>
    <div class="login" style="height: 100vh;">
        <form action="login.php" method="POST" class="login__form" onsubmit="return validateForm(event)">
            <h1 class="login__title">Login</h1>

            <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                <div class="alert alert-danger">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <div class="login__content">
    <div class="login__box">
        <i class="ri-mail-line login__icon"></i>
        <div class="login__box-input">
            <input type="email" name="email" class="login__input" id="login-email" placeholder=" ">
            <label for="login-email" class="login__label">Email</label>
        </div>
    </div>
    <div id="errorEmail" class="error"></div>

    <div class="login__box">
        <i class="ri-lock-2-line login__icon"></i>
        <div class="login__box-input">
            <input type="password" name="password" class="login__input" id="login-pass" placeholder=" ">
            <label for="login-pass" class="login__label">Password</label>
        </div>
    </div>
    <div id="errorPass" class="error"></div>

    <button type="submit" class="login__button">Login</button>
    
    <div class="button__container">
        <button type="button" class="home__button" onclick="location.href='index.php'">Home</button>
        <span class="login__register" style = "margin-left : 170px ; font-size : 15px ;">
            Don't have an account? <a href="register.php" class="login_btn">Signup</a>
        </span>
    </div>
</div>


</body>
</html>
