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
$admin = $user->getAdminProfile($_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | ThesisConnect</title>
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
            <a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="admin_manage_students.php"><i class="fa-solid fa-user-graduate"></i> Manage Students</a>
            <a href="admin_manage_teachers.php"><i class="fa-solid fa-chalkboard-user"></i> Manage Teachers</a>
            <a href="admin_manage_admins.php"><i class="fa-solid fa-user-plus"></i> Manage Admins</a>
            <a href="admin_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
            <a class="active" href="admin_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="admin_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
    </aside>
    <main class="dashboard-main">
        <header>
            <div>
                <h2>Admin Profile</h2>
                <p>Manage your account information.</p>
            </div>
        </header>
        <section class="card admin-profile-card">
            <h3>Profile Details</h3>
            <div class="profile-table-wrap">
                <table class="profile-detail-table">
                    <tbody>
                        <tr>
                            <th>Email</th>
                            <td><?= htmlspecialchars($_SESSION['user']['email']) ?></td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td><?= htmlspecialchars($admin['full_name'] ?? 'Admin User') ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?= htmlspecialchars($admin['phone'] ?? 'Not set') ?></td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td>Admin</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td><?= htmlspecialchars($admin['created_at'] ?? 'N/A') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
