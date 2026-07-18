<?php
require_once __DIR__ . '/teacher_helpers.php';
$context = loadTeacherContext();
extract($context);
clearTeacherFlash();

$groups = $user->getTeacherGroups((int) $_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head><?php renderTeacherHead('My Group'); ?></head>

<body>
<?php renderTeacherNavbar('groups', $teacherName, $teacherId, $profileImage, $notificationCount, $notifications); ?>

<main class="dashboard-workspace">
    <section class="page-heading">
        <h1>My Group</h1>
        <p>Accepted students grouped by thesis topic and supervised recruitment post.</p>
    </section>

    <section class="group-list">
        <?php foreach ($groups as $group): ?>
            <article class="group-card">
                <div class="group-card-head">
                    <div>
                        <span class="group-type"><?= e($group['type']) ?></span>
                        <h2><?= e($group['title']) ?></h2>
                    </div>
                    <span class="group-count"><i class="fa-solid fa-users"></i> <?= e($group['member_count']) ?> members</span>
                </div>

                <div class="post-info group-meta">
                    <span><i class="fa-solid fa-building"></i><?= e($group['department'] ?: 'Department not set') ?></span>
                    <?php if (!empty($group['research_area'])): ?>
                        <span><i class="fa-solid fa-magnifying-glass-chart"></i><?= e($group['research_area']) ?></span>
                    <?php endif; ?>
                    <span class="teacher-under"><i class="fa-solid fa-chalkboard-user"></i>Faculty: <?= e($group['faculty_name']) ?></span>
                    <?php if (!empty($group['faculty_designation']) || !empty($group['faculty_department'])): ?>
                        <span><i class="fa-solid fa-id-badge"></i><?= e(trim($group['faculty_designation'] . ' ' . $group['faculty_department'])) ?></span>
                    <?php endif; ?>
                </div>

                <div class="group-members">
                    <?php foreach ($group['members'] as $member): ?>
                        <article class="group-member-card">
                            <img src="<?= e(buildProfileImage($member)) ?>" alt="Member">
                            <div class="group-member-info">
                                <div class="group-member-title">
                                    <h3><?= e($member['full_name']) ?></h3>
                                    <span><?= e($member['member_role']) ?></span>
                                </div>
                                <dl>
                                    <div><dt>Email</dt><dd><?= e($member['email']) ?></dd></div>
                                    <div><dt>Student ID</dt><dd><?= e($member['student_id']) ?></dd></div>
                                    <div><dt>Department</dt><dd><?= e($member['department']) ?></dd></div>
                                    <div><dt>Semester</dt><dd><?= e($member['semester']) ?></dd></div>
                                    <div><dt>CGPA</dt><dd><?= e($member['cgpa']) ?></dd></div>
                                    <div><dt>Phone</dt><dd><?= e($member['phone']) ?></dd></div>
                                </dl>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="group-actions">
                    <?php if ($group['type'] === 'Recruitment Post' && !empty($group['title'])): ?>
                        <?php if (!empty($group['status']) && $group['status'] === 'completed'): ?>
                            <span class="status-label completed-label">Completed</span>
                            <form method="POST" action="../control/AuthController.php" onsubmit="return confirm('Delete this recruitment group? This cannot be undone.');">
                                <input type="hidden" name="action" value="teacher_delete_post">
                                <input type="hidden" name="post_id" value="<?= e($group['id'] ?? '') ?>">
                                <button type="submit" class="apply-btn danger-btn">Delete</button>
                            </form>
                        <?php else: ?>
                            <div style="display:flex;gap:8px;">
                                <form method="POST" action="../control/AuthController.php" onsubmit="return confirm('Mark this recruitment group as completed?');">
                                    <input type="hidden" name="action" value="teacher_complete_post">
                                    <input type="hidden" name="post_id" value="<?= e($group['id'] ?? '') ?>">
                                    <button type="submit" class="apply-btn applied">Complete</button>
                                </form>
                                <form method="POST" action="../control/AuthController.php" onsubmit="return confirm('Delete this recruitment group? This cannot be undone.');">
                                    <input type="hidden" name="action" value="teacher_delete_post">
                                    <input type="hidden" name="post_id" value="<?= e($group['id'] ?? '') ?>">
                                    <button type="submit" class="apply-btn danger-btn">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($group['type'] === 'Thesis Topic' && !empty($group['title'])): ?>
                        <?php if (!empty($group['status']) && $group['status'] === 'completed'): ?>
                            <span class="status-label completed-label">Completed</span>
                            <form method="POST" action="../control/AuthController.php" onsubmit="return confirm('Delete this thesis topic? This will remove its applications.');">
                                <input type="hidden" name="action" value="teacher_topic_delete">
                                <input type="hidden" name="topic_id" value="<?= e($group['id'] ?? '') ?>">
                                <button type="submit" class="apply-btn danger-btn">Delete</button>
                            </form>
                        <?php else: ?>
                            <div style="display:flex;gap:8px;">
                                <form method="POST" action="../control/AuthController.php" onsubmit="return confirm('Mark this thesis topic as completed?');">
                                    <input type="hidden" name="action" value="teacher_complete_topic">
                                    <input type="hidden" name="topic_id" value="<?= e($group['id'] ?? '') ?>">
                                    <button type="submit" class="apply-btn applied">Complete</button>
                                </form>
                                <form method="POST" action="../control/AuthController.php" onsubmit="return confirm('Delete this thesis topic? This will remove its applications.');">
                                    <input type="hidden" name="action" value="teacher_topic_delete">
                                    <input type="hidden" name="topic_id" value="<?= e($group['id'] ?? '') ?>">
                                    <button type="submit" class="apply-btn danger-btn">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (empty($groups)): ?>
            <div class="post-card">
                <div class="post-body">
                    <h2>No group yet</h2>
                    <p>Accepted students under your topics or supervised posts will appear here.</p>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>

<script src="../js/student_dashboard.js"></script>
</body>

</html>
