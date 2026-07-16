<?php
session_start();
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | ThesisConnect</title>

    <link rel="stylesheet" href="../css/login.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>

<body>

<div class="container">

    <!-- Left Section -->

    <div class="left-panel">

        <div class="overlay">

            <h1>ThesisConnect</h1>

            <p>
                Collaborate with teammates,
                connect with supervisors,
                and manage your thesis journey
                from one platform.
            </p>

        </div>

    </div>


    <!-- Right Section -->

    <div class="right-panel">

        <div class="login-box">

            <div class="logo">

                <i class="fa-solid fa-graduation-cap"></i>

            </div>

            <h2>Welcome Back 👋</h2>

            <span>
                Login to continue your academic journey
            </span>

           <form id="loginForm" method="POST" action="../control/AuthController.php">
                <input type="hidden" name="action" value="login">
                <?php if (!empty($error)): ?>
                    <div class="login-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

    <!-- Email -->

    <div class="input-box">

        <label for="email">Email Address</label>

        <div class="input-field">

            <i class="fa-solid fa-envelope"></i>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your university email"
                autocomplete="email"
             
            >

        </div>

    </div>

    <!-- Password -->

    <div class="input-box">

        <label for="password">Password</label>

        <div class="input-field">

            <i class="fa-solid fa-lock"></i>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter password"
                autocomplete="current-password"
               
            >

        </div>

    </div>

    <!-- Remember Me -->

    <div class="options">

        <label for="remember">

            <input
                type="checkbox"
                id="remember"
                name="remember"
            >

            Remember me

        </label>

        <a href="#">Forgot Password?</a>

    </div>

    <!-- Login Button -->

    <button
        type="submit"
        id="loginBtn"
        name="loginBtn">

        Login

    </button>

</form>

            <div class="divider">

                OR

            </div>

            <div class="register-links">

                <a href="student_registration.php">Student Registration</a>

                <a href="teacher_registration.php">Teacher Registration</a>

            </div>

        </div>

    </div>

</div>

</body>

</html>