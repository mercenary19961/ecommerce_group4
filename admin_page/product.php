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

    // Retrieve product details
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    // Handle image upload
    $target_dir = "images/";
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
            echo <<<do
            <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'This email is already registered.'
            });
            </script>
do;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo <<<"not"
         "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Error uploading file.',
                icon: 'error'
            });
        </script>"
        not;
    }
}

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $product_id = $_POST['product_id'];

    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                title: 'Deleted!',
                text: 'Product deleted successfully',
                icon: 'success'
            }).then(() => {
                window.location.href = 'product.php'; // Redirect to the product page
            });
        </script>";
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

    <!-- font google icon  -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- Montserrat Font -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/btinlogout.css" />

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
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .button.create {
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

        .button.delete {
            width: 70%;

            background-color: #dc3545;
            color: white;
        }

        .button.delete:hover {
            background-color: #c82333;
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
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-container textarea {
            resize: vertical;
        }

        button {
            margin-top: 10px;
            background-color: rgb(23, 111, 211);
            color: #fff;
            text-transform: uppercase;
            font-weight: bold;
        }

        button:hover {
            background-color: #1a91d0;
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

            </div>
            <div class="header-right">
                <span class="material-icons-outlined">
                    <a href="../login.php" style="text-decoration : none">
                        <button class="btnlogout">LogOut<div class="arrow-wrapper">
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
                <li class="sidebar-list-item">
                    <a href="dashboard.php">
                        <span class="material-icons-outlined">dashboard</span> Dashboard
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="product.php">
                        <span class="material-icons-outlined">inventory_2</span> Products
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="categories.php">
                        <span class="material-icons-outlined">category</span> Categories
                    </a>
                </li>
                <li class="sidebar-list-item">
                    <a href="users.php">
                        <span class="material-icons-outlined">groups</span> Customers
                    </a>
                </li>

            </ul>
        </aside>
        <!-- End Sidebar -->

        <div class="shadow" id="createForm">
            <!-- Form for creating a new product -->
            <form class="form" method="POST" enctype="multipart/form-data">
                <span class="title">Add product</span>
                <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($_SESSION['form_token']); ?>">
                <div class="input-container">
                    <label class="color_line" for="name">Product Name</label>
                    <input type="text" name="name" required />
                </div>
                <div class="input-container">
                    <label for="description" class="color_line">Product Description</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="input-container">
                    <label for="price" class="color_line"> Price</label>
                    <input type="number" name="price" required />
                </div>
                <div class="input-container">
                    <label for="stock" class="color_line">Stock</label>
                    <input type="number" name="stock" required />
                </div>
                <div class="input-container">
                    <label for="category_id" class="color_line">Category ID </label>
                    <br>
                    <select style=" width:109% ;
    border: 1px solid #ddd;
    outline: none;

    padding: 12px 16px;
    background-color: rgb(247, 243, 243);
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: border-color 0.3s ease, box-shadow 0.3s ease;" name="category_id" required>
                        <?php
                        $category_query = "SELECT * FROM category";
                        $category_result = $conn->query($category_query);
                        while ($category_row = $category_result->fetch_assoc()) {
                            echo '<option value="' . $category_row['category_id'] . '">' . $category_row['name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="input-container">
                    <label for="image" class="color_line">image </label>
                    <input style="color:#121212" type="file" name="image" required />
                </div>
                <button style=" width: 100%; margin-left: 0px;" type="submit" name="create" class="button create">Add Product</button>
                <button type="button" class="button" onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>

        <!-- Main Content -->
        <main class="main-container">
            <div class="main-title">
                <button id="Add_product" class="button create" onclick="toggleForm('createForm')">Add Product</button>
            </div>


            <div class="table-container">
                <table>
                    <thead>
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
                    </thead>
                    <tbody>
                        <?php
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['product_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['category_id']) . "</td>";
                            echo "<td><img src='images/" . htmlspecialchars($row['image']) . "' alt='Product Image' style='max-width: 100px;'></td>";
                            echo "<td>
        <form method='POST' style='display:inline'>
            <input type='hidden' name='product_id' value='" . htmlspecialchars($row['product_id']) . "'>
            <button type='submit' name='delete' class='button delete'>Delete</button>
        </form>
        <a href='product_update.php?product_id=" . htmlspecialchars($row['product_id']) . "'>
            <button class='button edit'>Edit</button>
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