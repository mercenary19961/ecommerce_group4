<?php
session_start();
include 'config/connection.php';

// ------------Hello Admin---------------
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
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
$alertMessage = '';
$alertType = '';
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
        $alertMessage = 'Coupon code already exists.';
        $alertType = 'error';
    } else {
        // Insert coupon details into the database
        $stmt = $conn->prepare("INSERT INTO coupons (code, discount_id, expiry_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $code, $discount_id, $expiry_date);

        if ($stmt->execute()) {
            $alertMessage = 'Coupon added successfully.';
            $alertType = 'success';
        } else {
            $alertMessage = 'Error: ' . $stmt->error;
            $alertType = 'error';
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

    // Check if the new coupon code already exists in another record
    $stmt = $conn->prepare("SELECT COUNT(*) FROM coupons WHERE code = ? AND id != ?");
    $stmt->bind_param("si", $code, $coupon_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $alertMessage = 'Coupon code already exists.';
        $alertType = 'error';
    } else {
        // Update coupon details in the database
        $stmt = $conn->prepare("UPDATE coupons SET code = ?, discount_id = ?, expiry_date = ? WHERE id = ?");
        $stmt->bind_param("sisi", $code, $discount_id, $expiry_date, $coupon_id);

        if ($stmt->execute()) {
            $alertMessage = 'Coupon updated successfully.';
            $alertType = 'success';
        } else {
            $alertMessage = 'Error: ' . $stmt->error;
            $alertType = 'error';
        }
        $stmt->close();
    }
}

// Handle coupon deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    if (isset($_POST['coupon_id'])) {
        $coupon_id = $_POST['coupon_id'];

        $stmt = $conn->prepare("DELETE FROM coupons WHERE id = ?");
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("i", $coupon_id);

        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Coupon deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    window.location.href = 'coupons.php'; // Redirect after deletion
                });
            </script>";
        } else {
            echo "Error: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        echo "Error: coupon_id not set.";
    }
}

// Fetch and display coupons
$sql = "SELECT * FROM coupons";
$result = $conn->query($sql);




function Discount_amount($role_id)
{
    global $conn;

    $sql = "SELECT discount_amount	FROM discount WHERE discount_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $role_name = '';
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role_name = $row['discount_amount'];
    }
    //
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/btinlogout.css" />
    <link rel="stylesheet" href="css/tables.css" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- ----------------  font icon -------------- -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" crossorigin="anonymous" />

    <style>
        .btn-wide {
            width: 100%;
            padding: 10px;
        }


        /* perfect style  */


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

        .table-container td {
            padding-right: 2%;
            border-bottom: solid #c3c3c3 1px;

            color: #000;
            background-color: #ffffff;
        }

        .table-container th,
        .table-container td {
            padding-top: 29px;
            text-align: left;
            padding-bottom: 10px;
        }

        .table-container tr:nth-child(even) td {
            background-color: #ffffff;
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
            border-radius: 20px;
            width: 14%;
            background-color: #007bff;
            color: white;
            margin-left: 22px;
        }

        .button.create:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .button.edit {
            width: 70%;
            background-color: #28a745;
            color: white;
        }

        .button.edit:hover {
            background-color: #218838;
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
            margin-left: 10px;
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
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-container textarea {
            resize: vertical;
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

        .admin {
            color: #000;
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
                <li class="sidebar-list-item"><a href="discount.php"><i class="fa-solid fa-colon-sign" style="color: #ffffff;"></i> Discount</a></li>
                <li class="sidebar-list-item"><a href="coupons.php"><i class="fa-solid fa-percent" style="color: #ffffff;"></i> Coupons</a></li>
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
                    <label style="color: #121212;" for="discount_id">Discount</label>
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
                <button style="width: 100%; margin: 0; " type="submit" name="create" class="button create">Add Coupon</button>
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
                    <label style="color: #121212;" for="discount_id">Discount</label>
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
                    <input type="date" name="expiry_date" id="edit_expiry_date" placeholder="Expiry Date" required />
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
            <div style="justify-content: flex-end; padding-right:1rem;" class="main-title">
                <button style="     border: none;    height: 43px;    border-radius: 20px;    border-radius: 20px;" id="Add_coupon" class="button create" onclick="toggleForm('createForm')">Add Coupon</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Discount </th>
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
                            echo "<td>" . htmlspecialchars(Discount_amount($row['discount_id'])) . "%" . "</td>";
                            echo "<td>" . htmlspecialchars($row['expiry_date']) . "</td>";
                            echo "<td>
                                <form method='POST' style='display:inline' onsubmit='return confirmDelete(this);'>
                                    <input type='hidden' name='coupon_id' value='" . htmlspecialchars($row['id']) . "'>
                                    <button type='button' class='Ed-btn' onclick='editCoupon(" . htmlspecialchars($row['id']) . ", \"" . htmlspecialchars($row['code']) . "\", " . htmlspecialchars($row['discount_id']) . ", \"" . htmlspecialchars($row['expiry_date']) . "\")'>
                                           <i class='fa-solid fa-pencil' style='color: #48b712; hight: 20px;'></i>
                                    </button>
                                    <button type='submit' name='delete' class='del-btn' aria-label='Delete'>
                                       <i class='fa-solid fa-x' style='color: #ed2e0c;'></i>
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

    <?php if ($alertMessage): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $alertType; ?>',
                title: '<?php echo $alertType === 'success' ? 'Success' : 'Oops...'; ?>',
                text: '<?php echo $alertMessage; ?>',
                confirmButtonText: 'OK',
                confirmButtonColor: '<?php echo $alertType === 'success' ? '#3085d6' : '#6a5acd'; ?>'
            });
        </script>
    <?php endif; ?>

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

        function confirmDelete(form) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this coupon?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Add hidden input for confirm_delete
                    var confirmDeleteInput = document.createElement('input');
                    confirmDeleteInput.type = 'hidden';
                    confirmDeleteInput.name = 'confirm_delete';
                    confirmDeleteInput.value = 'true';
                    form.appendChild(confirmDeleteInput);

                    form.submit(); // Submit the form if the user confirms the deletion
                }
            });
            return false; // Prevent the form from submitting immediately
        }
    </script>
</body>

</html>