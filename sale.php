<?php
include 'config/db_connect.php';    
include 'includes/header.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Array of discount percentages
$discounts = [50, 40, 30, 20, 10];
?>

<main class="container"> 
    <br>
    <div class="sale_container">
        <br>
        <h1>Our Offers</h1>
        <br>
        <!-- Dropdown menu for selecting discount percentage -->
        <div class="mb-1">
            <select id="discount-filter" class="form-select" onchange="filterDiscount()">
                <option value="">Select Discount</option>
                <?php foreach ($discounts as $discount): ?>
                    <option value="<?php echo $discount; ?>"><?php echo $discount; ?>%</option>
                <?php endforeach; ?>
            </select>
        </div>
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
        echo "<br> <br> <h2>Discount {$discountPercentage}%</h2> <br> <br>";

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
                                <button class="btn btn-secondary add-to-cart-btn" data-product-id="<?php echo htmlspecialchars($row["product_id"]); ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }

            echo '</div>'; 
        } else {
            echo "<p>No products with Discount {$discountPercentage}%.</p>";
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
    .form-select { 
        width: 20%;
    }
</style>
