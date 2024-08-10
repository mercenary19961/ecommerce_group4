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

$results_per_page = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

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

$sql .= " LIMIT $results_per_page OFFSET $offset";
$result = $conn->query($sql);

$sql_count = "SELECT COUNT(*) as count FROM products 
              JOIN category ON products.category_id = category.category_id 
              LEFT JOIN discount ON products.discount_id = discount.discount_id 
              WHERE 1=1";
if ($category !== 'All') {
    $sql_count .= " AND category.name = '$category'";
}
if ($price_min !== '') {
    $sql_count .= " AND products.price >= $price_min";
}
if ($price_max !== '') {
    $sql_count .= " AND products.price <= $price_max";
}
if ($in_stock !== 'All') {
    $sql_count .= " AND products.stock > 0";
}
if ($discount !== 'All') {
    $sql_count .= " AND discount.discount_amount = $discount";
}
if ($search_term !== '') {
    $sql_count .= " AND (products.name LIKE '%$search_term%' OR category.name LIKE '%$search_term%')";
}

$count_result = $conn->query($sql_count);
$row = $count_result->fetch_assoc();
$total_results = $row['count'];
$total_pages = ceil($total_results / $results_per_page);

include 'includes/header.php';
?>

<main class="container">
    <br>
    <h1>Products Store</h1> <br>
    <form method="GET" action="products.php" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="search_term">Search</label>
                <input type="text" name="search_term" id="search_term" placeholder="Type letters to search" class="form-control" value="<?php echo htmlspecialchars($search_term); ?>">
            </div>
            <div class="col-md-3">
                <label for="price_min">Price (Min)</label>
                <input type="number" name="price_min" id="price_min" class="form-control" value="<?php echo $price_min; ?>" step="100">
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
                <label for="price_max">Price (Max)</label>
                <input type="number" name="price_max" id="price_max" class="form-control" value="<?php echo $price_max; ?>" step="100">
            </div>
            <div class="col-md-6 d-flex align-items-end justify-content-between">
                <div class="d-flex">
                    <button type="submit" class="btn btn-primary" style="margin-right: 20px !important;">Filter</button>
                    <a href="products.php" class="btn btn-secondary" style="margin-right: 20px !important;">Clear Filter</a>
                    <p class="ml-2 mb-0 align-self-end">Found <span style="color: #72aec8;"> <?php echo $total_results; ?> </span>  results</p>
                </div>
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
                                <form id="add-to-cart-form-<?php echo $row['product_id']; ?>" class="add-to-cart-form d-inline">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <button type="button" class="btn btn-secondary add-to-cart-btn" data-product-id="<?php echo $row['product_id']; ?>">Add to Cart</button>
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
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                    <a class="page-link" href="products.php?page=<?php echo $i; ?>&category=<?php echo $category; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&in_stock=<?php echo $in_stock; ?>&discount=<?php echo $discount; ?>&search_term=<?php echo $search_term; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</main>

<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Attach event listeners to "Add to Cart" buttons
        document.querySelectorAll('.add-to-cart-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'add_to_cart.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.cartCount !== undefined) {
                            // Update cart count badge
                            const cartLink = document.querySelector('.fa-shopping-cart');
                            let cartBadge = cartLink.parentNode.querySelector('.badge');

                            if (cartBadge) {
                                // Update existing badge count
                                cartBadge.textContent = response.cartCount;
                            } else {
                                // Create a new badge if it doesn't exist
                                cartBadge = document.createElement('span');
                                cartBadge.className = 'badge rounded-pill bg-danger';
                                cartBadge.style.position = 'absolute';
                                cartBadge.style.top = '-0.1px';
                                cartBadge.style.right = '-5px';
                                cartBadge.textContent = response.cartCount;
                                cartLink.parentNode.appendChild(cartBadge);
                            }
                        }
                    }
                };
                xhr.send('product_id=' + productId);
            });
        });
    });
</script>

<style>
    .pagination .page-item.active .page-link {
        background-color: #72aec8;
        border-color: #72aec8;
    }
</style>
