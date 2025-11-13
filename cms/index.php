<?php
session_start();
$logged = isset($_SESSION['admin']) && $_SESSION['admin'] === true;
header('Location: ' . ($logged ? 'dashboard.php' : 'login.php'));
exit;
