<?php
// إعدادات قاعدة البيانات
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "e-commerce";

// إنشاء اتصال بـ MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من وجود أخطاء في الاتصال
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// تحديد معرّف المنتج أو أي معايير لتحديد الصورة المطلوبة
$product_id = '2'; // مثال: استخدم معرّف المنتج الذي ترغب في عرض صورته

// استعلام لجلب مسار الصورة من قاعدة البيانات
$sql = 'SELECT image FROM products WHERE product_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$stmt->bind_result($imagePath);
$stmt->fetch();
$stmt->close();

// التحقق من وجود مسار الصورة
if ($imagePath):
    $imagePath = htmlspecialchars($imagePath); // تعقيم المسار لعرضه بأمان
else:
    $imagePath = ''; // إذا لم تكن الصورة موجودة، اترك المسار فارغاً
endif;

// إغلاق الاتصال
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Image</title>
</head>
<body>
    <h1>View Image</h1>
    <?php if (!empty($imagePath)): ?>
        <img src="<?php echo $imagePath; ?>" alt="Image from database" style="max-width: 100%; height: auto;">
    <?php else: ?>
        <p>No image available.</p>
    <?php endif; ?>
</body>
</html>
