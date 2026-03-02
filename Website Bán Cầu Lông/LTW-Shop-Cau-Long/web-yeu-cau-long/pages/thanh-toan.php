<?php
session_start(); // Bắt đầu session
date_default_timezone_set('Asia/Ho_Chi_Minh');
include '../connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Vui lòng đăng nhập để tiến hành thanh toán.';
    header('Location: ../pages/dang-nhap.php'); // Thay đổi thành đường dẫn tới trang đăng nhập của bạn
    exit();
}

$cart_items_for_checkout = [];
$total_checkout_price = 0;
$shipping_fee = 30000; // Phí vận chuyển cố định
$final_total = 0;
$error_message = '';
$success_message = '';

// Lấy thông tin người dùng nếu đã đăng nhập
$user_info = [];
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $sql_user = "SELECT name, email, phone, address, city, district FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    if ($stmt_user === false) {
        error_log("Failed to prepare statement for user info: " . $conn->error);
    } else {
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        if ($result_user->num_rows > 0) {
            $user_info = $result_user->fetch_assoc();
        }
        $stmt_user->close();
    }
}

// Xử lý khi nhấn "Mua ngay" từ trang sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'buy_now') {
    $product_id = $_POST['product_id'] ?? null;
    $product_name = htmlspecialchars($_POST['product_name'] ?? 'Sản phẩm không tên');
    $product_price = floatval($_POST['product_price'] ?? 0);
    $product_image = htmlspecialchars($_POST['product_image'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);

    // Thêm truy vấn để lấy thông tin bảo hành từ bảng products cho 'Mua ngay'
    $warranty_duration = null;
    if ($product_id) {
        $stmt_warranty = $conn->prepare("SELECT warranty FROM products WHERE id = ?");
        if ($stmt_warranty) {
            $stmt_warranty->bind_param("i", $product_id);
            $stmt_warranty->execute();
            $result_warranty = $stmt_warranty->get_result();
            if ($warranty_data = $result_warranty->fetch_assoc()) {
                $warranty_duration = $warranty_data['warranty'];
            }
            $stmt_warranty->close();
        } else {
            error_log("Failed to prepare statement for product warranty: " . $conn->error);
        }
    }

    if ($product_id && $quantity > 0 && $product_price >= 0) {
        $_SESSION['buy_now_item'] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'price' => $product_price,
            'product_image' => $product_image,
            'quantity' => $quantity,
            'warranty_duration' => $warranty_duration // Lưu thời hạn bảo hành vào session
        ];
        header('Location: thanh-toan.php?source=buy_now');
        exit();
    } else {
        $error_message = "Không có sản phẩm hợp lệ để thanh toán ngay.";
    }
}

// Xác định các sản phẩm sẽ thanh toán
if (isset($_GET['source']) && $_GET['source'] == 'cart') {
    unset($_SESSION['buy_now_item']); // Xóa buy_now_item nếu đến từ giỏ hàng
    
    $cart_items_for_checkout = $_SESSION['cart'] ?? [];
    $valid_cart_items = [];
    foreach ($cart_items_for_checkout as $key => $item) {
        if (isset($item['product_id'], $item['product_name'], $item['price'], $item['quantity'], $item['image'])) {
            $product_id_cart = intval($item['product_id']);
            $warranty_duration_cart = null;
            $stmt_warranty = $conn->prepare("SELECT warranty FROM products WHERE id = ?");
            if ($stmt_warranty) {
                $stmt_warranty->bind_param("i", $product_id_cart);
                $stmt_warranty->execute();
                $result_warranty = $stmt_warranty->get_result();
                if ($warranty_data = $result_warranty->fetch_assoc()) {
                    $warranty_duration_cart = $warranty_data['warranty'];
                }
                $stmt_warranty->close();
            } else {
                error_log("Failed to prepare statement for product warranty (cart): " . $conn->error);
            }
            $item['warranty_duration'] = $warranty_duration_cart;
            $valid_cart_items[$key] = $item;
        } else {
            error_log("Cart item missing data: " . json_encode($item));
        }
    }
    $_SESSION['cart'] = $valid_cart_items;
    $cart_items_for_checkout = $valid_cart_items;

    if (empty($cart_items_for_checkout)) {
        $error_message = "Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm để thanh toán.";
    }
} elseif (isset($_GET['source']) && $_GET['source'] == 'buy_now') {
    if (isset($_SESSION['buy_now_item']) && !empty($_SESSION['buy_now_item'])) {
        $buy_now_item = $_SESSION['buy_now_item'];
        if (isset($buy_now_item['product_id'], $buy_now_item['product_name'], $buy_now_item['price'], $buy_now_item['quantity'], $buy_now_item['product_image'])) {
            $cart_items_for_checkout[] = $buy_now_item;
        } else {
            $error_message = "Thông tin sản phẩm 'Mua ngay' không đầy đủ hoặc không hợp lệ. Vui lòng thử lại.";
            error_log("Buy now item missing data: " . json_encode($buy_now_item));
            unset($_SESSION['buy_now_item']);
        }
    } else {
        $error_message = "Không có sản phẩm 'Mua ngay' hợp lệ để thanh toán.";
    }
} else {
    if (isset($_SESSION['buy_now_item']) && !empty($_SESSION['buy_now_item'])) {
        $buy_now_item = $_SESSION['buy_now_item'];
        if (isset($buy_now_item['product_id'], $buy_now_item['product_name'], $buy_now_item['price'], $buy_now_item['quantity'], $buy_now_item['product_image'])) {
            $cart_items_for_checkout[] = $buy_now_item;
        } else {
            $error_message = "Thông tin sản phẩm 'Mua ngay' không đầy đủ hoặc không hợp lệ. Vui lòng thử lại.";
            error_log("Buy now item (refresh) missing data: " . json_encode($buy_now_item));
            unset($_SESSION['buy_now_item']);
        }
    } elseif (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $valid_cart_items = [];
        foreach ($_SESSION['cart'] as $key => $item) {
            if (isset($item['product_id'], $item['product_name'], $item['price'], $item['quantity'], $item['image'])) {
                $product_id_cart = intval($item['product_id']);
                $warranty_duration_cart = null;
                $stmt_warranty = $conn->prepare("SELECT warranty FROM products WHERE id = ?");
                if ($stmt_warranty) {
                    $stmt_warranty->bind_param("i", $product_id_cart);
                    $stmt_warranty->execute();
                    $result_warranty = $stmt_warranty->get_result();
                    if ($warranty_data = $result_warranty->fetch_assoc()) {
                        $warranty_duration_cart = $warranty_data['warranty'];
                    }
                    $stmt_warranty->close();
                } else {
                    error_log("Failed to prepare statement for product warranty (cart refresh): " . $conn->error);
                }
                $item['warranty_duration'] = $warranty_duration_cart;
                $valid_cart_items[$key] = $item;
            } else {
                error_log("Cart item (refresh) missing data: " . json_encode($item));
            }
        }
        $_SESSION['cart'] = $valid_cart_items;
        $cart_items_for_checkout = $valid_cart_items;
    }
    if (empty($cart_items_for_checkout)) {
        $error_message = "Không có sản phẩm nào để thanh toán. Vui lòng thêm sản phẩm vào giỏ hàng hoặc sử dụng chức năng 'Mua ngay'.";
    }
}

// Tính toán tổng tiền dựa trên $cart_items_for_checkout đã xác định
foreach ($cart_items_for_checkout as $item) {
    $item_price = isset($item['price']) ? floatval($item['price']) : 0;
    $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
    $total_checkout_price += $item_price * $item_quantity;
}
$final_total = $total_checkout_price + $shipping_fee;

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'place_order') {
    $full_name = htmlspecialchars(trim($_POST['fullName']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $email = htmlspecialchars(trim($_POST['email']));
    $address = htmlspecialchars(trim($_POST['address']));
    $city = htmlspecialchars(trim($_POST['city']));
    $district = htmlspecialchars(trim($_POST['district']));
    $note = htmlspecialchars(trim($_POST['note'] ?? '')); // Lấy ghi chú từ form
    $payment_method = strtolower(trim($_POST['paymentMethod'])); // Normalize to lowercase for consistency

    // Xử lý status và payment_status theo payment_method
    $status = 'Chờ xử lý'; // Default status for both
    $payment_status = '';
    if ($payment_method == 'cod') {
        $payment_status = 'Chưa thanh toán';
    } elseif ($payment_method == 'bank') {
        $payment_status = 'Đã thanh toán'; // Assuming payment is verified
    } else {
        $error_message = "Phương thức thanh toán không hợp lệ. Giá trị nhận được: " . htmlspecialchars($payment_method);
        error_log($error_message);
    }

    if (empty($full_name) || empty($phone) || empty($address) || empty($city) || empty($district) || empty($payment_method)) {
        $error_message = "Vui lòng điền đầy đủ thông tin bắt buộc.";
    } elseif (empty($cart_items_for_checkout)) {
        $error_message = "Không có sản phẩm nào trong đơn hàng để thanh toán.";
    } elseif (empty($payment_status)) {
        $error_message = "Phương thức thanh toán không được hỗ trợ.";
        error_log("Payment status not set for method: " . htmlspecialchars($payment_method));
    } else {
        $conn->begin_transaction(); // Bắt đầu giao dịch

        try {
            $user_id_for_order = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $order_date_created = date('Y-m-d H:i:s'); // Lấy ngày giờ đặt hàng

            $stmt = $conn->prepare("INSERT INTO orders (user_id, full_name, phone, email, address, city, district, payment_method, status, payment_status, total_price, shipping_fee, final_total, order_date, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                throw new Exception("Lỗi khi chuẩn bị truy vấn đơn hàng: " . $conn->error);
            }
            $stmt->bind_param("isssssssssdddss", $user_id_for_order, $full_name, $phone, $email, $address, $city, $district, $payment_method, $status, $payment_status, $total_checkout_price, $shipping_fee, $final_total, $order_date_created, $note);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi khi chèn đơn hàng: " . $stmt->error);
            }
            $order_id = $conn->insert_id;
            $stmt->close();

            // Prepare statement for order_items
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, serial_number, price, quantity, warranty_expire_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt_item === false) {
                throw new Exception("Lỗi khi chuẩn bị truy vấn chi tiết đơn hàng: " . $conn->error);
            }

            $user_id_for_sn = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'GUEST';

            foreach ($cart_items_for_checkout as $item) {
                $product_id_int = intval($item['product_id'] ?? 0);
                $item_name = htmlspecialchars($item['product_name'] ?? 'Unknown Product');
                $item_price_float = floatval($item['price'] ?? 0);
                $item_quantity_int = intval($item['quantity'] ?? 0);
                $warranty_duration_item = $item['warranty_duration'] ?? null;

                // Tính toán ngày hết hạn bảo hành
                $calculated_warranty_expire_date = null;
                if (!empty($warranty_duration_item)) {
                    $warranty_parts = explode(' ', strtolower(trim($warranty_duration_item)));
                    if (count($warranty_parts) >= 2) {
                        $value = (int)$warranty_parts[0];
                        $unit = strtolower($warranty_parts[1]);

                        $date_object = new DateTime($order_date_created);
                        switch ($unit) {
                            case 'tháng':
                            case 'thang':
                                $date_object->modify("+$value months");
                                break;
                            case 'năm':
                            case 'nam':
                                $date_object->modify("+$value years");
                                break;
                        }
                        $calculated_warranty_expire_date = $date_object->format('Y-m-d');
                    }
                }

                // Tạo order_item cho mỗi đơn vị sản phẩm
                for ($i = 0; $i < $item_quantity_int; $i++) {
                    $unit_in_product_index = $i + 1;
                    $serial_number = 'ITEM-' . str_pad($order_id, 4, '0', STR_PAD_LEFT) . '-' . strtoupper(uniqid());
                    $single_unit_quantity = 1;
                    $stmt_item->bind_param("iissdis",
                        $order_id,
                        $product_id_int,
                        $item_name,
                        $serial_number,
                        $item_price_float,
                        $single_unit_quantity,
                        $calculated_warranty_expire_date
                    );

                    if (!$stmt_item->execute()) {
                        throw new Exception("Lỗi khi chèn sản phẩm vào đơn hàng (Product ID: {$product_id_int}, SN: {$serial_number}): " . $stmt_item->error);
                    }
                }
            }
            $stmt_item->close();

            $conn->commit(); // Xác nhận giao dịch

            // Chỉ xóa cart nếu checkout từ giỏ hàng
            if (isset($_GET['source']) && $_GET['source'] == 'cart') {
                unset($_SESSION['cart']);
            }
            unset($_SESSION['buy_now_item']); // Luôn xóa buy_now_item sau khi đặt hàng

            // Chuyển hướng đến trang xác nhận đơn hàng
            header('Location: chi-tiet-don-hang.php?source=buy_now&order_id=' . $order_id);
            exit();
        } catch (Exception $e) {
            $conn->rollback(); // Hủy giao dịch nếu có lỗi
            $error_message = "Có lỗi xảy ra khi tạo đơn hàng: " . $e->getMessage();
            error_log("Lỗi tạo đơn hàng: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Thanh toán</title>
    <?php include '../includes/header.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/thanhtoan.css">
</head>
<body>
<div class="checkout-container">
    <h1>Thanh toán đơn hàng</h1>

    <?php if (!empty($error_message)) : ?>
        <p class="message error show"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="hidden" name="action" value="place_order">

        <div class="section-title">Thông tin khách hàng</div>
        <div class="form-group">
            <label for="fullName">Họ và tên *</label>
            <input type="text" name="fullName" id="fullName" value="<?= htmlspecialchars($user_info['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Số điện thoại *</label>
            <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($user_info['phone'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user_info['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="address">Địa chỉ *</label>
            <input type="text" name="address" id="address" value="<?= htmlspecialchars($user_info['address'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="city">Tỉnh/Thành phố *</label>
            <input type="text" name="city" id="city" value="<?= htmlspecialchars($user_info['city'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="district">Quận/Huyện *</label>
            <input type="text" name="district" id="district" value="<?= htmlspecialchars($user_info['district'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="note">Ghi chú</label>
            <textarea name="note" id="note" rows="4" placeholder="Nhập ghi chú cho đơn hàng (nếu có)"></textarea>
        </div>

        <div class="section-title">Phương thức thanh toán</div>
        <div class="payment-methods">
            <label><input type="radio" name="paymentMethod" value="cod" checked> Thanh toán khi nhận hàng (COD)</label>
            <label><input type="radio" name="paymentMethod" value="bank"> Chuyển khoản ngân hàng</label>
        </div>

        <div class="section-title">Tóm tắt đơn hàng</div>
        <div class="order-summary">
            <ul>
                <?php
                if (!empty($cart_items_for_checkout)) {
                    foreach ($cart_items_for_checkout as $item):
                        $product_name = htmlspecialchars($item['product_name'] ?? 'Tên sản phẩm không xác định');
                        $quantity = htmlspecialchars($item['quantity'] ?? 0);
                        $price = htmlspecialchars($item['price'] ?? 0);
                        $subtotal_item = (isset($item['price']) && isset($item['quantity'])) ? ($item['price'] * $item['quantity']) : 0;
                ?>
                        <li>
                            <span><?= $product_name; ?> (x<?= $quantity; ?>)</span>
                            <span><?= number_format($subtotal_item, 0, ',', '.') ?>₫</span>
                        </li>
                <?php
                    endforeach;
                } else {
                    echo '<li><span style="color: #888;">Không có sản phẩm nào để hiển thị.</span></li>';
                }
                ?>
                <li><span>Tạm tính:</span><span><?= number_format($total_checkout_price, 0, ',', '.') ?>₫</span></li>
                <li><span>Phí vận chuyển:</span><span><?= number_format($shipping_fee, 0, ',', '.') ?>₫</span></li>
            </ul>
            <div class="order-total">Tổng cộng: <?= number_format($final_total, 0, ',', '.') ?>₫</div>
        </div>

        <button type="submit" class="place-order-button">Đặt hàng</button>
    </form>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>