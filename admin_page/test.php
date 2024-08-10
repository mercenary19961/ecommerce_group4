<?php
session_start();
include 'config/connection.php';
// include 'vendor/autoload.php'; // Make sure SweetAlert2 is included if using Composer

use SweetAlert\SweetAlert; // Assuming SweetAlert is used with Composer, adjust as needed

// Check if user is logged in
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
        echo "<script>document.getElementById('alertimg').style.display = 'block';</script>";
        exit();
    }

    // Check file size (limit to 2MB)
    if ($_FILES["image"]["size"] > 2000000) {
        echo "<script>document.getElementById('alertimg').style.display = 'block';</script>";
        exit();
    }

    // Allow certain file formats
    $allowed_types = array("jpg", "jpeg", "png", "gif");
    if (!in_array($image_file_type, $allowed_types)) {
        echo "<script>document.getElementById('alertimg').style.display = 'block';</script>";
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
                });
                setTimeout(function() { window.location.href = 'product.php'; }, 3000);
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error: " . $stmt->error . "'
                });
            </script>";
        }
        $stmt->close();
    } else {
        echo "<script>document.getElementById('alertimg').style.display = 'block';</script>";
        exit();
    }
}

// Handle product deletion (Ensure this is wrapped in the appropriate condition, e.g., POST request)
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
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error: " . $stmt->error . "'
            });
        </script>";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>
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
    <link rel="stylesheet" href="css/tables.css" />
    <!-- ----------------  font icon -------------- -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* alert for image  */
        .alert {
            padding: 20px;
            background-color: #f44336;
            color: white;
        }

        .closebtn {
            margin-left: 15px;
            color: white;
            font-weight: bold;
            float: right;
            font-size: 22px;
            line-height: 20px;
            cursor: pointer;
            transition: 0.3s;
        }

        .closebtn:hover {
            color: black;
        }

        /* -----end alert image ------- */
        body {
            background-color: #e6e8ed;
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
            background-color: #000000;
            color: #fff;
        }

        .table-container td {
            color: #000;
            background-color: #ffffff;

        }

        .table-container tr:nth-child(even) td {
            background-color: #f5f0f0;
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
            /* box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-container textarea {
            resize: vertical;
        }

        /* button {
        margin-top: 10px;
        background-color: rgb(23, 111, 211);
        color: #fff;
        text-transform: uppercase;
        font-weight: bold;
    }

    button:hover {
        background-color: #1a91d0;
    } */

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
        <!-- End Sidebar -->

        <div class="shadow" id="createForm">
            <!-- Form for creating a new product -->
            <form id="imageUploadForm" class="form" method="POST" enctype="multipart/form-data">
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
                    <label id="imageInput" for="image" class="color_line">image </label>
                    <input style="color:#121212" type="file" name="image" accept="image/jpeg" required />
                </div>
                <div class="input-container">
                    <div id="alretimge" class="alert" style="display: none;">
                        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
                        <strong>Danger!</strong> Indicates a dangerous or potentially negative action.
                    </div>

                </div>
                <button style=" width: 100%; margin-left: 0px;" type="submit" name="create" class="button create">Add
                    Product</button>
                <button type="button" class="button" onclick="toggleForm('createForm')">Close</button>
            </form>
        </div>

        <!-- Main Content -->
        <main class="main-container">
            <h2 style="color:#666666; text-align:center;    font-weight: bold;">PRODUCTS</h2>
            <div style="  justify-content: flex-end;" class="main-title">

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
                        // <!-- --------------name category-------- -->
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
        <form method='POST' style='display:inline' id='delete-form-" . htmlspecialchars($row['product_id']) . "'>
            <input type='hidden' name='product_id' value='" . htmlspecialchars($row['product_id']) . "'>
            <button type='button' name='delete' class='button delete' aria-label='Delete' onclick='confirmDelete(" . htmlspecialchars($row['product_id']) . ")'>
                <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 48 48' role='img'>
                    <path fill='#F44336' d='M21.5 4.5H26.501V43.5H21.5z' transform='rotate(45.001 24 24)'></path>
                    <path fill='#F44336' d='M21.5 4.5H26.5V43.501H21.5z' transform='rotate(135.008 24 24)'></path>
                </svg>
            </button>
        </form>
        <a href='product_update.php?product_id=" . htmlspecialchars($row['product_id']) . "'>
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

        function confirmDelete(productId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + productId).submit();
                }
            });
        }
        document.getElementById('imageUploadForm').addEventListener('submit', function(event) {
            var imageInput = document.getElementById('imageInput');
            var file = imageInput.files[0];
            var errorMsg = document.getElementById('errorMsg');
            errorMsg.textContent = ''; // Clear any previous errors

            // Check if a file is selected
            if (!file) {
                errorMsg.textContent = 'Please select an image file.';
                event.preventDefault();
                return;
            }

            // Validate file type (e.g., allow only JPEG, PNG, GIF)
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                errorMsg.textContent = 'Only JPEG, PNG, and GIF files are allowed.';
                event.preventDefault();
                return;
            }

            // Validate file size (e.g., limit to 2MB)
            var maxSize = 2 * 1024 * 1024; // 2MB in bytes
            if (file.size > maxSize) {
                errorMsg.textContent = 'File size must be less than 2MB.';
                event.preventDefault();
                return;
            }

            // Validate image dimensions (optional)
            var img = new Image();
            img.onload = function() {
                var width = img.width;
                var height = img.height;

                // Example: Ensure the image is at least 100x100 pixels
                if (width < 100 || height < 100) {
                    errorMsg.textContent = 'Image dimensions must be at least 100x100 pixels.';
                    event.preventDefault();
                    return;
                }

                // If all validations pass, submit the form
                document.getElementById('imageUploadForm').submit();
            };

            img.onerror = function() {
                errorMsg.textContent = 'Invalid image file.';
                event.preventDefault();
            };

            // Read the image file as a data URL
            var reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);

            // Prevent form submission until validation completes
            event.preventDefault();
        });


        // alert image 
        function alert() {
            var alertimg = document.getElementById('alretimge');
            alertimag.style = 'display:block;';





        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>