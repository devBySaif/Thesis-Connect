<?php
require_once __DIR__ . '/student_helpers.php';
$context = loadStudentContext();
extract($context);
clearStudentFlash();

$groups = $user->getStudentGroups((int) $_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Group | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <?php renderStudentNavbar('groups', $studentName, $studentId, $profileImage, $notificationCount, $notifications); ?>

    <main class="dashboard-workspace">
        <section class="page-heading">
            <h1>My Group</h1>
            <p>Your accepted thesis topic and recruitment post teams.</p>
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
                </article>
            <?php endforeach; ?>

            <?php if (empty($groups)): ?>
                <div class="post-card">
                    <div class="post-body">
                        <h2>No group yet</h2>
                        <p>Your accepted topic or recruitment team will appear here.</p>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="../js/student_dashboard.js"></script>
</body>

</html>
