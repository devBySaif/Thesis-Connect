<?php
require_once __DIR__ . '/teacher_helpers.php';
$context = loadTeacherContext();
extract($context);
clearTeacherFlash();

$totalTopics = $user->countThesisTopicsByTeacher((int) $_SESSION['user']['id']);
$activeRequests = $user->countActiveRecruitmentRequests();
$supervisedStudents = $user->countSupervisedStudents((int) $_SESSION['user']['id']);
$announcements = $user->getAnnouncements(3);
$recentPosts = $user->getRecentRecruitmentPosts(0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head><?php renderTeacherHead('Teacher Dashboard'); ?></head>
<body>
<?php renderTeacherNavbar('dashboard', $teacherName, $teacherId, $profileImage, $notificationCount, $notifications); ?>

<main class="dashboard-workspace">
    <section class="page-heading">
        <h1>Teacher Dashboard</h1>
        <p>Overview of thesis topics, recruitment activity, and student supervision.</p>
    </section>

    <section class="teacher-stats-grid">
        <div class="profile-card"><h2>Total Thesis Topics</h2><p class="stat-number"><?= e($totalTopics) ?></p></div>
        <div class="profile-card"><h2>Active Recruitment Requests</h2><p class="stat-number"><?= e($activeRequests) ?></p></div>
        <div class="profile-card"><h2>Total Supervised Students</h2><p class="stat-number"><?= e($supervisedStudents) ?></p></div>
    </section>

    <section class="profile-card">
        <div class="section-title-row"><div><h2>Latest Student Recruitment Posts</h2><p>Recent posts from students.</p></div></div>
        <div class="feed-container compact-feed">
            <?php foreach ($recentPosts as $post): ?>
                <article class="post-card">
                    <div class="post-body"><h2><?= e($post['title']) ?></h2><p><?= e($post['description']) ?></p></div>
                    <div class="post-info">
                        <span><i class="fa-solid fa-user"></i><?= e($post['owner_name']) ?></span>
                        <span><i class="fa-solid fa-building"></i><?= e($post['department']) ?></span>
                        <span><i class="fa-solid fa-user-group"></i>Need <?= e($post['members_needed']) ?></span>
                        <span><i class="fa-solid fa-calendar"></i><?= e($post['deadline']) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (empty($recentPosts)): ?><p class="muted-text">No recruitment posts found.</p><?php endif; ?>
        </div>
    </section>

    <section class="profile-card">
        <div class="section-title-row"><div><h2>Recent Announcements</h2><p>Latest platform announcements.</p></div></div>
        <div class="announcement-list">
            <?php foreach ($announcements as $announcement): ?>
                <article class="announcement-item"><h3><?= e($announcement['title']) ?></h3><p><?= e($announcement['body']) ?></p><small><?= e($announcement['created_at']) ?></small></article>
            <?php endforeach; ?>
            <?php if (empty($announcements)): ?><p class="muted-text">No announcements yet.</p><?php endif; ?>
        </div>
    </section>
</main>
<script src="../js/student_dashboard.js"></script>
</body>
</html>
