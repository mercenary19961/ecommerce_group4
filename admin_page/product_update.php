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

// Fetch the product data to be updated
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $sql_product = "SELECT * FROM products WHERE product_id = ?";
    $stmt_product = $conn->prepare($sql_product);
    $stmt_product->bind_param('i', $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();
    $product = $result_product->fetch_assoc();
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

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
                window.location.href = 'product_update.php?product_id=$product_id';
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
                window.location.href = 'product_update.php?product_id=$product_id';
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
                window.location.href = 'product_update.php?product_id=$product_id';
            </script>";
            exit();
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image=? WHERE product_id=?");
            $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $category_id, $image_name, $product_id);
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to upload the image.'
                });
                window.location.href = 'product_update.php?product_id=$product_id';
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update Product</title>

    <!-- font google icon  -->
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

    <style>
        body {
            background-color: #e6e8ed;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
        }

        .shadow {
            position: absolute;
            left: 50%;
            top: 5%;
            transform: translate(-50%, 0);
            display: block;
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
            width: 100%;
            background-color: #007bff;
            color: white;
            margin-left: 0px;
        }

        .button.create:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .button.close {
            background-color: #cccccc;
            color: #000;
        }

        .button.close:hover {
            background-color: #aaaaaa;
            transform: scale(1.05);
        }
    </style>

    <script>
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
    </script>
</head>

<body>
    <div class="grid-container">
        <div class="shadow" id="updateForm">
            <form class="form" method="POST" enctype="multipart/form-data" onsubmit="return validateImage()">
                <span class="title">Update product</span>

                <div class="input-container">
                    <label class="color_line" for="name">Product Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required />
                </div>
                <div class="input-container">
                    <label for="description" class="color_line">Product Description</label>
                    <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="input-container">
                    <label for="price" class="color_line"> Price</label>
                    <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required />
                </div>
                <div class="input-container">
                    <label for="stock" class="color_line">Stock</label>
                    <input type="number" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required />
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
                            $selected = ($category_row['category_id'] == $product['category_id']) ? 'selected' : '';
                            echo '<option value="' . $category_row['category_id'] . '" ' . $selected . '>' . $category_row['name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="input-container">
                    <label for="image" class="color_line">Product Image (leave blank to keep current)</label>
                    <input style="color:#121212" type="file" name="image" />
                </div>
                <button style=" width: 100%; margin-left: 0px;" type="submit" name="update" class="button create">Update Product</button>
                <button type="button" class="button close" onclick="window.location.href='product.php'">Back to Dashboard</button>
            </form>
        </div>
    </div>
</body>

</html>
