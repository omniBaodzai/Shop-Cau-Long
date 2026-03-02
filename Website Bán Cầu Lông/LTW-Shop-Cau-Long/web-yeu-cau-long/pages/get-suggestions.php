<?php
include '../connect.php';
header('Content-Type: application/json; charset=UTF-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$suggestions = [];

if ($q !== '') {
    $q_escaped = $conn->real_escape_string($q);
    $sql = "SELECT id, name, image FROM products WHERE name LIKE '%$q_escaped%' ORDER BY id DESC LIMIT 8";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'image' => $row['image']
            ];
        }
    }
}

echo json_encode($suggestions); 