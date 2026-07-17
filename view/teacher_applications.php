<?php
require_once __DIR__ . '/teacher_helpers.php';
$context = loadTeacherContext();
extract($context);
clearTeacherFlash();
$applications = $user->getTopicApplicationsForTeacher((int) $_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head><?php renderTeacherHead('Student Applications'); ?></head>
<body>
<?php renderTeacherNavbar('applications', $teacherName, $teacherId, $profileImage, $notificationCount, $notifications); ?>
<main class="dashboard-workspace" id="topic-applications">
    <section class="page-heading"><h1>Student Applications</h1><p>Review student applications for your thesis topics.</p></section>
    <?php if ($success): ?><div class="profile-alert success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="profile-alert error"><?= e($error) ?></div><?php endif; ?>
    <section class="applicant-grid">
        <?php foreach ($applications as $application): ?>
            <article class="applicant-card">
                <div class="applicant-top"><img src="<?= e(buildProfileImage($application)) ?>" alt="Student"><div><h4><?= e($application['full_name']) ?></h4><span class="status-pill <?= e($application['status']) ?>"><?= e(ucfirst($application['status'])) ?></span></div></div>
                <dl>
                    <div><dt>Topic</dt><dd><?= e($application['topic_title']) ?></dd></div>
                    <div><dt>Email</dt><dd><?= e($application['email']) ?></dd></div>
                    <div><dt>Student ID</dt><dd><?= e($application['student_id']) ?></dd></div>
                    <div><dt>CGPA</dt><dd><?= e($application['cgpa']) ?></dd></div>
                    <div><dt>Department</dt><dd><?= e($application['department']) ?></dd></div>
                    <div><dt>Message</dt><dd><?= e($application['message'] ?: 'No message') ?></dd></div>
                </dl>
                <div class="applicant-actions">
                    <?php if ($application['status'] === 'pending'): ?>
                        <form method="POST" action="../control/AuthController.php"><input type="hidden" name="action" value="topic_application_action"><input type="hidden" name="application_id" value="<?= e($application['id']) ?>"><input type="hidden" name="application_status" value="accepted"><button class="mini-btn accept" type="submit"><i class="fa-solid fa-check"></i> Accept</button></form>
                        <form method="POST" action="../control/AuthController.php"><input type="hidden" name="action" value="topic_application_action"><input type="hidden" name="application_id" value="<?= e($application['id']) ?>"><input type="hidden" name="application_status" value="rejected"><button class="mini-btn reject" type="submit"><i class="fa-solid fa-xmark"></i> Reject</button></form>
                    <?php elseif ($application['status'] === 'accepted'): ?>
                        <span class="team-badge"><i class="fa-solid fa-user-check"></i> Supervised student</span>
                    <?php else: ?>
                        <span class="team-badge rejected"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (empty($applications)): ?><p class="muted-text">No applications yet.</p><?php endif; ?>
    </section>
</main>
<script src="../js/student_dashboard.js"></script>
</body>
</html>
