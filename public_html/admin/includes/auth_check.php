<?php
session_start();

// Check if logged in
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

// Session timeout (2 hours)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 7200) {
    session_destroy();
    header('Location: index.php?timeout=1');
    exit;
}
