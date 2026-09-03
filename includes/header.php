<?php

if(session_status() === PHP_SESSION_NONE)
{
    session_start();
}

$baseUrl = '/SafeTrade-SA2/';

?>

<?php

$baseUrl = '/SafeTrade-SA2/';

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>SafeTrade SA</title>

<link rel="stylesheet"
href="<?= $baseUrl; ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
</head>

<body>

<nav>

<div class="nav-container">

<a
href="<?= $baseUrl; ?>index.php"
class="site-logo">
SafeTrade SA
</a>

<ul>

<li>
<a href="<?= $baseUrl; ?>index.php">
Home
</a>
</li>

<li>
<a href="<?= $baseUrl; ?>products.php">
Products
</a>
</li>

<?php if(isset($_SESSION['user_id'])): ?>

<li>
<a href="<?= $baseUrl; ?>dashboard.php">
Dashboard
</a>
</li>

<?php else: ?>

<li>
<a href="<?= $baseUrl; ?>login.php">
Login
</a>
</li>

<li>
<a href="<?= $baseUrl; ?>register.php">
Register
</a>
</li>

<?php endif; ?>

</ul>

</div>

</nav>