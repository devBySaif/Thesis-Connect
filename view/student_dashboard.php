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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | ThesisConnect</title>

    <!-- CSS -->
    <link rel="stylesheet" href="../css/student_dashboard.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>

    <!-- ================= NAVBAR ================= -->

    <header class="navbar">

        <div class="logo">

            <a href="student_dashboard.php">
                <h2>ThesisConnect</h2>
            </a>

        </div>


        <nav class="nav-links">

            <a href="student_dashboard.php" class="active">Dashboard</a>

            <a href="browse_topics.php">Browse Topics</a>

            <a href="create_post.php">Recruitment Post</a>

            <a href="announcements.php">Announcements</a>

        </nav>


        <div class="nav-right">

            <!-- Notification -->

            <div class="notification-wrapper">

                <button
                    id="notificationBtn"
                    class="notification-btn">

                    <i class="fa-solid fa-bell"></i>

                    <span
                        id="notificationCount"
                        class="notification-count">

                        4

                    </span>

                </button>



                <!-- Notification Dropdown -->

                <div
                    id="notificationDropdown"
                    class="notification-dropdown">

                    <h3>Notifications</h3>

                    <div class="notification-list">

                        <div class="notification-item">

                            <i class="fa-solid fa-user-check"></i>

                            <div>

                                <p>Your account has been verified.</p>

                                <small>2 min ago</small>

                            </div>

                        </div>


                        <div class="notification-item">

                            <i class="fa-solid fa-book"></i>

                            <div>

                                <p>New Thesis Topic Added.</p>

                                <small>10 min ago</small>

                            </div>

                        </div>


                        <div class="notification-item">

                            <i class="fa-solid fa-users"></i>

                            <div>

                                <p>Someone applied to your post.</p>

                                <small>25 min ago</small>

                            </div>

                        </div>


                        <div class="notification-item">

                            <i class="fa-solid fa-bullhorn"></i>

                            <div>

                                <p>New Announcement Published.</p>

                                <small>1 hour ago</small>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Profile -->

            <div class="profile-wrapper">

                <img

                    src="<?= htmlspecialchars($profileImage) ?>"

                    alt="Profile"

                    id="profileBtn"

                    class="profile-image">




                <!-- Profile Dropdown -->

                <div
                    id="profileDropdown"
                    class="profile-dropdown">


                    <div class="profile-header">

                        <img
                            src="<?= htmlspecialchars($profileImage) ?>"
                            alt="Profile">

                        <h3 id="studentName">

                            <?= htmlspecialchars($studentName) ?>

                        </h3>

                        <p id="studentId">

                            <?= htmlspecialchars($studentId) ?>

                        </p>

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

                            <a href="my_posts.php">

                                <i class="fa-solid fa-rectangle-list"></i>

                                My Posts

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



    <!-- ================= HERO ================= -->

    <section class="hero">

        <div class="hero-content">

            <h1>

                Welcome Back,

                <span id="welcomeStudent">

                    <?= htmlspecialchars($studentName) ?>

                </span>

            </h1>

            <p>

                Find thesis partners and explore new research opportunities.

            </p>

        </div>

    </section>




    <!-- ================= FEED ================= -->

    <section class="feed-section">

        <div class="feed-header">

            <h2>

                Recent Recruitment Posts

            </h2>

        </div>




        <div
            id="recruitmentFeed"
            class="feed-container">


            <!-- CARD 1 -->

            <div class="post-card">

                <div class="post-top">

                    <img
                        src="../../assets/profile/default.png"
                        class="post-profile">

                    <div>

                        <h3>

                            Abdullah Al Mamun

                        </h3>

                        <small>

                            2 hours ago

                        </small>

                    </div>

                </div>


                <div class="post-body">

                    <h2>

                        Need 2 Members For AI Based Smart Glass

                    </h2>

                    <p>

                        Looking for enthusiastic CSE students interested in
                        Artificial Intelligence and Computer Vision.

                    </p>

                </div>


                <div class="post-info">

                    <span>

                        <i class="fa-solid fa-building"></i>

                        CSE

                    </span>

                    <span>

                        <i class="fa-solid fa-user-group"></i>

                        Need 2 Members

                    </span>

                    <span>

                        <i class="fa-solid fa-calendar"></i>

                        Deadline: 20 July

                    </span>

                </div>


                <div class="post-actions">

                    <button
                        class="apply-btn"
                        id="applyBtn1">

                        Apply

                    </button>

                </div>

            </div>




            <!-- CARD 2 -->

            <div class="post-card">

                <div class="post-top">

                    <img
                        src="../../assets/profile/default.png"
                        class="post-profile">

                    <div>

                        <h3>

                            Nusrat Jahan

                        </h3>

                        <small>

                            Yesterday

                        </small>

                    </div>

                </div>

                <div class="post-body">

                    <h2>

                        Blockchain Voting System

                    </h2>

                    <p>

                        Need one member with PHP and Database experience.

                    </p>

                </div>

                <div class="post-info">

                    <span>

                        <i class="fa-solid fa-building"></i>

                        CSE

                    </span>

                    <span>

                        <i class="fa-solid fa-user-group"></i>

                        Need 1 Member

                    </span>

                    <span>

                        <i class="fa-solid fa-calendar"></i>

                        Deadline: 25 July

                    </span>

                </div>

                <div class="post-actions">

                    <button
                        class="apply-btn"
                        id="applyBtn2">

                        Apply

                    </button>

                </div>

            </div>




            <!-- CARD 3 -->

            <div class="post-card">

                <div class="post-top">

                    <img
                        src="../../assets/profile/default.png"
                        class="post-profile">

                    <div>

                        <h3>

                            Rakib Hasan

                        </h3>

                        <small>

                            3 Days Ago

                        </small>

                    </div>

                </div>

                <div class="post-body">

                    <h2>

                        Medical Image Processing

                    </h2>

                    <p>

                        Looking for 3 members with Machine Learning interest.

                    </p>

                </div>

                <div class="post-info">

                    <span>

                        <i class="fa-solid fa-building"></i>

                        CSE

                    </span>

                    <span>

                        <i class="fa-solid fa-user-group"></i>

                        Need 3 Members

                    </span>

                    <span>

                        <i class="fa-solid fa-calendar"></i>

                        Deadline: 30 July

                    </span>

                </div>

                <div class="post-actions">

                    <button
                        class="apply-btn"
                        id="applyBtn3">

                        Apply

                    </button>

                </div>

            </div>

        </div>

    </section>


    <!-- JS -->

    <script src="../js/student_dashboard.js"></script>

</body>

</html>
