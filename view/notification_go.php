<?php
session_start();

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/User.php';

$database = new Database();
$conn = $database->connect();
$user = new User($conn);

$notificationId = (int) ($_GET['id'] ?? 0);
$linkUrl = $notificationId ? $user->readNotification($notificationId, (int) $_SESSION['user']['id']) : false;

if (!$linkUrl) {
    header('Location: student_dashboard.php');
    exit;
}

header('Location: ' . $linkUrl);
exit;
