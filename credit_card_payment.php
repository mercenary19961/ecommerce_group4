<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$credit_card_info = [];

// Fetch user's credit card info if it exists
$sql_credit = "SELECT * FROM credit_card WHERE user_id = ?";
$stmt_credit = $conn->prepare($sql_credit);
$stmt_credit->bind_param('i', $user_id);
$stmt_credit->execute();
$result_credit = $stmt_credit->get_result();

if ($result_credit->num_rows > 0) {
    $credit_card_info = $result_credit->fetch_assoc();
} else {
    // Redirect to credit_card-edit.php if no credit card info exists
    header("Location: credit_card-edit.php");
    exit();
}

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
    <title>Credit Card Payment</title>
    <style>
        .payment-container {
            display: flex;
            margin-top: 20px;
        }
        .form-container {
            flex: 1;
            max-width: 50%;
        }
        .image-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-container h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .form-container form {
            background-color: #eef2f3;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container form .form-control {
            margin-bottom: 10px;
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container form button:hover {
            background-color: #0056b3;
        }
        .error-message, .success-message {
            color: red;
            margin-bottom: 10px;
        }
        .success-message {
            color: green;
        }
        .payment-image {
            max-width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <main class="container">
        <div class="payment-container">
            <div class="form-container">
                <h1>Credit Card Payment</h1>
                <form id="payment-form" method="post">
                    <div class="mb-3">
                        <label for="card_number" class="form-label">Card Number</label>
                        <input type="text" class="form-control" id="card_number" name="card_number" value="<?php echo isset($credit_card_info['card_number']) ? $credit_card_info['card_number'] : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="card_name" class="form-label">Card Name</label>
                        <input type="text" class="form-control" id="card_name" name="card_name" value="<?php echo isset($credit_card_info['card_holder_name']) ? $credit_card_info['card_holder_name'] : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="text" class="form-control" id="expiry_date" name="expiry_date" value="<?php echo isset($credit_card_info['expiry_date']) ? $credit_card_info['expiry_date'] : ''; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="cvv" class="form-label">CVV</label>
                        <input type="text" class="form-control" id="cvv" name="cvv" value="<?php echo isset($credit_card_info['cvv']) ? $credit_card_info['cvv'] : ''; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Payment</button>
                    <?php if (!empty($credit_card_info)): ?>
                        <button type="button" class="btn btn-secondary" onclick="fillCreditCardInfo()">Use Saved Card</button>
                    <?php endif; ?>
                </form>
            </div>
            <div class="image-container">
                <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
                <dotlottie-player src="https://lottie.host/bd37a1b7-959a-4278-a639-6a5061809339/FUeTSflzCi.json" background="transparent" speed="0.5" style="width: 600px; height: 600px;" loop autoplay></dotlottie-player>
            </div>
        </div>
    </main>
    <script>
        function fillCreditCardInfo() {
            document.getElementById('card_number').value = '<?php echo $credit_card_info['card_number']; ?>';
            document.getElementById('card_name').value = '<?php echo $credit_card_info['card_holder_name']; ?>';
            document.getElementById('expiry_date').value = '<?php echo $credit_card_info['expiry_date']; ?>';
            document.getElementById('cvv').value = '<?php echo $credit_card_info['cvv']; ?>';
        }

        document.getElementById('payment-form').addEventListener('submit', function(event) {
            event.preventDefault();

            var cardNumber = document.getElementById('card_number').value;
            var cardName = document.getElementById('card_name').value;
            var expiryDate = document.getElementById('expiry_date').value;
            var cvv = document.getElementById('cvv').value;

            // Perform validation
            if (cardNumber !== '<?php echo $credit_card_info['card_number']; ?>' || 
                cardName !== '<?php echo $credit_card_info['card_holder_name']; ?>' || 
                expiryDate !== '<?php echo $credit_card_info['expiry_date']; ?>' || 
                cvv !== '<?php echo $credit_card_info['cvv']; ?>') {
                
                Swal.fire({
                    icon: "error",
                    title: "Validation Error",
                    text: "The entered credit card information does not match the stored data. Please check your inputs.",
                    customClass: {
                        confirmButton: 'swal-custom-button' // Custom class for the OK button
                    }
                });
                return;
            }

            var formData = new FormData(this);

            fetch('process_credit_payment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: "Payment Successful!",
                        text: "Your payment has been processed successfully.",
                        icon: "success",
                        customClass: {
                            confirmButton: 'swal-custom-button' // Custom class for the OK button
                        }
                    }).then(() => {
                        fetch('clear_cart.php', {
                            method: 'POST'
                        }).then(() => {
                            window.location.href = "index.php"; // Redirect to the index page
                        });
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Payment Failed",
                        text: "There was an error processing your payment. Please try again.",
                        customClass: {
                            confirmButton: 'swal-custom-button' // Custom class for the OK button
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: "error",
                    title: "Payment Failed",
                    text: "There was an error processing your payment. Please try again.",
                    customClass: {
                        confirmButton: 'swal-custom-button' // Custom class for the OK button
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>
