<?php
include 'config/db_connect.php';    
include 'includes/header.php';
?>

<main class="container"> 
    <br> <br> 
    <h1>OUR SALES</h1>

    <!-- Dropdown menu for selecting discount percentage -->
    <div class="mb-4">
        <label for="discount-filter" class="form-label">Select Discount:</label>
        <select id="discount-filter" class="form-select" onchange="filterDiscount()" >
            <option value="">Select Discount</option>
            <option value="10">10%</option>
            <option value="20">20%</option>
            <option value="30">30%</option>
            <option value="40">40%</option>
            <option value="50">50%</option>
        </select>
    </div>

    <br> <br>  

    <?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Array of discount percentages
    $discounts = [50, 40, 30, 20, 10];

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
                echo '<div class="col-md-4 mb-4">'; 
                echo '<div class="card h-100">'; 
                echo '<div class="card-body">'; 
                
                echo "<div class='image-holder'>";
                echo "<img style='width: 400px; height: 300px;' src='images/" . htmlspecialchars($row["image"]) . "' alt='product-item' class='img-fluid'>";
                echo "</div>"; // image-holder
                
                echo '<h5 class="card-title">' . htmlspecialchars($row["name"]) . '</h5>';
                
                $oldPrice = (float) $row["price"];
                $discountAmount = (float) $row["discount_amount"];
                $newPrice = $oldPrice - ($oldPrice * ($discountAmount / 100));
                
                echo '<p class="card-text">before : ' . htmlspecialchars($oldPrice) . ' $ | after : ' . htmlspecialchars($newPrice) . ' $</p>';
                
                // Add to Cart button
                echo '<form method="post" action="add_to_cart.php">';
                echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($row["product_id"]) . '">';
                echo '<button type="submit" class="btn btn-primary">Add to Cart</button>';
                echo '</form>';
                
                echo '</div>'; 
                echo '</div>'; 
                echo '</div>'; 
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
    .form-select{ 
        width: 20%;
    }
</style>
