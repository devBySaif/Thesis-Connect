<?php
require_once __DIR__ . '/student_helpers.php';
$context = loadStudentContext();
extract($context);
clearStudentFlash();

$announcements = $user->getAnnouncements(50);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <?php renderStudentNavbar('announcements', $studentName, $studentId, $profileImage, $notificationCount, $notifications); ?>

    <main class="dashboard-workspace">
        <section class="page-heading">
            <h1>Announcements</h1>
            <p>Latest updates published by the admin panel.</p>
        </section>

        <section class="profile-card">
            <div class="announcement-list">
                <?php if (empty($announcements)): ?>
                    <p class="muted-text">No announcements published yet.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <article class="announcement-item">
                            <h3><?= e($announcement['title']) ?></h3>
                            <p><?= nl2br(e($announcement['body'])) ?></p>
                            <small>Published <?= e($announcement['created_at']) ?></small>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="../js/student_dashboard.js"></script>
</body>

</html>
