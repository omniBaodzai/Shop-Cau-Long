<?php
include '../connect.php'; // Kết nối cơ sở dữ liệu

// Lấy danh mục từ tham số GET
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Lấy số lượng sản phẩm cần hiển thị (mặc định là 4)
$limit = 4;

// Truy vấn sản phẩm theo danh mục
if ($category === 'all') {
    $sql = "SELECT id, name, image, price FROM products LIMIT ?";
} else {
    $sql = "SELECT id, name, image, price FROM products WHERE category = ? LIMIT ?";
}

$stmt = $conn->prepare($sql);
if ($category === 'all') {
    $stmt->bind_param("i", $limit);
} else {
    $stmt->bind_param("si", $category, $limit);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'image' => $row['image'],
        'price' => number_format($row['price'], 0, ',', '.')
    ];
}

// Trả về dữ liệu dưới dạng JSON
header('Content-Type: application/json');
echo json_encode($products);
?>