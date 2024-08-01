<?php
// session_start();
<<<<<<< Updated upstream
include '../config/db_connect.php';

$userLoggedIn = isset($_SESSION['user_id']);
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
=======
>>>>>>> Stashed changes
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<<<<<<< Updated upstream
    <title>My E-commerce Site</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/bootstrap.bundle.min.js"></script>
    <style>
        .cart-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 2px 5px;
            font-size: 12px;
=======
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <title>My E-commerce Site</title>
    <style>
        .card {
            width: 18rem; /* Set the width of the card */
            height: 28rem; /* Set the height of the card */
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
>>>>>>> Stashed changes
        }
    </style>
</head>
<body>
<<<<<<< Updated upstream
    <svg style="display: none;">
        <symbol xmlns="http://www.w3.org/2000/svg" id="cart" viewBox="0 0 16 16">
            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </symbol>
    </svg>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">My E-commerce Site</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <?php if ($userLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profile</a>
                        </li>
                        <li class="nav-item position-relative">
                            <a class="nav-link" href="cart.php">
                                <svg class="bi" width="24" height="24"><use xlink:href="#cart"/></svg>
                                <?php if ($cartCount > 0): ?>
                                    <span class="cart-count"><?php echo $cartCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</body>
</html>
=======
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="user" viewBox="0 0 16 16">
            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
        </symbol>
        <symbol id="cart" viewBox="0 0 16 16">
            <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
        </symbol>
        <symbol id="credit-card" viewBox="0 0 16 16">
            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4zM0 7h16v5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V7zm3 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1H3z" />
        </symbol>
        <symbol id="logout" viewBox="0 0 16 16">
            <path d="M9.5 3a.5.5 0 0 0 0 1h4.793l-2.147 2.146a.5.5 0 0 0 .708.708L15.707 4.5l-2.646-2.647a.5.5 0 0 0-.708.708L14.293 3H9.5zM2.5 2A1.5 1.5 0 0 0 1 3.5v9A1.5 1.5 0 0 0 2.5 14h5a.5.5 0 0 0 0-1h-5A.5.5 0 0 1 2 12.5v-9A.5.5 0 0 1 2.5 3h5a.5.5 0 0 0 0-1h-5z"/>
        </symbol>
    </svg>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="../public/index.php">Mac Store</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../public/products.php">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../public/contact.php">Contact</a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../public/cart.php">
                                    <svg class="bi" width="16" height="16" fill="currentColor">
                                        <use xlink:href="#cart"/>
                                    </svg>
                                    <span class="badge bg-danger" id="cart-count">
                                        <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg class="bi" width="16" height="16" fill="currentColor">
                                        <use xlink:href="#user"/>
                                    </svg>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                    <li>
                                        <a class="dropdown-item" href="../public/profile.php">
                                            <svg class="bi" width="16" height="16" fill="currentColor">
                                                <use xlink:href="#user"/>
                                            </svg>
                                            Edit Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="../public/credit_card.php">
                                            <svg class="bi" width="16" height="16" fill="currentColor">
                                                <use xlink:href="#credit-card"/>
                                            </svg>
                                            Credit Card
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="../public/logout.php">
                                            <svg class="bi" width="16" height="16" fill="currentColor">
                                                <use xlink:href="#logout"/>
                                            </svg>
                                            Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../public/login.php">Login</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

</body>
</html>

>>>>>>> Stashed changes
