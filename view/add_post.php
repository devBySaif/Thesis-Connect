<?php
require_once __DIR__ . '/student_helpers.php';
$context = loadStudentContext();
extract($context);
clearStudentFlash();

$teachers = $user->getVerifiedTeachers();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <?php renderStudentNavbar('my_posts', $studentName, $studentId, $profileImage, $notificationCount, $notifications); ?>

    <main class="dashboard-workspace">
        <section class="page-heading">
            <h1>Add Post</h1>
            <p>Create a new thesis recruitment post. You can create as many posts as you need.</p>
        </section>

        <?php if (!empty($success)): ?>
            <div class="profile-alert success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="profile-alert error"><?= e($error) ?></div>
        <?php endif; ?>

        <section class="profile-card create-post-panel">
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

                <div class="post-actions split-actions">
                    <button type="submit" class="profile-btn">
                        <i class="fa-solid fa-plus"></i>
                        Publish Post
                    </button>
                    <a href="my_posts.php" class="profile-btn secondary link-btn">Back to My Posts</a>
                </div>
            </form>
        </section>
    </main>
    <script src="../js/student_dashboard.js"></script>

    <!-- Confirmation modal (styled inline for simplicity) -->
    <div id="confirmModal" style="display:none;position:fixed;inset:0;align-items:center;justify-content:center;z-index:2000;">
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.6);"></div>
        <div style="position:relative;background:#0b1220;color:#fff;padding:20px;border-radius:8px;max-width:520px;width:90%;box-shadow:0 10px 30px rgba(0,0,0,0.6);">
            <h3 style="margin-top:0">Confirm teacher permission</h3>
            <p>Are you sure the supervising teacher has given permission for this post? If not, faculty may take action.</p>
            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px;">
                <button id="confirmCancel" class="profile-btn secondary" style="background:#374151;color:#fff;border:none;padding:8px 12px;border-radius:6px;">Cancel</button>
                <button id="confirmOk" class="profile-btn" style="background:#6C63FF;color:#fff;border:none;padding:8px 12px;border-radius:6px;">Yes, publish</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const postForm = document.querySelector('.js-post-form');
            const modal = document.getElementById('confirmModal');
            const okBtn = document.getElementById('confirmOk');
            const cancelBtn = document.getElementById('confirmCancel');

            if (!postForm || !modal) return;

            postForm.addEventListener('submit', function (e) {
                e.preventDefault();
                modal.style.display = 'flex';
            });

            okBtn.addEventListener('click', function () {
                modal.style.display = 'none';
                postForm.submit();
            });

            cancelBtn.addEventListener('click', function () {
                modal.style.display = 'none';
            });

            // close modal on overlay click
            modal.addEventListener('click', function (ev) {
                if (ev.target === modal) modal.style.display = 'none';
            });
        });
    </script>
</body>

</html>
