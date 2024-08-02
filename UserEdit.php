<?php

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "e-commerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["user_id"]) || empty($_GET["user_id"])) {
        header("Location: index.php");
        exit;
    }

    $user_id = intval($_GET["user_id"]);
    
    $sql = "SELECT * FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        header("Location: index.php");
        exit;
    }

    $fname = $row["fname"];
    $email = $row["email"];
    $address = $row["address"];
    $password = $row["password"];
    $phone = $row["phone"];
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = intval($_POST["user_id"]);
    $fname = $conn->real_escape_string($_POST["fname"]);
    $email = $conn->real_escape_string($_POST["email"]);
    $address = $conn->real_escape_string($_POST["address"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $phone = $conn->real_escape_string($_POST["phone"]);
    
    if (empty($fname) || empty($email) || empty($address) || empty($password) || empty($confirm_password) || empty($phone)) {
        $errorMessage = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $errorMessage = "Passwords do not match";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE user SET fname = ?, email = ?, address = ?, password = ?, phone = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssi', $fname, $email, $address, $hashed_password, $phone, $user_id);

        if ($stmt->execute()) {
            $successMessage = "Client updated correctly";
            header("Location: index.php");
            exit;
        } else {
            $errorMessage = "Invalid query: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title>Edit User</title>
    <style>
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .form-label {
            font-weight: bold;
        }
        .form-control {
            background-color: #f9f9f9;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-extra-lg {
            padding: 10px 20px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
<div class="container my-5">
    <h2 class="text-center mb-4">Edit User</h2>

    <?php
    if (!empty($errorMessage)) {
        echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <strong>$errorMessage</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
    ?>

    <?php
    if (!empty($successMessage)) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <strong>$successMessage</strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
    ?>

    <form method="post">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">First Name</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="fname" value="<?php echo htmlspecialchars($fname); ?>">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Email</label>
            <div class="col-sm-9">
                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Address</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($address); ?>">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Password</label>
            <div class="col-sm-9">
                <input type="password" class="form-control" name="password">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Confirm Password</label>
            <div class="col-sm-9">
                <input type="password" class="form-control" name="confirm_password">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Phone</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col d-flex justify-content-center">
                <input type="submit" class="btn btn-primary btn-extra-lg mx-2" value="Submit">
                <a href="index.php" class="btn btn-secondary btn-extra-lg mx-2" role="button">Cancel</a>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
