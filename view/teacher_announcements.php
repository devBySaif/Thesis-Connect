<?php
require_once __DIR__ . '/teacher_helpers.php';
$context = loadTeacherContext();
extract($context);
clearTeacherFlash();
$announcements = $user->getAnnouncements(30);
?>
<!DOCTYPE html>
<html lang="en">
<head><?php renderTeacherHead('Announcements'); ?></head>
<body>
<?php renderTeacherNavbar('announcements', $teacherName, $teacherId, $profileImage, $notificationCount, $notifications); ?>
<main class="dashboard-workspace">
    <section class="page-heading"><h1>Announcements</h1><p>Publish meeting, deadline, and lab schedule updates.</p></section>
    <?php if ($success): ?><div class="profile-alert success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="profile-alert error"><?= e($error) ?></div><?php endif; ?>
    <section class="profile-card">
        <h2>Publish Announcement</h2>
        <form method="POST" action="../control/AuthController.php" class="profile-form js-admin-announcement-form">
            <input type="hidden" name="action" value="teacher_announcement_create">
            <label>Title<input type="text" name="title" data-label="Title"></label>
            <label>Details<textarea name="body" rows="5" data-label="Details"></textarea></label>
            <button class="profile-btn" type="submit"><i class="fa-solid fa-paper-plane"></i> Publish</button>
        </form>
    </section>
    <section class="profile-card">
        <h2>Recent Announcements</h2>
        <div class="announcement-list">
            <?php foreach ($announcements as $announcement): ?>
                <article class="announcement-item">
                    <h3><?= e($announcement['title']) ?></h3>
                    <p><?= e($announcement['body']) ?></p>
                    <small><?= e($announcement['created_at']) ?></small>
                    <?php if ((int) $announcement['admin_user_id'] === (int) $_SESSION['user']['id']): ?>
                        <form method="POST" action="../control/AuthController.php" class="delete-post-form announcement-delete-form">
                            <input type="hidden" name="action" value="announcement_delete">
                            <input type="hidden" name="announcement_id" value="<?= e($announcement['id']) ?>">
                            <button type="submit" class="mini-btn reject"><i class="fa-solid fa-trash"></i> Delete</button>
                        </form>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<script src="../js/student_dashboard.js"></script>
</body>
</html>
