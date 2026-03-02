<?php

session_start(); // Bắt đầu session. Đảm bảo đây là dòng đầu tiên trong file.
include '../connect.php'; // Kết nối cơ sở dữ liệu.

if (!isset($_SESSION['user_id'])) {
    // Tùy chọn: Lưu thông báo để hiển thị sau khi chuyển hướng
    $_SESSION['message'] = 'Vui lòng đăng nhập để tiến hành thanh toán.';
    header('Location: ../pages/dang-nhap.php'); // Thay đổi thành đường dẫn tới trang đăng nhập của bạn
    exit();
}
// Kiểm tra xem giỏ hàng đã được khởi tạo trong session chưa
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- Xử lý thêm sản phẩm từ trang chi tiết sản phẩm (POST request từ AJAX hoặc form) ---
// Logic này thường được gọi từ trang chi tiết sản phẩm khi người dùng nhấn "Thêm vào giỏ hàng".
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Kiểm tra tính hợp lệ của dữ liệu đầu vào
    if ($product_id <= 0 || $quantity <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Dữ liệu sản phẩm không hợp lệ.']);
        exit();
    }

    // Truy vấn thông tin sản phẩm từ cơ sở dữ liệu
    $sql_product = "SELECT id, name, price, image FROM products WHERE id = ?";
    $stmt_product = $conn->prepare($sql_product);

    if ($stmt_product === false) {
        // Ghi log lỗi và gửi phản hồi lỗi nếu prepare statement thất bại
        error_log("Failed to prepare statement for product lookup in gio-hang.php: " . $conn->error);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Lỗi hệ thống khi tìm kiếm sản phẩm.']);
        exit();
    }

    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();

    if ($result_product->num_rows > 0) {
        $product = $result_product->fetch_assoc();
        $unique_item_id = 'product_' . $product['id']; // ID duy nhất cho mỗi sản phẩm trong giỏ hàng

        // Thêm sản phẩm vào giỏ hàng hoặc cập nhật số lượng
        if (!isset($_SESSION['cart'][$unique_item_id])) {
            $_SESSION['cart'][$unique_item_id] = [
                'product_id' => $product['id'],      // Consistent with thanh-toan.php
                'product_name' => $product['name'],  // Consistent with thanh-toan.php
                'price' => $product['price'],
                'image' => $product['image'],        // Store image path
                'quantity' => $quantity
            ];
        } else {
            $_SESSION['cart'][$unique_item_id]['quantity'] += $quantity;
        }

        // Tính tổng số lượng sản phẩm trong giỏ hàng
        $total_items_in_cart = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_items_in_cart += $item['quantity'];
        }

        // Gửi phản hồi JSON về client (thường dùng cho AJAX)
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'product_name' => htmlspecialchars($product['name']),
            'product_price' => number_format($product['price'], 0, ',', '.'),
            'product_image' => htmlspecialchars($product['image']),
            'total_items_in_cart' => $total_items_in_cart,
            'message' => 'Sản phẩm đã được thêm vào giỏ hàng!'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không tìm thấy trong cơ sở dữ liệu.']);
    }
    $stmt_product->close();
    exit(); // Rất quan trọng: Dừng script sau khi gửi phản hồi JSON
}

// --- Xử lý cập nhật số lượng hoặc xóa sản phẩm trong giỏ hàng (POST request từ form trên trang này) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && ($_POST['action'] == 'update' || $_POST['action'] == 'remove')) {
    $unique_item_id = $_POST['unique_item_id'] ?? null;
    $quantity = intval($_POST['quantity'] ?? 1); // Đảm bảo số lượng là số nguyên

    if ($unique_item_id && isset($_SESSION['cart'][$unique_item_id])) {
        if ($_POST['action'] == 'update') {
            // Cập nhật số lượng, đảm bảo không nhỏ hơn 1
            $_SESSION['cart'][$unique_item_id]['quantity'] = max(1, $quantity);
        } elseif ($_POST['action'] == 'remove') {
            // Xóa sản phẩm khỏi giỏ hàng
            unset($_SESSION['cart'][$unique_item_id]);
        }
    }
    // Chuyển hướng lại trang giỏ hàng để cập nhật hiển thị và tránh gửi lại form khi refresh
    header('Location: gio-hang.php');
    exit;
}

// --- Tính tổng giá trị giỏ hàng để hiển thị ---
$total_cart_price = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        // Đảm bảo các key 'price' và 'quantity' tồn tại trước khi tính toán để tránh lỗi
        $item_price = isset($item['price']) ? floatval($item['price']) : 0;
        $item_quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
        $total_cart_price += ($item_price * $item_quantity);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của bạn</title>
    <?php include '../includes/header.php'; // Chắc chắn rằng header.php có chứa các thẻ meta, title, và các link CSS/JS chung. ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* CSS của bạn đã được cung cấp và giữ nguyên, chỉ thêm một class cho nút checkout */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f9f9f9;
        }
        .cart-container {
            max-width: 900px;
            margin: 20px auto;
            border: 1px solid #eee;
            padding: 30px;
            background-color: white;
            border-radius: 12px; /* Slightly more rounded container */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* More prominent shadow */
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 25px; /* Slightly more space below heading */
            font-size: 2.2em; /* Larger heading */
        }
        .cart-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 18px 0; /* More vertical padding */
        }
        .cart-item img {
            width: 110px; /* Slightly larger image */
            height: 110px; /* Slightly larger image */
            object-fit: cover;
            border-radius: 8px; /* More rounded image corners */
            margin-right: 20px; /* More space to the right of the image */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* subtle shadow for image */
        }
        .item-details {
            flex-grow: 1;
        }
        .item-details h3 {
            margin: 0;
            font-size: 1.3em; /* Slightly larger item name */
            color: #333;
        }
        .item-details p {
            margin: 8px 0; /* More space for description */
            color: #777; /* Slightly darker grey for better readability */
            font-size: 1em; /* Slightly larger description font */
        }
        .item-price {
            font-weight: bold;
            color: #e63946; /* Using one of your gradient colors for emphasis */
            font-size: 1.25em; /* Larger price font */
        }

        /* Button styles with linear gradients and more rounding */
        .item-actions button,
        .cart-items button,
        .cart-summary .checkout-button { /* Added specific class for checkout button */
            color: white;
            border: none;
            padding: 10px 18px; /* Increased padding for all buttons */
            cursor: pointer;
            border-radius: 25px; /* Fully rounded buttons */
            font-size: 1em; /* Consistent font size for buttons */
            transition: all 0.3s ease; /* Smooth transition for all properties */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow for button pop */
        }

        /* Specific styles for "Remove" button */
        .item-actions button {
            background: linear-gradient(90deg, #e63946 0%, #c1121f 100%); /* Red gradient */
        }
        .item-actions button:hover {
            background: linear-gradient(90deg, #c1121f 0%, #e63946 100%); /* Reverse gradient on hover */
            transform: translateY(-2px); /* Slight lift on hover */
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        /* Specific styles for quantity update button */
        .cart-items button {
            background: linear-gradient(90deg,rgb(29, 87, 34) 0%, #457b9d 100%); /* Blue gradient */
        }
        .cart-items button:hover {
            background: linear-gradient(90deg,rgb(53, 143, 83) 0%, #1d3557 100%); /* Reverse gradient on hover */
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }

        /* Specific styles for "Checkout" button */
        .cart-summary .checkout-button {
            background: linear-gradient(90deg, #2a9d8f 0%,rgb(194, 109, 30) 100%); /* Green/Teal gradient */
            padding: 15px 30px; /* Larger padding for checkout button */
            font-size: 1.2em; /* Larger font for checkout */
        }
        .cart-summary .checkout-button:hover {
            background: linear-gradient(90deg,rgb(38, 59, 83) 0%, #2a9d8f 100%); /* Reverse gradient on hover */
            transform: scale(1.03) translateY(-2px); /* Slightly larger and lifted */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
        }

        .cart-summary {
            text-align: right;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 25px; /* More space above summary content */
        }
        .cart-summary h2 {
            margin: 0 0 15px; /* Space below total heading */
            color: #333;
            font-size: 1.6em; /* Larger total heading */
        }

        .cart-items form {
            display: flex;
            align-items: center;
            gap: 15px; /* More space between quantity input and button */
        }
        .cart-items input[type="number"] {
            width: 70px; /* Slightly wider input */
            padding: 8px; /* More padding */
            border: 1px solid #ccc; /* Slightly darker border */
            border-radius: 6px; /* Rounded corners for input */
            text-align: center;
            font-size: 1em;
        }
        .message.success {
            text-align: center;
            color: #28a745;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 1.1em; /* Slightly larger success message */
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h1>Giỏ hàng của bạn</h1>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'added') { ?>
            <p class="message success">Sản phẩm đã được thêm vào giỏ hàng!</p>
        <?php } ?>

        <div class="cart-items">
            <?php
            // Kiểm tra xem giỏ hàng có sản phẩm nào không
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $unique_item_id => $item) {
                    // Lấy dữ liệu sản phẩm từ session, với giá trị mặc định để tránh lỗi nếu thiếu key
                    $product_name = htmlspecialchars($item['product_name'] ?? 'Sản phẩm không xác định');
                    $product_price = isset($item['price']) ? floatval($item['price']) : 0;
                    $product_quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;
                    $product_image = htmlspecialchars($item['image'] ?? '');
                    // Lưu ý: Trường 'promotion' không được thêm vào session trong logic 'add_to_cart' hiện tại.
                    // Nếu bạn muốn hiển thị ưu đãi, cần bổ sung logic để lấy và lưu thông tin này vào session.
                    $promotion_text = htmlspecialchars($item['promotion'] ?? 'Không có ưu đãi');

                    $subtotal = $product_price * $product_quantity;
            ?>
                    <div class="cart-item">
                        <img src="<?= $product_image ?>" alt="<?= $product_name ?>">
                        <div class="item-details">
                            <h3><?= $product_name ?></h3>
                            <p>Ưu đãi: <?= $promotion_text ?></p>
                            <p class="item-price"><?= number_format($product_price, 0, ',', '.') ?> VNĐ</p>
                            <form action="gio-hang.php" method="POST">
                                <input type="hidden" name="unique_item_id" value="<?= htmlspecialchars($unique_item_id) ?>">
                                <input type="hidden" name="action" value="update">
                                <label for="quantity-<?= $unique_item_id ?>">Số lượng:</label>
                                <input type="number" id="quantity-<?= $unique_item_id ?>" name="quantity" value="<?= $product_quantity ?>" min="1" max="99">
                                <button type="submit">Cập nhật</button>
                            </form>
                        </div>
                        <div class="item-actions">
                            <form action="gio-hang.php" method="POST">
                                <input type="hidden" name="unique_item_id" value="<?= htmlspecialchars($unique_item_id) ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit">Xóa</button>
                            </form>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "<p style='text-align:center;'>Giỏ hàng của bạn đang trống.</p>";
            }
            ?>
        </div>
        <div class="cart-summary">
            <h2>Tổng cộng: <?= number_format($total_cart_price, 0, ',', '.') ?> VNĐ</h2>
            <?php if ($total_cart_price > 0) { ?>
                <button class="checkout-button" onclick="window.location.href='thanh-toan.php?source=cart'">Tiến hành thanh toán</button>
            <?php } ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>