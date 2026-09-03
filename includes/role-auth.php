<?php

function requireRole($role)
{
    if(
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== $role
    )
    {
        header(
            "Location: /SafeTrade-SA2/dashboard.php"
        );

        exit();
    }
}
?>