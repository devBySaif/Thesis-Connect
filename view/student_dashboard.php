<?php
require_once __DIR__ . '/student_helpers.php';
$context = loadStudentContext();
extract($context);
clearStudentFlash();

$teachers = $user->getVerifiedTeachers();
$recentPosts = $user->getRecentRecruitmentPosts((int) $_SESSION['user']['id'], 6);
$announcements = $user->getAnnouncements(3);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <?php renderStudentNavbar('dashboard', $studentName, $studentId, $profileImage, $notificationCount, $notifications); ?>

    <section class="hero">
        <div class="hero-content">
            <h1>Welcome Back, <span id="welcomeStudent"><?= e($studentName) ?></span></h1>
            <p>Find thesis partners, post recruitment requests, and track applications.</p>
        </div>
    </section>

    <main class="dashboard-workspace">
        <?php if (!empty($success)): ?>
            <div class="profile-alert success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="profile-alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <section class="profile-card">
            <div class="section-title-row">
                <div>
                    <h2>Create Recruitment Post</h2>
                    <p>Post your thesis group requirement and select the teacher you want to work under.</p>
                </div>
            </div>

            <form method="POST" action="../control/AuthController.php" class="profile-form js-post-form">
                <input type="hidden" name="action" value="recruitment_post_save">

                <div class="form-grid">
                    <label>
                        Post Title
                        <input type="text" name="title" data-label="Post title">
                    </label>

                    <label>
                        Thesis Under
                        <select name="teacher_user_id" data-label="Teacher">
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= e($teacher['user_id']) ?>">
                                    <?= e($teacher['full_name']) ?> (<?= e($teacher['department']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label>
                        Department
                        <input type="text" name="department" value="<?= e($student['department'] ?? '') ?>" data-label="Department">
                    </label>

                    <label>
                        Members Needed
                        <input type="number" name="members_needed" min="1" data-label="Members needed">
                    </label>

                    <label>
                        Deadline
                        <input type="date" name="deadline" data-label="Deadline">
                    </label>
                </div>

                <label>
                    Details
                    <textarea name="description" rows="4" data-label="Details"></textarea>
                </label>

                <button type="submit" class="profile-btn">
                    <i class="fa-solid fa-paper-plane"></i>
                    Publish Post
                </button>
            </form>
        </section>

        <section class="feed-section embedded-feed">
            <div class="feed-header">
                <h2>Recent Recruitment Posts</h2>
                <a href="create_post.php" class="text-link">View All</a>
            </div>

            <div id="recruitmentFeed" class="feed-container">
                <?php if (empty($recentPosts)): ?>
                    <div class="post-card">
                        <div class="post-body">
                            <h2>No recruitment posts yet</h2>
                            <p>Create the first post for your department.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentPosts as $post): ?>
                        <?php
                        $expired = strtotime($post['deadline']) < strtotime(date('Y-m-d'));
                        $isOwner = (int) $post['student_user_id'] === (int) $_SESSION['user']['id'];
                        $isFull = isset($post['accepted_count']) && (int)$post['accepted_count'] >= (int)$post['members_needed'];
                        ?>
                        <div class="post-card">
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
                                    <a href="my_posts.php" class="apply-btn link-btn">My Post</a>
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
                                        <?php if ($isFull): ?>
                                            <button class="apply-btn expired" type="button" disabled>Full</button>
                                        <?php else: ?>
                                            <form method="POST" action="../control/AuthController.php" class="inline-apply-form js-apply-form">
                                                <input type="hidden" name="action" value="post_apply">
                                                <input type="hidden" name="post_id" value="<?= e($post['id']) ?>">
                                                <input type="text" name="message" placeholder="Short message" data-label="Application message">
                                                <button type="submit" class="apply-btn">Apply</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="profile-card">
            <div class="section-title-row">
                <div>
                    <h2>Latest Announcements</h2>
                    <p>Admin announcements for students.</p>
                </div>
                <a href="announcements.php" class="text-link">View All</a>
            </div>
            <div class="announcement-list">
                <?php if (empty($announcements)): ?>
                    <p class="muted-text">No announcements published yet.</p>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <article class="announcement-item">
                            <h3><?= e($announcement['title']) ?></h3>
                            <p><?= e($announcement['body']) ?></p>
                            <small><?= e($announcement['created_at']) ?></small>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="../js/student_dashboard.js"></script>
</body>

</html>
