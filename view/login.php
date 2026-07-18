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

    <link rel="stylesheet" href="../css/login.css?v=20260718c">

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

        <div class="visual-network" aria-hidden="true">
          
                <line x1="95" y1="95" x2="360" y2="88"></line>
                <line x1="360" y1="88" x2="585" y2="110"></line>
                <line x1="585" y1="110" x2="680" y2="315"></line>
                <line x1="255" y1="420" x2="680" y2="315"></line>
                <line x1="255" y1="420" x2="360" y2="88"></line>
                <line x1="255" y1="420" x2="660" y2="485"></line>
                <line x1="660" y1="485" x2="680" y2="315"></line>
            </svg>
        </div>

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

            <h2>Welcome Back </h2>

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

        <a href="#" id="forgotPasswordLink">Forgot Password?</a>

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

<!-- Forgot Password Popup Modal -->
<div id="forgotPasswordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Forgot Password</h3>
            <button type="button" class="modal-close" id="modalCloseBtn">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="modal-subtitle">Enter your email address and we'll send you a link to reset your password.</p>
            
            <form id="forgotPasswordForm" method="POST" action="../control/AuthController.php">
                <input type="hidden" name="action" value="forgot_password">
                
                <div id="forgotErrorMessage" class="modal-error" style="display:none;"></div>
                <div id="forgotSuccessMessage" class="modal-success" style="display:none;"></div>
                
                <div class="modal-input-box">
                    <label for="forgotEmail">Email Address</label>
                    <div class="modal-input-field">
                        <i class="fa-solid fa-envelope"></i>
                        <input
                            type="email"
                            id="forgotEmail"
                            name="email"
                            placeholder="Enter your email"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="modal-btn">Send Reset Link</button>
            </form>
        </div>
    </div>
</div>

<script src="../js/auth.js?v=20260718c"></script>

</body>

</html>
