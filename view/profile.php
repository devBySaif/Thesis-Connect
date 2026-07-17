<?php
session_start();

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/User.php';

$database = new Database();
$conn = $database->connect();
$user = new User($conn);
$student = $user->getStudentProfile($_SESSION['user']['id']);

if (!$student || (int) $student['is_verified'] !== 1) {
    session_unset();
    session_destroy();
    header('Location: login.php?error=' . urlencode('Your student account is pending admin approval.'));
    exit;
}

$success = $_SESSION['student_profile_success'] ?? '';
$error = $_SESSION['student_profile_error'] ?? '';
unset($_SESSION['student_profile_success'], $_SESSION['student_profile_error']);

$studentName = $student['full_name'] ?? 'Student';
$studentId = $student['student_id'] ?? '';
$profileImage = '';

if (!empty($student['profile_picture'])) {
    $profileFile = basename($student['profile_picture']);
    $profilePath = __DIR__ . '/../uploads/profile/' . $profileFile;

    if (is_file($profilePath)) {
        $profileImage = '../uploads/profile/' . rawurlencode($profileFile);
    }
}

if (empty($profileImage)) {
    $initial = strtoupper(substr(trim($studentName), 0, 1)) ?: 'S';
    $fallbackSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160"><rect width="160" height="160" rx="80" fill="#6C63FF"/><text x="50%" y="54%" dominant-baseline="middle" text-anchor="middle" fill="#FFFFFF" font-family="Arial, sans-serif" font-size="72" font-weight="700">' . htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') . '</text></svg>';
    $profileImage = 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($fallbackSvg);
}

$departments = ['CSE', 'Software Engineering', 'Information Technology'];
$semesters = ['1st Semester', '2nd Semester', '3rd Semester', '4th Semester', '5th Semester', '6th Semester', '7th Semester', '8th Semester'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <header class="navbar">
        <div class="logo">
            <a href="student_dashboard.php">
                <h2>ThesisConnect</h2>
            </a>
        </div>

        <nav class="nav-links">
            <a href="student_dashboard.php">Dashboard</a>
            <a href="browse_topics.php">Browse Topics</a>
            <a href="create_post.php">Recruitment Post</a>
            <a href="announcements.php">Announcements</a>
        </nav>

        <div class="nav-right">
            <div class="profile-wrapper">
                <img
                    src="<?= htmlspecialchars($profileImage) ?>"
                    alt="Profile"
                    id="profileBtn"
                    class="profile-image">

                <div id="profileDropdown" class="profile-dropdown">
                    <div class="profile-header">
                        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile">
                        <h3 id="studentName"><?= htmlspecialchars($studentName) ?></h3>
                        <p id="studentId"><?= htmlspecialchars($studentId) ?></p>
                    </div>

                    <ul>
                        <li>
                            <a href="student_dashboard.php">
                                <i class="fa-solid fa-house"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="profile.php">
                                <i class="fa-solid fa-user"></i>
                                Profile
                            </a>
                        </li>
                        <li>
                            <a href="browse_topics.php">
                                <i class="fa-solid fa-book-open"></i>
                                Browse Topics
                            </a>
                        </li>
                        <li>
                            <a href="create_post.php">
                                <i class="fa-solid fa-users"></i>
                                Recruitment Posts
                            </a>
                        </li>
                        <li>
                            <a href="announcements.php">
                                <i class="fa-solid fa-bullhorn"></i>
                                Announcements
                            </a>
                        </li>
                        <li>
                            <a href="admin_logout.php">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <main class="profile-page">
        <section class="profile-hero">
            <div class="profile-identity">
                <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile">
                <div>
                    <h1><?= htmlspecialchars($studentName) ?></h1>
                    <p><?= htmlspecialchars($studentId) ?> · <?= htmlspecialchars($student['department'] ?? '') ?></p>
                </div>
            </div>
            <div class="verified-badge">
                <i class="fa-solid fa-circle-check"></i>
                Verified Student
            </div>
        </section>

        <?php if (!empty($success)): ?>
            <div class="profile-alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="profile-alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="profile-layout">
            <aside class="profile-card">
                <h2>Student Info</h2>
                <dl>
                    <div>
                        <dt>Email</dt>
                        <dd><?= htmlspecialchars($student['email'] ?? '') ?></dd>
                    </div>
                    <div>
                        <dt>Phone</dt>
                        <dd><?= htmlspecialchars($student['phone'] ?? '') ?></dd>
                    </div>
                    <div>
                        <dt>Semester</dt>
                        <dd><?= htmlspecialchars($student['semester'] ?? '') ?></dd>
                    </div>
                    <div>
                        <dt>CGPA</dt>
                        <dd><?= htmlspecialchars($student['cgpa'] ?? '') ?></dd>
                    </div>
                    <div>
                        <dt>Bio</dt>
                        <dd><?= htmlspecialchars($student['bio'] ?: 'Not added yet') ?></dd>
                    </div>
                </dl>
            </aside>

            <div class="profile-forms">
                <section class="profile-card">
                    <h2>Update Profile</h2>
                    <form method="POST" action="../control/AuthController.php" enctype="multipart/form-data" class="profile-form">
                        <input type="hidden" name="action" value="student_profile_update">

                        <div class="form-grid">
                            <label>
                                Full Name
                                <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name'] ?? '') ?>" required>
                            </label>

                            <label>
                                Student ID
                                <input type="text" name="student_id" value="<?= htmlspecialchars($student['student_id'] ?? '') ?>" required>
                            </label>

                            <label>
                                Department
                                <select name="department" required>
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= htmlspecialchars($department) ?>" <?= ($student['department'] ?? '') === $department ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($department) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label>
                                Semester
                                <select name="semester" required>
                                    <option value="">Select Semester</option>
                                    <?php foreach ($semesters as $semester): ?>
                                        <option value="<?= htmlspecialchars($semester) ?>" <?= ($student['semester'] ?? '') === $semester ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($semester) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label>
                                CGPA
                                <input type="number" name="cgpa" min="0" max="4" step="0.01" value="<?= htmlspecialchars($student['cgpa'] ?? '') ?>">
                            </label>

                            <label>
                                Phone
                                <input type="tel" name="phone" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" required>
                            </label>
                        </div>

                        <label>
                            Email
                            <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" required>
                        </label>

                        <label>
                            Profile Picture
                            <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/webp">
                        </label>

                        <label>
                            Bio
                            <textarea name="bio" rows="4"><?= htmlspecialchars($student['bio'] ?? '') ?></textarea>
                        </label>

                        <button type="submit" class="profile-btn">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Save Changes
                        </button>
                    </form>
                </section>

                <section class="profile-card">
                    <h2>Update Password</h2>
                    <form method="POST" action="../control/AuthController.php" class="profile-form">
                        <input type="hidden" name="action" value="student_password_update">

                        <div class="form-grid">
                            <label>
                                Current Password
                                <input type="password" name="current_password" autocomplete="current-password" required>
                            </label>

                            <label>
                                New Password
                                <input type="password" name="new_password" autocomplete="new-password" minlength="8" required>
                            </label>

                            <label>
                                Confirm Password
                                <input type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
                            </label>
                        </div>

                        <button type="submit" class="profile-btn secondary">
                            <i class="fa-solid fa-key"></i>
                            Update Password
                        </button>
                    </form>
                </section>
            </div>
        </section>
    </main>

    <script src="../js/student_dashboard.js"></script>
</body>

</html>
