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
            top: -5px;
            right: -5px;
        }
        
        .card_buttons {
            display: flex;
            justify-content: space-around;
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
                            <a class="nav-link" href="products.php">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.html">Contact</a>
                        </li>
                        <li class="nav-item cart-indicator">
                            <a class="nav-link" href="cart.php">
                                Cart 
                                <?php if ($total_quantity > 0): ?>
                                    <span class="badge bg-danger"><?php echo $total_quantity; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="user.php">Profile</a>
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
