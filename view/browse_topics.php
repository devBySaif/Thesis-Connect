<?php
require_once __DIR__ . '/student_helpers.php';
$context = loadStudentContext();
extract($context);
clearStudentFlash();
$topics = $user->getAllThesisTopics((int) $_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Topics | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <?php renderStudentNavbar('browse', $studentName, $studentId, $profileImage, $notificationCount, $notifications); ?>

    <main class="dashboard-workspace">
        <section class="page-heading">
            <h1>Browse Thesis Topics</h1>
            <p>Explore teacher-published topics and apply for available research work.</p>
        </section>

        <?php if (!empty($success)): ?><div class="profile-alert success"><?= e($success) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="profile-alert error"><?= e($error) ?></div><?php endif; ?>

        <section class="feed-container">
            <?php foreach ($topics as $topic): ?>
                <article class="post-card" id="topic-<?= e($topic['id']) ?>">
                    <div class="post-body">
                        <h2><?= e($topic['title']) ?></h2>
                        <p><?= e($topic['description']) ?></p>
                    </div>
                    <div class="post-info">
                        <span><i class="fa-solid fa-chalkboard-user"></i><?= e($topic['teacher_name']) ?></span>
                        <span><i class="fa-solid fa-building"></i><?= e($topic['department']) ?></span>
                        <span><i class="fa-solid fa-magnifying-glass-chart"></i><?= e($topic['research_area']) ?></span>
                        <span><i class="fa-solid fa-circle"></i><?= e(ucfirst($topic['status'])) ?></span>
                    </div>
                    <div class="post-actions">
                        <?php if (!empty($topic['my_status'])): ?>
                            <button type="button" class="apply-btn applied" disabled><?= e(ucfirst($topic['my_status'])) ?></button>
                        <?php elseif ($topic['status'] !== 'available'): ?>
                            <button type="button" class="apply-btn expired" disabled>Assigned</button>
                        <?php else: ?>
                            <form method="POST" action="../control/AuthController.php" class="inline-apply-form js-apply-form">
                                <input type="hidden" name="action" value="topic_apply">
                                <input type="hidden" name="topic_id" value="<?= e($topic['id']) ?>">
                                <input type="text" name="message" placeholder="Why are you interested?" data-label="Application message">
                                <button class="apply-btn" type="submit">Apply</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (empty($topics)): ?><div class="post-card"><div class="post-body"><h2>No topics available</h2><p>Teacher-published topics will appear here.</p></div></div><?php endif; ?>
        </section>
    </main>

    <script src="../js/student_dashboard.js"></script>
</body>
</html>
