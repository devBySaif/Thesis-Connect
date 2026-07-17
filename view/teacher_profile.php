<?php
require_once __DIR__ . '/teacher_helpers.php';
$context = loadTeacherContext();
extract($context);

$profileSuccess = $_SESSION['teacher_profile_success'] ?? '';
$profileError = $_SESSION['teacher_profile_error'] ?? '';
unset($_SESSION['teacher_profile_success'], $_SESSION['teacher_profile_error']);
clearTeacherFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head><?php renderTeacherHead('Teacher Profile'); ?></head>
<body>
<?php renderTeacherNavbar('profile', $teacherName, $teacherId, $profileImage, $notificationCount, $notifications); ?>
<main class="profile-page">
    <section class="profile-hero">
        <div class="profile-identity">
            <img src="<?= e($profileImage) ?>" alt="Profile">
            <div><h1><?= e($teacherName) ?></h1><p><?= e($teacher['designation']) ?> · <?= e($teacher['department']) ?></p></div>
        </div>
        <div class="verified-badge"><i class="fa-solid fa-circle-check"></i> Verified Teacher</div>
    </section>

    <?php if ($profileSuccess): ?><div class="profile-alert success"><?= e($profileSuccess) ?></div><?php endif; ?>
    <?php if ($profileError): ?><div class="profile-alert error"><?= e($profileError) ?></div><?php endif; ?>

    <section class="profile-layout">
        <aside class="profile-card">
            <h2>Teacher Info</h2>
            <dl>
                <div><dt>Email</dt><dd><?= e($teacher['email']) ?></dd></div>
                <div><dt>Teacher ID</dt><dd><?= e($teacher['teacher_id']) ?></dd></div>
                <div><dt>Phone</dt><dd><?= e($teacher['phone']) ?></dd></div>
                <div><dt>Office</dt><dd><?= e($teacher['office'] ?: 'Not set') ?></dd></div>
                <div><dt>Bio</dt><dd><?= e($teacher['bio'] ?: 'Not added yet') ?></dd></div>
            </dl>
        </aside>

        <div class="profile-forms">
            <section class="profile-card">
                <h2>Edit Profile</h2>
                <form method="POST" action="../control/AuthController.php" enctype="multipart/form-data" class="profile-form">
                    <input type="hidden" name="action" value="teacher_profile_update">
                    <div class="form-grid">
                        <label>Full Name<input type="text" name="full_name" value="<?= e($teacher['full_name']) ?>"></label>
                        <label>Teacher ID<input type="text" name="teacher_id" value="<?= e($teacher['teacher_id']) ?>"></label>
                        <label>Designation<input type="text" name="designation" value="<?= e($teacher['designation']) ?>"></label>
                        <label>Department<input type="text" name="department" value="<?= e($teacher['department']) ?>"></label>
                        <label>Office<input type="text" name="office" value="<?= e($teacher['office']) ?>"></label>
                        <label>Phone<input type="tel" name="phone" value="<?= e($teacher['phone']) ?>"></label>
                    </div>
                    <label>Email<input type="email" name="email" value="<?= e($teacher['email']) ?>"></label>
                    <label>Profile Picture<input type="file" name="profile_picture" accept="image/jpeg,image/png,image/webp"></label>
                    <label>Bio<textarea name="bio" rows="4"><?= e($teacher['bio']) ?></textarea></label>
                    <button class="profile-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                </form>
            </section>

            <section class="profile-card">
                <h2>Change Password</h2>
                <form method="POST" action="../control/AuthController.php" class="profile-form">
                    <input type="hidden" name="action" value="teacher_password_update">
                    <div class="form-grid">
                        <label>Current Password<input type="password" name="current_password"></label>
                        <label>New Password<input type="password" name="new_password"></label>
                        <label>Confirm Password<input type="password" name="confirm_password"></label>
                    </div>
                    <button class="profile-btn secondary" type="submit"><i class="fa-solid fa-key"></i> Update Password</button>
                </form>
            </section>
        </div>
    </section>
</main>
<script src="../js/student_dashboard.js"></script>
</body>
</html>
