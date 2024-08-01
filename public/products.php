<?php
include '../config/db_connect.php';

<<<<<<< Updated upstream
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit();
// }

=======
>>>>>>> Stashed changes
$category = isset($_GET['category']) ? $_GET['category'] : 'All';
$price_min = isset($_GET['price_min']) ? $_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) ? $_GET['price_max'] : 3000;
$in_stock = isset($_GET['in_stock']) ? $_GET['in_stock'] : 'All';

<<<<<<< Updated upstream
$sql = "SELECT products.*, category.name AS category_name FROM products JOIN category ON products.category_id = category.category_id WHERE 1=1";

if ($category !== 'All') {
    $sql .= " AND category.category_id = $category";
=======
$sql = "SELECT products.*, category.name AS category_name FROM products 
        JOIN category ON products.category_id = category.category_id 
        WHERE 1=1";

if ($category !== 'All') {
    $sql .= " AND category.name = '$category'";
>>>>>>> Stashed changes
}
if ($price_min !== '') {
    $sql .= " AND products.price >= $price_min";
}
if ($price_max !== '') {
    $sql .= " AND products.price <= $price_max";
}
if ($in_stock !== 'All') {
<<<<<<< Updated upstream
    $sql .= " AND products.in_stock = $in_stock";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
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
                        <option value="All">All</option>
                        <option value="1">Mobile</option>
                        <option value="2">Watch</option>
                        <option value="3">Console</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="price_min">Price (Min)</label>
                    <input type="number" name="price_min" id="price_min" class="form-control" placeholder="0" value="<?= $price_min ?>">
                </div>
                <div class="col-md-3">
                    <label for="price_max">Price (Max)</label>
                    <input type="number" name="price_max" id="price_max" class="form-control" placeholder="3000" value="<?= $price_max ?>">
                </div>
                <div class="col-md-3">
                    <label for="in_stock">In Stock</label>
                    <select name="in_stock" id="in_stock" class="form-control">
                        <option value="All">All</option>
                        <option value="1">In Stock</option>
                        <option value="0">Out of Stock</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Filter</button>
        </form>

        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="../images/<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= $row['name'] ?></h5>
                            <p class="card-text">$<?= $row['price'] ?></p>
                            <a href="#" class="btn btn-primary">Add to Cart</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
=======
    $sql .= " AND products.stock > 0";
}

$result = $conn->query($sql);

include '../includes/header.php';
?>
<main class="container">
    <h1>Products</h1>
    <form method="GET" action="products.php" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="category">Category</label>
                <select name="category" id="category" class="form-control">
                    <option value="All" <?php echo ($category == 'All') ? 'selected' : ''; ?>>All</option>
                    <option value="Phones" <?php echo ($category == 'Phones') ? 'selected' : ''; ?>>Phones</option>
                    <option value="Tablets" <?php echo ($category == 'Tablets') ? 'selected' : ''; ?>>Tablets</option>
                    <option value="Accessories" <?php echo ($category == 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                    <option value="Mac" <?php echo ($category == 'Mac') ? 'selected' : ''; ?>>Mac</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="price_min">Price (Min)</label>
                <input type="number" name="price_min" id="price_min" class="form-control" value="<?php echo $price_min; ?>">
            </div>
            <div class="col-md-3">
                <label for="price_max">Price (Max)</label>
                <input type="number" name="price_max" id="price_max" class="form-control" value="<?php echo $price_max; ?>">
            </div>
            <div class="col-md-3">
                <label for="in_stock">In Stock</label>
                <select name="in_stock" id="in_stock" class="form-control">
                    <option value="All" <?php echo ($in_stock == 'All') ? 'selected' : ''; ?>>All</option>
                    <option value="1" <?php echo ($in_stock == '1') ? 'selected' : ''; ?>>In Stock</option>
                </select>
            </div>
>>>>>>> Stashed changes
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="products.php" class="btn btn-secondary">Clear Filter</a>
            </div>
        </div>
    </form>
    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="../images/<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['name']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['name']; ?></h5>
                        <p class="card-text">$<?php echo $row['price']; ?></p>
                        <a href="#" class="btn btn-primary">Add to Cart</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
