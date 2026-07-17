<?php
session_start();

if (empty($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['student', 'teacher'], true)) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/User.php';

$database = new Database();
$conn = $database->connect();
$user = new User($conn);

$user->clearNotificationsForUser((int) $_SESSION['user']['id']);

$fallback = $_SESSION['user']['role'] === 'teacher' ? 'teacher_recruitment_posts.php' : 'create_post.php';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$target = $fallback;

if ($referer !== '') {
    $path = parse_url($referer, PHP_URL_PATH);
    $file = basename((string) $path);
    if ($file !== '' && preg_match('/^[A-Za-z0-9_\-]+\.php$/', $file)) {
        $target = $file;
    }
}

header('Location: ' . $target);
exit;
