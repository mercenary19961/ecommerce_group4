<?php
session_start();
include 'config/connection.php';

// Generate a unique token for form submission
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Handle creating a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    // Invalidate the token
    unset($_SESSION['form_token']);

    // Check if required fields are set
    if (isset($_POST['name'], $_POST['email'], $_POST['address'], $_POST['phone'], $_POST['password'], $_POST['con_password'])) {
        // Retrieve user details
        $name = $_POST['name'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $con_password = $_POST['con_password'];
        $role = 2;

        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo <<<HTML
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: 'Email already exists.',
                    icon: 'error'
                });
            });
            </script>
HTML;
        } elseif ($password == $con_password) {
            // Hash the password before storing it
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert user details into the database
            $stmt = $conn->prepare("INSERT INTO users (username, email, address, password, phone, role_id) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                die('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $stmt->bind_param("ssssii", $name, $email, $address, $hashed_password, $phone, $role);

            if ($stmt->execute()) {
                echo <<<HTML
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'User registered successfully.'
                    }).then(() => {
                        window.location.href = 'users.php'; // Redirect to the users page
                    });
                });
                </script>
HTML;
            } else {
                echo "Error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        } else {
            echo <<<HTML
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Error!',
                    text: 'Passwords do not match.',
                    icon: 'error'
                });
            });
            </script>
HTML;
        }
    } else {
        echo <<<HTML
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Error!',
                text: 'Required fields are missing.',
                icon: 'error'
            });
        });
        </script>
HTML;
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $user_id = $_POST['user_id'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo <<<HTML
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Deleted!',
                text: 'User deleted successfully.',
                icon: 'success'
            }).then(() => {
                window.location.href = 'users.php'; // Redirect to the users page
            });
        });
        </script>
HTML;
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}

// Fetch and display users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>

    <!-- font google icon  -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/btinlogout.css" />

    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
        }

        .table-container {
            margin: 20px;
        }

        .table-container h2 {
            color: #ffffff;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table-container th,
        .table-container td {
            padding: 10px;
            text-align: left;
        }

        .table-container th {
            background-color: #1f1f1f;
        }

        .table-container td {
            background-color: #2a2a2a;
        }

        .table-container tr:nth-child(even) td {
            background-color: #242424;
        }

        .table-container img {
            max-width: 100px;
        }

        .button {
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;

        }

        .button.create {
            width: 11%;
            background-color: #007bff;
            color: white;
            margin-left : 22px;
        }

        .button.create:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .button.edit {
            width: 50%;
            background-color: #28a745;
            color: white;
        }

        .button.edit:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        .button.delete {
            width: 50%;
            background-color: #dc3545;
            color: white;
        }

        .button.delete:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }

        .shadow {
            position: absolute;
            left: 50%;
            top: 5%;
            transform: translate(-50%, 0);
            display: none;
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
        }

        .form {

            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 380px;
            background-color: #fff;
            border-radius: 15px;
            padding: 30px 78px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;

        }

        .title {
            color: black;
            font-weight: bold;
            text-align: center;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .sub {
            text-align: center;
            color: black;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .sub a {
            color: rgb(23, 111, 211);
        }

        .avatar {
            height: 70px;
            width: 70px;
            background-color: rgb(23, 111, 211);
            background-image: url('uploads/icon.png');
            border-radius: 50%;
            align-self: center;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .input-container {
            position: relative;
        }

        .input-container input,
        .input-container textarea,
        button {
            border: 1px solid #ddd;
            outline: none;
            width: 100%;
            padding: 12px 16px;
            background-color: rgb(247, 243, 243);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-container textarea {
            resize: vertical;
        }

        button {
            margin-top: 10px;
            background-color: rgb(23, 111, 211);
            color: #fff;
            text-transform: uppercase;
            font-weight: bold;
        }

        button:hover {
            background-color: #1a91d0;
        }

        .input-container input:focus,
        .input-container textarea:focus {
            border-color: rgb(23, 111, 211);
            box-shadow: 0 0 5px rgba(23, 111, 211, 0.5);
        }

        #file {
            display: none;
        }

        .color_line {
            color: #121212;
        }
    </style>
</head>

<body>
    <div class="grid-container">
        <!-- Header -->
        <header class="header">
            <div class="menu-icon" onclick="openSidebar()">
                <span class="material-icons-outlined">menu</span>
            </div>
            <div class="header-left">

            </div>
            <div class="header-right">
                <span class="material-icons-outlined">
                    <button class="btnlogout">Logout<div class="arrow-wrapper">
                            <div class="arrow"></div>

                        </div>
                    </button></span>

            </div>
        </header>
        <!-- End Header -->

        <!-- Sidebar -->
        <aside id="sidebar">
            <div class="sidebar-title">
                <div class="sidebar-brand">
                    <span class="material-icons-outlined">shopping_cart</span>MAC STORE
                </div>
                <span class="material-icons-outlined" onclick="closeSidebar()">close</span>
            </div>

            <ul class="sidebar-list">
                <li class="sidebar-list-item">
                    <a href="dashboard.php">
                        <span class="material-icons-outlined">dashboard</span> Dashboard
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="product.php">
                        <span class="material-icons-outlined">inventory_2</span> Products
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="categories.php">
                        <span class="material-icons-outlined">category</span> Categories
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="users.php">
                        <span class="material-icons-outlined">groups</span> Customers
                    </a>
                </li>

            </ul>
        </aside>
        <!-- End Sidebar -->

        <div class="shadow" id="createForm">
            <!-- Form for creating a new product -->
            <form class="form" method="POST" enctype="multipart/form-data">
                <span class="title">Add user</span>


                <div class="input-container">
                    <label class="color_line" for="name"> Name</label>
                    <input type="text" name="name" placeholder=" Name" required />
                </div>
                <div class="input-container">
                    <label class="color_line" for="description">email</label>
                    <input name="email" placeholder="example@gmail.com" required></input>
                </div>
                <div class="input-container">
                    <label class="color_line" for="price">Address</label>
                    <input type="text" name="address" placeholder="address : Amman" required />
                </div>
                <div class="input-container">
                    <label class="color_line" for="price">phone</label>
                    <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" placeholder="07********">
                </div>
                <div class="input-container">
                    <label class="color_line" for="stock">Password</label>
                    <input type="password" name="password" placeholder="Password" required />
                </div>
                <div class="input-container">
                    <label class="color_line" for="category_id">confirm Password</label>
                    <input type="password" name="con_password" placeholder="confirm Password" required />
                </div>

                <button style=" width: 100%;margin-left: 0px;" type="submit" name="create" class="button create">Create User</button>
                <button type="button" class="button" onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>

        <!-- Main Content -->
        <main class="main-container">
            <div class="main-title">
                <button id="Add_product" class="button create" onclick="toggleForm('createForm')">Add User</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>email</th>
                            <th>phone</th>
                            <th>role_id</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['role_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['address']) . "</td>";

                            echo "<td>
        <form method='POST' style='display:inline'>
            <input type='hidden' name='user_id' value='" . htmlspecialchars($row['user_id']) . "'>
            <button type='submit' name='delete' class='button delete'>Delete</button>
        </form>
        <a href='users_update.php?user_id=" . htmlspecialchars($row['user_id']) . "'>
            <button class='button edit'>Edit</button>
        </a>
    </td>";
                            echo "</tr>";
                        }
                        ?>


                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function toggleForm(id) {
            const form = document.getElementById(id);
            const btn = document.getElementById("Add_product");
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            btn.style.display = form.style.display === 'none' ? 'block' : 'none';

        }
    </script>
</body>

</html>