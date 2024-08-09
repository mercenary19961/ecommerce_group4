<?php
session_start(); 

include 'config/db_connect.php';
include 'User_class.php';

$user = new User($conn);
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $user->validateInput($_POST['email']); 
    $username = $user->validateInput($_POST['username']); 
    $phone = $user->validateInput($_POST['phone']); 
    $address = $user->validateInput($_POST['address']);
    $password = $user->validateInput($_POST['password']);
    $confirmPassword = $user->validateInput($_POST['confirm_password']); 

    if ($password === $confirmPassword) {
        if ($user->register($email, $username, $phone, $address, $password, $confirmPassword)) { 
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['errors'] = $user->getErrors(); 
            header("Location: register.php"); 
            exit();
        }
    } else {
        $_SESSION['errors'] = ["Passwords do not match."];
        header("Location: register.php"); 
        exit();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styleRegester.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Registration</title>
    <style>
      html, body {
    margin: 0;
    padding: 0;
    height: 100%;
}
.login__form {
  background-color: hsl(0deg 0% 10% / 66%);
 border: 2px solid hsl(0, 0%, 100%);
  margin-inline: 1.5rem;
  padding: 2.5rem 1.5rem;
  border-radius: 1rem;
  backdrop-filter: blur(7px);
}

body {
    background: url('images/login_bg.png') no-repeat center center fixed;
    background-size: cover; 
    color: hsl(0, 0%, 100%);
}

.container {
    min-height: 100vh; 
}  .login_btn {
        color: white !important;
        font-size : large;
        font-weight: 600;
      }
        .login_btn {
            color: white !important;
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
        <form action="/ecommerce/ecommerce_group4/register.php" method="POST" class="login__form" onsubmit="return validateForm(event)">
            <h1 class="login__title">Sign Up</h1>

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
                        <input type="email" name="email" class="login__input" id="login-email" placeholder=" " >
                        <label for="login-email" class="login__label">Email</label>
                    </div>
                </div>
                <div id="errorEmail" class="error"></div>

                <div class="login__box">
                    <i class="ri-user-3-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="text" name="username" class="login__input" id="reg-userName" placeholder=" " >
                        <label for="reg-userName" class="login__label">UserName</label>
                    </div>
                </div>
                <div id="errorName" class="error"></div>

                <div class="login__box">
                    <i class="ri-phone-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="text" name="phone" class="login__input" id="reg-phone" placeholder=" " >
                        <label for="reg-phone" class="login__label">Phone Number</label>
                    </div>
                </div>
                <div id="errorPhone" class="error"></div>

                <div class="login__box">
                    <i class="ri-home-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="text" name="address" class="login__input" id="reg-address" placeholder=" " >
                        <label for="reg-address" class="login__label">Address</label>
                    </div>
                </div>
                <div id="errorAddress" class="error"></div>

                <div class="login__box">
                    <i class="ri-lock-2-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="password" name="password" class="login__input" id="login-pass" placeholder=" " >
                        <label for="login-pass" class="login__label">Password</label>
                    </div>
                </div>
                <div id="errorPass" class="error"></div>

                <div class="login__box">
                    <i class="ri-lock-2-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="password" name="confirm_password" class="login__input" id="login-confirmPass" placeholder=" " >
                        <label for="login-confirmPass" class="login__label">Confirm Password</label>
                    </div>
                </div>
                <div id="errorConfirm" class="error"></div>
            </div>

            <button type="submit" class="login__button">Register</button>
            <button type="button" class="home__button" onclick="location.href='index.php'">Home</button>

            <span class="login__register" style = "margin-left : 150px ; font-size : 15px ;">
                Already have an account? <a href="/ecommerce/ecommerce_group4/login.php" class="login_btn">Login</a>
            </span>
        </form>
    </div>

    <script>
        function validateEmail() {
            var email = document.getElementById("login-email").value;
            var errorDiv = document.getElementById("errorEmail");
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (regex.test(email)) {
                errorDiv.textContent = "";
                errorDiv.style.display = 'none';
                return true;
            } else {
                errorDiv.textContent = "Please enter a valid email address.";
                errorDiv.style.color = "red";
                errorDiv.style.display = 'block';
                return false;
            }
        }

        function validatePassword() {
            var password = document.getElementById("login-pass").value;
            var errorDiv = document.getElementById("errorPass");
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            if (regex.test(password)) {
                errorDiv.textContent = "";
                errorDiv.style.display = 'none';
                return true;
            } else {
                errorDiv.textContent = "Password must be 8+ chars with 1 uppercase, 1 lowercase, 1 digit, and 1 special character.";
                errorDiv.style.color = "red";
                errorDiv.style.display = 'block';
                return false;
            }
        }

        function confirmPassword() {
            var password0 = document.getElementById("login-pass").value;
            var password1 = document.getElementById("login-confirmPass").value;
            var errorDiv = document.getElementById("errorConfirm");
            if (password0 !== password1) {
                errorDiv.textContent = "Password does not match";
                errorDiv.style.color = "red";
                errorDiv.style.display = 'block';
                return false;
            } else {
                errorDiv.textContent = "";
                errorDiv.style.display = 'none';
                return true;
            }
        }

        function validateUser() {
            var userName = document.getElementById("reg-userName").value;
            var errorDiv = document.getElementById("errorName");
            var regex = /^(\b\w{2,}\b\s*){2}$/;

            if (regex.test(userName)) {
                errorDiv.textContent = "";
                errorDiv.style.display = 'none';
                return true;
            } else {
                errorDiv.textContent = "Please enter exactly 2 names, each word must be at least 2 letters.";
                errorDiv.style.color = "red";
                errorDiv.style.display = 'block';
                return false;
            }
        }

        function validatePhone() {
            var phone = document.getElementById("reg-phone").value;
            var errorDiv = document.getElementById("errorPhone");
            const regex = /^\d{10}$/;
            if (regex.test(phone)) {
                errorDiv.textContent = "";
                errorDiv.style.display = 'none';
                return true;
            } else {
                errorDiv.textContent = "Please enter a valid 10-digit phone number.";
                errorDiv.style.color = "red";
                errorDiv.style.display = 'block';
                return false;
            }
        }

        function validateAddress() {
            var address = document.getElementById("reg-address").value;
            var errorDiv = document.getElementById("errorAddress");
            if (address !== null && address !== "") {
                errorDiv.textContent = "";
                errorDiv.style.display = 'none';
                return true;
            } else {
                errorDiv.textContent = "Please enter your address";
                errorDiv.style.color = "red";
                errorDiv.style.display = 'block';
                return false;
            }
        }

        function validateForm(event) {
            var emailValid = validateEmail();
            var userValid = validateUser();
            var phoneValid = validatePhone();
            var addressValid = validateAddress();
            var passwordValid = validatePassword();
            var confirmValid = confirmPassword();

            if (emailValid && userValid && phoneValid && addressValid && passwordValid && confirmValid) {
                return true;
            } else {
                event.preventDefault(); 
                return false;
            }
        }

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