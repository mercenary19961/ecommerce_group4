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
                    title: 'Oops...',
                    text: 'Error: Email already exists.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#6C63FF',
                    iconHtml: '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 48 48" role="img"><path fill="#F44336" d="M21.5 4.5H26.501V43.5H21.5z" transform="rotate(45.001 24 24)"></path><path fill="#F44336" d="M21.5 4.5H26.5V43.501H21.5z" transform="rotate(135.008 24 24)"></path></svg>',
                    customClass: {
                        popup: 'swal-wide',
                        icon: 'custom-icon',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'custom-confirm-button'
                    }
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
                        text: 'User registered successfully.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
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
                    title: 'Oops...',
                    text: 'Passwords do not match.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#6C63FF',
                    iconHtml: '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 48 48" role="img"><path fill="#F44336" d="M21.5 4.5H26.501V43.5H21.5z" transform="rotate(45.001 24 24)"></path><path fill="#F44336" d="M21.5 4.5H26.5V43.501H21.5z" transform="rotate(135.008 24 24)"></path></svg>',
                    customClass: {
                        popup: 'swal-wide',
                        icon: 'custom-icon',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'custom-confirm-button'
                    }
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
                title: 'Oops...',
                text: 'Required fields are missing.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#6C63FF',
                iconHtml: '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 48 48" role="img"><path fill="#F44336" d="M21.5 4.5H26.501V43.5H21.5z" transform="rotate(45.001 24 24)"></path><path fill="#F44336" d="M21.5 4.5H26.5V43.501H21.5z" transform="rotate(135.008 24 24)"></path></svg>',
                customClass: {
                    popup: 'swal-wide',
                    icon: 'custom-icon',
                    title: 'custom-title',
                    text: 'custom-text',
                    confirmButton: 'custom-confirm-button'
                }
            });
        });
        </script>
HTML;
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $user_id = $_POST['user_id'];

    echo <<<HTML
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you really want to delete this user?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_id';
                input.value = '$user_id';

                var deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'confirm_delete';
                deleteInput.value = 'true';

                form.appendChild(input);
                form.appendChild(deleteInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
    </script>
HTML;
}

// Handle confirmed deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
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
                icon: 'success',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
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

// Handle user update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $con_password = $_POST['con_password'];

    // Check if email already exists for a different user
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND user_id != ?");
    $stmt->bind_param("si", $email, $user_id);
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
                title: 'Oops...',
                text: 'Error: Email already exists.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#6C63FF',
                iconHtml: '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 48 48" role="img"><path fill="#F44336" d="M21.5 4.5H26.501V43.5H21.5z" transform="rotate(45.001 24 24)"></path><path fill="#F44336" d="M21.5 4.5H26.5V43.501H21.5z" transform="rotate(135.008 24 24)"></path></svg>',
                customClass: {
                    popup: 'swal-wide',
                    icon: 'custom-icon',
                    title: 'custom-title',
                    text: 'custom-text',
                    confirmButton: 'custom-confirm-button'
                }
            });
        });
        </script>
HTML;
    } else {
        // Check if passwords match
        if ($password !== $con_password) {
            echo <<<HTML
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Oops...',
                    text: 'Passwords do not match.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#6C63FF',
                    iconHtml: '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 48 48" role="img"><path fill="#F44336" d="M21.5 4.5H26.501V43.5H21.5z" transform="rotate(45.001 24 24)"></path><path fill="#F44336" d="M21.5 4.5H26.5V43.501H21.5z" transform="rotate(135.008 24 24)"></path></svg>',
                    customClass: {
                        popup: 'swal-wide',
                        icon: 'custom-icon',
                        title: 'custom-title',
                        text: 'custom-text',
                        confirmButton: 'custom-confirm-button'
                    }
                });
            });
            </script>
HTML;
        } else {
            // Hash the password if provided
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

            if ($stmt_update->execute()) {
                echo <<<HTML
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'User updated successfully.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'users.php'; // Redirect to the users page
                    });
                });
                </script>
HTML;
            } else {
                echo "Error: " . htmlspecialchars($stmt_update->error);
            }
            $stmt_update->close();
        }
    }
}

// Fetch and display users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

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
    .btnlogout {
        --primary-color: #007bff;
        --secondary-color: #fff;
        --hover-color: #10539b;
        --arrow-width: 10px;
        --arrow-stroke: 2px;
        box-sizing: border-box;
        border: 0;
        border-radius: 20px;
        color: var(--secondary-color);
        padding: 1em 1.8em;
        background: var(--primary-color);
        display: flex;
        transition: 0.2s background;
        align-items: center;
        gap: 0.6em;
        font-weight: bold;
        cursor: pointer;
    }

    .Cr-btn {
        font-weight: bold;
        transition: 0.2s background;
        padding: 1em 1.8em;
        border-radius: 20px;
        cursor: pointer;
        width: 14%;
        background-color: #007bff;
        color: white;
        margin-left: 22px;
    }

    .Cr-btn:hover {
        background: #10539b;
    }

    .Ed-btn {
        cursor: pointer;
        font-size: large;
        border: none;
        padding: 1px;
        height: 50%;
        width: 20%;
        background: none;
    }

    .Ed-btn:hover {
        transition: hight 2s;

    }

    .del-btn {
        cursor: pointer;
        font-size: large;
        border: none;
        padding: 1px;
        height: 50%;
        width: 20%;
        background: none;
    }

    .table-container th,
    .table-container td {
        padding-top: 31px;
        text-align: left;
        padding-bottom: 31px;
    }

    .table-container tr:nth-child(even) td {
        background-color: #ffffff;
    }

    .table-container td {
        border-bottom: solid #c3c3c3 1px;

        color: #000;
        background-color: #ffffff;
    }

    /* ------------perfect css -------- */

    /* .button.create {
        border-radius: 20px;
        width: 14%;
        background-color: #007bff;
        color: white;
        margin-left: 22px;
    } */

    .admin {
        color: #000;
    }
    
       * {
            font-family: "Montserrat", sans-serif;

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
                <h2 class="admin">Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>
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
                <span class="title"  style = " font-family:Montserrat, sans-serif;">Add User</span>

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

                <button style="width: 100%; background:#007bff" type="submit" name="create" class="button">Create User</button>
                <button style="border-radius: 5px; color: #000;  margin-left:1px;  background:rgb(247, 243, 243);  width: 100%;" type="button" class="Cr-btn"
                    onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>

        <!-- Start update_popup window  -->
        <!-- Update User Form -->
        <div class="shadow" id="EditForm" style="display: none;">
            <form style="color:#000;" class="form" method="POST">
                <span class="title" >Update User</span>

                <input type="hidden" name="user_id" value="<?php echo isset($user['user_id']) ? htmlspecialchars($user['user_id']) : ''; ?>" />

                <div class="input-container">
                    <label for="name">Full Name</label>
                    <input type="text" name="name" value="<?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>" required />
                </div>
                <div class="input-container">
                    <label for="email">Email</label>
                    <input type="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required />
                </div>
                <div class="input-container">
                    <label for="address">Address</label>
                    <input type="text" name="address" value="<?php echo isset($user['address']) ? htmlspecialchars($user['address']) : ''; ?>" required />
                </div>
                <div class="input-container">
                    <label for="phone">Phone</label>
                    <input type="tel" name="phone" value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>" required />
                </div>
                <div class="input-container">
                    <label for="password">Password (Leave blank to keep current)</label>
                    <input type="password" name="password" />
                </div>
                <div class="input-container">
                    <label for="con_password">Confirm Password</label>
                    <input type="password" name="con_password" />
                </div>

                <button style=" border-radius: 5px; width: 100%; margin:0; " type="submit" name="update" class="Cr-btn">Update User</button>
                <button style=" color: #000;  margin-left:1px;  background: rgb(247, 243, 243); border-radius: 5px; width: 100%;" type="button" class="Cr-btn" onclick="toggleForm('EditForm')">Close</button>
            </form>
        </div>


        <!-- End  update_popup window -->

        <!-- Main Content -->
        <main class="main-container">
            <h2 style="color:#666666; text-align:center; font-weight: bold;">USERS</h2>
            <div style="justify-content: flex-end; padding-right: 1rem;" class="main-title">
                <button id=" Add_user" class="Cr-btn" onclick="toggleForm('createForm')" style = " font-family:Montserrat, sans-serif;">Add User</button>
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
                        // Corrected the if condition
                        while ($row = $result->fetch_assoc()) {
                            if (roleyname($row['role_id']) == 'user') {
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
                <button class='del-btn' type='button' onclick='editUser(" . htmlspecialchars(json_encode($row)) . ")'>
                    <i class='fa-solid fa-pencil' style='color: #48b712; height: 20px;'></i>
                </button>
                <button class='Ed-btn' type='submit' name='delete'>
                    <i class='fa-solid fa-x' style='color: #ed2e0c;'></i>
                </button>
            </form>
        </td>";
                                echo "</tr>";
                            }
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