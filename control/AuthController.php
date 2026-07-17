<?php

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../model/User.php";

$database = new Database();
$conn = $database->connect();

$user = new User($conn);
$user->ensureDefaultAdmin();

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid Request."
    ]);

    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {

    case "student_register":

        studentRegister($user, $conn);

        break;

    case "teacher_register":

        teacherRegister($user, $conn);

        break;

    case "login":

        loginUser($user);

        break;

    case "admin_student_action":

        adminStudentAction($user);

        break;

    case "admin_teacher_action":

        adminTeacherAction($user);

        break;

    case "admin_create":

        adminCreate($user);

        break;

    case "student_profile_update":

        studentProfileUpdate($user);

        break;

    case "student_password_update":

        studentPasswordUpdate($user);

        break;

    case "recruitment_post_save":

        recruitmentPostSave($user);

        break;

    case "post_apply":

        postApply($user);

        break;

    case "recruitment_post_delete":

        recruitmentPostDelete($user);

        break;

    case "post_application_action":

        postApplicationAction($user);

        break;

    case "announcement_create":

        announcementCreate($user);

        break;

    default:

        echo json_encode([
            "status" => "error",
            "message" => "Unknown Action."
        ]);

        break;
}

function loginUser($user)
{
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: ../view/login.php?error=' . urlencode('Please enter email and password.'));
        exit;
    }

    $userRow = $user->login($email);
    if (!$userRow || !password_verify($password, $userRow['password'])) {
        header('Location: ../view/login.php?error=' . urlencode('Invalid email or password.'));
        exit;
    }

    if ($userRow['role'] === 'admin') {
        $_SESSION['user'] = [
            'id' => $userRow['id'],
            'email' => $userRow['email'],
            'role' => $userRow['role'],
            'is_verified' => $userRow['is_verified']
        ];

        header('Location: ../view/admin_dashboard.php');
        exit;
    }

    if ($userRow['role'] === 'student') {
        if ((int) $userRow['is_verified'] !== 1) {
            header('Location: ../view/login.php?error=' . urlencode('Your student account is pending admin approval.'));
            exit;
        }

        $_SESSION['user'] = [
            'id' => $userRow['id'],
            'email' => $userRow['email'],
            'role' => $userRow['role'],
            'is_verified' => $userRow['is_verified']
        ];

        header('Location: ../view/create_post.php');
        exit;
    }

    header('Location: ../view/login.php?error=' . urlencode('Dashboard is not available for this account yet.'));
    exit;
}

/* ===========================================================
                    STUDENT REGISTER
=========================================================== */

function studentRegister($user, $conn)
{

    $full_name = trim($_POST['full_name']);

    $student_id = trim($_POST['student_id']);

    $department = trim($_POST['department']);

    $semester = trim($_POST['semester']);

    $cgpa = trim($_POST['cgpa']);

    $phone = trim($_POST['phone']);

    $email = trim($_POST['email']);

    $password = $_POST['password'];

    $confirm_password = $_POST['confirm_password'];

    $bio = trim($_POST['bio']);

    /* ===========================
        PHP Validation
    =========================== */

    if (
        empty($full_name) ||
        empty($student_id) ||
        empty($department) ||
        empty($semester) ||
        empty($phone) ||
        empty($email) ||
        empty($password)
    ) {

        echo json_encode([
            "status" => "error",
            "message" => "Please fill all required fields."
        ]);

        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo json_encode([
            "status" => "error",
            "message" => "Invalid Email Address."
        ]);

        exit;
    }

    if (strlen($password) < 8) {

        echo json_encode([
            "status" => "error",
            "message" => "Password must be at least 8 characters."
        ]);

        exit;
    }

    if ($password != $confirm_password) {

        echo json_encode([
            "status" => "error",
            "message" => "Passwords do not match."
        ]);

        exit;
    }

    if ($user->emailExists($email)) {

        echo json_encode([
            "status" => "error",
            "message" => "Email already exists."
        ]);

        exit;
    }

    /* ===========================
        Upload Profile Picture
    =========================== */

    $profile_picture = null;

    if (
        isset($_FILES['profile_picture']) &&
        $_FILES['profile_picture']['error'] == 0
    ) {

        $uploadDir = __DIR__ . "/../uploads/profile/";

        if (!is_dir($uploadDir)) {

            mkdir($uploadDir, 0777, true);

        }

        $extension = pathinfo(
            $_FILES['profile_picture']['name'],
            PATHINFO_EXTENSION
        );

        $filename = time() . "_" . uniqid() . "." . $extension;

        move_uploaded_file(

            $_FILES['profile_picture']['tmp_name'],

            $uploadDir . $filename

        );

        $profile_picture = $filename;
    }

    /* ===========================
        Database Transaction
    =========================== */

    try {

        $conn->beginTransaction();

        $user_id = $user->registerUser(

            $email,

            $password,

            "student"

        );

        if (!$user_id) {

            throw new Exception("User registration failed.");

        }

        $student = [

            "user_id" => $user_id,

            "full_name" => $full_name,

            "student_id" => $student_id,

            "department" => $department,

            "semester" => $semester,

            "cgpa" => $cgpa,

            "phone" => $phone,

            "profile_picture" => $profile_picture,

            "bio" => $bio

        ];

        if (!$user->insertStudentProfile($student)) {

            throw new Exception("Student profile insert failed.");

        }

        $conn->commit();

        echo json_encode([

            "status" => "success",

            "message" => "Registration successful. Your account is pending admin approval."

        ]);

    }

    catch (Exception $e) {

        $conn->rollBack();

        echo json_encode([

            "status" => "error",

            "message" => $e->getMessage()

        ]);

    }

}

function teacherRegister($user, $conn)
{

    $full_name = trim($_POST['full_name']);

    $teacher_id = trim($_POST['teacher_id']);

    $designation = trim($_POST['designation']);

    $department = trim($_POST['department']);

    $office = trim($_POST['office']);

    $phone = trim($_POST['phone']);

    $email = trim($_POST['email']);

    $password = $_POST['password'];

    $confirm_password = $_POST['confirm_password'];

    $bio = trim($_POST['bio']);

    if (
        empty($full_name) ||
        empty($teacher_id) ||
        empty($designation) ||
        empty($department) ||
        empty($office) ||
        empty($phone) ||
        empty($email) ||
        empty($password)
    ) {

        echo json_encode([
            "status" => "error",
            "message" => "Please fill all required fields."
        ]);

        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        echo json_encode([
            "status" => "error",
            "message" => "Invalid Email Address."
        ]);

        exit;
    }

    if (strlen($password) < 8) {

        echo json_encode([
            "status" => "error",
            "message" => "Password must be at least 8 characters."
        ]);

        exit;
    }

    if ($password != $confirm_password) {

        echo json_encode([
            "status" => "error",
            "message" => "Passwords do not match."
        ]);

        exit;
    }

    if ($user->emailExists($email)) {

        echo json_encode([
            "status" => "error",
            "message" => "Email already exists."
        ]);

        exit;
    }

    $profile_picture = null;

    if (
        isset($_FILES['profile_picture']) &&
        $_FILES['profile_picture']['error'] == 0
    ) {

        $uploadDir = __DIR__ . "/../uploads/profile/";

        if (!is_dir($uploadDir)) {

            mkdir($uploadDir, 0777, true);

        }

        $extension = pathinfo(
            $_FILES['profile_picture']['name'],
            PATHINFO_EXTENSION
        );

        $filename = time() . "_" . uniqid() . "." . $extension;

        move_uploaded_file(
            $_FILES['profile_picture']['tmp_name'], 
            $uploadDir . $filename
        );

        $profile_picture = $filename;
    }

    try {

        $conn->beginTransaction();

        $user_id = $user->registerUser(
            $email,
            $password,
            "teacher"
        );

        if (!$user_id) {
            throw new Exception("User registration failed.");
        }

        $teacher = [
            "user_id" => $user_id,
            "full_name" => $full_name,
            "teacher_id" => $teacher_id,
            "designation" => $designation,
            "department" => $department,
            "office" => $office,
            "phone" => $phone,
            "profile_picture" => $profile_picture,
            "bio" => $bio
        ];

        if (!$user->insertTeacherProfile($teacher)) {
            throw new Exception("Teacher profile insert failed.");
        }

        $conn->commit();

        echo json_encode([
            "status" => "success",
            "message" => "Registration successful. Your account is pending admin approval."
        ]);

    } catch (Exception $e) {

        $conn->rollBack();

        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);

    }
}

function adminStudentAction($user)
{
    $userId = $_POST['user_id'] ?? 0;
    $action = $_POST['student_action'] ?? '';

    if (!$userId || !in_array($action, ['approve', 'reject', 'delete'])) {
        header('Location: ../view/admin_manage_students.php');
        exit;
    }

    if ($action === 'approve') {
        $user->updateUserVerification($userId, 1);
    } elseif ($action === 'reject') {
        $user->updateUserVerification($userId, 0);
    } elseif ($action === 'delete') {
        $user->deleteUserById($userId);
    }

    header('Location: ../view/admin_manage_students.php');
    exit;
}

function adminTeacherAction($user)
{
    $userId = $_POST['user_id'] ?? 0;
    $action = $_POST['teacher_action'] ?? '';

    if (!$userId || !in_array($action, ['approve', 'reject', 'delete'])) {
        header('Location: ../view/admin_manage_teachers.php');
        exit;
    }

    if ($action === 'approve') {
        $user->updateUserVerification($userId, 1);
    } elseif ($action === 'reject') {
        $user->updateUserVerification($userId, 0);
    } elseif ($action === 'delete') {
        $user->deleteUserById($userId);
    }

    header('Location: ../view/admin_manage_teachers.php');
    exit;
}

function adminCreate($user)
{
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: ../view/login.php');
        exit;
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($fullName) || empty($email) || empty($password)) {
        $_SESSION['admin_error'] = 'Name, email, and password are required.';
        header('Location: ../view/admin_manage_admins.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['admin_error'] = 'Invalid email address.';
        header('Location: ../view/admin_manage_admins.php');
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['admin_error'] = 'Password must be at least 8 characters.';
        header('Location: ../view/admin_manage_admins.php');
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['admin_error'] = 'Passwords do not match.';
        header('Location: ../view/admin_manage_admins.php');
        exit;
    }

    if ($user->emailExists($email)) {
        $_SESSION['admin_error'] = 'Email already exists.';
        header('Location: ../view/admin_manage_admins.php');
        exit;
    }

    if (!$user->createAdmin($email, $password, $fullName, $phone)) {
        $_SESSION['admin_error'] = 'Admin account could not be created.';
        header('Location: ../view/admin_manage_admins.php');
        exit;
    }

    $_SESSION['admin_success'] = 'New admin account created successfully.';
    header('Location: ../view/admin_manage_admins.php');
    exit;
}

function requireStudentSession()
{
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
        header('Location: ../view/login.php');
        exit;
    }
}

function requireAdminSession()
{
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: ../view/login.php');
        exit;
    }
}

function redirectWithStudentFlash($location, $message, $type = 'error')
{
    $_SESSION[$type === 'success' ? 'student_success' : 'student_error'] = $message;
    header('Location: ' . $location);
    exit;
}

function redirectWithAdminFlash($location, $message, $type = 'error')
{
    $_SESSION[$type === 'success' ? 'admin_success' : 'admin_error'] = $message;
    header('Location: ' . $location);
    exit;
}

function redirectStudentProfile($message, $type = 'error')
{
    $_SESSION[$type === 'success' ? 'student_profile_success' : 'student_profile_error'] = $message;
    header('Location: ../view/profile.php');
    exit;
}

function uploadStudentProfilePicture()
{
    if (empty($_FILES['profile_picture']['name'])) {
        return null;
    }

    if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        redirectStudentProfile('Profile picture upload failed.');
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    $imageInfo = getimagesize($_FILES['profile_picture']['tmp_name']);
    $mimeType = $imageInfo['mime'] ?? '';

    if (!isset($allowedTypes[$mimeType])) {
        redirectStudentProfile('Only JPG, PNG, or WEBP profile pictures are allowed.');
    }

    if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
        redirectStudentProfile('Profile picture must be 2MB or smaller.');
    }

    $uploadDir = __DIR__ . '/../uploads/profile/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = time() . '_' . uniqid() . '.' . $allowedTypes[$mimeType];

    if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadDir . $filename)) {
        redirectStudentProfile('Profile picture could not be saved.');
    }

    return $filename;
}

function studentProfileUpdate($user)
{
    requireStudentSession();

    $userId = (int) $_SESSION['user']['id'];
    $fullName = trim($_POST['full_name'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $cgpa = trim($_POST['cgpa'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    if (empty($fullName) || empty($studentId) || empty($department) || empty($semester) || empty($phone) || empty($email)) {
        redirectStudentProfile('Please fill all required profile fields.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectStudentProfile('Invalid email address.');
    }

    if ($cgpa !== '' && ($cgpa < 0 || $cgpa > 4)) {
        redirectStudentProfile('CGPA must be between 0 and 4.');
    }

    if ($user->emailExistsExceptUser($email, $userId)) {
        redirectStudentProfile('This email is already used by another account.');
    }

    $profilePicture = uploadStudentProfilePicture();

    $updated = $user->updateStudentProfile($userId, [
        'full_name' => $fullName,
        'student_id' => $studentId,
        'department' => $department,
        'semester' => $semester,
        'cgpa' => $cgpa,
        'phone' => $phone,
        'email' => $email,
        'bio' => $bio,
        'profile_picture' => $profilePicture
    ]);

    if (!$updated) {
        redirectStudentProfile('Profile could not be updated.');
    }

    $_SESSION['user']['email'] = $email;
    redirectStudentProfile('Profile updated successfully.', 'success');
}

function studentPasswordUpdate($user)
{
    requireStudentSession();

    $userId = (int) $_SESSION['user']['id'];
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        redirectStudentProfile('Please fill all password fields.');
    }

    $userRow = $user->getUserById($userId);
    if (!$userRow || !password_verify($currentPassword, $userRow['password'])) {
        redirectStudentProfile('Current password is incorrect.');
    }

    if (strlen($newPassword) < 8) {
        redirectStudentProfile('New password must be at least 8 characters.');
    }

    if ($newPassword !== $confirmPassword) {
        redirectStudentProfile('New password and confirm password do not match.');
    }

    if (!$user->updateUserPassword($userId, $newPassword)) {
        redirectStudentProfile('Password could not be updated.');
    }

    redirectStudentProfile('Password updated successfully.', 'success');
}

function recruitmentPostSave($user)
{
    requireStudentSession();

    $studentUserId = (int) $_SESSION['user']['id'];
    $postId = (int) ($_POST['post_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $teacherUserId = (int) ($_POST['teacher_user_id'] ?? 0);
    $membersNeeded = (int) ($_POST['members_needed'] ?? 0);
    $deadline = trim($_POST['deadline'] ?? '');
    $status = trim($_POST['status'] ?? 'open');
    $redirect = $postId ? '../view/my_posts.php?edit=' . $postId : '../view/add_post.php';

    if ($title === '' || $description === '' || $department === '' || $membersNeeded < 1 || $deadline === '') {
        redirectWithStudentFlash($redirect, 'Please fill title, description, department, members needed, and deadline.');
    }

    $deadlineTime = strtotime($deadline);
    if (!$deadlineTime) {
        redirectWithStudentFlash($redirect, 'Please select a valid deadline.');
    }

    if (!$postId && $deadlineTime < strtotime(date('Y-m-d'))) {
        redirectWithStudentFlash($redirect, 'Deadline cannot be in the past.');
    }

    if (!in_array($status, ['open', 'closed'], true)) {
        $status = 'open';
    }

    $data = [
        'student_user_id' => $studentUserId,
        'teacher_user_id' => $teacherUserId,
        'title' => $title,
        'description' => $description,
        'department' => $department,
        'members_needed' => $membersNeeded,
        'deadline' => date('Y-m-d', $deadlineTime),
        'status' => $status
    ];

    $saved = $postId
        ? $user->updateRecruitmentPost($postId, $studentUserId, $data)
        : $user->createRecruitmentPost($data);

    if (!$saved) {
        redirectWithStudentFlash($redirect, 'Post could not be saved.');
    }

    redirectWithStudentFlash('../view/my_posts.php', $postId ? 'Post updated successfully.' : 'Post created successfully.', 'success');
}

function postApply($user)
{
    requireStudentSession();

    $postId = (int) ($_POST['post_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (!$postId) {
        redirectWithStudentFlash('../view/create_post.php', 'Invalid post selected.');
    }

    if ($message === '') {
        redirectWithStudentFlash('../view/create_post.php', 'Please write a short application message.');
    }

    $post = $user->getRecruitmentPostById($postId);

    if (!$user->applyToRecruitmentPost($postId, (int) $_SESSION['user']['id'], $message)) {
        redirectWithStudentFlash('../view/create_post.php', 'Could not apply. You may have already applied, own the post, or the deadline has passed.');
    }

    $student = $user->getStudentProfile((int) $_SESSION['user']['id']);
    if ($post) {
        $user->createNotification(
            (int) $post['student_user_id'],
            'New application received',
            ($student['full_name'] ?? 'A student') . ' applied to your post: ' . $post['title'],
            'my_posts.php#post-' . $postId
        );
    }

    redirectWithStudentFlash('../view/create_post.php', 'Application submitted successfully.', 'success');
}

function recruitmentPostDelete($user)
{
    requireStudentSession();

    $postId = (int) ($_POST['post_id'] ?? 0);

    if (!$postId) {
        redirectWithStudentFlash('../view/my_posts.php', 'Invalid post selected.');
    }

    if (!$user->deleteRecruitmentPost($postId, (int) $_SESSION['user']['id'])) {
        redirectWithStudentFlash('../view/my_posts.php', 'Post could not be deleted.');
    }

    redirectWithStudentFlash('../view/my_posts.php', 'Post and its applicants deleted successfully.', 'success');
}

function postApplicationAction($user)
{
    requireStudentSession();

    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $status = trim($_POST['application_status'] ?? '');

    if (!$applicationId || !in_array($status, ['accepted', 'rejected'], true)) {
        redirectWithStudentFlash('../view/my_posts.php', 'Invalid application action.');
    }

    $application = $user->getApplicationForOwner($applicationId, (int) $_SESSION['user']['id']);

    if (!$user->updateApplicationStatus($applicationId, (int) $_SESSION['user']['id'], $status)) {
        redirectWithStudentFlash('../view/my_posts.php', 'Application status could not be updated.');
    }

    if ($application) {
        $user->createNotification(
            (int) $application['applicant_user_id'],
            $status === 'accepted' ? 'Application accepted' : 'Application rejected',
            $status === 'accepted'
                ? 'Your application for "' . $application['post_title'] . '" was accepted. The post owner will contact you as soon as possible.'
                : 'Your application for "' . $application['post_title'] . '" was rejected.',
            'create_post.php#post-' . $application['post_id']
        );
    }

    redirectWithStudentFlash('../view/my_posts.php', 'Application ' . $status . ' successfully.', 'success');
}

function announcementCreate($user)
{
    requireAdminSession();

    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($title === '' || $body === '') {
        redirectWithAdminFlash('../view/admin_announcements.php', 'Please fill announcement title and details.');
    }

    if (!$user->createAnnouncement((int) $_SESSION['user']['id'], $title, $body)) {
        redirectWithAdminFlash('../view/admin_announcements.php', 'Announcement could not be published.');
    }

    redirectWithAdminFlash('../view/admin_announcements.php', 'Announcement published successfully.', 'success');
}
