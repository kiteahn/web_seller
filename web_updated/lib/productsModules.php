<?php
require_once __DIR__ . "/../database/connect.php";

function getProducts() {
    global $conn;
    $products = [];

    if (!$conn) {
        return $products;
    }

    $sql = "SELECT p.*, t.name as type_name, t.icon_class as type_icon
            FROM products p
            INNER JOIN types t ON p.type_id = t.id
            ORDER BY p.id DESC";
    $result = mysqli_query($conn, $sql);

    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }

    return $products;
}
function getCategories() {
    global $conn;
    $categories = [];

    if (!$conn) {
        return $categories;
    }

    $sql = "SELECT * FROM categories ORDER BY id DESC";
    $result = mysqli_query($conn, $sql);

    if($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
    }

    return $categories;
}
?>
