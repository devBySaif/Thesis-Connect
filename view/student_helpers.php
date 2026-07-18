<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/User.php';

function buildProfileImage($student)
{
    $studentName = $student['full_name'] ?? 'Student';

    if (!empty($student['profile_picture'])) {
        $profileFile = basename($student['profile_picture']);
        $profilePath = __DIR__ . '/../uploads/profile/' . $profileFile;

        if (is_file($profilePath)) {
            return '../uploads/profile/' . rawurlencode($profileFile);
        }
    }

    $initial = strtoupper(substr(trim($studentName), 0, 1)) ?: 'S';
    $fallbackSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160"><rect width="160" height="160" rx="80" fill="#6C63FF"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-size="72" font-weight="700">' . htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') . '</text></svg>';
    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($fallbackSvg);
}

function loadStudentContext()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
        header('Location: login.php');
        exit;
    }

    $database = new Database();
    $conn = $database->connect();
    $user = new User($conn);
    $student = $user->getStudentProfile($_SESSION['user']['id']);

    if (!$student || (int) $student['is_verified'] !== 1) {
        session_unset();
        session_destroy();
        header('Location: login.php?error=' . urlencode('Your student account is pending admin approval.'));
        exit;
    }

    $notifications = $user->getNotificationsForUser((int) $_SESSION['user']['id'], 8);

    return [
        'user' => $user,
        'student' => $student,
        'studentName' => $student['full_name'] ?? 'Student',
        'studentId' => $student['student_id'] ?? '',
        'profileImage' => buildProfileImage($student),
        'notificationCount' => $user->countUnreadNotifications((int) $_SESSION['user']['id']),
        'notifications' => $notifications,
        'success' => $_SESSION['student_success'] ?? '',
        'error' => $_SESSION['student_error'] ?? ''
    ];
}

function clearStudentFlash()
{
    unset($_SESSION['student_success'], $_SESSION['student_error']);
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function renderStudentNavbar($active, $studentName, $studentId, $profileImage, $notificationCount, $notifications)
{
?>
    <header class="navbar">
        <div class="logo">
            <a href="create_post.php">
                <span class="brand-mark">
                    <i class="fa-solid fa-graduation-cap"></i>
                </span>
                <h2>ThesisConnect</h2>
            </a>
        </div>

        <nav class="nav-links">
            <a href="create_post.php" class="<?= $active === 'recruitment' ? 'active' : '' ?>">Recruitment Posts</a>
            <a href="browse_topics.php" class="<?= $active === 'browse_topics' ? 'active' : '' ?>">Browse Topics</a>
            <a href="announcements.php" class="<?= $active === 'announcements' ? 'active' : '' ?>">Announcements</a>
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
                                <div>
                                    <p>No new applications yet.</p>
                                    <small>Check My Posts for details</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a class="notification-item <?= (int) $notification['is_read'] === 0 ? 'unread' : '' ?>" href="notification_go.php?id=<?= e($notification['id']) ?>">
                                    <i class="fa-solid fa-user-plus"></i>
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
                        <h3 id="studentName"><?= e($studentName) ?></h3>
                        <p id="studentId"><?= e($studentId) ?></p>
                    </div>
                    <ul>
                        <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
                        <li><a href="student_my_group.php"><i class="fa-solid fa-people-group"></i> My Group</a></li>
                        <li><a href="browse_topics.php"><i class="fa-solid fa-book-open"></i> Browse Topics</a></li>
                        <li><a href="create_post.php"><i class="fa-solid fa-users"></i> Recruitment Posts</a></li>
                        <li><a href="my_posts.php"><i class="fa-solid fa-rectangle-list"></i> My Posts</a></li>
                        <li><a href="announcements.php"><i class="fa-solid fa-bullhorn"></i> Announcements</a></li>
                        <li><a href="admin_logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
<?php
}

function postOwnerImage($post)
{
    return buildProfileImage([
        'full_name' => $post['owner_name'] ?? 'Student',
        'profile_picture' => $post['owner_picture'] ?? ''
    ]);
}
