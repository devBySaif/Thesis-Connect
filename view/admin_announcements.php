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
$announcements = $user->getAnnouncements(30);
$success = $_SESSION['admin_success'] ?? '';
$error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_success'], $_SESSION['admin_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | Admin | ThesisConnect</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=20260718c">
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
            <a class="active" href="admin_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
            <a href="admin_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="admin_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
    </aside>
    <main class="dashboard-main admin-manage-main">
        <header>
            <div>
                <h2>Announcements</h2>
                <p>Publish updates that students can see in their announcements page.</p>
            </div>
        </header>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="admin-panel-grid">
            <div class="form-card">
                <h3>Publish Announcement</h3>
                <form method="POST" action="../control/AuthController.php" class="admin-form announcement-form js-admin-announcement-form">
                    <input type="hidden" name="action" value="announcement_create">

                    <label for="title">
                        Title
                        <input type="text" id="title" name="title" data-label="Title">
                    </label>

                    <label for="body">
                        Details
                        <textarea id="body" name="body" rows="8" data-label="Details"></textarea>
                    </label>

                    <button type="submit" class="action-btn approve add-admin-btn">
                        <i class="fa-solid fa-paper-plane"></i>
                        Publish
                    </button>
                </form>
            </div>

            <div class="students-table admins-table">
                <h3>Published Announcements</h3>
                <?php if (empty($announcements)): ?>
                    <div class="card">
                        <p>No announcements found.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Details</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($announcements as $index => $announcement): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($announcement['title']) ?></td>
                                    <td><?= htmlspecialchars($announcement['body']) ?></td>
                                    <td><?= htmlspecialchars($announcement['created_at']) ?></td>
                                    <td>
                                        <?php if ((int) $announcement['admin_user_id'] === (int) $_SESSION['user']['id']): ?>
                                            <form method="POST" action="../control/AuthController.php">
                                                <input type="hidden" name="action" value="announcement_delete">
                                                <input type="hidden" name="announcement_id" value="<?= htmlspecialchars($announcement['id']) ?>">
                                                <button type="submit" class="action-btn delete">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
<script src="../js/student_dashboard.js"></script>
</body>
</html>
