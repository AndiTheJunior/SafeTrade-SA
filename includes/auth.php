<?php

if(session_status() === PHP_SESSION_NONE)
{
    session_start();
}

if(!isset($_SESSION['user_id']))
{
    header(
        "Location: /SafeTrade-SA2/login.php"
    );

    exit();
}
?>