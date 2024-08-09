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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create']) ) {

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
        echo '<script>alert("You must enter a category name that does not exist ");</script>';

    } else {
        // Insert category details into database
        $stmt = $conn->prepare("INSERT INTO category (name) VALUES (?)");
        $stmt->bind_param("s", $name_Category);

        if ($stmt->execute()) {
           
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle category deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    if (isset($_POST['category_id'])) {
        $category_id = $_POST['category_id'];

        $stmt = $conn->prepare("DELETE FROM category WHERE category_id = ?");
        $stmt->bind_param("i", $category_id);

        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Category deleted successfully.',
                    icon: 'success'
                }).then(() => {
                    window.location.href = 'product.php'; // Redirect to the product page
                });
            </>";
            header('location:categories.php');
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error: category_id not set.";
    }
}

// Fetch and display categories
$sql = "SELECT * FROM category";
$result = $conn->query($sql);
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
    <!-- ----------------  font icon -------------- -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <a href="../login.php" style="text-decoration : none">
                        <button class="btnlogout">LOGOUT<div class="arrow-wrapper">
                                <div class="arrow"></div>
                            </div>
                        </button></span>
                </a></span>
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

        <div class="shadow" id="createForm" style="display:none;">
            <!-- Form for creating a new category -->
            <form class="form" method="POST" enctype="multipart/form-data">
                <span class="title">Add Category</span>


                <div class="input-container">
                    <label style="color: #121212;" for="name_Category">Name</label>
                    <input type="text" name="name_Category" required>
                </div>

                <button style="width: 100%; margin: 0;" type="submit" name="create" class="button create">Add
                    Category</button>
                <button type="button" class="button" onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>

        <!-- Main Content -->
        <main class="main-container">
            <h2 style="color:#666666; text-align:center; font-weight: bold;">CATEGORIES</h2>
            <div style="justify-content: flex-end;" class="main-title">
                <button id="Add_product" class="button create" onclick="toggleForm('createForm')">Add Category</button>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['category_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>
                                <form method='POST' style='display:inline'>
                                    <input type='hidden' name='category_id' value='" . htmlspecialchars($row['category_id']) . "'>
                                    <button type='submit' name='delete' class='button delete' aria-label='Delete'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 48 48' role='img'>
                                            <path fill='#F44336' d='M21.5 4.5H26.501V43.5H21.5z' transform='rotate(45.001 24 24)'></path>
                                            <path fill='#F44336' d='M21.5 4.5H26.5V43.501H21.5z' transform='rotate(135.008 24 24)'></path>
                                        </svg>
                                    </button>
                                </form>
                                <a href='category_update.php?category_id=" . htmlspecialchars($row['category_id']) . "'>
                                      <button type='submit' name='delete' class='button delete' aria-label='Delete'>
  <i class='fa-solid fa-pencil' style='color: #48b712;'></i>
</button>
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