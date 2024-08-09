<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Calculate total quantity in cart
$total_quantity = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $total_quantity += $quantity;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <title>Mac store</title>
    <style>
        body {
            font-family:"Lato", sans-serif;
        }
        .card {
            width: 18rem; /* Set the width of the card */
            height: 30rem; /* Set the height of the card */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card img {
            object-fit: cover; /* Ensure the image covers the area without distortion */
            height: 18rem; /* Set the height of the image */
        }

        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .justify-content-center {
            display: flex;
            justify-content: center;
        }

        .justify-content-end {
            display: flex;
            justify-content: flex-end;
        }

        .cart-indicator {
            position: relative;
        }

        .cart-indicator .badge {
            position: absolute;
            top: -0.1px;
            right: -5px;
        }

        .card_buttons {
            display: flex;
            justify-content: space-around;
        }

        .nav-link svg {
            vertical-align: middle;
            margin-right: 5px;
        }
        .icon-wrapper {
        position: relative;
        display: inline-block;
        cursor: pointer;
        }

        .icon-tooltip {
            visibility: hidden;
            opacity: 0;
            width: 150px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 5px 0;
            position: absolute;
            z-index: 1;
            top: 125%; /* Position the tooltip above the icon */
            left: 50%;
            margin-left: -75px; /* Center the tooltip */
            transition: opacity 0.3s ease;
            pointer-events: none; /* Prevent the tooltip from being clickable */
        }

        .icon-tooltip::after {
            content: "";
            position: absolute;
            bottom: 100%; /* Bottom of the tooltip */
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #333 transparent transparent transparent;
        }

        .icon-wrapper:hover .icon-tooltip {
            visibility: visible;
            opacity: 1;
        }
        .form-control {
            width: 94%;
        } 
        .page-link {
            color: #72aec8;
        }
        .navbar.sticky-top {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1020; /* Ensure it stays on top */
        }
        .header_margin {
            margin-bottom: 5rem;
        }
        .sale_container {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: auto;
            justify-content: center;
            align-items: center;
        }
        .form-select {
            width: 100% !important;
        }
    </style>
</head>
<body>
    <header class="header_margin">
        <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
            <div class="container">
                <a class="navbar-brand" href="index.php"><img src="images/newlogo.png" class="logo"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul id="navbar" class="navbar-nav text-uppercase justify-content-end align-items-center flex-grow-1 pe-3">
                        <li class="nav-item">
                            <a class="nav-link me-4 active" href="#billboard">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link me-4" href="products.php">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link me-4" href="sale.php">Offers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link me-4" href="contact.html">Contact us</a>
                        </li>
                        <!-- Cart Icon -->
                        <li class="nav-item">
                            <a href="cart.php" class="nav-link position-relative icon-wrapper">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="icon-tooltip">Check the cart</span>
                                <?php if ($total_quantity > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?php echo $total_quantity; ?>
                                </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if ($isLoggedIn): ?>
                            <!-- Profile Icon -->
                            <li class="nav-item">
                                <a href="user.php" class="nav-link icon-wrapper">
                                    <i class="fas fa-user"></i>
                                    <span class="icon-tooltip">Go to profile</span>
                                </a>
                            </li>
                            <!-- Logout Icon -->
                            <li class="nav-item">
                                <a href="logout.php" class="nav-link icon-wrapper">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span class="icon-tooltip">Want to logout?</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <!-- Login Icon -->
                            <li class="nav-item">
                                <a href="login.php" class="nav-link icon-wrapper">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <span class="icon-tooltip">Go to login page</span>
                                </a>
                            </li>
                            <!-- Signup Icon -->
                            <li class="nav-item">
                                <a href="register.php" class="nav-link icon-wrapper">
                                    <i class="fas fa-user-plus"></i>
                                    <span class="icon-tooltip">Go to signup page</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
</body>

</html>
