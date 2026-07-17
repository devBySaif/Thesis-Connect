<?php
require_once __DIR__ . '/teacher_helpers.php';
$context = loadTeacherContext();
extract($context);
clearTeacherFlash();
$students = $user->getSupervisedStudents((int) $_SESSION['user']['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head><?php renderTeacherHead('Supervised Students'); ?></head>
<body>
<?php renderTeacherNavbar('students', $teacherName, $teacherId, $profileImage, $notificationCount, $notifications); ?>
<main class="dashboard-workspace">
    <section class="page-heading"><h1>Supervised Students</h1><p>Students accepted under your thesis topics.</p></section>
    <section class="profile-card">
        <div class="responsive-table"><table><thead><tr><th>Name</th><th>Student ID</th><th>Topic</th><th>Department</th><th>CGPA</th><th>Status</th></tr></thead><tbody>
            <?php foreach ($students as $studentRow): ?><tr><td><?= e($studentRow['full_name']) ?></td><td><?= e($studentRow['student_id']) ?></td><td><?= e($studentRow['topic_title']) ?></td><td><?= e($studentRow['department']) ?></td><td><?= e($studentRow['cgpa']) ?></td><td><?= e(ucfirst($studentRow['status'])) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <?php if (empty($students)): ?><p class="muted-text">No supervised students yet.</p><?php endif; ?>
    </section>
</main>
<script src="../js/student_dashboard.js"></script>
</body>
</html>
