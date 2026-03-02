<?php
session_start(); // Start the session at the top

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Vui lòng đăng nhập để tiến hành thanh toán.';
    header('Location: ../pages/dang-nhap.php'); // Redirect to login page
    exit();
}

include '../connect.php'; // Kết nối cơ sở dữ liệu (after redirect check)
include '../includes/header.php'; 

$user_id = $_SESSION['user_id']; // Lấy ID người dùng từ session
$order_items_info = []; // Biến để lưu thông tin các sản phẩm trong đơn hàng
$order_info = null;     // Biến để lưu thông tin chung của đơn hàng
$error_message = '';    // Biến để lưu thông báo lỗi

// Lấy danh sách đơn hàng có status = 'Đã giao' của người dùng
$sql_orders = "SELECT id, order_date FROM orders WHERE user_id = ? AND status = 'Đã giao'";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
$orders = $result_orders->fetch_all(MYSQLI_ASSOC);
$stmt_orders->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['order_id']) && !empty($_POST['order_id'])) {
        $order_id = intval($_POST['order_id']); // Chuyển đổi sang số nguyên

        // Bước 1: Lấy thông tin chung của đơn hàng
        $stmt_order = $conn->prepare("
            SELECT 
                id, order_date, full_name, phone, email, address, city, district 
            FROM orders 
            WHERE id = ? AND status = 'Đã giao'
        ");
        if ($stmt_order === false) {
            $error_message = "Lỗi chuẩn bị truy vấn đơn hàng: " . $conn->error;
        } else {
            $stmt_order->bind_param("i", $order_id);
            $stmt_order->execute();
            $result_order = $stmt_order->get_result();
            if ($result_order->num_rows > 0) {
                $order_info = $result_order->fetch_assoc();
            } else {
                $error_message = "Không tìm thấy đơn hàng với Mã đơn hàng này hoặc đơn hàng chưa được giao.";
            }
            $stmt_order->close();
        }

        // Bước 2: Nếu tìm thấy đơn hàng, lấy thông tin chi tiết các sản phẩm trong đơn
        if ($order_info) {
            $stmt_items = $conn->prepare("
                SELECT 
                    oi.product_name, 
                    oi.serial_number, 
                    oi.warranty_expire_date,
                    p.warranty AS product_warranty_duration
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            if ($stmt_items === false) {
                $error_message = "Lỗi chuẩn bị truy vấn sản phẩm đơn hàng: " . $conn->error;
            } else {
                $stmt_items->bind_param("i", $order_id);
                $stmt_items->execute();
                $result_items = $stmt_items->get_result();

                if ($result_items->num_rows > 0) {
                    while ($item = $result_items->fetch_assoc()) {
                        // Tính toán trạng thái bảo hành cho từng sản phẩm
                        $current_date = new DateTime();
                        $expire_date_obj = null;
                        $warranty_status = "Không xác định";

                        if (!empty($item['warranty_expire_date'])) {
                            try {
                                $expire_date_obj = new DateTime($item['warranty_expire_date']);
                                if ($current_date <= $expire_date_obj) {
                                    $warranty_status = "Còn bảo hành";
                                } else {
                                    $warranty_status = "Hết bảo hành";
                                }
                            } catch (Exception $e) {
                                $warranty_status = "Ngày hết hạn không hợp lệ";
                            }
                        } else {
                            // Nếu warranty_expire_date chưa được lưu trong order_items, tính lại dựa trên order_date và product.warranty
                            if (!empty($item['product_warranty_duration']) && $order_info['order_date']) {
                                try {
                                    $order_date_obj = new DateTime($order_info['order_date']);
                                    $warranty_duration_parts = explode(' ', strtolower($item['product_warranty_duration']));
                                    
                                    if (count($warranty_duration_parts) >= 2) {
                                        $value = (int)$warranty_duration_parts[0];
                                        $unit = strtolower($warranty_duration_parts[1]);

                                        switch ($unit) {
                                            case 'tháng':
                                                $order_date_obj->modify("+$value months");
                                                break;
                                            case 'năm':
                                                $order_date_obj->modify("+$value years");
                                                break;
                                            default:
                                                // Đơn vị không hợp lệ, không thể tính
                                                break;
                                        }
                                        $expire_date_obj = $order_date_obj; // Cập nhật ngày hết hạn tính toán
                                        if ($current_date <= $expire_date_obj) {
                                            $warranty_status = "Còn bảo hành";
                                        } else {
                                            $warranty_status = "Hết bảo hành";
                                        }
                                    } else {
                                        $warranty_status = "Định dạng thời hạn bảo hành không hợp lệ";
                                    }
                                } catch (Exception $e) {
                                    $warranty_status = "Lỗi tính toán bảo hành";
                                }
                            } else {
                                $warranty_status = "Không có thông tin bảo hành từ sản phẩm";
                            }
                        }
                        $item['calculated_expire_date'] = $expire_date_obj ? $expire_date_obj->format('d/m/Y') : "Không xác định";
                        $item['warranty_status'] = $warranty_status;
                        $order_items_info[] = $item; // Thêm sản phẩm vào danh sách
                    }
                } else {
                    $error_message = "Đơn hàng này không có sản phẩm nào.";
                }
                $stmt_items->close();
            }
        }
    } else {
        $error_message = "Vui lòng chọn một đơn hàng.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kiểm tra bảo hành sản phẩm</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css">
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
        color: #333;
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

    .container {
        max-width: 850px;
        width: 90%;
        margin: 40px auto;
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15), 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #e0e0e0;
        overflow: hidden;
        flex: 1;
    }

    h1 {
        text-align: center;
        font-size: 2.8rem;
        color: #1d3557;
        margin-bottom: 30px;
        position: relative;
        padding-bottom: 15px;
    }

    h1::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: 0;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(90deg, #1d3557 0%, #e63946 100%);
        border-radius: 2px;
    }

    .form-group {
        margin-bottom: 25px;
        text-align: center;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;
        color: #457b9d;
        font-size: 1.1rem;
    }

    .form-group select {
        width: 70%;
        max-width: 400px;
        padding: 14px 18px;
        border: 1px solid #b0c4de;
        border-radius: 8px;
        font-size: 1.1rem;
        box-sizing: border-box;
        color: #000;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-group select:focus {
        border-color: #457b9d;
        box-shadow: 0 0 0 3px rgba(69, 123, 157, 0.2);
        outline: none;
    }

    .form-group button {
        padding: 14px 30px;
        background: linear-gradient(90deg, #1d3557 0%, #e63946 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1.2rem;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 10px rgba(29, 53, 87, 0.3);
    }

    .form-group button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(29, 53, 87, 0.4);
        background: linear-gradient(90deg, #e63946 0%, #1d3557 100%);
    }

    .error-message {
        color: #e74c3c;
        text-align: center;
        margin-top: 25px;
        font-size: 1.2rem;
        font-weight: bold;
        padding: 10px 15px;
        background-color: #ffe6e6;
        border: 1px solid #e74c3c;
        border-radius: 8px;
    }

    .order-info, .warranty-result {
        margin-top: 35px;
        padding-top: 25px;
        border-top: 1px dashed #e0e0e0;
    }

    .order-info h2, .warranty-result h2 {
        font-size: 2rem;
        color: #2c3e50;
        text-align: center;
        margin-bottom: 25px;
        position: relative;
    }

    .order-info h2::after, .warranty-result h2::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: -5px;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: #457b9d;
        border-radius: 1.5px;
    }

    .detail-box, .product-item {
        background-color: #fcfcfc;
        border: 1px solid #e9ecef;
        border-radius: 17px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .detail-box:hover, .product-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .detail-box p, .product-item p {
        margin-bottom: 8px;
        font-size: 1.05rem;
        line-height: 1.8;
    }

    .detail-box .label, .product-item .label {
        font-weight: bold;
        color: #3d5a80;
        display: inline-block;
        min-width: 200px;
        text-align: left;
    }

    .status-active {
        color: #27ae60;
        font-weight: bold;
        background-color: #e6ffee;
        padding: 5px 10px;
        border-radius: 5px;
        display: inline-block;
    }

    .status-expired {
        color: #e74c3c;
        font-weight: bold;
        background-color: #ffebeb;
        padding: 5px 10px;
        border-radius: 5px;
        display: inline-block;
    }

    .product-item h3 { 
        color: #1d3557;
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.8rem;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 8px;
    }

    footer {
        width: 100%;
        text-align: center;
        padding: 20px 0;
        background-color: #eceff1;
        color: #555;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
</style>
</head>
<body>
<div class="page-wrapper">
    <div class="container">
        <h1>Kiểm tra thông tin bảo hành</h1>

        <div class="form-container">
        <form action="" method="post">
            <div class="form-group">
                <label for="order_id">Chọn đơn hàng:</label>
                <select id="order_id" name="order_id" required>
                    <option value="">-- Chọn một đơn hàng --</option>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?= htmlspecialchars($order['id']) ?>">
                            Đơn hàng #<?= htmlspecialchars($order['id']) ?> - Ngày đặt: <?= htmlspecialchars(date('d/m/Y', strtotime($order['order_date']))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Kiểm tra</button>
            </div>
        </form>
    </div>

        <?php if (!empty($error_message)) : ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <?php if ($order_info) : // Hiển thị thông tin chung của đơn hàng ?>
            <div class="order-info">
                <h2>Thông tin đơn hàng #<?= htmlspecialchars($order_info['id']) ?></h2>
                <div class="detail-box">
                    <p><span class="label">Ngày đặt hàng:</span> <?= htmlspecialchars(date('d/m/Y', strtotime($order_info['order_date']))) ?></p>
                    <p><span class="label">Họ và tên khách hàng:</span> <?= htmlspecialchars($order_info['full_name']) ?></p>
                    <p><span class="label">Điện thoại:</span> <?= htmlspecialchars($order_info['phone']) ?></p>
                    <p><span class="label">Email:</span> <?= htmlspecialchars($order_info['email']) ?></p>
                    <p><span class="label">Địa chỉ:</span> 
                        <?= htmlspecialchars($order_info['address']) ?>, 
                        <?= htmlspecialchars($order_info['district']) ?>, 
                        <?= htmlspecialchars($order_info['city']) ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($order_items_info)) : // Hiển thị thông tin từng sản phẩm trong đơn hàng ?>
            <div class="warranty-result">
                <h2>Chi tiết sản phẩm và bảo hành</h2>
                <?php foreach ($order_items_info as $item) : ?>
                    <div class="product-item">
                        <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                        <p><span class="label">Serial Number:</span> <?= !empty($item['serial_number']) ? htmlspecialchars($item['serial_number']) : "Không có SN" ?></p>
                        <p><span class="label">Thời hạn bảo hành:</span> 
                            <?= !empty($item['product_warranty_duration']) ? htmlspecialchars($item['product_warranty_duration']) : "Không áp dụng" ?>
                        </p>
                        <p><span class="label">Ngày hết hạn bảo hành:</span> 
                            <?= htmlspecialchars($item['calculated_expire_date']) ?>
                        </p>
                        <p><span class="label">Trạng thái bảo hành:</span> 
                            <span class="<?= ($item['warranty_status'] == 'Còn bảo hành') ? 'status-active' : 'status-expired' ?>">
                                <?= htmlspecialchars($item['warranty_status']) ?>
                            </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($order_info && empty($order_items_info)) : ?>
            <p class="error-message">Đơn hàng này không có sản phẩm nào được ghi nhận.</p>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
</div>
</body>
</html>