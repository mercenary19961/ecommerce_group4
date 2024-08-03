<?php
include 'config/db_connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$category = isset($_GET['category']) ? $_GET['category'] : 'All';
$price_min = isset($_GET['price_min']) ? $_GET['price_min'] : 0;
$price_max = isset($_GET['price_max']) ? $_GET['price_max'] : 3000;
$in_stock = isset($_GET['in_stock']) ? $_GET['in_stock'] : 'All';
$discount = isset($_GET['discount']) ? $_GET['discount'] : 'All';
$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';

$sql = "SELECT products.*, category.name AS category_name, discount.discount_amount FROM products 
        JOIN category ON products.category_id = category.category_id 
        LEFT JOIN discount ON products.discount_id = discount.discount_id 
        WHERE 1=1";

if ($category !== 'All') {
    $sql .= " AND category.name = '$category'";
}
if ($price_min !== '') {
    $sql .= " AND products.price >= $price_min";
}
if ($price_max !== '') {
    $sql .= " AND products.price <= $price_max";
}
if ($in_stock !== 'All') {
    $sql .= " AND products.stock > 0";
}
if ($discount !== 'All') {
    $sql .= " AND discount.discount_amount = $discount";
}
if ($search_term !== '') {
    $sql .= " AND (products.name LIKE '%$search_term%' OR category.name LIKE '%$search_term%')";
}

$result = $conn->query($sql);
$result_count = $result->num_rows; // Count the number of results

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1; // Default quantity to 1

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    header("Location: products.php");
    exit();
}

include 'includes/header.php';
?>

<main class="container">
    <h1>Store</h1>
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
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <label for="search_term">Search</label>
                <input type="text" name="search_term" id="search_term" class="form-control" value="<?php echo htmlspecialchars($search_term); ?>">
            </div>
            <div class="col-md-3">
                <label for="discount">Discount</label>
                <select name="discount" id="discount" class="form-control">
                    <option value="All" <?php echo ($discount == 'All') ? 'selected' : ''; ?>>All</option>
                    <option value="10" <?php echo ($discount == '10') ? 'selected' : ''; ?>>10%</option>
                    <option value="20" <?php echo ($discount == '20') ? 'selected' : ''; ?>>20%</option>
                    <option value="30" <?php echo ($discount == '30') ? 'selected' : ''; ?>>30%</option>
                    <option value="40" <?php echo ($discount == '40') ? 'selected' : ''; ?>>40%</option>
                    <option value="50" <?php echo ($discount == '50') ? 'selected' : ''; ?>>50%</option>
                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="products.php" class="btn btn-secondary">Clear Filter</a>
            </div>
            <div class="col-md-9">
                <p class="text-right">Found <?php echo $result_count; ?> results</p>
            </div>
        </div>
    </form>
    <div class="row">
        <?php 
        while ($row = $result->fetch_assoc()): 
            $price = $row['price'];
            if ($row['discount_amount']) {
                $discounted_price = $price - ($price * ($row['discount_amount'] / 100));
            }
        ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="images/<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['name']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['name']; ?></h5>
                        <?php if ($row['discount_amount']): ?>
                            <p class="card-text">
                                <span class="text-danger">$<?php echo number_format($discounted_price, 2); ?></span>
                                <span class="text-muted"><s>$<?php echo number_format($price, 2); ?></s></span>
                            </p>
                            <p class="card-text text-danger">Discount: <?php echo $row['discount_amount']; ?>%</p>
                        <?php else: ?>
                            <p class="card-text">$<?php echo number_format($price, 2); ?></p>
                        <?php endif; ?>
                        <?php if ($row['stock'] <= 0): ?>
                            <p class="card-text text-warning">Out of Stock</p>
                        <?php endif; ?>
                        <div class="card_buttons">
                            <?php if ($row['stock'] > 0): ?>
                                <a href="view_product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary">Check Product</a>
                                <form method="POST" action="products.php" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
