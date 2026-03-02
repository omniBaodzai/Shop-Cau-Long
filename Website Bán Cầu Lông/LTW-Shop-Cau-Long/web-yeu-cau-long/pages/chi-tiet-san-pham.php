<?php
session_start(); // Bắt đầu session để lấy thông tin người dùng
include '../connect.php'; // Kết nối cơ sở dữ liệu
include '../includes/header.php'; 

// Kiểm tra xem người dùng đã đăng nhập hay chưa
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

// Lấy tên người dùng từ bảng users
$user_name = '';
if ($user_id > 0) {
    $sql_user = "SELECT name FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    if ($stmt_user === false) {
        die("Prepare failed for user query: " . $conn->error);
    }
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
        $user_name = htmlspecialchars($user['name']);
    }
    $stmt_user->close();
}

// Lấy id sản phẩm từ URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Truy vấn thông tin sản phẩm từ cơ sở dữ liệu
$sql_product = "SELECT id, name, image, price, old_price, sku, warranty, description, specs, promotion, category FROM products WHERE id = ?";
$stmt_product = $conn->prepare($sql_product);
if ($stmt_product === false) {
    die("Prepare failed for product query: " . $conn->error);
}
$stmt_product->bind_param("i", $product_id);
$stmt_product->execute();
$result_product = $stmt_product->get_result();

if ($result_product->num_rows > 0) {
    $product = $result_product->fetch_assoc();
    $category_name = htmlspecialchars($product['category']);
} else {
    echo "<p>Sản phẩm không tồn tại.</p>";
    exit();
}
$stmt_product->close();

$promotion = isset($product['promotion']) ? $product['promotion'] : '';

// Xử lý form gửi đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    $rating = intval($_POST['rating']);
    $content = htmlspecialchars($_POST['content']);

    // Kiểm tra xem người dùng đã đăng nhập chưa trước khi gửi đánh giá
    if ($user_id == 0) {
        echo '<div id="review-message" class="alert-error">Vui lòng đăng nhập để gửi đánh giá.</div>';
    } elseif ($rating >= 1 && $rating <= 5 && !empty($user_name) && !empty($content)) {
        $sql_insert_review = "INSERT INTO reviews (product_id, user_name, rating, content) VALUES (?, ?, ?, ?)";
        $stmt_insert_review = $conn->prepare($sql_insert_review);
        if ($stmt_insert_review === false) {
            die("Prepare failed for review query: " . $conn->error);
        }
        $stmt_insert_review->bind_param("isis", $product_id, $user_name, $rating, $content);
        if ($stmt_insert_review->execute()) {
            echo '<div id="review-message" class="alert-success">Đánh giá của bạn đã được gửi!</div>';
        } else {
            echo '<div id="review-message" class="alert-error">Đã xảy ra lỗi khi gửi đánh giá. Vui lòng thử lại.</div>';
        }
        $stmt_insert_review->close();
    } else {
        echo '<div id="review-message" class="alert-error">Vui lòng nhập đầy đủ thông tin và chọn số sao hợp lệ.</div>';
    }
}

// Truy vấn đánh giá sản phẩm từ bảng reviews
$sql_reviews = "SELECT user_name, rating, content FROM reviews WHERE product_id = ?";
$stmt_reviews = $conn->prepare($sql_reviews);
if ($stmt_reviews === false) {
    die("Prepare failed for reviews query: " . $conn->error);
}
$stmt_reviews->bind_param("i", $product_id);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();
$stmt_reviews->close();

// Tính toán đánh giá trung bình và tổng số đánh giá
$avg_rating = 0;
$total_reviews = 0;
$sql_avg_rating = "SELECT AVG(rating) AS avg_rating, COUNT(id) AS total_reviews FROM reviews WHERE product_id = ?";
$stmt_avg_rating = $conn->prepare($sql_avg_rating);
if ($stmt_avg_rating === false) {
    die("Prepare failed for avg rating query: " . $conn->error);
}
$stmt_avg_rating->bind_param("i", $product_id);
$stmt_avg_rating->execute();
$result_avg_rating = $stmt_avg_rating->get_result();
if ($result_avg_rating->num_rows > 0) {
    $row_avg_rating = $result_avg_rating->fetch_assoc();
    $avg_rating = round($row_avg_rating['avg_rating'], 1);
    $total_reviews = $row_avg_rating['total_reviews'];
}
$stmt_avg_rating->close();
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/ctsp.css">

<script src="../assets/js/ctsp.js"></script>

<main class="product-detail-main sporty">
  <div class="product-detail-container">
    <nav class="badminton-breadcrumb">
      <a href="../index.php">Trang chủ</a>
      <span class="breadcrumb-sep">›</span>
      <a href="products.php?category=<?php echo urlencode($category_name); ?>"><?php echo $category_name; ?></a>
      <span class="breadcrumb-sep">›</span>
      <span><?php echo htmlspecialchars($product['name']); ?></span>
    </nav>
    <div class="product-detail-body">
      <div class="product-detail-gallery">
        <div class="product-detail-img-zoom sporty-zoom">
          <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" id="mainProductImg" />
        </div>
      </div>
      <div class="product-detail-info">
        <h1 class="product-detail-name sporty-gradient"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="product-detail-meta">
          <span><i class="ri-barcode-box-line"></i> Mã SP: <b><?php echo htmlspecialchars($product['sku']); ?></b></span>
          <span><i class="ri-shield-check-line"></i> Bảo hành: <b><?php echo htmlspecialchars($product['warranty']); ?></b></span>
          <span><i class="ri-truck-line"></i> Vận chuyển: <b>Toàn quốc</b></span>
        </div>
        <div class="product-detail-promo sporty-promo">
          <div class="promo-title"><i class="ri-gift-2-fill"></i> Ưu đãi hôm nay</div>
          <ul>
            <?php 
            $promo_items = explode(',', $promotion);
            foreach ($promo_items as $item): 
            ?>
              <li><i class="ri-check-double-line"></i> <?php echo htmlspecialchars(trim($item)); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="product-detail-pricebox">
          <span class="product-detail-price"><?php echo number_format($product['price'], 0, ',', '.'); ?> đ</span>
          <?php if ($product['old_price']): ?>
            <span class="product-detail-oldprice"><?php echo number_format($product['old_price'], 0, ',', '.'); ?> đ</span>
            <span class="product-detail-sale"><?php echo round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>%</span>
          <?php endif; ?>
        </div>
        <div class="product-detail-qtybox">
          <label for="qty">Số lượng:</label>
          <div class="qty-control">
            <button type="button" class="qty-btn" onclick="changeQty(-1)">-</button>
            <input type="number" name="quantity" id="qty" value="1" min="1" max="99" required>
            <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
          </div>
        </div>
        <div class="product-detail-actions">
          <form method="POST" action="thanh-toan.php" onsubmit="syncQty(this)">
            <input type="hidden" name="action" value="buy_now">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
            <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
            <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image']); ?>">
            <input type="hidden" name="quantity" id="buy-now-qty">
            <button type="submit" class="btn-buy sporty-btn">Mua ngay</button>
          </form>
          <button type="button" class="btn-cart sporty-btn-outline" onclick="addToCart(<?php echo $product['id']; ?>, document.getElementById('qty').value)">
            <i class="ri-shopping-cart-2-fill"></i> Thêm vào giỏ
          </button>
        </div>
        <!-- Modal thông báo -->
        <div id="cart-modal" class="cart-modal" style="display: none;">
          <div class="cart-modal-header">
            <span><i class="ri-check-line"></i> Thêm sản phẩm vào giỏ hàng thành công</span>
            <span class="close-btn" onclick="closeCartModal()">×</span>
          </div>
          <div class="cart-modal-content">
            <img id="cart-modal-product-image" src="" alt="Sản phẩm">
            <h4 id="cart-modal-product-name">Tên sản phẩm</h4>
            <div id="cart-modal-product-price" class="price">Giá sản phẩm</div>
            <div class="cart-info">
              Giỏ hàng của bạn hiện có <span id="cart-modal-total-items"></span> sản phẩm
            </div>
          </div>
          <div class="cart-modal-buttons">
            <button onclick="closeCartModal()">Tiếp tục mua hàng</button>
            <button onclick="window.location.href='gio-hang.php'">Xem giỏ hàng</button>
          </div>
        </div>
        <div class="product-detail-share">
          <span>Chia sẻ:</span>
          <a href="#"><i class="ri-facebook-circle-fill"></i></a>
          <a href="#"><i class="ri-messenger-fill"></i></a>
          <a href="#"><i class="ri-zalo-fill"></i></a>
        </div>
        <div class="product-detail-extra sporty-extra">
          <div><i class="ri-award-fill"></i> Cam kết chính hãng 100%</div>
          <div><i class="ri-customer-service-2-fill"></i> Hỗ trợ tư vấn 24/7</div>
          <div><i class="ri-refresh-line"></i> Đổi trả trong 7 ngày</div>
        </div>
      </div>
    </div>
    <div class="product-detail-tabs">
      <button class="tab-btn active" onclick="showTab('desc')">Mô tả sản phẩm</button>
      <button class="tab-btn" onclick="showTab('specs')">Thông số kỹ thuật</button>
      <button class="tab-btn" onclick="showTab('review')">Đánh giá</button>
    </div>
    <div class="product-detail-tab-content">
      <div class="tab-pane active" id="desc">
        <h2>Giới thiệu sản phẩm</h2>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
      </div>
      <div class="tab-pane" id="specs">
        <h2>Thông số kỹ thuật</h2>
        <p><?php echo nl2br(htmlspecialchars($product['specs'])); ?></p>
      </div>
      <div class="tab-pane" id="review">
        <h2 class="review-title">Đánh giá sản phẩm</h2>
        <div class="review-summary">
          <span class="review-score"><?php echo $avg_rating; ?></span>
          <span class="review-stars">
            <?php 
            for ($i = 1; $i <= 5; $i++): 
              if ($i <= floor($avg_rating)) {
                echo '<i class="ri-star-fill"></i>';
              } elseif ($i - 0.5 <= $avg_rating) {
                echo '<i class="ri-star-half-fill"></i>';
              } else {
                echo '<i class="ri-star-line"></i>';
              }
            endfor; 
            ?>
          </span>
          <span class="review-count">(<?php echo $total_reviews; ?> đánh giá)</span>
        </div>
        <div class="product-detail-review-list">
          <?php 
          if ($result_reviews->num_rows > 0) {
              $result_reviews->data_seek(0);
          }
          if ($result_reviews->num_rows > 0): ?>
            <?php while ($review = $result_reviews->fetch_assoc()): ?>
              <div class="review-item">
                <div class="review-user"><i class="ri-user-3-fill"></i> <?php echo htmlspecialchars($review['user_name']); ?></div>
                <div class="review-stars">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="ri-star<?php echo $i <= $review['rating'] ? '-fill' : '-line'; ?>"></i>
                  <?php endfor; ?>
                </div>
                <div class="review-content"><?php echo nl2br(htmlspecialchars($review['content'])); ?></div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
          <?php endif; ?>
        </div>
        <div class="product-detail-review-form">
          <h3 class="form-title">Gửi đánh giá của bạn</h3>
          <form method="POST">
            <p><b>Người dùng:</b> <?php echo $user_name ? $user_name : 'Bạn cần đăng nhập để đánh giá'; ?></p>
            <textarea name="content" placeholder="Nhận xét của bạn..." <?php echo $user_id == 0 ? 'disabled' : ''; ?> required></textarea>
            <div class="review-form-stars">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="ri-star-line" data-value="<?php echo $i; ?>"></i>
              <?php endfor; ?>
            </div>
            <input type="hidden" name="rating" id="rating" required />
            <button type="submit" name="review_submit" class="btn-review-send" <?php echo $user_id == 0 ? 'disabled' : ''; ?>>Gửi đánh giá</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
setTimeout(function() {
    var msg = document.getElementById("review-message");
    if (msg) {
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 500);
    }
}, 2000);
</script>
<?php 
$result_reviews->close();
$conn->close();
include '../includes/footer.php'; 
?>