<?php
session_start();
if (isset($_SESSION['token'])) {
    header('Location: /public/home.php');
} else {
    header('Location: /public/login.php');
}
exit();