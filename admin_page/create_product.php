<?php 
session_start();
include 'config/connection.php';

// Generate a unique token for form submission
if (empty($_SESSION['form_token'])) {
    $_SESSION['form_token'] = bin2hex(random_bytes(32));
}

// Handle creating a new product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create']) && hash_equals($_SESSION['form_token'], $_POST['form_token'])) {
    // Invalidate the token
    unset($_SESSION['form_token']);

    // Other product details
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    
    // Handle image upload
    $target_dir = "uploads/";
    // Check if the directory exists, if not, create it
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert product details into database
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $category_id, $image_name);

        if ($stmt->execute()) {
            echo "New product created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error uploading file.";
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $product_id = $_POST['product_id'];

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo "Product deleted successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch and display products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <!-- Montserrat Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css" />
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
    }

    .button.create {
        background-color: #007bff;
        color: white;
    }

    .button.edit {
        background-color: #28a745;
        color: white;
    }

    .button.delete {
        background-color: #dc3545;
        color: white;
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
                <span class="material-icons-outlined">search</span>
            </div>
            <div class="header-right">
                <span class="material-icons-outlined">notifications</span>
                <span class="material-icons-outlined">email</span>
                <span class="material-icons-outlined">account_circle</span>
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
                    <a href="index.php">
                        <span class="material-icons-outlined">dashboard</span> Dashboard
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="product.php">
                        <span class="material-icons-outlined">inventory_2</span> Products
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="#">
                        <span class="material-icons-outlined">category</span> Categories
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="#">
                        <span class="material-icons-outlined">groups</span> Customers
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="#" target="_blank">
                        <span class="material-icons-outlined">fact_check</span> Inventory
                    </a>
                </li>
            </ul>
        </aside>
        <!-- End Sidebar -->

        <!-- Main -->
        <main class="main-container">
            <div class="content">
                <!-- Form to create a new product -->
                <h2>Create Product</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="form_token"
                        value="<?php echo htmlspecialchars($_SESSION['form_token']); ?>">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>

                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" required>

                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" required>

                    <label for="category_id">Category ID:</label>
                    <input type="number" id="category_id" name="category_id" required>

                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" required>

                    <button type="submit" name="create" class="button create">Create Product</button>
                </form>

                <!-- Product Table -->
                <h2>Products</h2>
                <div class="table-container">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Category ID</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                        <?php
                        if ($result === FALSE) {
                            echo "<tr><td colspan='8'>Error: " . $conn->error . "</td></tr>";
                        } else {
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>{$row['product_id']}</td>
                                            <td>{$row['name']}</td>
                                            <td>{$row['description']}</td>
                                            <td>{$row['price']}</td>
                                            <td>{$row['stock']}</td>
                                            <td>{$row['category_id']}</td>
                                            <td><img src='uploads/{$row['image']}' alt='{$row['name']}'></td>
                                            <td>
                                                <button class='button edit' onclick='showUpdateForm({$row['product_id']}, \"{$row['name']}\", \"{$row['description']}\", {$row['price']}, {$row['stock']}, {$row['category_id']})'>Edit</button>
                                                <form method='POST' style='display:inline-block;'>
                                                    <input type='hidden' name='product_id' value='{$row['product_id']}'>
                                                    <button type='submit' name='delete' class='button delete'>Delete</button>
                                                </form>
                                            </td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No products found</td></tr>";
                            }
                        }
                        ?>
                    </table>
                </div>
            </div>
        </main>
        <!-- End Main -->
    </div>

    <!-- Scripts -->
    <!-- ApexCharts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.5/apexcharts.min.js"></script>
    <!-- Custom JS -->
    <script src="js/scripts.js"></script>
</body>

</html>