<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/student_helpers.php';

function loadTeacherContext()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'teacher') {
        header('Location: login.php');
        exit;
    }

    $database = new Database();
    $conn = $database->connect();
    $user = new User($conn);
    $teacher = $user->getTeacherProfile($_SESSION['user']['id']);

    if (!$teacher || (int) $teacher['is_verified'] !== 1) {
        session_unset();
        session_destroy();
        header('Location: login.php?error=' . urlencode('Your teacher account is pending admin approval.'));
        exit;
    }

    return [
        'user' => $user,
        'teacher' => $teacher,
        'teacherName' => $teacher['full_name'] ?? 'Teacher',
        'teacherId' => $teacher['teacher_id'] ?? '',
        'profileImage' => buildProfileImage($teacher),
        'notificationCount' => $user->countUnreadNotifications((int) $_SESSION['user']['id']),
        'notifications' => $user->getNotificationsForUser((int) $_SESSION['user']['id'], 8),
        'success' => $_SESSION['teacher_success'] ?? '',
        'error' => $_SESSION['teacher_error'] ?? ''
    ];
}

function clearTeacherFlash()
{
    unset($_SESSION['teacher_success'], $_SESSION['teacher_error']);
}

function renderTeacherNavbar($active, $teacherName, $teacherId, $profileImage, $notificationCount, $notifications)
{
?>
    <header class="navbar">
        <div class="logo">
            <a href="teacher_recruitment_posts.php">
                <span class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></span>
                <h2>ThesisConnect</h2>
            </a>
        </div>

        <nav class="nav-links teacher-nav">
            <a href="teacher_recruitment_posts.php" class="<?= $active === 'recruitment' ? 'active' : '' ?>">Recruitment Posts</a>
            <a href="teacher_announcements.php" class="<?= $active === 'announcements' ? 'active' : '' ?>">Announcements</a>
        </nav>

        <div class="nav-right">
            <div class="notification-wrapper">
                <button id="notificationBtn" class="notification-btn">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($notificationCount > 0): ?>
                        <span id="notificationCount" class="notification-count"><?= e($notificationCount) ?></span>
                    <?php endif; ?>
                </button>
                <div id="notificationDropdown" class="notification-dropdown">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <?php if (!empty($notifications)): ?>
                            <a href="clear_notifications.php" class="notification-clear-link">Clear All</a>
                        <?php endif; ?>
                    </div>
                    <div class="notification-list">
                        <?php if (empty($notifications)): ?>
                            <div class="notification-item">
                                <i class="fa-solid fa-circle-info"></i>
                                <div><p>No notifications yet.</p><small>Teacher updates will appear here</small></div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a class="notification-item <?= (int) $notification['is_read'] === 0 ? 'unread' : '' ?>" href="teacher_notification_go.php?id=<?= e($notification['id']) ?>">
                                    <i class="fa-solid fa-bell"></i>
                                    <div>
                                        <p><?= e($notification['title']) ?></p>
                                        <small><?= e($notification['body']) ?></small>
                                        <small><?= e($notification['created_at']) ?></small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="profile-wrapper">
                <img src="<?= e($profileImage) ?>" alt="Profile" id="profileBtn" class="profile-image">
                <div id="profileDropdown" class="profile-dropdown">
                    <div class="profile-header">
                        <img src="<?= e($profileImage) ?>" alt="Profile">
                        <h3><?= e($teacherName) ?></h3>
                        <p><?= e($teacherId) ?></p>
                    </div>
                    <ul>
                        <li><a href="teacher_dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
                        <li><a href="teacher_profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                        <li><a href="teacher_my_group.php"><i class="fa-solid fa-people-group"></i> My Group</a></li>
                        <li><a href="teacher_topics.php"><i class="fa-solid fa-lightbulb"></i> Thesis Topics</a></li>
                        <li><a href="teacher_recruitment_posts.php"><i class="fa-solid fa-users"></i> Recruitment Posts</a></li>
                        <li><a href="other_faculty_groups.php"><i class="fa-solid fa-globe"></i> Other Faculty Groups</a></li>
                        <li><a href="teacher_applications.php"><i class="fa-solid fa-clipboard-check"></i> Applications</a></li>
                        <li><a href="teacher_supervised_students.php"><i class="fa-solid fa-user-graduate"></i> Supervised Students</a></li>
                        <li><a href="teacher_announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                        <li><a href="admin_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
<?php
}

function renderTeacherHead($title)
{
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<?php
}
