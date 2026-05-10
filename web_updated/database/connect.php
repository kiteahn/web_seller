<?php
require_once __DIR__ . '/../config/database_config.php';
$conn = mysqli_connect($host, $username, $password, $dbname);
if (!$conn) {
    die("Kết nối CSDL thất bại: " . mysqli_connect_error());
}
