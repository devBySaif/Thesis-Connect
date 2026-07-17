<?php
require_once __DIR__ . '/teacher_helpers.php';
$context = loadTeacherContext();
extract($context);
clearTeacherFlash();

$topics = $user->getTeacherTopics((int) $_SESSION['user']['id']);
$editTopicId = (int) ($_GET['edit'] ?? 0);
$editTopic = $editTopicId ? $user->getThesisTopicById($editTopicId) : null;
if ($editTopic && (int) $editTopic['teacher_user_id'] !== (int) $_SESSION['user']['id']) {
    $editTopic = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head><?php renderTeacherHead('Thesis Topics'); ?></head>
<body>
<?php renderTeacherNavbar('topics', $teacherName, $teacherId, $profileImage, $notificationCount, $notifications); ?>
<main class="dashboard-workspace">
    <section class="page-heading"><h1>Thesis Topics</h1><p>Add, edit, delete, and mark topics as available or assigned.</p></section>
    <?php if ($success): ?><div class="profile-alert success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="profile-alert error"><?= e($error) ?></div><?php endif; ?>

    <section class="profile-card">
        <h2><?= $editTopic ? 'Edit Topic' : 'Add Thesis Topic' ?></h2>
        <form method="POST" action="../control/AuthController.php" class="profile-form js-topic-form">
            <input type="hidden" name="action" value="teacher_topic_save">
            <?php if ($editTopic): ?><input type="hidden" name="topic_id" value="<?= e($editTopic['id']) ?>"><?php endif; ?>
            <div class="form-grid">
                <label>Title<input type="text" name="title" value="<?= e($editTopic['title'] ?? '') ?>" data-label="Title"></label>
                <label>Department<input type="text" name="department" value="<?= e($editTopic['department'] ?? $teacher['department']) ?>" data-label="Department"></label>
                <label>Research Area<input type="text" name="research_area" value="<?= e($editTopic['research_area'] ?? '') ?>" data-label="Research area"></label>
                <label>Status<select name="status"><option value="available" <?= ($editTopic['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option><option value="assigned" <?= ($editTopic['status'] ?? '') === 'assigned' ? 'selected' : '' ?>>Assigned</option></select></label>
            </div>
            <label>Description<textarea name="description" rows="4"><?= e($editTopic['description'] ?? '') ?></textarea></label>
            <button class="profile-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Topic</button>
        </form>
    </section>

    <section class="feed-container">
        <?php foreach ($topics as $topic): ?>
            <article class="post-card" id="topic-<?= e($topic['id']) ?>">
                <div class="post-body"><h2><?= e($topic['title']) ?></h2><p><?= e($topic['description']) ?></p></div>
                <div class="post-info">
                    <span><i class="fa-solid fa-building"></i><?= e($topic['department']) ?></span>
                    <span><i class="fa-solid fa-magnifying-glass-chart"></i><?= e($topic['research_area']) ?></span>
                    <span><i class="fa-solid fa-circle"></i><?= e(ucfirst($topic['status'])) ?></span>
                    <span><i class="fa-solid fa-users"></i><?= e($topic['application_count']) ?> applications</span>
                </div>
                <div class="post-actions split-actions">
                    <a href="teacher_topics.php?edit=<?= e($topic['id']) ?>" class="apply-btn link-btn">Edit</a>
                    <form method="POST" action="../control/AuthController.php" class="delete-post-form">
                        <input type="hidden" name="action" value="teacher_topic_delete">
                        <input type="hidden" name="topic_id" value="<?= e($topic['id']) ?>">
                        <button type="submit" class="apply-btn danger-btn">Delete</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (empty($topics)): ?><div class="post-card"><div class="post-body"><h2>No topics yet</h2><p>Add your first thesis topic above.</p></div></div><?php endif; ?>
    </section>
</main>
<script src="../js/student_dashboard.js"></script>
</body>
</html>
