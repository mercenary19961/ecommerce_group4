<?php
session_start();
include 'config/db_connect.php';

$user_id = $_SESSION['user_id'];

$sql = "SELECT order_id, order_date, total, payment_method, 
        (SELECT SUM(products.price * order_items.quantity) 
         FROM order_items 
         JOIN products ON order_items.product_id = products.product_id 
         WHERE order_items.order_id = orders.order_id) as total_before_discounts 
        FROM orders 
        WHERE user_id = ? 
        ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Purchase History</title>
    <style>
        .details-row {
            display: none;
        }
        .details-row.show {
            display: table-row;
        }
        .order-details-table {
            margin-top: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ccc;
        }
        .order-details-table th, .order-details-table td {
            padding: 8px;
            text-align: left;
        }
        .order-details-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<main class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Purchase History</h1>
        <a href="user.php" class="btn btn-secondary">Back</a>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Order Date</th>
                <th>Total Before Discounts</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                    <td><?php echo htmlspecialchars('$' . number_format($row['total_before_discounts'], 2)); ?></td>
                    <td><?php echo htmlspecialchars('$' . number_format($row['total'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                    <td>
                        <button class="btn btn-info btn-details" data-order-id="<?php echo $row['order_id']; ?>">Details</button>
                    </td>
                </tr>
                <tr class="details-row" id="details-<?php echo $row['order_id']; ?>">
                    <td colspan="6">
                        <div class="order-details"></div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
<script>
    $(document).ready(function () {
        $('.btn-details').on('click', function () {
            var orderId = $(this).data('order-id');
            var detailsRow = $('#details-' + orderId);

            if (detailsRow.hasClass('show')) {
                detailsRow.removeClass('show');
            } else {
                $.post('get_order_details.php', {order_id: orderId}, function (response) {
                    if (response.success) {
                        detailsRow.find('.order-details').html(response.data);
                        detailsRow.addClass('show');
                    }
                }, 'json');
            }
        });
    });
</script>
</body>
</html>

<?php include 'includes/footer.php'; ?>
