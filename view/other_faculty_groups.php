<?php
require_once __DIR__ . '/teacher_helpers.php';
$context = loadTeacherContext();
extract($context);
clearTeacherFlash();

$posts = $user->getRecentRecruitmentPosts((int) $_SESSION['user']['id'], 50);
$topics = $user->getAllThesisTopics((int) $_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head><?php renderTeacherHead('Other Faculty Groups'); ?></head>
<body>
<?php renderTeacherNavbar('recruitment', $teacherName, $teacherId, $profileImage, $notificationCount, $notifications); ?>

<main class="dashboard-workspace">
    <section class="page-heading">
        <h1>Other Faculty Groups</h1>
        <p>Browse thesis topics and recruitment posts supervised by other faculty.</p>
    </section>

    <section class="feed-section embedded-feed">
        <h2>Thesis Topics</h2>
        <div class="feed-container">
            <?php foreach ($topics as $topic): ?>
                <?php if ((int)$topic['teacher_user_id'] === (int) $_SESSION['user']['id']) continue; ?>
                <div class="post-card">
                    <div class="post-top">
                        <div>
                            <h3><?= e($topic['title']) ?></h3>
                            <small>Faculty: <?= e($topic['teacher_name']) ?></small>
                        </div>
                    </div>
                    <div class="post-body">
                        <p><?= e($topic['description']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2>Recruitment Posts</h2>
        <div class="feed-container">
            <?php foreach ($posts as $post): ?>
                <?php if (empty($post['teacher_user_id']) || (int)$post['teacher_user_id'] === (int) $_SESSION['user']['id']) continue; ?>
                <div class="post-card">
                    <div class="post-top">
                        <div>
                            <h3><?= e($post['title']) ?></h3>
                            <small>Faculty: <?= e($post['teacher_name'] ?: 'Faculty not selected') ?></small>
                        </div>
                    </div>
                    <div class="post-body">
                        <p><?= e($post['description']) ?></p>
                    </div>
                    <div class="post-info">
                        <span><i class="fa-solid fa-user-group"></i>Need <?= e($post['members_needed']) ?></span>
                        <span><i class="fa-solid fa-users"></i><?= e($post['apply_count']) ?> applied</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<script src="../js/student_dashboard.js"></script>
</body>
</html>
