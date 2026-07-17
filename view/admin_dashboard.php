<?php
session_start();
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/User.php';

$database = new Database();
$conn = $database->connect();
$user = new User($conn);

$totalStudents = $user->countUsersByRole('student');
$totalTeachers = $user->countUsersByRole('teacher');
$totalAdmins = $user->countUsersByRole('admin');
$pendingStudents = $user->countPendingUsersByRole('student');
$pendingTeachers = $user->countPendingUsersByRole('teacher');
$totalThesisTopics = $user->countExistingTables(['thesis_topics', 'topics']);
$totalGroups = $user->countExistingTables(['groups', 'research_groups']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ThesisConnect</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <div class="brand">
            <h1>ThesisConnect</h1>
            <span>Admin Panel</span>
        </div>
        <nav>
            <a class="active" href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="admin_manage_students.php"><i class="fa-solid fa-user-graduate"></i> Manage Students</a>
            <a href="admin_manage_teachers.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Teachers</a>
            <a href="admin_manage_admins.php"><i class="fa-solid fa-user-plus"></i> Manage Admins</a>
            <a href="admin_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
            <a href="admin_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="admin_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
    </aside>
    <main class="dashboard-main">
        <header>
            <div>
                <h2>Welcome, Admin</h2>
                <p>Overview of your ThesisConnect platform.</p>
            </div>
            <div class="user-card">
                <i class="fa-solid fa-user-shield"></i>
                <div>
                    <span>Admin</span>
                    <strong><?= htmlspecialchars($_SESSION['user']['email']) ?></strong>
                </div>
            </div>
        </header>
        <section class="dashboard-grid">
            <div class="card">
                <h3>Total Students</h3>
                <p><?= $totalStudents ?></p>
            </div>
            <div class="card">
                <h3>Total Teachers</h3>
                <p><?= $totalTeachers ?></p>
            </div>
            <div class="card card-secondary">
                <h3>Total Admins</h3>
                <p><?= $totalAdmins ?></p>
            </div>
            <div class="card card-warning">
                <h3>Pending Students</h3>
                <p><?= $pendingStudents ?></p>
            </div>
            <div class="card card-warning">
                <h3>Pending Teachers</h3>
                <p><?= $pendingTeachers ?></p>
            </div>
            <div class="card card-secondary">
                <h3>Total Thesis Topics</h3>
                <p><?= $totalThesisTopics ?></p>
            </div>
            <div class="card card-secondary">
                <h3>Total Groups</h3>
                <p><?= $totalGroups ?></p>
            </div>
        </section>
    </main>
</div>
</body>
</html>
