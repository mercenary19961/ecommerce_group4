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

// Handle creating a new discount
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create']) ) {
    // Invalidate the token


    // Retrieve discount details
    $discount_amount = $_POST['discount_amount'];

    // Check if the discount amount already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM discount WHERE discount_amount = ?");
    $stmt->bind_param("s", $discount_amount);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo <<<HTML
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Discount already exists.'
            });
        </script>
HTML;
    } else {
        // Insert discount details into database
        $stmt = $conn->prepare("INSERT INTO discount (discount_amount) VALUES (?)");
        $stmt->bind_param("s", $discount_amount);

        if ($stmt->execute()) {
            echo <<<HTML
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Discount added successfully.'
                });
            </script>
HTML;
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
                    window.location.href = 'product.php'; // Redirect to the product page
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

</head>

<style>
.button.edit:hover {
    background-color: #c6b8b8;
    transform: scale(1.05);
}

.button.edit {
    width: 19%;
    color: white;
    background-color: white;

}


.button.delete {
    width: 29%;

    color: white;
}

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
                <li class="sidebar-list-item"><a href="dashboard.php"><span
                            class="material-icons-outlined">dashboard</span> Dashboard</a></li>
                <li class="sidebar-list-item"><a href="product.php"><span
                            class="material-icons-outlined">inventory_2</span> Products</a></li>
                <li class="sidebar-list-item"><a href="categories.php"><span
                            class="material-icons-outlined">category</span> Categories</a></li>
                <li class="sidebar-list-item"><a href="users.php"><span class="material-icons-outlined">groups</span>
                        Customers</a></li>
                <li class="sidebar-list-item"><a href="discount.php"> <i class="fa-solid fa-colon-sign"
                            style="color: #ffffff;"></i>
                        Discount </a></li>
                <li class="sidebar-list-item"><a href="coupons.php"> <i class="fa-solid fa-percent"
                            style="color: #ffffff;"></i> Coupons </a></li>

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

                <button style="width: 100%; margin: 0;" type="submit" name="create" class="button create">Add
                    Discount</button>
                <button type="button" class="button" onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>

        <!-- Main Content -->
        <main class="main-container">
            <h2 style="color:#666666; text-align:center; font-weight: bold;">DISCOUNTS</h2>
            <div style="justify-content: flex-end;" class="main-title">
                <button id="Add_discount" class="button create" onclick="toggleForm('createForm')">Add Discount</button>
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
                            echo "<td>" . htmlspecialchars($row['discount_amount']) . "</td>";
                            echo "<td>
                                <form method='POST' style='display:inline'>
                                    <input type='hidden' name='discount_id' value='" . htmlspecialchars($row['discount_id']) . "'>
                                    <button type='submit' name='delete' class='button delete' aria-label='Delete'>
                                        <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 48 48' role='img'>
                                            <path fill='#F44336' d='M21.5 4.5H26.501V43.5H21.5z' transform='rotate(45.001 24 24)'></path>
                                            <path fill='#F44336' d='M21.5 4.5H26.5V43.501H21.5z' transform='rotate(135.008 24 24)'></path>
                                        </svg>
                                    </button>
                                </form>
                                <a href='discount_update.php?discount_id=" . htmlspecialchars($row['discount_id']) . "'>
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
        const btn = document.getElementById("Add_discount");
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        btn.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
    </script>
</body>

</html>