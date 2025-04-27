<?php
ob_start();
require __DIR__ . '/service_users.php';
$users = json_decode(ob_get_clean(), true);

ob_start();
require __DIR__ . '/service_products.php';
$products = json_decode(ob_get_clean(), true);

header('Content-Type: application/json');
echo json_encode([
    "users" => $users,
    "products" => $products
]);