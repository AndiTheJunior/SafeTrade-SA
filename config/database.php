<?php

$host = "sql102.infinityfree.com";
$dbname = "if0_42118699_safetrade";
$user = "if0_42118699";
$pass = "TNgjox6Nbwz5jV";
try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch(PDOException $e) {

    die("Connection Failed: " . $e->getMessage());

}
?>