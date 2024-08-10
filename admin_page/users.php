<?php
session_start();
include 'config/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /ecommerce_group4-main/login.php");
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

// Handle creating a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    // Check if required fields are set
    if (isset($_POST['name'], $_POST['email'], $_POST['address'], $_POST['phone'], $_POST['password'], $_POST['con_password'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $phone = $_POST['phone'];
        $password = $_POST['password'];
        $con_password = $_POST['con_password'];
        $role = 2; // Default role for new users

        // Check if email already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
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
        } elseif ($password === $con_password) {
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

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
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

// Function to get role name
function roleyname($role_id)
{
    global $conn;

    $sql = "SELECT role_name FROM roles WHERE role_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $role_name = '';
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role_name = $row['role_name'];
    }

    return $role_name;
}




?>
<?php
// Handle updating the user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $con_password = $_POST['con_password'];

    // Check if passwords match
    if ($password !== $con_password) {
        echo "<script>alert('Passwords do not match');</script>";
    } else {
        // Hash the password if it's provided
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE users SET username = ?, email = ?, address = ?, phone = ?, password = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('sssssi', $name, $email, $address, $phone, $hashed_password, $user_id);
        } else {
            $sql_update = "UPDATE users SET username = ?, email = ?, address = ?, phone = ? WHERE user_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('ssssi', $name, $email, $address, $phone, $user_id);
        }

        $stmt_update->execute();
        $stmt_update->close();

        // Redirect to the same page to reflect changes
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>

    <!-- Font Google Icons -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- Montserrat Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/btinlogout.css" />
    <link rel="stylesheet" href="css/tables.css" />
    <!-- Font Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<style>
    .button.create {
        border-radius: 20px;
        width: 14%;
        background-color: #007bff;
        color: white;
        margin-left: 22px;
    }

    .admin {
        color: #000;
    }
</style>

<body>
    <div class="grid-container">
        <!-- Header -->
        <header class="header">
            <div class="menu-icon" onclick="openSidebar()">
                <span class="material-icons-outlined">menu</span>
            </div>
            <div class="header-left">
                <h2 class="admin">Welcome , <?php echo htmlspecialchars($user['username']); ?></h2>
            </div>
            <div class="header-right">
                <span class="material-icons-outlined">
                    <button class="btnlogout">LOGOUT<div class="arrow-wrapper">
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
                <li class="sidebar-list-item"><a href="dashboard.php"><span
                            class="material-icons-outlined">dashboard</span> Dashboard</a></li>
                <li class="sidebar-list-item"><a href="product.php"><span
                            class="material-icons-outlined">inventory_2</span> Products</a></li>
                <li class="sidebar-list-item"><a href="categories.php"><span
                            class="material-icons-outlined">category</span> Categories</a></li>
                <li class="sidebar-list-item"><a href="users.php"><span class="material-icons-outlined">groups</span>
                        Customers</a></li>
                <li class="sidebar-list-item"><a href="discount.php"> <i class="fa-solid fa-colon-sign"
                            style="color: #ffffff;"></i> Discount </a></li>
                <li class="sidebar-list-item"><a href="coupons.php"> <i class="fa-solid fa-percent"
                            style="color: #ffffff;"></i> Coupons </a></li>
            </ul>
        </aside>
        <!-- End Sidebar -->

        <div class="shadow" id="createForm">
            <!-- Form for creating a new user -->
            <form class="form" method="POST">
                <span class="title">Add User</span>

                <div class="input-container">
                    <label class="color_line" for="name">Name</label>
                    <input type="text" name="name" placeholder="Name" required />
                </div>
                <div class="input-container">
                    <label class="color_line" for="email">Email</label>
                    <input type="email" name="email" placeholder="example@gmail.com" required />
                </div>
                <div class="input-container">
                    <label class="color_line" for="address">Address</label>
                    <input type="text" name="address" placeholder="Address" required />
                </div>
                <div class="input-container">
                    <label class="color_line" for="phone">Phone</label>
                    <input type="tel" name="phone" pattern="[0-9]{10}" placeholder="07********" required />
                </div>
                <div class="input-container">
                    <label class="color_line" for="password">Password</label>
                    <input type="password" name="password" placeholder="Password" required />
                </div>
                <div class="input-container">
                    <label class="color_line" for="con_password">Confirm Password</label>
                    <input type="password" name="con_password" placeholder="Confirm Password" required />
                </div>

                <button style="width: 22%;" type="submit" name="create" class="button create">Create User</button>
                <button style=" background-color:#000" type="button" class="button"
                    onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>

        <!-- Start update_popup window  -->
        <!-- Update User Form -->
        <div class="shadow" id="EditForm" style="display: none;">
            <form class="form" method="POST">
                <span class="title">Update User</span>

                <input type="hidden" name="user_id" value="<?php echo isset($user['user_id']) ? htmlspecialchars($user['user_id']) : ''; ?>" />

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
                    <label for="password">Password (Leave blank to keep current)</label>
                </div>
                <div class="input-container">
                    <input type="password" name="con_password" />
                    <label for="con_password">Confirm Password</label>
                </div>

                <button style="width: 22%;" type="submit" name="update" class="button create">Update User</button>
                <button style=" background-color:#000" type="button" class="button" onclick="toggleForm('EditForm')">Close</button>
            </form>
        </div>


        <!-- End  update_popup window -->

        <!-- Main Content -->
        <main class="main-container">
            <h2 style="color:#666666; text-align:center; font-weight: bold;">USERS</h2>
            <div style="justify-content: flex-end;" class="main-title">
                <button id="Add_user" class="button create" onclick="toggleForm('createForm')">Add User</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
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
                            echo "<td>" . htmlspecialchars(roleyname($row['role_id'])) . "</td>";
                            echo "<td>" . htmlspecialchars($row['address']) . "</td>";

                            echo "<td>
        <form method='POST' style='display: inline;'>
            <input type='hidden' name='user_id' value='" . htmlspecialchars($row['user_id']) . "' />
            <button class='button edit' type='button' onclick='editUser(" . htmlspecialchars(json_encode($row)) . ")'> <i class='fa-solid fa-edit' style='color: #ffffff;'></i></button>
            <button class='button delete' type='submit' name='delete'>Delete</button>
        </form>
     
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
            const btn = document.getElementById("Add_user");
            const btnup = document.getElementById("edit_user");
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            btn.style.display = form.style.display === 'none' ? 'block' : 'none';
            btnup.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function toggleForm(formId) {
            var form = document.getElementById(formId);
            form.style.display = form.style.display === "block" ? "none" : "block";
        }

        function editUser(userData) {
            var form = document.getElementById('EditForm');

            form.querySelector('input[name="user_id"]').value = userData.user_id;
            form.querySelector('input[name="name"]').value = userData.username;
            form.querySelector('input[name="email"]').value = userData.email;
            form.querySelector('input[name="address"]').value = userData.address;
            form.querySelector('input[name="phone"]').value = userData.phone;

            toggleForm('EditForm');
        }
    </script>
</body>

</html>