<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <title>Mac store</title>
    <style>
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
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php"><img src="images/newlogo.png" class="logo"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link mt-2" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mt-2" href="products.php">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mt-2" href="sale.php">Offers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link me-3 mt-2" href="contact.html">Contact us</a>
                        </li>
                        <li class="nav-item cart-indicator">
                            <a class="nav-link mt-2" href="cart.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                            </svg>
                                <?php if ($total_quantity > 0): ?>
                                    <span class="badge bg-danger"><?php echo $total_quantity; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a class="nav-link mt-2" href="user.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM8 9a5 5 0 0 0-4.546 2.916A7.498 7.498 0 0 0 8 16a7.5 7.5 0 0 0 4.546-4.084A5 5 0 0 0 8 9z"/>
                                    </svg>
                                </a>
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
    </header>
</body>
</html>
