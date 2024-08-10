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

// Fetch and display products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Handle product update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $image_name = '';

    // Check if a new image was uploaded
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'File is not a valid image.'
                });
                window.location.href = 'product.php';
            </script>";
            exit();
        }

        // Check file size (limit to 2MB)
        if ($_FILES["image"]["size"] > 2000000) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'File size exceeds 2MB limit.'
                });
                window.location.href = 'product.php';
            </script>";
            exit();
        }

        // Allow certain file formats
        $allowed_types = array("jpg", "jpeg", "png", "gif");
        if (!in_array($image_file_type, $allowed_types)) {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Only JPG, JPEG, PNG & GIF files are allowed.'
                });
                window.location.href = 'product.php';
            </script>";
            exit();
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Update with the new image
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image=? WHERE product_id=?");
            $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $category_id, $image_name, $product_id);
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to upload the image.'
                });
                window.location.href = 'product.php';
            </script>";
            exit();
        }
    } else {
        // Update without changing the image
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=? WHERE product_id=?");
        $stmt->bind_param("ssdiii", $name, $description, $price, $stock, $category_id, $product_id);
    }

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Product updated successfully.'
            }).then(() => {
                window.location.href = 'product.php';
            });
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle creating a new product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
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
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'File is not a valid image.'
            });
            window.location.href = 'product.php';
        </script>";
        exit();
    }

    // Check file size (limit to 2MB)
    if ($_FILES["image"]["size"] > 2000000) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'File size exceeds 2MB limit.'
            });
            window.location.href = 'product.php';
        </script>";
        exit();
    }

    // Allow certain file formats
    $allowed_types = array("jpg", "jpeg", "png", "gif");
    if (!in_array($image_file_type, $allowed_types)) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Only JPG, JPEG, PNG & GIF files are allowed.'
            });
            window.location.href = 'product.php';
        </script>";
        exit();
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert product details into database
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $category_id, $image_name);

        if ($stmt->execute()) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Product added successfully.'
                }).then(() => {
                    window.location.href = 'product.php';
                });
            </script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unable to upload the image.'
            });
            window.location.href = 'product.php';
        </script>";
        exit();
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>

    <!-- Google Fonts and Icons -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css" />
    <link rel="stylesheet" href="css/btinlogout.css" />
    <link rel="stylesheet" href="css/tables.css" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        .left-Et {
            padding-right: 2px;
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
            font-size: 20px;
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
            font-size: 20px;
            border: none;
            padding: 1px;
            height: 50%;
            width: 20%;
            background: none;
        }

        .table-container th,
        .table-container td {
            /* padding-top: 31px;
            text-align: left;
            padding-bottom: 31px; */
        }

        .table-container tr:nth-child(even) td {
            background-color: #ffff;
        }

        .table-container td {
            border-bottom: solid #c3c3c3 1px;

            color: #000;
            background-color: #ffffff;

        }

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

        /* css perfct */







        body {
            background-color: #e6e8ed;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
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
        *
        {
            font-family: "Montserrat", sans-serif;

        }
    </style>

    <script>
        function toggleForm(id) {
            const form = document.getElementById(id);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function validateImage() {
            const fileInput = document.querySelector('input[name="image"]');
            const filePath = fileInput.value;
            const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;

            if (!allowedExtensions.exec(filePath)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid file type',
                    text: 'Please upload a file with a valid image format (JPG, JPEG, PNG, GIF).'
                });
                fileInput.value = '';
                return false;
            }

            return true;
        }

        function openUpdateForm(productId, name, description, price, stock, categoryId) {
            document.getElementById('updateForm').style.display = 'block';
            document.querySelector('input[name="product_id"]').value = productId;
            document.querySelector('input[name="name"]').value = name;
            document.querySelector('textarea[name="description"]').value = description;
            document.querySelector('input[name="price"]').value = price;
            document.querySelector('input[name="stock"]').value = stock;
            document.querySelector('select[name="category_id"]').value = categoryId;

            // Hide the Add Product form when editing
            document.getElementById('createForm').style.display = 'none';
        }

        function confirmDelete(productId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this product?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form dynamically and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'product.php';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_id';
                    input.value = productId;
                    form.appendChild(input);

                    const deleteInput = document.createElement('input');
                    deleteInput.type = 'hidden';
                    deleteInput.name = 'delete';
                    form.appendChild(deleteInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
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
                            style="color: #ffffff;"></i>
                        Discount </a></li>
                <li class="sidebar-list-item"><a href="coupons.php"> <i class="fa-solid fa-percent"
                            style="color: #ffffff;"></i> Coupons </a></li>

            </ul>
        </aside>
        <!-- End Sidebar -->

        <!-- Create Product Form -->
        <div class="shadow" id="createForm">
            <form class="form" method="POST" enctype="multipart/form-data" onsubmit="return validateImage()">
                <span class="title">Add product</span>

                <div class="input-container">
                    <label class="color_line" for="name">Product Name</label>
                    <input type="text" name="name" required />
                </div>
                <div class="input-container">
                    <label for="description" class="color_line">Product Description</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="input-container">
                    <label for="price" class="color_line">Price</label>
                    <input type="number" name="price" required />
                </div>
                <div class="input-container">
                    <label for="stock" class="color_line">Stock</label>
                    <input type="number" name="stock" required />
                </div>
                <div class="input-container">
                    <label for="category_id" class="color_line">Category ID</label>
                    <select style=" width:109% ; border: 1px solid #ddd; outline: none; padding: 12px 16px; background-color: rgb(247, 243, 243); border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: border-color 0.3s ease, box-shadow 0.3s ease;" name="category_id" required>
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
                    <label for="image" class="color_line">image</label>
                    <input style="color:#121212" type="file" name="image" required />
                </div>
                <button style="width: 100%; margin-left: 0px;" type="submit" name="create" class="button create">Add Product</button>
                <button type="button" class="button close" onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>

        <!-- Update Product Form -->
        <div class="shadow" id="updateForm">
            <form class="form" method="POST" enctype="multipart/form-data" onsubmit="return validateImage()">
                <input type="hidden" name="product_id" />
                <span class="title">Update product</span>

                <div class="input-container">
                    <label class="color_line" for="name">Product Name</label>
                    <input type="text" name="name" required />
                </div>
                <div class="input-container">
                    <label for="description" class="color_line">Product Description</label>
                    <textarea name="description" required></textarea>
                </div>
                <div class="input-container">
                    <label for="price" class="color_line">Price</label>
                    <input type="number" name="price" required />
                </div>
                <div class="input-container">
                    <label for="stock" class="color_line">Stock</label>
                    <input type="number" name="stock" required />
                </div>
                <div class="input-container">
                    <label for="category_id" class="color_line">Category ID</label>
                    <select style=" width:109% ; border: 1px solid #ddd; outline: none; padding: 12px 16px; background-color: rgb(247, 243, 243); border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: border-color 0.3s ease, box-shadow 0.3s ease;" name="category_id" required>
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
                    <label for="image" class="color_line">Product Image (leave blank to keep current)</label>
                    <input style="color:#121212" type="file" name="image" />
                </div>
                <button style="width: 100%; margin-left: 0px;" type="submit" name="update" class="button create">Update Product</button>
                <button type="button" class="button close" onclick="toggleForm('updateForm')">close</button>
            </form>
        </div>

        <!-- Main Content -->
        <main class="main-container">
            <h2 style="color:#666666; text-align:center; font-weight: bold;">PRODUCTS</h2>
            <div style="justify-content: flex-end; padding: 1rem;" class="main-title">
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
                            <th>Category</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Function to get the category name
                        function categoryname($category_id)
                        {
                            global $conn;

                            $sql = "SELECT category.name AS category_name FROM category WHERE category_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $category_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            $Cate_Name = '';
                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $Cate_Name = $row['category_name'];
                            }

                            return $Cate_Name;
                        }

                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['product_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['price']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['stock']) . "</td>";
                            echo "<td>" . htmlspecialchars(categoryname($row['category_id'])) . "</td>";
                            echo "<td><img src='images/" . htmlspecialchars($row['image']) . "' alt='Product Image' style='max-width: 100px;'></td>";
                            echo "<td>
                            <div>
                            <div class='left-Et'>
                            <button type='button' class='Ed-btn' onclick=\"openUpdateForm('" . htmlspecialchars($row['product_id']) . "', '" . htmlspecialchars(addslashes($row['name'])) . "', '" . htmlspecialchars(addslashes($row['description'])) . "', '" . htmlspecialchars($row['price']) . "', '" . htmlspecialchars($row['stock']) . "', '" . htmlspecialchars($row['category_id']) . "')\">
                               <i class='fa-solid fa-pencil' style='color: #48b712; hight: 20px;'></i>
                            </button>
                            </div>
                            <div class='rite-Dt'>
                                <button type='button' class='del-btn' onclick=\"confirmDelete('" . htmlspecialchars($row['product_id']) . "')\">
                                     <i class='fa-solid fa-x' style='color: #ed2e0c;'></i>
                                </button>
                                </div>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>


</html>