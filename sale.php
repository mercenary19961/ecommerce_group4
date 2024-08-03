<?php
include 'config/db_connect.php';    
include 'includes/header.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Array of discount percentages
$discounts = [50, 40, 30, 20, 10];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1; // Default quantity to 1

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    header("Location: sale.php");
    exit();
}
?>

<main class="container"> 
    <br>
    <h1>OUR SALES</h1>

    <!-- Dropdown menu for selecting discount percentage -->
    <div class="mb-1">
        <label for="discount-filter" class="form-label">Select Discount:</label>
        <select id="discount-filter" class="form-select" onchange="filterDiscount()" >
            <option value="">Select Discount</option>
            <?php foreach ($discounts as $discount): ?>
                <option value="<?php echo $discount; ?>"><?php echo $discount; ?>%</option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
    foreach ($discounts as $discountPercentage) {
        // Prepare SQL query
        $sql = "SELECT products.*, discount.discount_amount 
                FROM products 
                INNER JOIN discount ON products.discount_id = discount.discount_id
                WHERE discount.discount_amount = ?";
                
        // Prepare statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $discountPercentage);
        $stmt->execute();
        $result = $stmt->get_result();

        // Display section
        echo "<div id='discount-{$discountPercentage}' class='discount-section'>";
        echo "<br> <br> <h2>{$discountPercentage}%</h2> <br> <br>";

        if ($result->num_rows > 0) {
            echo '<div class="row">'; 

            while ($row = $result->fetch_assoc()) {
                $oldPrice = (float) $row["price"];
                $discountAmount = (float) $row["discount_amount"];
                $newPrice = $oldPrice - ($oldPrice * ($discountAmount / 100));
                ?>
                <div class="col-md-4 mb-2">
                    <div class="card h-100">
                        <img src="images/<?php echo htmlspecialchars($row["image"]); ?>" alt="product-item" class="card-img-top img-fluid">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row["name"]); ?></h5>
                            <p class="card-text">
                                <span class="text-danger">$<?php echo number_format($newPrice, 2); ?></span>
                                <span class="text-muted"><s>$<?php echo number_format($oldPrice, 2); ?></s></span>
                            </p>
                            <p class="card-text text-danger">Discount: <?php echo htmlspecialchars($row["discount_amount"]); ?>%</p>
                            <div class="card_buttons">
                                <a href="view_product.php?id=<?php echo htmlspecialchars($row["product_id"]); ?>" class="btn btn-primary">Check Product</a>
                                <form method="post" action="sale.php" class="d-inline">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row["product_id"]); ?>">
                                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }

            echo '</div>'; 
        } else {
            echo "<p>No products with {$discountPercentage}% discount.</p>";
        }

        echo '</div>'; // discount-section
    }

    $conn->close();
    ?>
</main>

<?php include 'includes/footer.php'; ?>

<script>
    function filterDiscount() {
        var selectedValue = document.getElementById('discount-filter').value;
        var sections = document.querySelectorAll('.discount-section');

        sections.forEach(function(section) {
            if (selectedValue === "" || section.id === 'discount-' + selectedValue) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    }

    // Initialize all sections to be visible on page load
    document.addEventListener('DOMContentLoaded', function() {
        filterDiscount();
    });
</script>
<style> 
    .form-select { 
        width: 20%;
    }
</style>
