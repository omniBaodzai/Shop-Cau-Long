<?php
session_start();
include '../connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/dang-nhap.php');
    exit;
}

// Xử lý hủy đơn hàng
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $order_id = intval($_POST['cancel_order_id']);

    // Xác định xem yêu cầu có phải AJAX không
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Lấy thông tin đơn hàng từ DB
    $stmt = $conn->prepare("SELECT status, payment_status, payment_method FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if ($order && $order['status'] === 'Chờ xử lý') {
        $new_status = 'Hủy';
        $new_payment_status = $order['payment_status'];

        if (
            $order['payment_method'] === 'Bank' && 
            $order['payment_status'] === 'Đã thanh toán'
        ) {
            $new_payment_status = 'Hoàn tiền';
        }

        if (
            $order['payment_method'] === 'COD' &&
            $order['payment_status'] === 'Đã thanh toán'
        ) {
            $new_payment_status = 'Hoàn tiền';
        }

        // Cập nhật trạng thái
        $update = $conn->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
        $update->bind_param("ssi", $new_status, $new_payment_status, $order_id);
        $update->execute();

        if ($is_ajax) {
            // Trả về JSON cho AJAX
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Đơn hàng đã được hủy thành công.',
                'order_id' => $order_id,
                'new_status' => $new_status,
                'new_payment_status' => $new_payment_status
            ]);
            exit;
        } else {
            // Fallback cho non-AJAX
            $success_message = 'Đơn hàng đã được hủy thành công.';
        }
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Bạn không thể hủy đơn hàng này.'
            ]);
            exit;
        } else {
            $error_message = 'Bạn không thể hủy đơn hàng này.';
        }
    }
}

include '../includes/header.php'; // Header (included after redirect check to avoid output issues)

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ session

// Truy vấn danh sách đơn hàng của người dùng
$sqlOrders = "SELECT id, order_date, final_total, payment_method, status, payment_status FROM orders WHERE user_id = ?";
$stmtOrders = $conn->prepare($sqlOrders);
$stmtOrders->bind_param("i", $user_id);
$stmtOrders->execute();
$resultOrders = $stmtOrders->get_result();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đơn hàng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css">
    <script src="../assets/js/tra-cuu-don-hang.js"></script>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .order-main {
            flex: 1;
        }

        .order-container {
            width: fit-content;
            max-width: 100%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-family: "Segoe UI", Tahoma, sans-serif;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }

        .order-table th, .order-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            white-space: nowrap;
        }

        .order-table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .btn-detail, .btn-cancel {
            display: inline-block;
            padding: 7px 10px;
            color: #fff;
            text-decoration: none;
            border-radius: 15px;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
            font-weight: 500;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }

        .btn-detail {
            background: linear-gradient(90deg, #43a047 0%, #e63946 100%);
        }

        .btn-detail:hover {
            background-color: #0056b3;
        }

        .btn-cancel {
            background: linear-gradient(90deg, #e63946 0%, #ff6b6b 100%);
        }

        .btn-cancel:hover {
            background-color: #c82333;
        }

        .status-cancelled {
            color: #e63946;
            font-weight: 500;
        }

        .status-non-cancellable {
            color: #999;
            font-weight: 500;
        }

        .success-message, .error-message {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border-radius: 8px;
            font-weight: bold;
        }

        .success-message {
            color: #27ae60;
            background-color: #e6ffee;
        }

        .error-message {
            color: #e63946;
            background-color: #ffebeb;
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    <main class="order-main">
        <div class="order-container">
            <h1>Danh sách đơn hàng của bạn</h1>
            <div id="message-container">
                <?php if (!empty($success_message)): ?>
                    <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
                <?php endif; ?>
                <?php if (!empty($error_message)): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
            </div>
            <?php if ($resultOrders->num_rows > 0): ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Phương thức thanh toán</th>
                            <th>Trạng thái</th>
                            <th>Thanh toán</th>
                            <th>Chi tiết</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $resultOrders->fetch_assoc()): ?>
                            <tr data-order-id="<?= htmlspecialchars($order['id']) ?>">
                                <td><?= htmlspecialchars($order['id']) ?></td>
                                <td><?= htmlspecialchars($order['order_date']) ?></td>
                                <td><?= number_format($order['final_total'], 0, ',', '.') ?> đ</td>
                                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                <td class="status-cell"><?= htmlspecialchars($order['status']) ?></td>
                                <td class="payment-status-cell"><?= htmlspecialchars($order['payment_status']) ?></td>
                                <td>
                                    <a href="chi-tiet-don-hang.php?order_id=<?= $order['id'] ?>" class="btn-detail">Xem chi tiết</a>
                                </td>
                                <td class="action-cell">
                                    <?php if ($order['status'] === 'Chờ xử lý'): ?>
                                        <form method="post" class="cancel-form" data-order-id="<?= $order['id'] ?>">
                                            <input type="hidden" name="cancel_order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" class="btn-cancel">Hủy đơn</button>
                                        </form>
                                    <?php elseif ($order['status'] === 'Hủy'): ?>
                                        <span class="status-cancelled">Đã hủy</span>
                                    <?php else: ?>
                                        <span class="status-non-cancellable">Không thể hủy</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center;">Bạn chưa có đơn hàng nào.</p>
            <?php endif; ?>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</div>

</body>
</html>