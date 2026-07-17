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

    $_SESSION['user'] = [
        'id' => $userRow['id'],
        'email' => $userRow['email'],
        'role' => $userRow['role'],
        'is_verified' => $userRow['is_verified']
    ];

    if ($userRow['role'] === 'admin') {
        header('Location: ../view/admin_dashboard.php');
        exit;
    }

    // Redirect non-admin users to login page or their own dashboard if implemented
    header('Location: ../view/login.php');
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
