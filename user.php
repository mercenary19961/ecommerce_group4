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
            margin-top: 20px;
        }
        .sidebar {
            width: 200px;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 20px;
        }
        .content {
            flex: 1;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            max-width: 500px;
        }
        .content h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .content p {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #333;
        }
        .sidebar a {
            display: block;
            color: #72AEC8;
            text-decoration: none;
            margin-bottom: 10px;
            font-size: 1.1rem;
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
        .user-details p strong {
            color: #000;
        }
        .profile-image-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .profile-image-container img {
            max-width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="container profile-container">
        <div class="sidebar">
            <a href="cart.php">Check Cart</a>
            <a href="purchase_history.php">Purchase History</a>
            <a href="edit_profile.php">Edit Profile</a>
            <a href="credit_card-edit.php">Edit Credit Card</a>
            <a href="logout.php" style="color: #ef4444;">Logout</a>
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
        <div class="profile-image-container">
            <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script> 
            <dotlottie-player src="https://lottie.host/2238dea2-61f3-4368-a11d-071ae3d931a2/rSV0ZJztPy.json" background="transparent" speed="1" style="width: 500px; height: 500px;" loop autoplay></dotlottie-player>
        </div>
    </div>
</body>
</html>
<br>
<?php include 'includes/footer.php'; ?>
