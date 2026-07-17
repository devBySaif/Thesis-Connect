<?php
require_once __DIR__ . '/student_helpers.php';
$context = loadStudentContext();
extract($context);
clearStudentFlash();

$posts = $user->getRecentRecruitmentPosts((int) $_SESSION['user']['id'], 50);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Posts | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <?php renderStudentNavbar('recruitment', $studentName, $studentId, $profileImage, $notificationCount, $notifications); ?>

    <main class="dashboard-workspace">
        <section class="page-heading">
            <h1>Recruitment Posts</h1>
            <p>Recent thesis group requirements from verified students.</p>
        </section>

        <?php if (!empty($success)): ?>
            <div class="profile-alert success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="profile-alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <section class="feed-section embedded-feed">
            <div id="recruitmentFeed" class="feed-container">
                <?php if (empty($posts)): ?>
                    <div class="post-card">
                        <div class="post-body">
                            <h2>No posts found</h2>
                            <p>When students publish recruitment posts, they will appear here.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <?php
                        $expired = strtotime($post['deadline']) < strtotime(date('Y-m-d'));
                        $isOwner = (int) $post['student_user_id'] === (int) $_SESSION['user']['id'];
                        ?>
                        <div class="post-card" id="post-<?= e($post['id']) ?>">
                            <div class="post-top">
                                <img src="<?= e(postOwnerImage($post)) ?>" class="post-profile" alt="Profile">
                                <div>
                                    <h3><?= e($post['owner_name']) ?></h3>
                                    <small><?= e($post['created_at']) ?></small>
                                </div>
                            </div>

                            <div class="post-body">
                                <h2><?= e($post['title']) ?></h2>
                                <p><?= e($post['description']) ?></p>
                            </div>

                            <div class="post-info">
                                <span><i class="fa-solid fa-building"></i><?= e($post['department']) ?></span>
                                <span class="teacher-under"><i class="fa-solid fa-chalkboard-user"></i>Thesis under: <?= e($post['teacher_name'] ?: 'Teacher not selected') ?></span>
                                <span><i class="fa-solid fa-user-group"></i>Need <?= e($post['members_needed']) ?></span>
                                <span><i class="fa-solid fa-users"></i><?= e($post['apply_count']) ?> applied</span>
                                <span><i class="fa-solid fa-calendar"></i><?= e($post['deadline']) ?></span>
                            </div>

                            <div class="post-actions">
                                <?php if ($isOwner): ?>
                                    <a href="my_posts.php" class="apply-btn link-btn">Manage My Post</a>
                                <?php elseif (!empty($post['my_status'])): ?>
                                    <div class="application-status-box <?= e($post['my_status']) ?>">
                                        <button class="apply-btn applied" type="button" disabled><?= e(ucfirst($post['my_status'])) ?></button>
                                        <?php if ($post['my_status'] === 'accepted'): ?>
                                            <p>The post owner will contact you as soon as possible.</p>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($expired || $post['status'] === 'closed'): ?>
                                    <button class="apply-btn expired" type="button" disabled>Deadline Ended</button>
                                <?php else: ?>
                                    <form method="POST" action="../control/AuthController.php" class="inline-apply-form js-apply-form">
                                        <input type="hidden" name="action" value="post_apply">
                                        <input type="hidden" name="post_id" value="<?= e($post['id']) ?>">
                                        <input type="text" name="message" placeholder="Short message" data-label="Application message">
                                        <button type="submit" class="apply-btn">Apply</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="../js/student_dashboard.js"></script>
</body>

</html>
