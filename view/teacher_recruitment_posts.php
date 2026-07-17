<?php
require_once __DIR__ . '/teacher_helpers.php';
$context = loadTeacherContext();
extract($context);
clearTeacherFlash();

$filterDepartment = trim($_GET['department'] ?? 'All');
$filterStatus = trim($_GET['status'] ?? 'All');
$posts = $user->getRecentRecruitmentPosts(0, 100);
$posts = array_filter($posts, function ($post) use ($filterDepartment, $filterStatus) {
    $departmentOk = $filterDepartment === 'All' || strcasecmp($post['department'], $filterDepartment) === 0;
    $closed = $post['status'] === 'closed' || strtotime($post['deadline']) < strtotime(date('Y-m-d'));
    $statusValue = $closed ? 'Closed' : 'Open';
    $statusOk = $filterStatus === 'All' || $filterStatus === $statusValue;
    return $departmentOk && $statusOk;
});
$departments = ['All', 'CSE', 'EEE', 'BBA'];
?>
<!DOCTYPE html>
<html lang="en">
<head><?php renderTeacherHead('Recruitment Posts'); ?></head>
<body>
<?php renderTeacherNavbar('recruitment', $teacherName, $teacherId, $profileImage, $notificationCount, $notifications); ?>
<main class="dashboard-workspace">
    <section class="page-heading"><h1>Student Recruitment Posts</h1><p>View student recruitment feed and filter by department or status.</p></section>
    <form method="GET" class="filter-bar">
        <select name="department"><?php foreach ($departments as $department): ?><option value="<?= e($department) ?>" <?= $filterDepartment === $department ? 'selected' : '' ?>><?= e($department) ?></option><?php endforeach; ?></select>
        <select name="status"><option <?= $filterStatus === 'All' ? 'selected' : '' ?>>All</option><option <?= $filterStatus === 'Open' ? 'selected' : '' ?>>Open</option><option <?= $filterStatus === 'Closed' ? 'selected' : '' ?>>Closed</option></select>
        <button class="profile-btn" type="submit">Filter</button>
    </form>
    <section class="feed-container">
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <div class="post-top"><img src="<?= e(postOwnerImage($post)) ?>" class="post-profile" alt="Profile"><div><h3><?= e($post['owner_name']) ?></h3><small><?= e($post['created_at']) ?></small></div></div>
                <div class="post-body"><h2><?= e($post['title']) ?></h2><p><?= e($post['description']) ?></p></div>
                <div class="post-info">
                    <span><i class="fa-solid fa-building"></i><?= e($post['department']) ?></span>
                    <span><i class="fa-solid fa-chalkboard-user"></i>Faculty: <?= e($post['teacher_name'] ?: 'Not selected') ?></span>
                    <span><i class="fa-solid fa-user-group"></i>Need <?= e($post['members_needed']) ?></span>
                    <span><i class="fa-solid fa-users"></i><?= e($post['apply_count']) ?> applied</span>
                    <span><i class="fa-solid fa-calendar"></i><?= e($post['deadline']) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?><div class="post-card"><div class="post-body"><h2>No posts found</h2><p>Try another filter.</p></div></div><?php endif; ?>
    </section>
</main>
<script src="../js/student_dashboard.js"></script>
</body>
</html>
