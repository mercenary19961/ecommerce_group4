<?php
session_start();
include 'config/db_connect.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT products.*, category.name AS category_name FROM products 
        JOIN category ON products.category_id = category.category_id 
        WHERE products.product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

$sql_comments = "SELECT comments.*, users.username FROM comments 
                 JOIN users ON comments.user_id = users.user_id 
                 WHERE comments.product_id = ?";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param('i', $product_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_SESSION['user_id'])) {
    $comment = htmlspecialchars(trim($_POST['comment']));
    $user_id = $_SESSION['user_id'];

    $sql_insert = "INSERT INTO comments (product_id, user_id, comment) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('iis', $product_id, $user_id, $comment);
    $stmt_insert->execute();
    
    header("Location: view_product.php?id=$product_id");
    exit();
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <style>
        .product-details-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .product-image img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .product-details {
            padding-left: 20px;
        }
        .comments-section {
            background-color: #eef2f3;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .comments-section h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .comment {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            background-color: #e0e0e0;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .comment:last-child {
            border-bottom: none;
        }
        .comment p {
            margin: 0;
        }
        .comment .comment-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: green;
        }
        .comment-meta-username {
            color: #404040;
        }
        .comment-color {
            color: #4b5563;
        }
        .comment .comment-meta .comment-date {
            color: #666;
            font-size: 0.85rem;
        }
        .add-comment {
            margin-bottom: 20px;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container product-details-container">
        <div class="row">
            <div class="col-md-6 product-image">
                <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="col-md-6 product-details">
                <div class="button-group">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <form method="post" action="cart.php">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <input type="hidden" name="action" value="add">
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                    </form>
                </div>
                <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
                <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
                <p><strong>Stock:</strong> <?php echo htmlspecialchars($product['stock']); ?></p>
                <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12 comments-section">
                <h2>Comments</h2>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post" class="add-comment">
                        <div class="form-group">
                            <label for="comment">Add a comment:</label>
                            <textarea name="comment" id="comment" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">Submit</button>
                    </form>
                <?php else: ?>
                    <p><a href="login.php">Log in</a> to add a comment.</p>
                <?php endif; ?>

                <?php while ($comment = $result_comments->fetch_assoc()): ?>
                    <div class="comment">
                        <div class="comment-meta">
                            <p class="comment-meta-username"><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong></p>
                            <p class="comment-date"><?php echo htmlspecialchars($comment['created_at']); ?></p>
                        </div>
                        <p class="comment-color"><?php echo htmlspecialchars($comment['comment']); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>
