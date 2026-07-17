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
$students = $user->getPendingStudents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | Admin | ThesisConnect</title>
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
            <a class="active" href="admin_manage_students.php"><i class="fa-solid fa-user-graduate"></i> Manage Students</a>
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
                <h2>Manage Students</h2>
                <p>Approve, reject, or delete student registrations.</p>
            </div>
        </header>
        <section class="students-table">
            <?php if (empty($students)): ?>
                <div class="card">
                    <p>No student registrations found.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Student ID</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                <td><?= htmlspecialchars($student['department']) ?></td>
                                <td><?= htmlspecialchars($student['semester']) ?></td>
                                <td><?= htmlspecialchars($student['phone']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td>
                                    <form method="POST" action="../control/AuthController.php" style="display:inline-block;">
                                        <input type="hidden" name="action" value="admin_student_action">
                                        <input type="hidden" name="user_id" value="<?= $student['user_id'] ?>">
                                        <input type="hidden" name="student_action" value="approve">
                                        <button type="submit" class="action-btn approve">Approve</button>
                                    </form>
                                    <form method="POST" action="../control/AuthController.php" style="display:inline-block;">
                                        <input type="hidden" name="action" value="admin_student_action">
                                        <input type="hidden" name="user_id" value="<?= $student['user_id'] ?>">
                                        <input type="hidden" name="student_action" value="reject">
                                        <button type="submit" class="action-btn reject">Reject</button>
                                    </form>
                                    <form method="POST" action="../control/AuthController.php" style="display:inline-block;">
                                        <input type="hidden" name="action" value="admin_student_action">
                                        <input type="hidden" name="user_id" value="<?= $student['user_id'] ?>">
                                        <input type="hidden" name="student_action" value="delete">
                                        <button type="submit" class="action-btn delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
