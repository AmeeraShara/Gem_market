<?php
$type = $_GET['type'] ?? '';
$color = $_GET['color'] ?? '';
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 999999999;

$sql = "SELECT * FROM gems 
        WHERE type LIKE '%$type%'
        AND color LIKE '%$color%'
        AND price BETWEEN $min_price AND $max_price
        AND status='approved'";
