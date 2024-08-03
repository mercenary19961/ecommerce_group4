<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    // Save current cart to session and redirect to login if user is not logged in
    $_SESSION['redirect_to'] = 'checkout.php?method=' . $_GET['method'];
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$method = isset($_GET['method']) ? $_GET['method'] : '';

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Checkout</title>
</head>
<body>
    <div class="container">
        <h1>Checkout</h1>
        <p>Processing your order...</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const method = "<?php echo $method; ?>";

            if (method === 'cash') {
                fetch('process_cash_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ method: method })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Order Placed Successfully!",
                            text: data.message,
                            icon: "success",
                            customClass: {
                                confirmButton: 'swal-custom-button'
                            }
                        }).then(() => {
                            window.location.href = "index.php"; // Redirect to the index page
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Order Failed",
                            text: data.message,
                            customClass: {
                                confirmButton: 'swal-custom-button'
                            }
                        }).then(() => {
                            window.location.href = "cart.php"; // Redirect to the cart page
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: "error",
                        title: "Order Failed",
                        text: "An error occurred. Please try again.",
                        customClass: {
                            confirmButton: 'swal-custom-button'
                        }
                    });
                });
            } else if (method === 'credit') {
                window.location.href = 'credit_card_payment.php';
            }
        });
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>
