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

// Handle creating a new category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {

    // Retrieve category details
    $name_Category = $_POST['name_Category'];

    // Check if the category already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM category WHERE name = ?");
    $stmt->bind_param("s", $name_Category);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        // Display an alert if the category already exists
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Oops...",
                    text: "Error: Category name already exists.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>';
    } else {
        // Insert category details into the database
        $stmt = $conn->prepare("INSERT INTO category (name) VALUES (?)");
        $stmt->bind_param("s", $name_Category);

        if ($stmt->execute()) {
            echo '<script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "Success!",
                        text: "Category added successfully.",
                        icon: "success",
                        confirmButtonText: "OK"
                    }).then(() => {
                        window.location.href = "categories.php";
                    });
                });
            </script>';
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle updating a category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $category_id = $_POST['category_id'];
    $name_Category = $_POST['name_Category'];

    // Check if the updated category name already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM category WHERE name = ? AND category_id != ?");
    $stmt->bind_param("si", $name_Category, $category_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        // Display an alert if the category already exists
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Oops...",
                    text: "Error: Category name already exists.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>';
    } else {
        // Update category details in the database
        $stmt = $conn->prepare("UPDATE category SET name = ? WHERE category_id = ?");
        $stmt->bind_param("si", $name_Category, $category_id);

        if ($stmt->execute()) {
            echo '<script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "Success!",
                        text: "Category updated successfully.",
                        icon: "success",
                        confirmButtonText: "OK"
                    }).then(() => {
                        window.location.href = "categories.php";
                    });
                });
            </script>';
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle category deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare("DELETE FROM category WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);

    if ($stmt->execute()) {
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Deleted!",
                    text: "Category deleted successfully.",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    window.location.href = "categories.php";
                });
            });
        </script>';
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch and display categories and users
$sql_categories = "SELECT * FROM category";
$result_categories = $conn->query($sql_categories);

$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);
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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .btn-narrow-confirm,
        .btn-narrow-cancel {
            min-width: 100px;
            /* Adjust this value for the desired button width */
            font-size: 16px;
            padding: 8px 24px;
        }

        /* You can still adjust the width of the entire popup */
        .swal2-wide {
            width: 400px !important;
            /* Adjust the width to match your needs */
        }


        /* -----------------   perfect css------------ */
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
        *
        {
            font-family: "Montserrat", sans-serif;

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

        <!-- --------- Start pop up add categories -------- -->
        <div class="shadow" id="createForm" style="display:none;">
            <form class="form" method="POST" enctype="multipart/form-data">
                <span class="title">Add Category</span>

                <div class="input-container">
                    <label style="color: #121212;" for="name_Category">Name</label>
                    <input type="text" name="name_Category" required>
                </div>

                <button style="width: 100%; margin: 0;" type="submit" name="create" class="button create">Add Category</button>
                <button type="button" class="button" onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>
        <!-- --------- End pop up add categories -------- -->

        <!-- --------- Start pop up update categories -------- -->
        <div class="shadow" id="editForm" style="display:none;">
            <form class="form" method="POST" enctype="multipart/form-data">
                <span class="title">Update Category</span>

                <div class="input-container">
                    <label style="color: #121212;" for="name_Category">Name</label>
                    <input type="text" name="name_Category" id="editCategoryName" required>
                    <input type="hidden" name="category_id" id="editCategoryId">
                </div>

                <button style="width: 100%; margin: 0;" type="submit" name="update" class="button create">Update Category</button>
                <button type="button" class="button" onclick="toggleForm('editForm')">Close</button>
            </form>
        </div>
        <!-- --------- End pop up update categories -------- -->

        <!-- Main Content -->
        <main class="main-container">
            <h2 style="color:#666666; text-align:center; font-weight: bold;">CATEGORIES</h2>
            <div style="justify-content: flex-end; padding-right:1rem;" class="main-title">
                <button style="     border-radius: 20px;" id="Add_product" class="button create" onclick="toggleForm('createForm')">Add Category</button>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Category Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_categories->num_rows > 0) {

                            while ($row = $result_categories->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($row["category_id"]) . "</td>
                                    <td>" . htmlspecialchars($row["name"]) . "</td>
                                    <td>
                                        <div style='display: flex; gap: 40px;'>
                                            <!-- Edit Button -->
                                            <button class='Ed-btn' onclick='editCategory(\"" . htmlspecialchars($row["category_id"]) . "\", \"" . htmlspecialchars($row["name"]) . "\")'>
                                                <i class='fa-solid fa-pencil' style='color: #48b712; height: 20px;'></i>
                                            </button>
                                            
                                            <!-- Delete Form and Button -->
                                            <form id='deleteForm" . htmlspecialchars($row["category_id"]) . "' method='POST' style='display: inline;'>
                                                <input type='hidden' name='category_id' value='" . htmlspecialchars($row["category_id"]) . "'>
                                                <button type='button' class='del-btn' onclick='confirmDelete(\"category\", \"" . htmlspecialchars($row["category_id"]) . "\")'>
                                                    <i class='fa-solid fa-x' style='color: #ed2e0c; height: 20px;'></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No categories found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>


        </main>
        <!-- End Main Content -->
    </div>

    <!-- Custom Scripts -->
    <script src="js/scripts.js"></script>
    <script>
        function editCategory(categoryId, categoryName) {
            document.getElementById('editCategoryId').value = categoryId;
            document.getElementById('editCategoryName').value = categoryName;
            toggleForm('editForm');
        }

        function toggleForm(formId) {
            const form = document.getElementById(formId);
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }

        function confirmDelete(entityType, entityId) {
            Swal.fire({
                title: "Are you sure?",
                text: `Do you really want to delete this ${entityType}?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
                customClass: {
                    popup: "swal2-wide",
                    confirmButton: "btn-narrow-confirm",
                    cancelButton: "btn-narrow-cancel"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Add hidden input for confirm_delete
                    var confirmDeleteInput = document.createElement('input');
                    confirmDeleteInput.type = 'hidden';
                    confirmDeleteInput.name = 'confirm_delete';
                    confirmDeleteInput.value = 'true';
                    document.getElementById("deleteForm" + entityId).appendChild(confirmDeleteInput);

                    document.getElementById("deleteForm" + entityId).submit();
                }
            });
        }
    </script>
</body>

</html>