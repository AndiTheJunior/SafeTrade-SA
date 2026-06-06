<?php

echo "DATABASE FILE LOADED<br>";

$host = "localhost";
$dbname = "safetrade";
$user = "root";
$pass = "mysql";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname",
        $user,
        $pass
    );

    echo "PDO CREATED<br>";

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch(PDOException $e) {

    die("Connection Failed: " . $e->getMessage());

}
?>