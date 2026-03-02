<?php
// Bắt đầu session để quản lý trạng thái đăng nhập của người dùng.
session_start();

// Kiểm tra xem admin đã đăng nhập chưa. Nếu chưa, chuyển hướng về trang đăng nhập/đăng ký.
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: dangnhap-dangky.php');
    exit;
}

// Thông tin kết nối cơ sở dữ liệu.
$host = 'localhost';
$dbname = 'ltw_shop_cau_long';
$username = 'root';
$password = '';

// Cố gắng thiết lập kết nối cơ sở dữ liệu sử dụng PDO (PHP Data Objects).
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Đặt chế độ báo lỗi của PDO thành exception để xử lý lỗi tốt hơn.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Đặt chế độ lấy dữ liệu mặc định là mảng kết hợp (associative array).
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Nếu kết nối thất bại, dừng script và hiển thị thông báo lỗi.
    die("Kết nối thất bại: " . $e->getMessage());
}

// ======= XỬ LÝ GỬI FORM =======
// Khối này xử lý các yêu cầu POST để thêm, sửa sản phẩm và tạo đơn hàng.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Chức năng thêm sản phẩm.
    if (isset($_POST['add_product'])) {
        // Chuẩn bị câu lệnh SQL INSERT cho bảng 'products'.
        $stmt = $pdo->prepare("INSERT INTO products (name, image, price, sku, brand, category, warranty, stock, description)
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // Thực thi câu lệnh đã chuẩn bị với dữ liệu từ yêu cầu POST.
        $stmt->execute([
            $_POST['name'],
            $_POST['image'] ?: '../assets/images/default.jpg', // Sử dụng '../assets/images/default.jpg' nếu ảnh trống.
            $_POST['price'],
            'SKU-' . uniqid(), // Tạo SKU duy nhất.
            'Generic', // Thương hiệu mặc định.
            $_POST['category'],
            '12 tháng', // Bảo hành mặc định.
            $_POST['stock'],
            $_POST['description']
        ]);
        // Chuyển hướng đến tab sản phẩm sau khi thêm, giữ nguyên danh mục đã chọn.
        header("Location: admin.php?tab=products&category=" . urlencode($_POST['category']));
        exit;
    }

    // Chức năng sửa sản phẩm.
    elseif (isset($_POST['edit_product'])) {
        // Chuẩn bị câu lệnh SQL UPDATE cho bảng 'products'.
        $stmt = $pdo->prepare("UPDATE products SET name=?, image=?, price=?, category=?, stock=?, description=? WHERE id=?");
        // Thực thi câu lệnh đã chuẩn bị với dữ liệu cập nhật.
        $stmt->execute([
            $_POST['name'],
            $_POST['image'] ?: '../assets/images/default.jpg', // Sử dụng '../assets/images/default.jpg' nếu ảnh trống.
            $_POST['price'],
            $_POST['category'],
            $_POST['stock'],
            $_POST['description'],
            $_POST['id'] // ID sản phẩm cho điều kiện WHERE.
        ]);
        // Chuyển hướng đến tab sản phẩm sau khi sửa, giữ nguyên danh mục đã chọn.
        header("Location: admin.php?tab=products&category=" . urlencode($_POST['category']));
        exit;
    }

    // Chức năng tạo đơn hàng.
    elseif (isset($_POST['create_order'])) {
        $user_id = $_POST['customer'];
        $product_id = $_POST['product'];
        $quantity = $_POST['quantity'];
        $discount = $_POST['discount'] ?? 0;
        $note = $_POST['note'] ?? '';

        // Lấy thông tin người dùng từ bảng 'users'
        $userStmt = $pdo->prepare("SELECT name, phone, email, address, city, district FROM users WHERE id = ?");
        $userStmt->execute([$user_id]);
        $user = $userStmt->fetch();

        // Lấy thông tin sản phẩm từ bảng 'products'
        $productStmt = $pdo->prepare("SELECT name, price FROM products WHERE id = ?");
        $productStmt->execute([$product_id]);
        $product = $productStmt->fetch();

        // Tính toán tổng giá, phí vận chuyển và tổng cuối cùng
        $total_price = $product['price'] * $quantity * (1 - $discount / 100);
        $shipping_fee = 30000;
        $final_total = $total_price + $shipping_fee;
        $payment_method = 'COD';

        // Chuẩn bị và thực thi câu lệnh INSERT cho bảng 'orders'
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (user_id, full_name, phone, email, address, city, district, payment_method, total_price, shipping_fee, final_total, order_date, note)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ");
        $orderStmt->execute([
            $user_id,
            $user['name'],
            $user['phone'],
            $user['email'],
            $user['address'],
            $user['city'],
            $user['district'],
            $payment_method,
            $total_price,
            $shipping_fee,
            $final_total,
            $note
        ]);

        // Lấy ID của đơn hàng vừa được tạo
        $order_id = $pdo->lastInsertId();

        // Tạo serial_number duy nhất cho mục đơn hàng
        $serial_number = 'ITEM-' . str_pad($order_id, 4, '0', STR_PAD_LEFT) . '-' . strtoupper(uniqid());

        // Chuẩn bị và thực thi câu lệnh INSERT cho bảng 'order_items', bao gồm serial_number
        $orderItemStmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, price, quantity, serial_number)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $orderItemStmt->execute([
            $order_id,
            $product_id,
            $product['name'],
            $product['price'],
            $quantity,
            $serial_number
        ]);

        // Chuyển hướng đến tab đơn hàng
        header("Location: admin.php?tab=orders");
        exit;
    }
    // Xử lý nhập kho (thêm số lượng sản phẩm).
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'import_stock') {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];

        // Kiểm tra số lượng hợp lệ
        if ($quantity <= 0) {
            die("Lỗi: Số lượng nhập phải lớn hơn 0.");
        }

        // Tăng số lượng tồn trong bảng 'products'.
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + :quantity WHERE id = :id");
        $stmt->execute(['quantity' => $quantity, 'id' => $product_id]);

        // Thêm hoặc cập nhật bản ghi trong bảng 'inventory' (trigger sẽ xử lý import_price).
        $stmt = $pdo->prepare("
            INSERT INTO inventory (product_id, min_stock)
            VALUES (:id, 5)
            ON DUPLICATE KEY UPDATE min_stock = 5
        ");
        $stmt->execute(['id' => $product_id]);

        header("Location: admin.php?tab=inventory");
        exit;
    }
}

// ======= XỬ LÝ XÓA =======
// Khối này xử lý các yêu cầu xóa sản phẩm hoặc đơn hàng.
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = $_GET['delete'];
    $type = $_GET['type'];

    if ($type === 'product') {
        // Xóa sản phẩm khỏi bảng 'products'.
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin.php?tab=products");
    } elseif ($type === 'order') {
        // Xóa đơn hàng khỏi bảng 'orders'.
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: admin.php?tab=orders");
    }
    exit;
}

// ======= ĐĂNG XUẤT =======
// Xử lý yêu cầu đăng xuất.
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Xóa cookie remember_admin_user
    if (isset($_COOKIE['remember_admin_user'])) {
        setcookie('remember_admin_user', '', time() - 3600, "/", "", false, true);
    }
    session_unset();
    session_destroy();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    header('Location: dangnhap-dangky.php?logout=1');
    exit;
}
// Cập nhật trạng thái đơn hàng.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order_status'])) {
    $orderId = $_POST['order_id'];
    $newStatus = $_POST['new_status'];

    // Validate new status
    $validStatuses = ['Chờ xử lý', 'Đang xử lý', 'Đã giao', 'Hủy'];
    if (!in_array($newStatus, $validStatuses)) {
        $newStatus = 'Chờ xử lý'; // Fấpback to default if invalid
    }

    // Get the current payment method and payment status
    $orderStmt = $pdo->prepare("SELECT payment_method, payment_status FROM orders WHERE id = ?");
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch();
    $paymentMethod = $order['payment_method'] ?? 'COD';
    $currentPaymentStatus = $order['payment_status'] ?? 'Chưa thanh toán';

    // Determine new payment status based on payment method and new status
    $newPaymentStatus = $currentPaymentStatus;
    if ($paymentMethod === 'COD') {
        if ($newStatus === 'Đã giao') {
            $newPaymentStatus = 'Đã thanh toán';
        } elseif ($newStatus === 'Hủy') {
            $newPaymentStatus = 'Chưa thanh toán'; // Default for COD on cancel; refund logic would need separate handling
        }
    } elseif ($paymentMethod === 'Bank') {
        if ($newStatus === 'Đang xử lý') {
            $newPaymentStatus = 'Đã thanh toán'; // Assumes pre-payment verified
        } elseif ($newStatus === 'Hủy') {
            $newPaymentStatus = 'Hoàn tiền'; // Indicates refund needed
        } else {
            $newPaymentStatus = 'Đã thanh toán'; // Default for "Đã giao" or other states
        }
    }

    // Cập nhật trạng thái đơn hàng và trạng thái thanh toán.
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $newPaymentStatus, $orderId]);

    header("Location: admin.php?tab=orders");
    exit;
}

// Lấy danh sách đơn hàng của một khách hàng cụ thể (sử dụng AJAX).
if (isset($_GET['action']) && $_GET['action'] === 'get_customer_orders' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $stmt = $pdo->prepare("SELECT id, order_date, final_total, payment_method, payment_status FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hiển thị đơn hàng của khách hàng dưới dạng bảng HTML.
    if (empty($orders)) {
        echo "<p>Khách hàng chưa có đơn hàng nào.</p>";
    } else {
        echo '<table class="table">';
        echo '<thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Thanh toán</th><th>Trạng thái</th></tr></thead><tbody>';
        foreach ($orders as $order) {
            echo '<tr>';
            echo '<td>#ORD' . str_pad($order['id'], 3, '0', STR_PAD_LEFT) . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($order['order_date'])) . '</td>';
            echo '<td>₫' . number_format($order['final_total'], 0, ',', '.') . '</td>';
            echo '<td>' . htmlspecialchars($order['payment_method']) . '</td>';
            echo '<td>' . htmlspecialchars($order['payment_status']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    exit;
}

// Lấy dữ liệu doanh thu theo tháng/ngày cho biểu đồ (sử dụng AJAX).
if (isset($_GET['action']) && $_GET['action'] === 'monthly_revenue') {
    header('Content-Type: application/json; charset=utf-8');
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 365;

    try {
        if ($days <= 30) {
            $stmt = $pdo->prepare("
                SELECT DATE(order_date) AS label, SUM(final_total) AS total
                FROM orders
                WHERE order_date >= CURDATE() - INTERVAL :days DAY
                GROUP BY DATE(order_date)
                ORDER BY DATE(order_date)
            ");
        } else {
            $stmt = $pdo->prepare("
                SELECT DATE_FORMAT(order_date, '%m/%Y') AS label, SUM(final_total) AS total
                FROM orders
                WHERE order_date >= CURDATE() - INTERVAL :days DAY
                GROUP BY YEAR(order_date), MONTH(order_date)
                ORDER BY order_date
            ");
        }
        $stmt->execute(['days' => $days]);

        $labels = [];
        $revenue = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $labels[] = $days <= 30 ? date('d/m', strtotime($row['label'])) : 'Tháng ' . $row['label'];
            $revenue[] = (float)$row['total'];
        }

        echo json_encode([
            'labels' => $labels,
            'revenue' => $revenue
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi khi lấy dữ liệu doanh thu: ' . $e->getMessage()]);
    }
    exit;
}

// Lấy 5 danh mục sản phẩm bán chạy nhất (dựa trên số lượng).
if (isset($_GET['action']) && $_GET['action'] === 'top_categories') {
    header('Content-Type: application/json; charset=utf-8');
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 365;

    try {
        $stmt = $pdo->prepare("
            SELECT p.category, SUM(oi.quantity) AS total_quantity
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            JOIN products p ON p.id = oi.product_id
            WHERE o.order_date >= CURDATE() - INTERVAL :days DAY
            GROUP BY p.category
            ORDER BY total_quantity DESC
            LIMIT 5
        ");
        $stmt->execute(['days' => $days]);

        $labels = [];
        $quantities = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $labels[] = $row['category'];
            $quantities[] = (int)$row['total_quantity'];
        }

        echo json_encode([
            'labels' => $labels,
            'quantities' => $quantities
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi khi lấy dữ liệu danh mục: ' . $e->getMessage()]);
    }
    exit;
}

// Lấy doanh thu nhanh trong 7 ngày gần nhất.
if (isset($_GET['action']) && $_GET['action'] === 'quick_revenue_7days') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $stmt = $pdo->prepare("
            SELECT DATE(order_date) AS date, SUM(final_total) AS total
            FROM orders
            WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(order_date)
            ORDER BY date
        ");
        $stmt->execute();

        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $dates[$d] = 0;
        }

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dates[$row['date']] = (float)$row['total'];
        }

        echo json_encode([
            'labels' => array_map(fn($d) => date('d/m', strtotime($d)), array_keys($dates)),
            'revenue' => array_values($dates)
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi khi lấy dữ liệu doanh thu 7 ngày: ' . $e->getMessage()]);
    }
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'growth_percent') {
    header('Content-Type: application/json; charset=utf-8');
    $days = 30;

    try {
        $stmt1 = $pdo->prepare("SELECT SUM(final_total) FROM orders WHERE order_date >= CURDATE() - INTERVAL :days DAY");
        $stmt1->execute(['days' => $days]);
        $current = $stmt1->fetchColumn() ?? 0;

        $stmt2 = $pdo->prepare("SELECT SUM(final_total) FROM orders WHERE order_date >= CURDATE() - INTERVAL :days2 DAY AND order_date < CURDATE() - INTERVAL :days DAY");
        $stmt2->execute(['days2' => $days * 2, 'days' => $days]);
        $previous = $stmt2->fetchColumn() ?? 0;

        $growth = 0;
        if ($previous > 0) {
            $growth = round((($current - $previous) / $previous) * 100, 1);
        }

        echo json_encode(['growth' => $growth]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Lỗi khi tính tăng trưởng: ' . $e->getMessage()]);
    }
    exit;
}

// Handle password change
$error = '';
$success = '';
$showChangePassword = isset($_GET['action']) && $_GET['action'] === 'change-password';

if ($showChangePassword && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = trim($_POST['old'] ?? '');
    $new = trim($_POST['new'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');
    $username = $_SESSION['admin_username'];
    if ($old === '' || $new === '' || $confirm === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    } elseif ($new !== $confirm) {
        $error = 'Mật khẩu mới xác nhận không khớp!';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if ($row && password_verify($old, $row['password'])) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt2 = $pdo->prepare("UPDATE admin SET password = ? WHERE username = ?");
            $stmt2->execute([$hash, $username]);
            $success = 'Đổi mật khẩu thành công!';
        } else {
            $error = 'Mật khẩu cũ không đúng!';
        }
    }
}
// ======= THỐNG KÊ & DỮ LIỆU CHUNG =======
// Xác định tab hiện tại để hiển thị nội dung phù hợp.
$tab = $_GET['tab'] ?? 'dashboard';

// Lấy tổng doanh thu tháng hiện tại.
$total_revenue = $pdo->query("SELECT SUM(final_total) as revenue FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE())")->fetch()['revenue'] ?? 0;
// Lấy tổng số đơn hàng.
$total_orders = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'] ?? 0;
// Lấy tổng số sản phẩm.
$total_products = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch()['count'] ?? 0;
// Lấy tổng số khách hàng.
$total_customers = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'] ?? 0;

// Lấy 4 đơn hàng gần đây nhất để hiển thị.
$recent_orders = $pdo->query("
    SELECT o.id, o.full_name, p.name as product_name, o.final_total, o.payment_status
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    ORDER BY o.order_date DESC
    LIMIT 4
")->fetchAll();

// Lấy tất cả đơn hàng cùng với tên khách hàng.
$orders = $pdo->query("
    SELECT o.id, u.name as customer_name, o.order_date, o.final_total, o.payment_method, o.payment_status, o.status
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
")->fetchAll(PDO::FETCH_ASSOC);
// Lấy tất cả khách hàng cùng với tổng số đơn hàng của họ.
$customers = $pdo->query("
    SELECT id, name, email, phone, created_at, 
           (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as total_orders 
    FROM users 
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
$inventory = $pdo->query("SELECT id, name, stock FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 2")->fetchAll();
// Tính tổng giá trị tồn kho.
$total_inventory_value = $pdo->query("SELECT SUM(price * stock) as value FROM products")->fetch()['value'] ?? 0;
// Đếm số lượng sản phẩm có tồn kho thấp (<=5).
$low_stock_count = $pdo->query("SELECT COUNT(*) as count FROM products WHERE stock <= 5")->fetch()['count'] ?? 0;

// ======= LỌC DANH MỤC & SẢN PHẨM =======
// Lấy danh mục được chọn từ URL hoặc mặc định là 'all'.
$selected_category = $_GET['category'] ?? 'all';
// Lấy danh sách các danh mục sản phẩm khác nhau.
$categories = $pdo->query("SELECT DISTINCT category FROM products")->fetchAll(PDO::FETCH_COLUMN);

// Lấy tất cả sản phẩm hoặc sản phẩm theo danh mục được chọn.
if ($selected_category === 'all') {
    $products = $pdo->query("SELECT id, image, name, price, stock, category FROM products ORDER BY category, name")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT id, image, name, price, stock, category FROM products WHERE category = ? ORDER BY name");
    $stmt->execute([$selected_category]);
    $products = $stmt->fetchAll();
}

// ======= NHÓM SẢN PHẨM THEO DANH MỤC =======
$grouped_products = [];
foreach ($products as $product) {
    $cat = $product['category'];
    if (!isset($grouped_products[$cat])) {
        $grouped_products[$cat] = [];
    }
    $grouped_products[$cat][] = $product;
}

// ======= LẤY SẢN PHẨM ĐỂ CHỈNH SỬA =======
$edit_product = null;
// Nếu có yêu cầu chỉnh sửa sản phẩm, lấy thông tin sản phẩm đó.
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT id, name, image, category, price, stock, description FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_product = $stmt->fetch();
}

// Các mục tiêu KPI (Key Performance Indicators).
$target_revenue = 50000000; // Mục tiêu doanh thu: 50 triệu VND
$target_orders = 50;         // Mục tiêu đơn hàng: 50 đơn
$target_customers = 30;      // Mục tiêu khách hàng mới: 30 người

// Doanh thu tháng hiện tại.
$stmt = $pdo->query("SELECT SUM(final_total) FROM orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())");
$current_revenue = (float) $stmt->fetchColumn();
// Tính phần trăm hoàn thành mục tiêu doanh thu.
$revenue_percent = $target_revenue > 0 ? round($current_revenue / $target_revenue * 100) : 0;

// Đơn hàng tháng hiện tại.
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())");
$current_orders = (int) $stmt->fetchColumn();
// Tính phần trăm hoàn thành mục tiêu đơn hàng.
$order_percent = $target_orders > 0 ? round($current_orders / $target_orders * 100) : 0;

// Khách hàng mới trong tháng hiện tại.
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$current_customers = (int) $stmt->fetchColumn();
// Tính phần trăm hoàn thành mục tiêu khách hàng mới.
$customer_percent = $target_customers > 0 ? round($current_customers / $target_customers * 100) : 0;

// Lấy thông tin tồn kho chi tiết, bao gồm giá nhập và mức tồn kho tối thiểu.
$stmt = $pdo->prepare("
    SELECT
        p.id, p.name, p.sku, p.stock,
        COALESCE(i.import_price, p.price * 0.8) AS import_price,
        COALESCE(i.min_stock, 5) AS min_stock
    FROM products p
    LEFT JOIN inventory i ON p.id = i.product_id
");
$stmt->execute();
$inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng số sản phẩm (dựa trên số lượng sản phẩm trong tồn kho chi tiết).
$total_products = count($inventory);

// Tính sản phẩm sắp hết và tổng giá trị tồn kho.
$low_stock_count = 0;
$total_inventory_value = 0;
foreach ($inventory as $product) {
    // Mức tồn kho tối thiểu mặc định là 5 nếu không được đặt trong bảng inventory.
    $min_stock = $product['min_stock'] ?? 5;
    // Đếm số sản phẩm có tồn kho thấp hơn hoặc bằng mức tối thiểu.
    $low_stock_count += ($product['stock'] <= $min_stock) ? 1 : 0;
    // Tính tổng giá trị tồn kho dựa trên giá nhập và số lượng tồn kho.
    $total_inventory_value += ($product['import_price'] ?? 0) * $product['stock'];
}

?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cửa Hàng Cầu Lông</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/admin.js"></script>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h1><i class="fas fa-shuttlecock"></i> SportAdmin</h1>
                <p class="subtitle">Quản lý cửa hàng cầu lông</p>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="?tab=dashboard" class="nav-link <?= $tab === 'dashboard' ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tổng quan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=products" class="nav-link <?= $tab === 'products' ? 'active' : '' ?>">
                            <i class="fas fa-box"></i>
                            <span>Sản phẩm</span>
                            <span class="badge"><?= $total_products ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=orders" class="nav-link <?= $tab === 'orders' ? 'active' : '' ?>">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Đơn hàng</span>
                            <span class="badge"><?= $total_orders ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=customers" class="nav-link <?= $tab === 'customers' ? 'active' : '' ?>">
                            <i class="fas fa-users"></i>
                            <span>Khách hàng</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=inventory" class="nav-link <?= $tab === 'inventory' ? 'active' : '' ?>">
                            <i class="fas fa-warehouse"></i>
                            <span>Kho hàng</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=reports" class="nav-link <?= $tab === 'reports' ? 'active' : '' ?>">
                            <i class="fas fa-chart-bar"></i>
                            <span>Báo cáo</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=settings" class="nav-link <?= $tab === 'settings' ? 'active' : '' ?>">
                            <i class="fas fa-cog"></i>
                            <span>Cài đặt</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?action=logout" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-content">
                    <h2 id="page-title">
                        <?php
                            $titles = [
                                'dashboard' => 'Tổng quan',
                                'products' => 'Quản lý sản phẩm',
                                'orders' => 'Quản lý đơn hàng',
                                'customers' => 'Quản lý khách hàng',
                                'inventory' => 'Quản lý kho hàng',
                                'reports' => 'Báo cáo & Thống kê',
                                'settings' => $showChangePassword ? 'Đổi mật khẩu' : 'Cài đặt hệ thống'
                            ];
                            echo $titles[$tab] ?? 'Tổng quan';
                        ?>
                    </h2>
                    <div class="user-info">
                        <span>Xin chào, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></div>
                    </div>
                </div>
            </header>
            
            <div id="notification-alert" class="alert"></div>
            
            <!-- Dashboard Content -->
            <div id="dashboard-content" style="<?= $tab !== 'dashboard' ? 'display: none;' : '' ?>">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>₫<?= number_format($total_revenue, 0, ',', '.') ?></h3>
                                <p>Doanh thu tháng</p>
                            </div>
                            <div class="stat-icon revenue">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3><?= $total_orders ?></h3>
                                <p>Đơn hàng</p>
                            </div>
                            <div class="stat-icon orders">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3><?= $total_products ?></h3>
                                <p>Sản phẩm</p>
                            </div>
                            <div class="stat-icon products">
                                <i class="fas fa-cubes"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3><?= number_format($total_customers, 0, ',', '.') ?></h3>
                                <p>Khách hàng</p>
                            </div>
                            <div class="stat-icon customers">
                                <i class="fas fa-user-friends"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Recent Orders -->
                    <div class="content-section">
                        <div class="section-header">
                            <h3 class="section-title">Đơn hàng gần đây</h3>
                            <button class="btn btn-primary" onclick="openModal('order-modal')">
                                <i class="fas fa-plus"></i>
                                Thêm đơn hàng
                            </button>
                        </div>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Sản phẩm</th>
                                        <th>Giá trị</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#ORD<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                            <td>₫<?php echo number_format($order['final_total'], 0, ',', '.'); ?></td>
                                            <td><span class="status <?php echo ($order['payment_status'] == 'completed') ? 'completed' : ($order['payment_status'] == 'processing' ? 'processing' : 'pending'); ?>">
                                                <?php echo htmlspecialchars($order['payment_status'] ?? 'Chờ xử lý'); ?>
                                            </span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <!-- Quick Actions -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Thao tác nhanh</h3>
                    </div>
                    <div class="quick-actions" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div class="action-card" onclick="openModal('product-modal')" style="cursor: pointer; text-align: center; padding: 1rem;">
                            <i class="fas fa-plus-circle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <h4 style="font-size: 1rem; margin: 0.5rem 0;">Thêm sản phẩm</h4>
                            <p style="font-size: 0.9rem; color: #777;">Thêm sản phẩm mới vào kho</p>
                        </div>
                        <div class="action-card" onclick="openModal('order-modal')" style="cursor: pointer; text-align: center; padding: 1rem;">
                            <i class="fas fa-receipt" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <h4 style="font-size: 1rem; margin: 0.5rem 0;">Tạo đơn hàng</h4>
                            <p style="font-size: 0.9rem; color: #777;">Tạo đơn hàng mới</p>
                        </div>
                        <div class="action-card" style="cursor: pointer; text-align: center; padding: 1rem;">
                            <i class="fas fa-chart-line" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <h4 style="font-size: 1rem; margin: 0.5rem 0;">Xem báo cáo</h4>
                            <p style="font-size: 0.9rem; color: #777;">Báo cáo doanh thu</p>
                        </div>
                        <div class="action-card" style="cursor: pointer; text-align: center; padding: 1rem;">
                            <i class="fas fa-bell" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <h4 style="font-size: 1rem; margin: 0.5rem 0;">Thông báo</h4>
                            <p style="font-size: 0.9rem; color: #777;">Gửi thông báo khuyến mãi</p>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="quickRevenueChart" height="250"></canvas>
                    </div>
                    <div style="margin-top: 2rem;">
                        <h4 style="margin-bottom: 1rem;">Mục tiêu tháng</h4>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Doanh thu</span>
                                <span><?= $revenue_percent ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $revenue_percent ?>%"></div>
                            </div>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Đơn hàng</span>
                                <span><?= $order_percent ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $order_percent ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Khách hàng mới</span>
                                <span><?= $customer_percent ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $customer_percent ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- Products Content -->
            <div id="products-content" style="<?= $tab !== 'products' ? 'display: none;' : '' ?>">
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Quản lý sản phẩm</h3>
                        <button class="btn btn-primary" onclick="openModal('product-modal')">
                            <i class="fas fa-plus"></i>
                            Thêm sản phẩm
                        </button>
                    </div>
                    <!-- Bộ lọc theo danh mục -->
                    <form method="GET" class="filter-form" style="margin-bottom: 1rem;">
                        <input type="hidden" name="tab" value="products">
                        <label>Lọc theo danh mục:</label>
                        <select name="category" onchange="this.form.submit()">
                            <option value="all" <?= $selected_category === 'all' ? 'selected' : '' ?>>Tất cả</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $selected_category === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>

                    <?php if (empty($grouped_products)): ?>
                        <div style="text-align: center; padding: 1rem;">Không có sản phẩm nào.</div>
                    <?php else: ?>
                        <?php foreach ($grouped_products as $category => $products): ?>
                            <div style="margin-bottom: 2rem;">
                                <h4 style="color: #333; font-size: 1.2rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($category); ?> (<?php echo count($products); ?> sản phẩm)</h4>
                                <div class="table-container">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Hình ảnh</th>
                                                <th>Tên sản phẩm</th>
                                                <th>Giá</th>
                                                <th>Kho</th>
                                                <th>Trạng thái</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($products as $product): ?>
                                                <tr>
                                                    <td><img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Product" style="width: 50px; height: 50px; border-radius: 8px;" onerror="this.src='../assets/images/default.jpg';"></td>
                                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                    <td>₫<?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                                    <td><?php echo $product['stock']; ?></td>
                                                    <td><span class="status <?php echo $product['stock'] > 5 ? 'completed' : 'pending'; ?>">
                                                        <?php echo $product['stock'] > 0 ? 'Còn hàng' : 'Sắp hết'; ?>
                                                    </span></td>
                                                    <td>
                                                        <button class="btn btn-warning" style="padding: 0.5rem; margin-right: 0.5rem;" onclick='openEditModal(<?= json_encode($product, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <button class="btn btn-danger" style="padding: 0.5rem;" onclick="showConfirmDeleteModal('?delete=<?= $product['id'] ?>&type=product')"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Orders Content -->
            <div id="orders-content" style="<?= $tab !== 'orders' ? 'display: none;' : '' ?>">
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Quản lý đơn hàng</h3>
                        <button class="btn btn-primary" onclick="openModal('order-modal')">
                            <i class="fas fa-plus"></i>
                            Tạo đơn hàng
                        </button>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Thanh toán</th>
                                    <th>Trạng thái thanh toán</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#ORD<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                                        <td>₫<?php echo number_format($order['final_total'], 0, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                        <td>
                                            <span class="status <?php 
                                                echo ($order['payment_status'] === 'Đã thanh toán') ? 'completed' : 
                                                    (($order['payment_status'] === 'Hoàn tiền') ? 'canceled' : 
                                                    (($order['payment_status'] === 'Chưa thanh toán' || $order['payment_status'] === '') ? 'pending' : 'pending')); 
                                            ?>">
                                                <?php echo htmlspecialchars($order['payment_status'] ?? 'Chưa thanh toán'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status <?php 
                                                echo ($order['status'] === 'Đã giao') ? 'completed' : 
                                                    (($order['status'] === 'Đang xử lý') ? 'processing' : 
                                                    (($order['status'] === 'Hủy') ? 'canceled' : 
                                                    (($order['status'] === 'Chờ xử lý' || $order['status'] === '') ? 'pending' : 'pending'))); 
                                            ?>">
                                                <?php echo htmlspecialchars($order['status'] ?? 'Chờ xử lý'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-warning" style="padding: 0.5rem; margin-right: 0.5rem;" onclick="openEditOrderModal(<?= $order['id'] ?>, '<?= htmlspecialchars($order['payment_status'] ?? 'Chờ xử lý') ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Customers Content -->
            <div id="customers-content" style="<?= $tab !== 'customers' ? 'display: none;' : '' ?>">
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Quản lý khách hàng</h3>
                        
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Avatar</th>
                                    <th>Tên khách hàng</th>
                                    <th>Email</th>
                                    <th>Điện thoại</th>
                                    <th>Tổng đơn hàng</th>
                                    <th>Ngày tham gia</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td><div class="user-avatar" style="width: 40px; height: 40px;"><?php echo strtoupper(substr($customer['name'], 0, 1)); ?></div></td>
                                        <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                        <td><?php echo $customer['total_orders']; ?> đơn</td>
                                        <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                                        <td>
                                           <button class="btn btn-info" style="padding: 0.5rem; margin-right: 0.5rem; display: flex; align-items: center; justify-content: center;" onclick="viewCustomerOrders(<?= $customer['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Inventory Content -->
            <!-- Giao diện quản lý kho -->
            <div id="inventory-content" style="<?= $tab !== 'inventory' ? 'display: none;' : '' ?>">
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Quản lý kho hàng</h3>

                    </div>

                    <!-- Thống kê -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3><?= $total_products ?></h3>
                                    <p>Tổng sản phẩm</p>
                                </div>
                                <div class="stat-icon products">
                                    <i class="fas fa-cubes"></i>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3><?= $low_stock_count ?></h3>
                                    <p>Sản phẩm sắp hết</p>
                                </div>
                                <div class="stat-icon orders">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3>₫<?= number_format($total_inventory_value, 0, ',', '.') ?></h3>
                                    <p>Giá trị kho</p>
                                </div>
                                <div class="stat-icon revenue">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bảng dữ liệu -->
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>SKU</th>
                                    <th>Số lượng</th>
                                    <th>Tồn kho tối thiểu</th>
                                    <th>Giá nhập</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventory as $product): 
                                    $min_stock = $product['min_stock'] ?? 5;
                                    $import_price = (float)($product['import_price'] ?? 0);
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= htmlspecialchars($product['sku']) ?></td>
                                    <td><?= $product['stock'] ?></td>
                                    <td><?= $min_stock ?></td>
                                    <td>₫<?= number_format($import_price, 0, ',', '.') ?></td>
                                    <td>
                                        <span class="status <?= $product['stock'] > $min_stock ? 'completed' : 'pending' ?>">
                                            <?= $product['stock'] > $min_stock ? 'Đủ hàng' : 'Sắp hết' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" style="padding: 0.5rem;"
                                            onclick="openImportModal(<?= $product['id'] ?>)">
                                            Nhập
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


 <!-- Reports Content -->
            <div id="reports-content" style="<?= $tab !== 'reports' ? 'display: none;' : '' ?>">
                <div class="content-section">

                    <div class="stats-grid">
                        <div class="stat-card">
                            
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3>₫<?= number_format($total_revenue * 12, 0, ',', '.') ?></h3>
                                    <p>Doanh thu năm</p>
                                </div>
                                <div class="stat-icon revenue">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3><?= $total_orders ?></h3>
                                    <p>Tổng đơn hàng</p>
                                </div>
                                <div class="stat-icon orders">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3 id="growth-display"></h3>
                                    <p>Tăng trưởng</p>
                                </div>
                                <div class="stat-icon customers">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-content">
                                <div class="stat-info">
                                    <h3>₫<?= $total_orders ? number_format($total_revenue / $total_orders, 0, ',', '.') : 0 ?></h3>
                                    <p>Giá trị TB/đơn</p>
                                </div>
                                <div class="stat-icon revenue">
                                    <i class="fas fa-calculator"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                                                            
                    <div class="content-grid">
                        <div class="content-section">
                            <div class="section-header">
                            <div>
                                <button class="btn btn-primary" data-range="7">7 ngày qua</button>
                                <button class="btn btn-primary" data-range="30">30 ngày qua</button>
                                <button class="btn btn-primary" data-range="90">3 tháng qua</button>
                                <button class="btn btn-primary" data-range="365">12 tháng qua</button>
                            </div>
                            </div>
                            <div class="chart-container">
                                <canvas id="monthlyRevenueChart"></canvas>
                            </div>
                            <h4 style="text-align: center; margin-top: 10px;">Biểu đồ doanh thu</h4>
                        </div>
                        <div class="content-section">
                            <div class="chart-container">
                                <canvas id="topCategoriesChart"></canvas>
                            </div>
                            <h4 style="text-align: center; margin-top: 10px;">Phân bố sản phẩm bán chạy</h4>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Settings Content -->
            <div id="settings-content" style="<?= $tab !== 'settings' ? 'display: none;' : '' ?>">
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">Cài đặt hệ thống</h3>
                    </div>
                    <div class="quick-actions">
                    <div class="quick-actions">
                        <div class="action-card">
                            <i class="fas fa-store"></i>
                            <h4>Thông tin cửa hàng</h4>
                            <p>Cập nhật thông tin, địa chỉ cửa hàng</p>
                        </div>
                        <div class="action-card">
                            <i class="fas fa-credit-card"></i>
                            <h4>Phương thức thanh toán</h4>
                            <p>Cấu hình các phương thức thanh toán</p>
                        </div>
                        <div class="action-card">
                            <i class="fas fa-truck"></i>
                            <h4>Vận chuyển</h4>
                            <p>Thiết lập phí và khu vực giao hàng</p>
                        </div>
                        <div class="action-card">
                            <i class="fas fa-bell"></i>
                            <h4>Thông báo</h4>
                            <p>Cấu hình email và SMS thông báo</p>
                        </div>
                        <div class="action-card">
                            <i class="fas fa-shield-alt"></i>
                            <h4>Bảo mật</h4>
                            <p><a href="?tab=settings&action=change-password" style="color: #777; text-decoration: none;">Thay đổi mật khẩu, xác thực 2 lớp</a></p>
                        </div>
                        <div class="action-card">
                            <i class="fas fa-database"></i>
                            <h4>Sao lưu dữ liệu</h4>
                            <p>Sao lưu và khôi phục dữ liệu</p>
                        </div>
                    </div>                        
                    </div>
                    <?php if ($showChangePassword): ?>
                        <div class="content-section">
                            <h3 class="section-title">Đổi mật khẩu</h3>
                            <form method="post" class="form-group">
                                <div class="form-group">
                                    <label class="form-label">Mật khẩu cũ</label>
                                    <input type="password" name="old" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <input type="password" name="new" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Xác nhận mật khẩu mới</label>
                                    <input type="password" name="confirm" class="form-input" required>
                                </div>
                                <div style="display: flex; gap: 1rem;">
                                    <a href="?tab=settings" class="btn btn-danger">Hủy</a>
                                    <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Thêm script inline để xử lý thông báo -->
            <script>
                // Hàm hiển thị thông báo với hiệu ứng
                function showAlert(message, type) {
                    const alertDiv = document.getElementById('notification-alert');
                    alertDiv.className = `alert ${type}`; // Áp dụng class alert và type
                    alertDiv.textContent = message;

                    // Hiển thị thông báo
                    alertDiv.style.display = 'block';

                    // Reset animation để chạy lại hiệu ứng
                    alertDiv.style.animation = 'none';
                    alertDiv.offsetHeight; // Trigger reflow
                    alertDiv.style.animation = 'slideInRight 0.5s ease-out forwards, fadeOut 2.5s ease-out 2.5s forwards';

                    // Ẩn hoàn toàn sau khi fade out
                    setTimeout(() => {
                        alertDiv.style.display = 'none';
                    }, 5000); // Tổng thời gian hiển thị là 5s (2.5s trượt vào + 2.5s fade out)
                }

                // Kiểm tra và hiển thị thông báo khi trang load
                document.addEventListener('DOMContentLoaded', () => {
                    <?php if ($success): ?>
                        showAlert('<?= htmlspecialchars($success) ?>', 'alert-success');
                    <?php endif; ?>
                    <?php if ($error): ?>
                        showAlert('<?= htmlspecialchars($error) ?>', 'alert-danger');
                    <?php endif; ?>
                });
            </script>
            

    <!-- Product Modal -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('product-modal')">&times;</span>
            <h2>Thêm sản phẩm mới</h2>
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Tên sản phẩm</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Link hình ảnh</label>
                    <input type="text" name="image" class="form-input" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label class="form-label">Danh mục</label>
                    <select name="category" class="form-select" required>
                        <option value="">Chọn danh mục</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>

                </div>
                <div class="form-group">
                    <label class="form-label">Giá bán</label>
                    <input type="number" name="price" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Số lượng</label>
                    <input type="number" name="stock" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-input" rows="4"></textarea>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="button" class="btn btn-danger" onclick="closeModal('product-modal')">Hủy</button>
                    <button type="submit" name="add_product" class="btn btn-primary">Thêm sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
        <!-- Order Modal -->
        <div id="order-modal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('order-modal')">×</span>
                <h2>Tạo đơn hàng mới</h2>
                <form method="post">
                    <div class="form-group">
                        <label class="form-label">Khách hàng</label>
                        <select name="customer" class="form-select" required>
                            <option value="">Chọn khách hàng</option>
                            <?php
                            $users = $pdo->query("
                                SELECT id, name 
                                FROM users 
                                WHERE name IS NOT NULL 
                                AND email IS NOT NULL 
                                AND phone IS NOT NULL 
                                AND address IS NOT NULL 
                                AND city IS NOT NULL 
                                AND district IS NOT NULL
                            ")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($users as $user) {
                                echo "<option value='" . $user['id'] . "'>" . htmlspecialchars($user['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sản phẩm</label>
                        <select name="product" class="form-select" required>
                            <option value="">Chọn sản phẩm</option>
                            <?php
                            $products = $pdo->query("SELECT id, name FROM products ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($products as $p) {
                                echo "<option value='" . $p['id'] . "'>" . htmlspecialchars($p['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Số lượng</label>
                        <input type="number" name="quantity" class="form-input" required min="1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giảm giá (%)</label>
                        <input type="number" name="discount" class="form-input" min="0" max="100" value="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="note" class="form-input" rows="4"></textarea>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <button type="button" class="btn btn-danger" onclick="closeModal('order-modal')">Hủy</button>
                        <button type="submit" name="create_order" class="btn btn-primary">Tạo đơn hàng</button>
                    </div>
                </form>
            </div>
        </div>

    <!-- Confirm Delete Modal -->
    <div id="confirm-delete-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('confirm-delete-modal')">&times;</span>
            <h2>Xác nhận xóa</h2>
            <p>Bạn có chắc chắn muốn xóa mục này?</p>
            <div style="display: flex; gap: 1rem;">
                <button class="btn btn-danger" onclick="closeModal('confirm-delete-modal')">Hủy</button>
                <button class="btn btn-primary" id="confirm-delete-btn">Xóa</button>
            </div>
        </div>
    </div>
    <!-- Modal Sửa sản phẩm -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-modal')">&times;</span>
            <h2>Sửa sản phẩm</h2>
            <form method="post">
                <input type="hidden" name="id" id="edit-id">
                <div class="form-group">
                    <label class="form-label">Tên sản phẩm</label>
                    <input type="text" name="name" id="edit-name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Link hình ảnh</label>
                    <input type="text" name="image" id="edit-image" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Danh mục</label>
                    <input type="text" name="category" id="edit-category" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Giá bán</label>
                    <input type="number" name="price" id="edit-price" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Số lượng</label>
                    <input type="number" name="stock" id="edit-stock" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" id="edit-description" class="form-input" rows="4"></textarea>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="button" class="btn btn-danger" onclick="closeModal('edit-modal')">Hủy</button>
                    <button type="submit" name="edit_product" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Sửa trạng thái đơn hàng -->
    <div id="edit-order-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('edit-order-modal')">×</span>
            <h2>Sửa trạng thái đơn hàng</h2>
            <form method="post" action="admin.php">
                <input type="hidden" name="order_id" id="edit-order-id">
                <div class="form-group">
                    <label class="form-label">Trạng thái đơn hàng</label>
                    <select name="new_status" id="edit-order-status" class="form-select" required>
                        <option value="Chờ xử lý">Chờ xử lý</option>
                        <option value="Đang xử lý">Đang xử lý</option>
                        <option value="Đã giao">Đã giao</option>
                        <option value="Hủy">Hủy</option>
                    </select>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('edit-order-modal')">Hủy</button>
                    <button type="submit" name="update_order_status" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Xem đơn hàng của khách hàng -->
    <div id="customer-orders-modal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close" onclick="closeModal('customer-orders-modal')">&times;</span>
            <h2>Đơn hàng của khách hàng</h2>
            <div id="customer-orders-body">
                <p>Đang tải đơn hàng...</p>
            </div>
            <div style="margin-top: 1rem;">
                <button class="btn btn-secondary" onclick="closeModal('customer-orders-modal')">Đóng</button>
            </div>
        </div>
    </div>
    <!-- Modal Nhập kho -->
    <div id="importModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h4>Nhập kho sản phẩm</h4>
            <form id="importForm" method="POST" action="admin.php?action=import_stock">
                <input type="hidden" name="product_id" id="import_product_id">
                <div>
                    <label>Số lượng nhập:</label>
                    <input type="number" name="quantity" required min="1">
                </div>
                <button type="submit" class="btn btn-success">Xác nhận</button>
                <button type="button" class="btn btn-secondary" onclick="closeImportModal()">Hủy</button>
            </form>
        </div>
    </div>
</body>
</html>
