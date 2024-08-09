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
    .modal-dialog {
        max-width: 900px;
    }

    .modal-content {
        padding: 20px;
    }

    .modal-header {
        background-color: #f2f2f2;
        border-bottom: 1px solid #ccc;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        border-top: 1px solid #ccc;
    }

    .order-details-table th,
    .order-details-table td {
        padding: 8px;
        text-align: left;
    }

    .order-details-table th {
        background-color: #f2f2f2;
    }

    /* Adjust the column widths */
    .order-details-table th:nth-child(1),
    .order-details-table td:nth-child(1) {
        width: 30%; /* Name column */
    }

    .order-details-table th:nth-child(2),
    .order-details-table td:nth-child(2) {
        width: 15%; /* Quantity column */
    }

    .order-details-table th:nth-child(3),
    .order-details-table td:nth-child(3) {
        width: 20%; /* Price Before Discount column */
    }

    .order-details-table th:nth-child(4),
    .order-details-table td:nth-child(4) {
        width: 20%; /* Price After Discount column */
    }

    .order-details-table th:nth-child(5),
    .order-details-table td:nth-child(5) {
        width: 15%; /* Image column */
    }
    .btn-details {
        color: white;
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
                <th>Payment Method</th>
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
                        <button class="btn btn-info btn-details " data-order-id="<?php echo $row['order_id']; ?>" data-bs-toggle="modal" data-bs-target="#orderDetailsModal">Details</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<!-- Modal Structure -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Added modal-lg class for a wider modal -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Order details will be loaded here via AJAX -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
    $(document).ready(function () {
        $('.btn-details').on('click', function () {
            var orderId = $(this).data('order-id');

            $.post('get_order_details.php', {order_id: orderId}, function (response) {
                if (response.success) {
                    $('#orderDetailsModal .modal-body').html(response.data);
                }
            }, 'json');
        });
    });
</script>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include 'includes/footer.php'; ?>
