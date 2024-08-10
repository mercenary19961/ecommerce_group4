<?php
session_start();
include 'config/connection.php';

// ------------Hello Admin---------------
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

// Handle creating a new coupon
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    // Retrieve coupon details
    $code = $_POST['code'];
    $discount_id = $_POST['discount_id'];
    $expiry_date = $_POST['expiry_date'];

    // Check if the coupon code already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM coupons WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "<script type='text/javascript'>
        document.addEventListener('DOMContentLoaded', function(event) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Error: Coupon already exists.',
            });
        });
        </script>";
    } else {
        // Insert coupon details into database
        $stmt = $conn->prepare("INSERT INTO coupons (code, discount_id, expiry_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $code, $discount_id, $expiry_date);
    
        if ($stmt->execute()) {
            echo <<<HTML
            <script type='text/javascript'>
            document.addEventListener('DOMContentLoaded', function(event) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Coupon added successfully.',
                }).then(() => {
                    window.location.reload();
                });
            });
            </script>
    HTML;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
// Handle coupon update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $coupon_id = $_POST['coupon_id'];
    $code = $_POST['code'];
    $discount_id = $_POST['discount_id'];
    $expiry_date = $_POST['expiry_date'];

    // Check if the coupon code already exists for another coupon
    $stmt = $conn->prepare("SELECT COUNT(*) FROM coupons WHERE code = ? AND id != ?");
    $stmt->bind_param("si", $code, $coupon_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "<script type='text/javascript'>
        document.addEventListener('DOMContentLoaded', function(event) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Error: Coupon code already exists for another coupon.',
            });
        });
        </script>";
    } else {
        // Update the coupon if the code is unique
        $stmt = $conn->prepare("UPDATE coupons SET code = ?, discount_id = ?, expiry_date = ? WHERE id = ?");
        $stmt->bind_param("sisi", $code, $discount_id, $expiry_date, $coupon_id);

        if ($stmt->execute()) {
            echo <<<HTML
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: 'Coupon updated successfully.'
                }).then(() => {
                    window.location.reload();
                });
            </script>
HTML;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
// Handle coupon deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    if (isset($_POST['coupon_id'])) {
        $coupon_id = $_POST['coupon_id'];

        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param("i", $coupon_id);

        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Coupon deleted successfully.',
                    icon: 'success'
                }).then(() => {
                    window.location.reload();
                });
            </script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: coupon_id not set.";
    }
}

// Fetch and display coupons
$sql = "SELECT * FROM coupons";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>

    <!-- Font Google Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/btinlogout.css" />
    <link rel="stylesheet" href="css/tables.css" />
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- ----------------  font icon -------------- -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<STYLE>
    .button.create {
        border-radius: 20px;
        width: 14%;
        background-color: #007bff;
        color: white;
        margin-left: 22px;
    }

    .button.edit {
        border-radius: 20px;
        width: 14%;
        background-color: #28a745;
        color: white;
    }

    .button.delete {
        border-radius: 20px;
        width: 14%;
        background-color: #dc3545;
        color: white;
    }

    .admin {
        color: #000;
    }
</STYLE>

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
                    <a href="../login.php" style="text-decoration: none;">
                        <button class="btnlogout">LOGOUT
                            <div class="arrow-wrapper">
                                <div class="arrow"></div>
                            </div>
                        </button>
                    </a>
                </span>
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
                <li class="sidebar-list-item"><a href="dashboard.php"><span class="material-icons-outlined">dashboard</span> Dashboard</a></li>
                <li class="sidebar-list-item"><a href="product.php"><span class="material-icons-outlined">inventory_2</span> Products</a></li>
                <li class="sidebar-list-item"><a href="categories.php"><span class="material-icons-outlined">category</span> Categories</a></li>
                <li class="sidebar-list-item"><a href="users.php"><span class="material-icons-outlined">groups</span> Customers</a></li>
                <li class="sidebar-list-item"><a href="discount.php"> <i class="fa-solid fa-colon-sign" style="color: #ffffff;"></i> Discount </a></li>
                <li class="sidebar-list-item"><a href="coupons.php"> <i class="fa-solid fa-percent" style="color: #ffffff;"></i> Coupons </a></li>
            </ul>
        </aside>
        <!-- End Sidebar -->

        <!-- Start Add Coupons -->
        <div class="shadow" id="createForm">
            <!-- Form for creating a new coupon -->
            <form class="form" method="POST" enctype="multipart/form-data">
                <span class="title">Add Coupon</span>

                <div class="input-container">
                    <label style="color: #121212;" for="code">Code</label>
                    <input type="text" name="code" required>
                    <label style="color: #121212;" for="discount_id">Discount </label>
                    <select style="width: 109%; border: 1px solid #ddd; outline: none; padding: 12px 16px; background-color: rgb(247, 243, 243); border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: border-color 0.3s ease, box-shadow 0.3s ease;" name="discount_id" required>
                        <?php
                        $discount_query = "SELECT * FROM discount";
                        $discount_result = $conn->query($discount_query);
                        while ($discount_row = $discount_result->fetch_assoc()) {
                            echo '<option value="' . $discount_row['discount_id'] . '">' . $discount_row['discount_amount'] . " %" . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="input-container">
                    <input type="date" name="expiry_date" required>
                </div>
                <button onclick="run()" style="width: 100%; margin: 0;" type="submit" name="create" class="button create">Add Coupon</button>
                <button type="button" class="button" onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>
        <!-- --------- End Add Coupons -------- -->

        <!-- Start Edit Coupons -->
        <div class="shadow" id="editForm" style="display:none;">
            <!-- Form for updating a coupon -->
            <form class="form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="coupon_id" id="edit_coupon_id">
                <span class="title">Update Coupon</span>
                <div class="input-container">
                    <input type="text" name="code" id="edit_code" placeholder="Coupon Code" required />
                    <label for="code">Coupon Code</label>
                </div>
                <div class="input-container">
                    <label style="color: #121212;" for="discount_id">Discount </label>
                    <select style="width: 109%; border: 1px solid #ddd; outline: none; padding: 12px 16px; background-color: rgb(247, 243, 243); border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: border-color 0.3s ease, box-shadow 0.3s ease;" name="discount_id" id="edit_discount_id" required>
                        <?php
                        $discount_query = "SELECT * FROM discount";
                        $discount_result = $conn->query($discount_query);
                        while ($discount_row = $discount_result->fetch_assoc()) {
                            echo '<option value="' . $discount_row['discount_id'] . '">' . $discount_row['discount_amount'] . " %" . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="input-container">
                    <input style="color: white;" type="date" name="expiry_date" id="edit_expiry_date" placeholder="Expiry Date" required />
                    <label for="expiry_date">Expiry Date</label>
                </div>
                <button style="width: 100%; margin: 0;" type="submit" name="update" class="button create">Update Coupon</button>
                <button type="button" class="button" onclick="toggleForm('editForm')">Close</button>
            </form>
        </div>
        <!-- --------- End Edit Coupons -------- -->

        <!-- Main Content -->
        <main class="main-container">
            <h2 style="color:#666666; text-align:center; font-weight: bold;">Coupons</h2>
            <div style="justify-content: flex-end;" class="main-title">
                <button id="Add_coupon" class="button create" onclick="toggleForm('createForm')">Add Coupon</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Discount ID</th>
                            <th>Expiry Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['code']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['discount_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['expiry_date']) . "</td>";
                            echo "<td>
                                <form method='POST' style='display:inline'>
                                    <input type='hidden' name='coupon_id' value='" . htmlspecialchars($row['id']) . "'>
                                    <button type='button' class='button edit' onclick='editCoupon(" . htmlspecialchars($row['id']) . ", \"" . htmlspecialchars($row['code']) . "\", " . htmlspecialchars($row['discount_id']) . ", \"" . htmlspecialchars($row['expiry_date']) . "\")'>
                                        <i class='fa-solid fa-edit' style='color: #ffffff;'></i>
                                    </button>
                                    <button type='submit' name='delete' class='button delete' aria-label='Delete'>
                                        <i class='fa-solid fa-trash' style='color: #ffffff;'></i>
                                    </button>
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
            const btn = document.getElementById("Add_coupon");
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            btn.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function editCoupon(id, code, discountId, expiryDate) {
            document.getElementById('edit_coupon_id').value = id;
            document.getElementById('edit_code').value = code;
            document.getElementById('edit_discount_id').value = discountId;
            document.getElementById('edit_expiry_date').value = expiryDate;
            toggleForm('editForm');
        }

        function run() {
            location.reload();
        }
    </script>
</body>

</html>