<?php
require_once __DIR__ . '/student_helpers.php';
$context = loadStudentContext();
extract($context);
clearStudentFlash();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Topics | ThesisConnect</title>
    <link rel="stylesheet" href="../css/student_dashboard.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body>
    <?php renderStudentNavbar('browse', $studentName, $studentId, $profileImage, $notificationCount, $notifications); ?>

    <main class="dashboard-workspace">
        <section class="page-heading">
            <h1>Browse Topics</h1>
            <p>Topic browsing will be connected here when thesis topic records are added.</p>
        </section>

        <section class="profile-card">
            <p class="muted-text">For now, use Recruitment Posts to find thesis group opportunities.</p>
        </section>
    </main>

    <script src="../js/student_dashboard.js"></script>
</body>

</html>
