<?php
include '../connect.php';
include '../includes/header.php';

// Lấy biến từ URL nếu chưa có
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$brandFilter = isset($_GET['brand']) ? trim($_GET['brand']) : '';

// Tạo tiêu đề động
$pageTitleParts = ['Kết quả tìm kiếm'];

if (!empty($keyword)) {
    $pageTitleParts[] = '"' . $keyword . '"';
}
if (!empty($category)) {
    $pageTitleParts[] = '' . $category;
}
if (!empty($brandFilter)) {
    $pageTitleParts[] = '' . $brandFilter;
}

// Ghép và chuyển sang in hoa có dấu
$pageTitle = mb_strtoupper(implode(' - ', $pageTitleParts), 'UTF-8');
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.iconify.design/iconify-icon/1.0.8/iconify-icon.min.js"></script>

</head>
<body>
<main class="badminton-main">
    <div class="badminton-container">
        <nav class="badminton-breadcrumb">
            <a href="../index.php">Trang chủ</a>
            <span class="breadcrumb-sep">›</span>
            <a href="tim-kiem.php?q=<?= urlencode($keyword ?? '') ?>">Kết quả tìm kiếm</a>
            <?php if (!empty($category)): ?>
                <span class="breadcrumb-sep">›</span>
                <a href="?q=<?= urlencode($keyword ?? '') ?>&category=<?= urlencode($category) ?>"><?= htmlspecialchars($category) ?></a>
            <?php endif; ?>
            <?php if (!empty($brandFilter)): ?>
                <span class="breadcrumb-sep">›</span>
                <span><?= htmlspecialchars($brandFilter) ?></span>
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
<script src="../assets/js/sanpham.js"></script>

<?php include '../includes/footer.php'; ?>
</body>
</html>