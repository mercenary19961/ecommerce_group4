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

// Initialize a variable to hold the status message
$status_message = "";

// Handle creating a new discount
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    $discount_amount = $_POST['discount_amount'];

    // Check if the discount amount already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM discount WHERE discount_amount = ?");
    $stmt->bind_param("s", $discount_amount);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $status_message = "exists";
    } else {
        // Insert discount details into database
        $stmt = $conn->prepare("INSERT INTO discount (discount_amount) VALUES (?)");
        $stmt->bind_param("s", $discount_amount);

        if ($stmt->execute()) {
            $status_message = "success";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle discount deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    if (isset($_POST['discount_id'])) {
        $discount_id = $_POST['discount_id'];

        $stmt = $conn->prepare("DELETE FROM discount WHERE discount_id = ?");
        $stmt->bind_param("i", $discount_id);

        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Discount deleted successfully.',
                    icon: 'success'
                }).then(() => {
                    window.location.href = 'discount.php'; // Redirect to the discount page
                });
            </script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: discount_id not set.";
    }
}

// Handle updating the discount
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    if (isset($_POST['discount_id'])) {
        $discount_id = $_POST['discount_id'];
        $discount_amount = $_POST['discount_amount'];

        // Check if the new discount amount already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM discount WHERE discount_amount = ?");
        $stmt->bind_param("s", $discount_amount);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $status_message = "exists";
        } else {
            // Update discount details in database
            $stmt = $conn->prepare("UPDATE discount SET discount_amount = ? WHERE discount_id = ?");
            $stmt->bind_param("si", $discount_amount, $discount_id);

            if ($stmt->execute()) {
                $status_message = "success";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        echo "Error: discount_id not set.";
    }
}

// Fetch and display discounts
$sql = "SELECT * FROM discount";
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
    <!-- ----------------  font icon -------------- -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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

        .table-container td {
            border-bottom: solid #c3c3c3 1px;
            padding-right: 27%;
            color: #000;
            background-color: #ffffff;
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

        /* -------------css perfct------- */
        .admin {
            color: #000;
        }

        .swal-wide {
            width: 400px !important;
        }

        .btn-confirm-delete {
            border: 2px solid #d33 !important;
            /* Red border */
            background-color: #d33 !important;
            /* Red background */
            color: white !important;
            /* White text color */
        }

        .btn-cancel {
            border: 2px solid #3085d6 !important;
            /* Blue border */
            background-color: #3085d6 !important;
            /* Blue background */
            color: white !important;
            /* White text color */
        }

        .btn-confirm-delete {
            border: 2px solid #d33 !important;
            /* Red border */
            background-color: #d33 !important;
            /* Red background */
            color: white !important;
            /* White text color */
        }

        .btn-cancel {
            border: 2px solid #3085d6 !important;
            /* Blue border */
            background-color: #3085d6 !important;
            /* Blue background */
            color: white !important;
            /* White text color */
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
                <h2 class="admin">Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>
            </div>
            <div class="header-right">
                <span class="material-icons-outlined">
                    <a href="../login.php" style="text-decoration: none">
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

        <div class="shadow" id="createForm">
            <!-- Form for creating a new discount -->
            <form class="form" method="POST" enctype="multipart/form-data">
                <span class="title">Add Discount</span>
                <div class="input-container">
                    <label style="color: #121212;" for="discount_amount">Discount Amount</label>
                    <input type="number" name="discount_amount" required>
                </div>
                <button style="width: 100%; margin: 0; " type="submit" name="create" class="button create">Add Discount</button>
                <button type="button" class="button" onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>

        <!-- --------- Start pop up update categories -------- -->
        <div class="shadow" id="editForm" style="display:none;">
            <form class="form" method="POST" enctype="multipart/form-data">
                <span class="title">Update Discount</span>
                <div class="input-container">
                    <label style="color:#000" for="discount_amount">Discount Amount</label>
                    <input type="number" name="discount_amount" placeholder="Discount Amount" value="<?php echo htmlspecialchars($discount['discount_amount']); ?>" required />
                    <input type="hidden" name="discount_id" id="editDiscountId">
                </div>
                <button style="width: 100%; margin: 0;" type="submit" name="update" class="button create">Update Discount</button>
                <button type="button" class="button" onclick="toggleForm('editForm')">Close</button>
            </form>
        </div>
        <!-- --------- End pop up update categories -------- -->

        <!-- Main Content -->
        <main class="main-container">
            <h2 style="color:#666666; text-align:center; font-weight: bold;">DISCOUNTS</h2>
            <div style="justify-content: flex-end ; padding-right: 1rem;" class="main-title">
                <button style="    border: none;" id="Add_discount" class="Cr-btn" onclick="toggleForm('createForm')">Add Discount</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['discount_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['discount_amount']) . "%" . "</td>";
                            echo "<td>
                            <button type='button' class='Ed-btn' onclick='editDiscount(" . htmlspecialchars($row['discount_id']) . ", \"" . htmlspecialchars($row['discount_amount']) . "\")'>
                                <i class='fa-solid fa-pencil' style='color: #48b712; hight: 20px;'></i>
                            </button>
                          
                                <button type='button' class='del-btn' onclick='confirmDelete(" . htmlspecialchars($row['discount_id']) . ")' aria-label='Delete'>
                                   <i class='fa-solid fa-x' style='color: #ed2e0c;'></i>
                                </button>
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
            const btn = document.getElementById("Add_discount");
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            btn.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function editDiscount(discountId, discountAmount) {
            document.getElementById("editDiscountId").value = discountId;
            document.querySelector("#editForm input[name='discount_amount']").value = discountAmount;
            toggleForm('editForm');
        }

        function confirmDelete(discountId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this discount?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn-confirm-delete', // Apply custom class for further styling
                    cancelButton: 'btn-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form and submit it to delete the discount
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = ''; // Leave action blank to submit to the same page

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'discount_id';
                    input.value = discountId;

                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete';
                    deleteInput.value = 'true';

                    form.appendChild(input);
                    form.appendChild(deleteInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        <?php
        if ($status_message === "exists") {
            echo "Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Error: Discount already exists.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#6C63FF',
                iconHtml: '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"80\" height=\"80\" viewBox=\"0 0 48 48\" role=\"img\"><path fill=\"#F44336\" d=\"M21.5 4.5H26.501V43.5H21.5z\" transform=\"rotate(45.001 24 24)\"></path><path fill=\"#F44336\" d=\"M21.5 4.5H26.5V43.501H21.5z\" transform=\"rotate(135.008 24 24)\"></path></svg>',
                customClass: {
                    popup: 'swal-wide'
                }
            });";
        } elseif ($status_message === "success") {
            echo "Swal.fire({
                title: 'Success!',
                text: 'Discount updated successfully.',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#6C63FF',
                customClass: {
                    popup: 'swal-wide'
                }
            });";
        }
        ?>
    </script>
</body>

</html>