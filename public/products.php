<?php
session_start();
include '../config/db_connect.php';

// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit();
// }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Store</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <script src="../js/jquery-1.11.0.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="container">
        <h1>Store</h1>
        <form method="GET" action="products.php" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="form-control">
                        <option value="">All</option>
                        <option value="Mobile">Mobile</option>
                        <option value="Watch">Watch</option>
                        <option value="Console">Console</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="price_min">Price (Min)</label>
                    <input type="number" name="price_min" id="price_min" class="form-control" placeholder="0">
                </div>
                <div class="col-md-3">
                    <label for="price_max">Price (Max)</label>
                    <input type="number" name="price_max" id="price_max" class="form-control" placeholder="3000">
                </div>
                <div class="col-md-3">
                    <label for="in_stock">In Stock</label>
                    <select name="in_stock" id="in_stock" class="form-control">
                        <option value="">All</option>
                        <option value="1">In Stock</option>
                        <option value="0">Out of Stock</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <div class="row">
            <?php
            $category = isset($_GET['category']) ? $_GET['category'] : '';
            $price_min = isset($_GET['price_min']) ? $_GET['price_min'] : 0;
            $price_max = isset($_GET['price_max']) ? $_GET['price_max'] : 3000;
            $in_stock = isset($_GET['in_stock']) ? $_GET['in_stock'] : '';

            $sql = "SELECT * FROM products WHERE 1=1";

            if ($category) {
                $sql .= " AND category = '$category'";
            }
            if ($price_min) {
                $sql .= " AND price >= $price_min";
            }
            if ($price_max) {
                $sql .= " AND price <= $price_max";
            }
            if ($in_stock !== '') {
                $sql .= " AND in_stock = $in_stock";
            }

            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='col-md-4'>
                            <div class='card mb-4'>
                                <img src='" . $row['image'] . "' class='card-img-top' alt='" . $row['name'] . "'>
                                <div class='card-body'>
                                    <h5 class='card-title'>" . $row['name'] . "</h5>
                                    <p class='card-text'>$" . $row['price'] . "</p>
                                    <a href='#' class='btn btn-primary'>Add to Cart</a>
                                </div>
                            </div>
                          </div>";
                }
            } else {
                echo "<p>No products found</p>";
            }
            ?>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
