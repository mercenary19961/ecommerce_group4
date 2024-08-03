<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$sql_user = "SELECT * FROM credit_card WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

$validation_error = null;
$success_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_number = $_POST['card_number'];
    $card_holder_name = $_POST['card_holder_name'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];

    // Validate input
    if (strlen($card_number) != 16) {
        $validation_error = "Card number must be 16 digits.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $card_holder_name)) {
        $validation_error = "Card holder name should only contain letters and spaces.";
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $expiry_date)) {
        $validation_error = "Expiry date format should be YYYY-MM-DD.";
    } elseif (!preg_match("/^\d{3}$/", $cvv)) {
        $validation_error = "CVV must be 3 digits.";
    } else {
        // Check if the user already has a credit card record
        if ($user) {
            // Prepare and execute update statement
            $sql_update = "UPDATE credit_card SET card_number = ?, card_holder_name = ?, expiry_date = ?, cvv = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('ssssi', $card_number, $card_holder_name, $expiry_date, $cvv, $user_id);
            $stmt_update->execute();

            if ($stmt_update->affected_rows > 0) {
                // Refresh user data after update
                $stmt_update->close();

                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param('i', $user_id);
                $stmt_user->execute();
                $result_user = $stmt_user->get_result();
                $user = $result_user->fetch_assoc();

                // Set success message
                $success_message = "Credit card updated successfully!";
            } else {
                $validation_error = "Update failed. Data didn't change.";
            }
        } else {
            // Prepare and execute insert statement
            $sql_insert = "INSERT INTO credit_card (user_id, card_number, card_holder_name, expiry_date, cvv) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param('issss', $user_id, $card_number, $card_holder_name, $expiry_date, $cvv);
            $stmt_insert->execute();

            if ($stmt_insert->affected_rows > 0) {
                // Refresh user data after insert
                $stmt_insert->close();

                $stmt_user = $conn->prepare($sql_user);
                $stmt_user->bind_param('i', $user_id);
                $stmt_user->execute();
                $result_user = $stmt_user->get_result();
                $user = $result_user->fetch_assoc();

                // Set success message
                $success_message = "Credit card added successfully!";
            } else {
                $validation_error = "Insert failed. Please try again.";
            }
        }
    }
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
    <title>Edit Credit Card</title>
    <style>
        .profile-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            max-width: 1500px;
        }
        .content {
            display: flex;
            padding: 20px;
            max-width: 800px;
            margin-left: 12.5%;
        }
        .form-container h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .update-form {
            background-color: #eef2f3;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .update-form input {
            margin-bottom: 10px;
            padding: 10px;
            width: calc(100% - 22px); /* Adjust for padding and border */
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .update-form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .update-form button:hover {
            background-color: #0056b3;
        }
        .error-message, .success-message {
            color: red;
            margin-bottom: 10px;
        }
        .success-message {
            color: green;
        }
        .picture-container {
            flex: 1;
            margin-left: 20px;
            align-self: center;
        }
        .profile-picture {
            max-width: 150px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .proceed_btn {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="content">
            <div class="form-container">
                <h1>Edit Credit Card</h1>
                <div class="update-form">
                    <?php if ($validation_error): ?>
                        <p class="error-message"><?php echo htmlspecialchars($validation_error); ?></p>
                    <?php elseif ($success_message): ?>
                        <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
                    <?php endif; ?>
                    <form method="post" action="">
                        <input type="text" name="card_number" value="<?php echo htmlspecialchars($user['card_number'] ?? ''); ?>" placeholder="Card Number" required>
                        <input type="text" name="card_holder_name" value="<?php echo htmlspecialchars($user['card_holder_name'] ?? ''); ?>" placeholder="Card Holder Name" required>
                        <input type="text" name="expiry_date" value="<?php echo htmlspecialchars($user['expiry_date'] ?? ''); ?>" placeholder="Expiry Date (YYYY-MM-DD)" required>
                        <input type="text" name="cvv" value="<?php echo htmlspecialchars($user['cvv'] ?? ''); ?>" placeholder="CVV">
                        <button type="submit">Update</button>
                    </form>
                    <?php if (!empty($_SESSION['cart']) && $user): ?>
                        <a href="credit_card_payment.php" class="btn btn-primary proceed_btn">Proceed to Payment</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="picture-container">
            <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
            <dotlottie-player src="https://lottie.host/5d94bd52-8765-435d-8407-8f3f8a2585bf/Hfm0UfUwBy.json" background="transparent" speed="1" style="width: 500px; height: 400px;" loop autoplay></dotlottie-player>
        </div>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>
