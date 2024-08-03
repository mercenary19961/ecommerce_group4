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

// Initialize the user variable
$user = null;

// Fetch user data on GET request
$sql_user = "SELECT * FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

$validation_error = null;
$success_message = null; // Initialize success message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Validate input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validation_error = "Invalid email format.";
    } elseif (!preg_match("/^[^\s]+ [^\s]+$/", $username)) {
        $validation_error = "Username must consist of two parts separated by a space.";
    } elseif (!preg_match("/^07\d{8}$/", $phone)) {
        $validation_error = "Phone number must be 10 digits long and start with '07'.";
    } elseif (strlen($address) == 0) {
        $validation_error = "Address is required.";
    } else {
        // Prepare and execute update statement
        $sql_update = "UPDATE users SET username = ?, email = ?, phone = ?, address = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('ssssi', $username, $email, $phone, $address, $user_id);
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
            $success_message = "Profile updated successfully!";
        } else {
            $validation_error = "Update failed. Data didn't change.";
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
    <title>Edit Profile</title>
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
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="content">
            <div class="form-container">
                <h1>Edit Profile</h1>
                <div class="update-form">
                    <?php if ($validation_error): ?>
                        <p class="error-message"><?php echo htmlspecialchars($validation_error); ?></p>
                    <?php elseif ($success_message): ?>
                        <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
                    <?php endif; ?>
                    <form method="post" action="">
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" placeholder="Username" required>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email" required>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" placeholder="Phone" required>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" placeholder="Address">
                        <button type="submit">Update</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="picture-container">
            <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
            <dotlottie-player src="https://lottie.host/88018106-0090-4b74-8a72-f344e45bfd43/EsdDlsUIXa.json" background="transparent" speed="1" style="width: 500px; height: 300px; margin-top:10%" loop autoplay></dotlottie-player>
        </div>
    </div>
</body>
</html>
<br>
<br>
<br>
<br>

<?php include 'includes/footer.php'; ?>
