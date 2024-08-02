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
$sql_user = "SELECT * FROM users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>User Profile</title>
    <style>
        .profile-container {
            display: flex;
        }
        .sidebar {
            width: 200px;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        .content {
            flex: 1;
            padding: 20px;
        }
        .content h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .content p {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #4b5563;
        }
        .sidebar a {
            display: block;
            color: #007bff;
            text-decoration: none;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            text-decoration: underline;
        }
        .user-details {
            background-color: #eef2f3;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .user-details p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container profile-container">
        <div class="sidebar">
            <a href="cart.php">Check Cart</a>
            <a href="purchase_history.php">Purchase History</a>
            <a href="edit_profile.php">Edit Profile</a>
            <a href="add_edit_credit_card.php">Add/Edit Credit Card</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="content">
            <h1>User Profile</h1>
            <div class="user-details">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                <p><strong>Account Creation Date:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
            </div>
        </div>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>
