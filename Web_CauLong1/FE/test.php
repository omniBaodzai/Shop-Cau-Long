<?php
include '../Includes/db.php';

$category = isset($_GET['category']) ? mysqli_real_escape_string($link, $_GET['category']) : null;
$where_sql = $category ? "WHERE category = '$category'" : "";

// Lấy sản phẩm rẻ nhất bên trái
$featured_sql = "SELECT id, name, image, price FROM products $where_sql ORDER BY price ASC LIMIT 1";
$featured = mysqli_query($link, $featured_sql)->fetch_assoc();

// Lấy 8 sản phẩm đầu tiên bên phải
$product_sql = "SELECT id, name, image, price FROM products $where_sql ORDER BY id ASC LIMIT 8";
$products = mysqli_query($link, $product_sql);
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trang chủ VNSPORTS</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .Content { margin-top: 20px; }
        .Content .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .Content .section-header ul.tabs { list-style: none; display: flex; gap: 10px; }
        .Content .section-header ul.tabs li a { text-decoration: none; padding: 5px 10px; background: #eee; border-radius: 4px; }
        .Content .section-header ul.tabs li a.active, 
        .Content .section-header ul.tabs li a:hover { background: #e60000; color: #fff; }

        .content-row { display: flex; gap: 20px; }
        .content-left { flex: 1; }
        .content-left img { width: 100%; border-radius: 8px; transition: 0.3s; }
        .content-left img:hover { transform: scale(1.03); }

        .content-right { flex: 2; display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
        .product { background: #fff; border: 1px solid #ddd; text-align: center; padding: 10px; border-radius: 6px; transition: 0.3s; }
        .product:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.2); transform: translateY(-5px); }
        .product img { width: 100%; aspect-ratio: 1/1; object-fit: cover; border-radius: 4px; }
        .product h4 { font-size: 14px; margin: 8px 0; }
        .product p { color: #e60000; font-weight: bold; }
    </style>
</head>
<body>
<div id="khung">
<?php include("../Includes/layout/header.php"); ?>

<div class="Banner">
    <div class="banner-ads" style="text-align: center;">
    <a href="#">
        <img src="../assets/Picture/slide1.jpg" alt="Quảng cáo vợt cầu lông" style="max-width: 100%; height: auto; border-radius: 5px;">
    </a>
    </div>
</div>

<div class="Content">
    <div class="section-header">
        <h3>VỢT CẦU LÔNG</h3>
        <ul class="tabs">
            <li><a href="#">Xem tất cả</a></li>
        </ul>
    </div>

<div class="content-row">
        <div class="content-left">
            <?php if($featured): ?>
                <a href="product.php?id=<?= $featured['id'] ?>">
                    <img src="<?= htmlspecialchars($featured['image']) ?>" alt="<?= htmlspecialchars($featured['name']) ?>">
                </a>
            <?php else: ?>
                <p>Không có sản phẩm nào phù hợp.</p>
            <?php endif; ?>
        </div>
        <div class="content-right">
            <?php if(mysqli_num_rows($products) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($products)): ?>
                    <div class="product">
                        <a href="product.php?id=<?= $row['id'] ?>">
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                            <h4><?= htmlspecialchars($row['name']) ?></h4>
                            <p><?= number_format($row['price'], 0, ',', '.') ?>₫</p>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không có sản phẩm nào.</p>
            <?php endif; ?>
        </div>
    </div>

      <div class="Banner">
                <div class="banner-ads" style="text-align: center;">
                <a href="#">
                    <img src="../assets/Picture/slide4.jpg" alt="Quảng cáo vợt cầu lông" style="max-width: 100%; height: auto; border-radius: 5px;">
                </a>
                </div>
            </div>

            <div class="Content">
                <div class="section-header">
                    <h3>TÚI CẦU LÔNG</h3>
                    <ul class="tabs">
                        <li><a href="#">Mới nhất</a></li>
                        <li><a href="#" class="active">Bán chạy</a></li>
                        <li><a href="#">Xem tất cả</a></li>
                    </ul>
                </div>

                <div class="left">
                    Content1
                </div>
                <div class="right">
                    <div class="product-grid">
                    <div class="product">
                        <a href="https://dasxsport.vn/san-pham/xstorm-tre-em-copa-icon-xanh">
                        <img src="https://dasxsport.vn/storage/day/dsc06527-min.jpg" alt="Xstorm Trẻ Em Copa Icon - Xanh trắng">
                        <h4>Xstorm Trẻ Em Copa Icon - Xanh trắng</h4>
                        <p>280.000₫</p>
                        </a>
                    </div>

                    <div class="product">
                        <a href="https://dasxsport.vn/san-pham/xstorm-tre-em-copa-icon-2">
                        <img src="https://dasxsport.vn/storage/day/dsc06510-min.jpg" alt="Xstorm Trẻ Em Copa Icon - Trắng đen cam">
                        <h4>Xstorm Trẻ Em Copa Icon - Trắng đen cam</h4>
                        <p>280.000₫</p>
                        </a>
                    </div>

                    <div class="product">
                        <a href="https://dasxsport.vn/san-pham/xstorm-tre-em-copa-icon-trang-do-den-1">
                        <img src="https://dasxsport.vn/storage/day/dsc06495-min.jpg" alt="Xstorm Trẻ Em Copa Icon - Trắng xanh đen">
                        <h4>Xstorm Trẻ Em Copa Icon - Trắng xanh đen</h4>
                        <p>280.000₫</p>
                        </a>
                    </div>

                    <div class="product">
                        <a href="https://dasxsport.vn/san-pham/xstorm-tre-em-copa-icon-xanh">
                        <img src="https://dasxsport.vn/storage/day/dsc06527-min.jpg" alt="Xstorm Trẻ Em Copa Icon - Xanh trắng">
                        <h4>Xstorm Trẻ Em Copa Icon - Xanh trắng</h4>
                        <p>280.000₫</p>
                        </a>
                    </div>

                    <div class="product">
                        <a href="https://dasxsport.vn/san-pham/xstorm-tre-em-copa-icon-xanh">
                        <img src="https://dasxsport.vn/storage/day/dsc06527-min.jpg" alt="Xstorm Trẻ Em Copa Icon - Xanh trắng">
                        <h4>Xstorm Trẻ Em Copa Icon - Xanh trắng</h4>
                        <p>280.000₫</p>
                        </a>
                    </div>

                    <!-- Lặp tương tự cho các sản phẩm còn lại -->
                    </div>
                </div>
            </div>

<?php include("../Includes/layout/footer.php"); ?>
</div>
</body>
</html>
