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

// Function to count products
function countProduct()
{
    global $conn;
    $sql = "SELECT COUNT(product_id) AS pr_number FROM products;";
    $result = $conn->query($sql);

    $pr_number = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pr_number = $row['pr_number'];
    }
    return $pr_number;
}
$printproduct = countProduct();

// Function to count categories
function countCate()
{
    global $conn;
    $sql = "SELECT COUNT(category_id) AS ca_number FROM category;";
    $result = $conn->query($sql);

    $cat_number = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $cat_number = $row['ca_number'];
    }
    return $cat_number;
}
$printcat = countCate();

// Function to count users
function countUsers()
{
    global $conn;
    $sql = "SELECT COUNT(user_id) AS pr_number FROM users;";
    $result = $conn->query($sql);

    $user_number = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_number = $row['pr_number'];
    }
    return $user_number;
}
$printuser = countUsers();

// Function to get total sales
function total()
{
    global $conn;
    $sql = "SELECT SUM(total) AS totalQ FROM orders;";
    $result = $conn->query($sql);

    $total_number = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_number = $row['totalQ'];
    }
    return $total_number;
}
$printtotal = total();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/btinlogout.css" />

    <!-- ----------------  font icon -------------- -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div class="grid-container">
        <!-- Header -->
        <header class="header">
            <div class="menu-icon" onclick="openSidebar()">
                <span class="material-icons-outlined">menu</span>
            </div>
            <div class="header-left">
                <h2 style="color:#000" class="admin">Welcome , <?php echo htmlspecialchars($user['username']); ?></h2>
            </div>
            <div class="header-right">
                <span class="material-icons-outlined">
                    <a href="../logout.php" style="text-decoration: none;">
                        <button class="btnlogout">LOGOUT
                            <div class="arrow-wrapper">
                                <div class="arrow"></div>
                            </div>
                        </button>
                    </a>
                </span>
            </div>
        </header>
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
                <li class="sidebar-list-item"><a href="users.php"><span class="material-icons-outlined">groups</span>
                        Customers</a></li>
                <li class="sidebar-list-item"><a href="discount.php"> <i class="fa-solid fa-colon-sign" style="color: #ffffff;"></i>
                        Discount </a></li>
                <li class="sidebar-list-item"><a href="coupons.php"> <i class="fa-solid fa-percent" style="color: #ffffff;"></i> Coupons </a></li>

            </ul>
        </aside>
        <!-- Main -->
        <main class="main-container">
            <div class="main-title">
                <h2>DASHBOARD</h2>
            </div>
            <div class="main-cards">
                <div class="card">
                    <div class="card-inner">
                        <h3>PRODUCTS</h3>
                        <span class="material-icons-outlined">inventory_2</span>
                    </div>
                    <h1><?php echo $printproduct; ?></h1>
                </div>
                <div class="card">
                    <div class="card-inner">
                        <h3>CATEGORIES</h3>
                        <span class="material-icons-outlined">category</span>
                    </div>
                    <h1><?php echo $printcat; ?></h1>
                </div>
                <div class="card">
                    <div class="card-inner">
                        <h3>CUSTOMERS</h3>
                        <span><i class="fa-solid fa-colon-sign" style="color: #ffffff; height:24; WIDTH:24;"></i></span>
                    </div>
                    <h1><?php echo $printuser; ?></h1>
                </div>
                <div class="card">
                    <div class="card-inner">
                        <h3>TOTAL SALES</h3>
                        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
                        <span style="height: 50px;" class="material-symbols-outlined">equalizer</span>
                    </div>
                    <h1>$<?php echo number_format($printtotal); ?></h1>
                </div>
            </div>
            <div class="charts">
                <div class="charts-card">
                    <h2 class="chart-title">Top Requested Products</h2>
                    <?php include 'getProducts_API.php'; ?>
                    <div> <?php echo $x; ?></div>
                    <!-- <div id="bar-chart"></div> -->
                </div>
                <div class="charts-card">
                    <h2 class="chart-title"> Order Types Analysis</h2>
                    <div id="area-chart"></div>
                </div>
            </div>
        </main>
    </div>
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.5/apexcharts.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>