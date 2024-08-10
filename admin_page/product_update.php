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

// Check if product_id is provided in the URL
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Product not found.',
                icon: 'error'
            }).then(() => {
                window.location.href = 'products.php'; // Redirect to the products page
            });
        </script>";
        exit();
    }
} else {
    echo "<script>
        Swal.fire({
            title: 'Error!',
            text: 'Product ID is missing.',
            icon: 'error'
        }).then(() => {
            window.location.href = 'products.php'; // Redirect to the products page
        });
    </script>";
    exit();
}

// Handle updating the product
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    // Handle image upload
    $image_name = $product['image'];
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Image uploaded successfully
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to upload image.',
                    icon: 'error'
                });
            </script>";
        }
    }

    // Update product details in database
    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ? WHERE product_id = ?");
    $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $category_id, $image_name, $product_id);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Product updated successfully',
                icon: 'success'
            }).then(() => {
                window.location.href = 'product.php'; // Redirect to the products page
            });
        </script>";
        header('Location: product.php');
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    .btnlogout {
        height: 1vh;
        font-size: 17px;
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

    .header {
        grid-area: header;
        height: 70px;
        background-color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 30px 0 30px;
        box-shadow: 0 6px 7px -4px rgba(0, 0, 0, 0.2);
    }

    h2.admin {
        font-weight: bold;

        font-size: 22px;
        color: #000;
    }

    .menu-icon {
        display: none;
    }

    /* -----------perfct--------- */

    form {
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 700px;
    }

    h2 {
        margin-bottom: 24px;
        font-size: 24px;
        font-weight: 600;
        color: #1d1d1f;
        text-align: center;
    }

    .input-container {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        color: #6e6e73;
    }

    input[type="text"],
    input[type="number"],
    input[type="file"],
    textarea {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 1px solid #d2d2d7;
        border-radius: 8px;
        background-color: #f9f9f9;
        text-align: left;
        transition: border-color 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    input[type="file"]:focus,
    textarea:focus {
        border-color: #007aff;
        outline: none;
        background-color: #ffffff;
    }

    textarea {
        resize: vertical;
        min-height: 80px;
    }

    button {
        width: 100%;
        padding: 12px;
        font-size: 26px;
        font-weight: 500;
        color: #ffffff;
        background-color: #007aff;
        border: none;
        height: 8vh;
        color: #000;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #15385f;
        color: #ffffff;
    }

    .back-to-dashboard {
        display: block;
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
        color: #007aff;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .back-to-dashboard:hover {
        color: #005bb5;
    }

    .contaner {

        display: flex;
        justify-content: center;

    }

    .uddate-btn:hover {
        background-color: #15385f;
        color: #ffffff;
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
                <h2 style="color:#000" class="admin">Welcome , <?php echo htmlspecialchars($user['username']); ?></h2>
            </div>
            <div class="header-right">
                <span class="material-icons-outlined">
                    <a href="logout.php" style="text-decoration: none; padding :15px">
                        <button class="btnlogout">LOGOUT
                            <div class="arrow-wrapper">

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
        <!-- Main -->
        <main class="main-container">
            <div class="contaner">
                <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                    <h2>Update Product</h2>
                    <div class="input-container">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required />
                    </div>
                    <div class="input-container">
                        <label for="description">Product Description</label>
                        <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    <div class="input-container">
                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required />
                    </div>
                    <div class="input-container">
                        <label for="stock">Stock Quantity</label>
                        <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required />
                    </div>
                    <div class="input-container">
                        <label for="category_id">Category</label>

                        <select style=" width:100% ; border: 1px solid #ddd; outline: none; padding: 12px 16px; background-color: rgb(247, 243, 243); border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); transition: border-color 0.3s ease, box-shadow 0.3s ease;" name="category_id" required>
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
                        <label for="image">Product Image</label>
                        <input type="file" id="image" name="image" accept="image/*" />
                    </div>
                    <button class="uddate-btn" style="font-size: large; background:#007aff; color :#fff" type="submit" name="update">Update Product</button>
                    <a style="font-size: large;" href="product.php" class="back-to-dashboard">Back to Products</a>
                </form>
            </div>
        </main>
    </div>

    <script>
        function validateForm() {
            const imageInput = document.getElementById('image');
            const file = imageInput.files[0];

            if (file) {
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                const maxSize = 2 * 1024 * 1024; // 2 MB

                if (!validTypes.includes(file.type)) {
                    Swal.fire({
                        title: 'Invalid File Type!',
                        text: 'Please select an image file (JPEG, PNG, GIF).',
                        icon: 'error'
                    });
                    return false;
                }

                if (file.size > maxSize) {
                    Swal.fire({
                        title: 'File Too Large!',
                        text: 'Please select an image file less than 2 MB.',
                        icon: 'error'
                    });
                    return false;
                }
            }

            return true;
        }
    </script>
</body>

</html>