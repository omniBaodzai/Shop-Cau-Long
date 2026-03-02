<?php
include '../connect.php';
include '../includes/header.php';

// Lấy biến từ URL nếu có
$category = isset($_GET['category']) ? $_GET['category'] : '';
$brandFilter = isset($_GET['brand']) ? $_GET['brand'] : '';

// Tạo tiêu đề động
$pageTitleParts = [];

if (!empty($category)) {
    $pageTitleParts[] = '' . $category;
} else {
    $pageTitleParts[] = 'Tất cả sản phẩm';
}

if (!empty($brandFilter)) {
    $pageTitleParts[] = '' . $brandFilter;
}

// Ghép tiêu đề và chuyển sang chữ in hoa có dấu
$pageTitle = mb_strtoupper(implode(' - ', $pageTitleParts), 'UTF-8');
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/sanpham.js"></script>
</head>
<body>
<main class="badminton-main">
    <div class="badminton-container">
        <nav class="badminton-breadcrumb">
            <a href="../index.php">Trang chủ</a>
            <span class="breadcrumb-sep">›</span>
            <a href="products.php<?php echo !empty($category) ? '?category=' . urlencode($category) : ''; ?>"><?php echo htmlspecialchars(!empty($category) ? $category : 'Tất cả sản phẩm'); ?></a>
            <?php if (!empty($brandFilter)): ?>
                <span class="breadcrumb-sep">›</span>
                <span><?php echo htmlspecialchars($brandFilter); ?></span>
            <?php endif; ?>
        </nav>
        <div class="badminton-body">
            <?php include 'loc.php'; ?>
            <section class="badminton-content">
                <div class="badminton-content-header">
                    <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                </div>
                <div class="badminton-list"></div>
                <div class="badminton-pagination"></div>
            </section>
        </div>
    </div>
</main>

<script>
    window.products = <?php echo json_encode($products); ?>;
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>