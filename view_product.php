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
        .product-details {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .product-details img {
            max-width: 100%;
            height: auto;
            margin-bottom: 20px;
        }
        .product-details h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .product-details p {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #4b5563;
        }
        .comments-section {
            margin-top: 40px;
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
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container product-details">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <img src="images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
        <p><?php echo htmlspecialchars($product['description']); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
        <p><strong>Stock:</strong> <?php echo htmlspecialchars($product['stock']); ?></p>

        <div class="comments-section">
            <h2>Comments</h2>
            <?php while ($comment = $result_comments->fetch_assoc()): ?>
                <div class="comment">
                    <div class="comment-meta">
                        <p class="comment-meta-username"><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong></p>
                        <p class="comment-date"><?php echo htmlspecialchars($comment['created_at']); ?></p>
                    </div>
                    <p class="comment-color"><?php echo htmlspecialchars($comment['comment']); ?></p>
                </div>
            <?php endwhile; ?>

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
        </div>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>
