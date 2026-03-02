<?php
// Fetch filter parameters
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$priceFilter = isset($_GET['price']) ? $_GET['price'] : '';
$brandFilter = isset($_GET['brand']) ? $conn->real_escape_string($_GET['brand']) : '';

// Build WHERE clause for product filtering
$whereClauses = [];
if ($keyword !== '') {
    $keyword_escaped = $conn->real_escape_string($keyword);
    $whereClauses[] = "name LIKE '%$keyword_escaped%'";
}
if (!empty($category)) {
    $whereClauses[] = "category = '$category'";
}
if (!empty($brandFilter)) {
    $whereClauses[] = "brand = '$brandFilter'";
}
switch ($priceFilter) {
    case 'under500':
        $whereClauses[] = "price < 500000";
        break;
    case '500to1m':
        $whereClauses[] = "price >= 500000 AND price <= 1000000";
        break;
    case '1to2m':
        $whereClauses[] = "price > 1000000 AND price <= 2000000";
        break;
    case '2to3m':
        $whereClauses[] = "price > 2000000 AND price <= 3000000";
        break;
    case 'above3m':
        $whereClauses[] = "price > 3000000";
        break;
}

$whereSQL = '';
if (count($whereClauses) > 0) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Fetch all products for JavaScript pagination
$sql = "SELECT id, image, name, price, brand FROM products $whereSQL ORDER BY id DESC";
$result = $conn->query($sql);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            'id' => $row['id'],
            'img' => $row['image'],
            'name' => $row['name'],
            'price' => number_format($row['price'], 0, ',', '.') . ' đ',
            'brand' => $row['brand']
        ];
    }
}

// Fetch available categories
$sql_categories = "SELECT DISTINCT category FROM products ORDER BY category ASC";
$result_categories = $conn->query($sql_categories);
$availableCategories = [];
if ($result_categories && $result_categories->num_rows > 0) {
    while ($row_category = $result_categories->fetch_assoc()) {
        $availableCategories[] = $row_category['category'];
    }
}

// Fetch available brands based on selected category and keyword (if any)
$brandWhereClauses = [];
if (!empty($category)) {
    $brandWhereClauses[] = "category = '$category'";
}
if ($keyword !== '') {
    $keyword_escaped = $conn->real_escape_string($keyword);
    $brandWhereClauses[] = "name LIKE '%$keyword_escaped%'";
}
$brandWhere = $brandWhereClauses ? 'WHERE ' . implode(' AND ', $brandWhereClauses) : '';
$sql_brands = "SELECT DISTINCT brand FROM products $brandWhere ORDER BY brand ASC";
$result_brands = $conn->query($sql_brands);
$availableBrands = [];
if ($result_brands && $result_brands->num_rows > 0) {
    while ($row_brand = $result_brands->fetch_assoc()) {
        $availableBrands[] = $row_brand['brand'];
    }
}

// Base URL for filter links
$baseQuery = $keyword !== '' ? "q=" . urlencode($keyword) . "&" : "";
?>

<aside class="badminton-sidebar">
    <section class="badminton-filter">
        <div class="filter-title">MỨC GIÁ</div>
        <ul class="filter-list">
            <li><a href="?<?= $baseQuery ?>category=<?= urlencode($category) ?>&brand=<?= urlencode($brandFilter) ?>" class="<?= empty($priceFilter) ? 'active' : '' ?>"><i class="ri-price-tag-3-line"></i> Tất cả</a></li>
            <li><a href="?<?= $baseQuery ?>category=<?= urlencode($category) ?>&price=under500&brand=<?= urlencode($brandFilter) ?>" class="<?= $priceFilter === 'under500' ? 'active' : '' ?>"><i class="ri-price-tag-3-line"></i> Dưới 500.000đ</a></li>
            <li><a href="?<?= $baseQuery ?>category=<?= urlencode($category) ?>&price=500to1m&brand=<?= urlencode($brandFilter) ?>" class="<?= $priceFilter === '500to1m' ? 'active' : '' ?>"><i class="ri-price-tag-3-line"></i> 500.000đ - 1tr</a></li>
            <li><a href="?<?= $baseQuery ?>category=<?= urlencode($category) ?>&price=1to2m&brand=<?= urlencode($brandFilter) ?>" class="<?= $priceFilter === '1to2m' ? 'active' : '' ?>"><i class="ri-price-tag-3-line"></i> 1tr - 2tr</a></li>
            <li><a href="?<?= $baseQuery ?>category=<?= urlencode($category) ?>&price=2to3m&brand=<?= urlencode($brandFilter) ?>" class="<?= $priceFilter === '2to3m' ? 'active' : '' ?>"><i class="ri-price-tag-3-line"></i> 2tr - 3tr</a></li>
            <li><a href="?<?= $baseQuery ?>category=<?= urlencode($category) ?>&price=above3m&brand=<?= urlencode($brandFilter) ?>" class="<?= $priceFilter === 'above3m' ? 'active' : '' ?>"><i class="ri-price-tag-3-line"></i> Trên 3tr</a></li>
        </ul>
    </section>
    <section class="badminton-filter">
        <div class="filter-title">THƯƠNG HIỆU</div>
        <ul class="filter-list">
            <li><a href="?<?= $baseQuery ?>category=<?= urlencode($category) ?>&price=<?= urlencode($priceFilter) ?>" class="<?= empty($brandFilter) ? 'active' : '' ?>"><i class="ri-store-line"></i> Tất cả</a></li>
            <?php foreach ($availableBrands as $brand): ?>
                <li><a href="?<?= $baseQuery ?>category=<?= urlencode($category) ?>&price=<?= urlencode($priceFilter) ?>&brand=<?= urlencode($brand) ?>" class="<?= $brandFilter === $brand ? 'active' : '' ?>"><i class="ri-store-line"></i> <?= htmlspecialchars($brand) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="badminton-filter">
        <div class="filter-title">DANH MỤC</div>
        <ul class="filter-list">
            <li><a href="?<?= $baseQuery ?>price=<?= urlencode($priceFilter) ?>&brand=<?= urlencode($brandFilter) ?>" class="<?= empty($category) ? 'active' : '' ?>"><i class="ri-store-line"></i> Tất cả sản phẩm</a></li>
            <?php foreach ($availableCategories as $cat): ?>
                <li><a href="?<?= $baseQuery ?>category=<?= urlencode($cat) ?>&price=<?= urlencode($priceFilter) ?>&brand=<?= urlencode($brandFilter) ?>" class="<?= $category === $cat ? 'active' : '' ?>">
                    <?php if ($cat === 'Vợt Cầu Lông'): ?>
                        <iconify-icon icon="mdi:badminton" width="18" style="margin-right: 6px;"></iconify-icon> <?= htmlspecialchars($cat) ?>
                    <?php elseif ($cat === 'Giày Cầu Lông'): ?>
                        <i class="ri-footprint-line"></i> <?= htmlspecialchars($cat) ?>
                    <?php elseif ($cat === 'Áo Cầu Lông' || $cat === 'Quần Cầu Lông' || $cat === 'Váy Cầu Lông'): ?>
                        <i class="ri-t-shirt-line"></i> <?= htmlspecialchars($cat) ?>
                    <?php elseif ($cat === 'Ống Cầu Lông'): ?>
                        <i class="ri-capsule-line"></i> <?= htmlspecialchars($cat) ?>
                    <?php elseif ($cat === 'Phụ Kiện Cầu Lông'): ?>
                        <i class="ri-tools-line"></i> Phụ Kiện
                    <?php else: ?>
                        <i class="ri-store-line"></i> <?= htmlspecialchars($cat) ?>
                    <?php endif; ?>
                </a></li>
            <?php endforeach; ?>
        </ul>
    </section>
</aside>

<style>
    /* Compact Filter Styling */
    .badminton-sidebar {
        width: 200px;
        padding: 12px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .badminton-filter {
        margin-bottom: 15px;
    }

    .filter-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1d3557;
        margin-bottom: 8px;
        text-transform: uppercase;
        position: relative;
        padding-bottom: 4px;
    }

    .filter-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 25px;
        height: 2px;
        background: linear-gradient(90deg, #e63946, #ff7f2a);
        border-radius: 1px;
    }

    .filter-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .filter-list a {
        display: flex;
        align-items: center;
        padding: 6px 10px;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .filter-list a:hover {
        background-color: #f8f9fa;
        color: #e63946;
    }

    .filter-list a.active {
        background-color: #f1f3f5;
        color: #e63946;
        font-weight: 600;
    }

    .filter-list a i, .filter-list a iconify-icon {
        margin-right: 6px;
        font-size: 0.85rem;
        color: #666;
    }

    .filter-list a:hover i, .filter-list a:hover iconify-icon,
    .filter-list a.active i, .filter-list a.active iconify-icon {
        color: #e63946;
    }

    @media (max-width: 768px) {
        .badminton-sidebar {
            width: 100%;
            padding: 10px;
        }

        .filter-title {
            font-size: 0.9rem;
        }

        .filter-list a {
            padding: 5px 8px;
            font-size: 0.8rem;
        }

        .filter-list a i, .filter-list a iconify-icon {
            font-size: 0.8rem;
            margin-right: 5px;
        }
    }
</style>