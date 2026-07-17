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
</body>

</html>
