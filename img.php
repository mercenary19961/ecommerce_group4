<?php
// إعدادات قاعدة البيانات
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "e-commerce";

$conn = new mysqli($host, $username, $password, $dbname);

// التحقق من وجود أخطاء في الاتصال
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
//sdjfndsjfbdsjb
// القيم التي سيتم إدخالها
$product_id = '1';
$name = 'Example Product';
$description = 'This is an example product description.';
$price = '19.99';
$stock = '100';
$category_id = '2';
$image = '';

// تحضير الاستعلام SQL مع علامات مجهولة
$stmt = $conn->prepare('INSERT INTO products (product_id, name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)');

// ربط القيم بالعلامات المجهولة
$stmt->bind_param('issdiss', $product_id, $name, $description, $price, $stock, $category_id, $image);

// تنفيذ الاستعلام
if ($stmt->execute()) {
    echo 'Product inserted successfully!';
} else {
    echo 'Error: ' . $stmt->error;
}

// إغلاق الاستعلام
$stmt->close();

// إغلاق الاتصال
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload and View Image</title>
</head>
<body>
    <h1>Upload Image</h1>
    <form action="index.php" method="post" enctype="multipart/form-data">
        <input type="file" name="image" accept="image/*" required>
        <input type="submit" value="Upload Image">
    </form>

    <?php if ($imagePath): ?>
        <h1>View Image</h1>
        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Image from database" style="max-width: 100%; height: auto;">
    <?php endif; ?>
</body>
</html>
