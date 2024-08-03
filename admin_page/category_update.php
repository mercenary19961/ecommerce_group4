<?php
session_start();
include 'config/connection.php';

// Check if category_id is provided in the URL
if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];

    // Fetch category details
    $stmt = $conn->prepare("SELECT * FROM category WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    $stmt->close();

    if (!$category) {
        echo "<script>
            Swal.fire({
                title: 'Error!',
                text: 'Category not found.',
                icon: 'error'
            }).then(() => {
                window.location.href = 'category.php'; // Redirect to the categories page
            });
        </script>";
        exit();
    }
} else {
    echo "<script>
        Swal.fire({
            title: 'Error!',
            text: 'Category ID is missing.',
            icon: 'error'
        }).then(() => {
            window.location.href = 'category.php'; // Redirect to the categories page
        });
    </script>";
    exit();
}

// Handle updating the category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];

    // Update category details in database
    $stmt = $conn->prepare("UPDATE category SET name = ? WHERE category_id = ?");
    $stmt->bind_param("si", $name, $category_id);

    if ($stmt->execute()) {
        echo "<script>
            Swal.fire({
                title: 'Success!',
                text: 'Category updated successfully',
                icon: 'success'
            }).then(() => {
                window.location.href = 'category.php'; // Redirect to the categories page
            });
        </script>";
        header('location:categories.php');
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update Category</title>
    <!-- Include SweetAlert and your CSS files here -->
    <link rel="stylesheet" href="css/styles.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
     body {
    font-family: Arial, sans-serif;
    background-color: #263043; /* Light gray background */
    color: #1d2634;
    margin: 0;
    padding: 0;
}

.form-container {
    width: 90%;
    max-width: 600px;
    margin: 50px auto;
    background: #1d2634; /* White background for the form */
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.form h2 {
    margin-bottom: 20px;
    color:#e6e8ed; /* Warm gold color */
    text-align: center;
}

.input-container {
    margin-bottom: 20px; /* زيادة المسافة بين الحقول */
    position: relative;
}

.input-container input,
.input-container textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
    background: #1d2634; /* Light gray background for inputs */
    color: #e6e8ed;
    margin-top: 10px; /* إضافة مسافة بين الـ input و label */
}

.input-container label {
    position: absolute;
    top: 10px;
    left: 12px;
    font-size: 14px;
    color: #e6e8ed;
    transition: 0.2s ease;
    pointer-events: none;
}

.input-container input:focus+label,
.input-container textarea:focus+label,
.input-container input:not(:placeholder-shown)+label,
.input-container textarea:not(:placeholder-shown)+label {
    top: -10px;
    left: 8px;
    font-size: 15px;
    color: #e6e8ed; /* Warm gold color for focused label */
}

.button {
    display: inline-block;
    background-color: #ff6f61; /* Warm gold color */
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
    transition: background-color 0.3s ease;
    text-decoration: none;
    margin: 5px;
}

.button:hover {
    background-color: #e55d50; /* Slightly darker shade of gold */
}

.button.update {
    background-color: #4caf50; /* Green for update button */
}

.button.update:hover {
    background-color: #388e3c; /* Darker green for hover */
}

.button.back-to-dashboard {
    background-color: #2196f3; /* Blue for back to dashboard button */
    color: #ffffff;
}

.button.back-to-dashboard:hover {
    background-color: #1976d2; /* Darker blue for hover */
}
</style>

<body>
    <div class="form-container">
        <form class="form" method="POST">
            <h2>Update Category</h2>
            <div class="input-container">
                <input type="text" name="name" placeholder="Category Name" value="<?php echo htmlspecialchars($category['name']); ?>" required />
                <label for="name">Category Name</label>
            </div>
            <button type="submit" name="update" class="button update">Update Category</button>
            <a href="categories.php" class="button back-to-dashboard">Back to Dashboard</a>
        </form>
    </div>
</body>

</html>