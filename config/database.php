<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "qlsinhvien";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>