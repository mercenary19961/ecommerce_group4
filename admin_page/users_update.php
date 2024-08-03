<?php
session_start();
include 'config/connection.php';

// Generate a unique token for form submission
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Include SweetAlert2 library
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>';

// Check if user_id is provided in the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user details
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'User not found.',
                icon: 'error'
            }).then(() => {
                window.location.href = 'users.php'; // Redirect to the users page
            });
        </script>";
        exit();
    }
} else {
    echo "<script>
        Swal.fire({
            title: 'Error!',
            text: 'User ID is missing.',
            icon: 'error'
        }).then(() => {
            window.location.href = 'users.php'; // Redirect to the users page
        });
    </script>";
    exit();
}

// Handle updating the user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update']) && isset($_POST['form_token']) && hash_equals($_SESSION['form_token'], $_POST['form_token'])) {
        // Invalidate the token
        unset($_SESSION['form_token']);

        // Retrieve user details
        $name = $_POST['name'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $conpass = $_POST['con_password'];

        if ($password === $conpass) {
            // Check if email already exists (excluding the current user)
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?");
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<script>
                    Swal.fire({
                        title: 'Error!',
                        text: 'Email is already taken.',
                        icon: 'error'
                    });
                </script>";
            } else {
                // Hash the password before storing it if it's not empty
                $hashed_password = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : $user['password'];

                // Update user details in the database
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, address = ?, phone = ?, password = ? WHERE user_id = ?");
                if ($stmt === false) {
                    die("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param("sssssi", $name, $email, $address, $phone, $hashed_password, $user_id);

                if ($stmt->execute()) {
                    echo "<script>
                        Swal.fire({
                            title: 'Success!',
                            text: 'User updated successfully',
                            icon: 'success'
                        }).then(() => {
                            window.location.href = 'users.php'; // Redirect to the users page
                        });
                    </script>";
                } else {
                    echo "<script>
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error updating user: " . $stmt->error . "',
                            icon: 'error'
                        });
                    </script>";
                }
                $stmt->close();
            }
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Passwords do not match.',
                    icon: 'error'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Invalid form submission.',
                icon: 'error'
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update User</title>
    <link rel="stylesheet" href="css/styles.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #263043; /* Dark background */
            color: #1d2634;
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            background: #1d2634; /* Form background */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form h2 {
            margin-bottom: 20px;
            color: #e6e8ed; /* Light text color */
            text-align: center;
        }

        .input-container {
            margin-bottom: 20px;
            position: relative;
        }

        .input-container input,
        .input-container textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            background: #1d2634; /* Input background */
            color: #e6e8ed;
            margin-top: 10px;
        }

        .input-container label {
            position: absolute;
            top: 10px;
            left: 12px;
            font-size: 14px;
            color: #e6e8ed;
            transition: 0.2s ease;
            pointer-events: none;
        }

        .input-container input:focus+label,
        .input-container textarea:focus+label,
        .input-container input:not(:placeholder-shown)+label,
        .input-container textarea:not(:placeholder-shown)+label {
            top: -10px;
            left: 8px;
            font-size: 15px;
            color: #e6e8ed;
        }

        .button {
            display: inline-block;
            background-color: #ff6f61; /* Button color */
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
            text-decoration: none;
            margin: 5px;
        }

        .button:hover {
            background-color: #e55d50; /* Button hover color */
        }

        .button.update {
            background-color: #4caf50; /* Green for update button */
        }

        .button.update:hover {
            background-color: #388e3c; /* Darker green for hover */
        }

        .button.back-to-dashboard {
            background-color: #2196f3; /* Blue for back to dashboard button */
            color: #ffffff;
        }

        .button.back-to-dashboard:hover {
            background-color: #1976d2; /* Darker blue for hover */
        }
    </style>
</head>
<body>
    <div class="form-container">
        <form class="form" method="POST">
            <h2>Update User</h2>
            <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($_SESSION['form_token']); ?>">

            <div class="input-container">
                <input type="text" name="name" value="<?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>" required />
                <label for="name">Full Name</label>
            </div>
            <div class="input-container">
                <input type="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required />
                <label for="email">Email</label>
            </div>
            <div class="input-container">
                <input type="text" name="address" value="<?php echo isset($user['address']) ? htmlspecialchars($user['address']) : ''; ?>" required />
                <label for="address">Address</label>
            </div>
            <div class="input-container">
                <input type="tel" name="phone" value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>" required />
                <label for="phone">Phone</label>
            </div>
            <div class="input-container">
                <input type="password" name="password" />
                <label for="password">Password</label>
            </div>
            <div class="input-container">
                <input type="password" name="con_password" />
                <label for="con_password">Confirm Password</label>
            </div>
            <button type="submit" name="update" class="button update">Update User</button>
            <a href="users.php" class="button back-to-dashboard">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
