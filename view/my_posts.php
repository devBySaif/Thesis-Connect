<?php
require_once __DIR__ . '/student_helpers.php';
$context = loadStudentContext();
extract($context);
clearStudentFlash();

$teachers = $user->getVerifiedTeachers();
$myPosts = $user->getMyRecruitmentPosts((int) $_SESSION['user']['id']);
$applications = $user->getApplicationsForOwner((int) $_SESSION['user']['id']);
$editPostId = (int) ($_GET['edit'] ?? 0);
$editPost = $editPostId ? $user->getRecruitmentPostById($editPostId) : null;

if ($editPost && (int) $editPost['student_user_id'] !== (int) $_SESSION['user']['id']) {
    $editPost = null;
}

$appsByPost = [];
foreach ($applications as $application) {
    $appsByPost[$application['post_id']][] = $application;
}

$user->markApplicationsSeenForOwner((int) $_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <?php renderStudentNavbar('my_posts', $studentName, $studentId, $profileImage, $notificationCount, $notifications); ?>

    <main class="dashboard-workspace">
        <section class="page-heading">
            <h1>My Posts</h1>
            <p>Manage your recruitment posts and review student applications.</p>
        </section>

        <?php if (!empty($success)): ?>
            <div class="profile-alert success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="profile-alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($editPost): ?>
            <section class="profile-card">
                <h2>Edit Post</h2>
                <form method="POST" action="../control/AuthController.php" class="profile-form js-post-form">
                    <input type="hidden" name="action" value="recruitment_post_save">
                    <input type="hidden" name="post_id" value="<?= e($editPost['id']) ?>">

                    <div class="form-grid">
                        <label>
                            Post Title
                            <input type="text" name="title" value="<?= e($editPost['title']) ?>" data-label="Post title">
                        </label>

                        <label>
                            Teacher
                            <select name="teacher_user_id" data-label="Teacher">
                                <option value="">Select Teacher</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= e($teacher['user_id']) ?>" <?= (int) $editPost['teacher_user_id'] === (int) $teacher['user_id'] ? 'selected' : '' ?>>
                                        <?= e($teacher['full_name']) ?> (<?= e($teacher['department']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Department
                            <input type="text" name="department" value="<?= e($editPost['department']) ?>" data-label="Department">
                        </label>

                        <label>
                            Members Needed
                            <input type="number" name="members_needed" min="1" value="<?= e($editPost['members_needed']) ?>" data-label="Members needed">
                        </label>

                        <label>
                            Deadline
                            <input type="date" name="deadline" value="<?= e($editPost['deadline']) ?>" data-label="Deadline">
                        </label>

                        <label>
                            Status
                            <select name="status">
                                <option value="open" <?= $editPost['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                <option value="closed" <?= $editPost['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                            </select>
                        </label>
                    </div>

                    <label>
                        Details
                        <textarea name="description" rows="4" data-label="Details"><?= e($editPost['description']) ?></textarea>
                    </label>

                    <button type="submit" class="profile-btn">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Update Post
                    </button>
                </form>
            </section>
        <?php endif; ?>

        <section class="feed-section embedded-feed">
            <div class="feed-header">
                <h2>Posts I Created</h2>
            </div>

            <div class="feed-container">
                <?php if (empty($myPosts)): ?>
                    <div class="post-card">
                        <div class="post-body">
                            <h2>No posts yet</h2>
                            <p>Create a recruitment post from your dashboard.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($myPosts as $post): ?>
                        <div class="post-card">
                            <div class="post-body">
                                <h2><?= e($post['title']) ?></h2>
                                <p><?= e($post['description']) ?></p>
                            </div>

                            <div class="post-info">
                                <span><i class="fa-solid fa-building"></i><?= e($post['department']) ?></span>
                                <span><i class="fa-solid fa-chalkboard-user"></i><?= e($post['teacher_name'] ?: 'Teacher not selected') ?></span>
                                <span><i class="fa-solid fa-user-group"></i>Need <?= e($post['members_needed']) ?></span>
                                <span><i class="fa-solid fa-users"></i><?= e($post['apply_count']) ?> applied</span>
                                <span><i class="fa-solid fa-calendar"></i><?= e($post['deadline']) ?></span>
                                <span><i class="fa-solid fa-circle"></i><?= e(ucfirst($post['status'])) ?></span>
                            </div>

                            <div class="post-actions split-actions">
                                <a href="my_posts.php?edit=<?= e($post['id']) ?>" class="apply-btn link-btn">Edit</a>
                            </div>

                            <div class="applicant-section">
                                <h3>Applicants</h3>
                                <?php if (empty($appsByPost[$post['id']])): ?>
                                    <p class="muted-text">No one has applied yet.</p>
                                <?php else: ?>
                                    <div class="applicant-grid">
                                        <?php foreach ($appsByPost[$post['id']] as $application): ?>
                                            <article class="applicant-card">
                                                <div class="applicant-top">
                                                    <img src="<?= e(buildProfileImage($application)) ?>" alt="Applicant">
                                                    <div>
                                                        <h4><?= e($application['full_name']) ?></h4>
                                                        <span class="status-pill <?= e($application['status']) ?>"><?= e(ucfirst($application['status'])) ?></span>
                                                    </div>
                                                </div>
                                                <dl>
                                                    <div><dt>Email</dt><dd><?= e($application['email']) ?></dd></div>
                                                    <div><dt>Student ID</dt><dd><?= e($application['student_id']) ?></dd></div>
                                                    <div><dt>Department</dt><dd><?= e($application['department']) ?></dd></div>
                                                    <div><dt>Semester</dt><dd><?= e($application['semester']) ?></dd></div>
                                                    <div><dt>CGPA</dt><dd><?= e($application['cgpa']) ?></dd></div>
                                                    <div><dt>Phone</dt><dd><?= e($application['phone']) ?></dd></div>
                                                    <div><dt>Message</dt><dd><?= e($application['message'] ?: 'No message') ?></dd></div>
                                                    <div><dt>Bio</dt><dd><?= e($application['bio'] ?: 'Not added') ?></dd></div>
                                                </dl>
                                                <div class="applicant-actions">
                                                    <form method="POST" action="../control/AuthController.php">
                                                        <input type="hidden" name="action" value="post_application_action">
                                                        <input type="hidden" name="application_id" value="<?= e($application['id']) ?>">
                                                        <input type="hidden" name="application_status" value="accepted">
                                                        <button type="submit" class="mini-btn accept">Accept</button>
                                                    </form>
                                                    <form method="POST" action="../control/AuthController.php">
                                                        <input type="hidden" name="action" value="post_application_action">
                                                        <input type="hidden" name="application_id" value="<?= e($application['id']) ?>">
                                                        <input type="hidden" name="application_status" value="rejected">
                                                        <button type="submit" class="mini-btn reject">Reject</button>
                                                    </form>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
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
