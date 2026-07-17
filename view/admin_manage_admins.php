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
$admins = $user->getAdmins();
$success = $_SESSION['admin_success'] ?? '';
$error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_success'], $_SESSION['admin_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins | Admin | ThesisConnect</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css?v=20260718b">
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
            <a class="active" href="admin_manage_admins.php"><i class="fa-solid fa-user-plus"></i> Manage Admins</a>
            <a href="admin_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a>
            <a href="admin_profile.php"><i class="fa-solid fa-user"></i> Profile</a>
            <a href="admin_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </nav>
    </aside>
    <main class="dashboard-main admin-manage-main">
        <header>
            <div>
                <h2>Manage Admins</h2>
                <p>Add a new admin account that can log in to this panel.</p>
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
                <h3>Add Admin</h3>
                <form method="POST" action="../control/AuthController.php" class="admin-form">
                    <input type="hidden" name="action" value="admin_create">

                    <label for="full_name">
                        Admin Name
                        <input type="text" id="full_name" name="full_name" placeholder="Enter admin name" required>
                    </label>

                    <label for="email">
                        Email
                        <input type="email" id="email" name="email" placeholder="admin@example.com" required>
                    </label>

                    <label for="phone">
                        Phone
                        <input type="text" id="phone" name="phone" placeholder="Optional phone number">
                    </label>

                    <label for="password">
                        Password
                        <input type="password" id="password" name="password" placeholder="At least 8 characters" required>
                    </label>

                    <label for="confirm_password">
                        Confirm Password
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                    </label>

                    <button type="submit" class="action-btn approve add-admin-btn">
                        <i class="fa-solid fa-user-plus"></i>
                        Add Admin
                    </button>
                </form>
            </div>

            <div class="students-table admins-table">
                <h3>Current Admins</h3>
                <?php if (empty($admins)): ?>
                    <div class="card">
                        <p>No admin accounts found.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $index => $admin): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($admin['full_name'] ?? 'Admin User') ?></td>
                                    <td><?= htmlspecialchars($admin['email']) ?></td>
                                    <td><?= htmlspecialchars($admin['phone'] ?? 'Not set') ?></td>
                                    <td><?= htmlspecialchars($admin['profile_created_at'] ?? $admin['user_created_at']) ?></td>
                                    <td>
                                        <?php if ((int) $admin['user_id'] !== (int) $_SESSION['user']['id']): ?>
                                            <form method="POST" action="../control/AuthController.php" onsubmit="return confirm('Delete this admin account?');">
                                                <input type="hidden" name="action" value="admin_delete">
                                                <input type="hidden" name="admin_user_id" value="<?= htmlspecialchars($admin['user_id']) ?>">
                                                <button type="submit" class="action-btn delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                    Delete
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="admin-self-label">Current</span>
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
</body>
</html>
