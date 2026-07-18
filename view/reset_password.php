<?php
session_start();
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | ThesisConnect</title>
    <link rel="stylesheet" href="../css/login.css?v=20260718c">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .reset-container {
            display: flex;
            width: 100%;
            height: 100vh;
            height: 100dvh;
            align-items: center;
        }

        .reset-panel {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f8fafc;
            padding: 16px;
        }

        .reset-box {
            width: 100%;
            max-width: 430px;
            background: white;
            padding: clamp(20px, 4vh, 32px) 32px;
            border-radius: 22px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.08);
        }

        .reset-box .logo {
            width: clamp(52px, 8vh, 64px);
            height: clamp(52px, 8vh, 64px);
            background: #2563eb;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            font-size: clamp(24px, 4vh, 28px);
            margin: 0 auto clamp(14px, 2.5vh, 20px);
        }

        .reset-box h2 {
            text-align: center;
            font-size: clamp(24px, 4vh, 30px);
            color: #222;
        }

        .reset-box p {
            text-align: center;
            color: #777;
            margin-top: 8px;
            margin-bottom: clamp(18px, 4vh, 28px);
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .invalid-token-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="reset-container">
    <div class="reset-panel">
        <div class="reset-box">
            <div class="logo">
                <i class="fa-solid fa-lock"></i>
            </div>

            <h2>Reset Password</h2>
            <p>Enter your new password below</p>

            <?php if (empty($token)): ?>
                <div class="invalid-token-message">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <p>Invalid reset link. Please request a new password reset.</p>
                </div>
                <div class="back-to-login">
                    <a href="login.php">Back to Login</a>
                </div>
            <?php else: ?>
                <form id="resetPasswordForm" method="POST" action="../control/AuthController.php">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div id="resetErrorMessage" class="modal-error" style="display:none;"></div>
                    <div id="resetSuccessMessage" class="modal-success" style="display:none;"></div>

                    <!-- New Password -->
                    <div class="input-box">
                        <label for="new_password">New Password</label>
                        <div class="input-field">
                            <i class="fa-solid fa-lock"></i>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                placeholder="Enter new password (min 8 characters)"
                                required
                            >
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="input-box">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-field">
                            <i class="fa-solid fa-lock"></i>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Confirm your new password"
                                required
                            >
                        </div>
                    </div>

                    <!-- Reset Button -->
                    <button type="submit" id="resetBtn" name="resetBtn">
                        Reset Password
                    </button>
                </form>

                <div class="back-to-login">
                    <a href="login.php">Back to Login</a>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        const form = document.getElementById("resetPasswordForm");
                        const errorDiv = document.getElementById("resetErrorMessage");
                        const successDiv = document.getElementById("resetSuccessMessage");

                        form.addEventListener("submit", (e) => {
                            e.preventDefault();

                            const newPassword = document.getElementById("new_password").value;
                            const confirmPassword = document.getElementById("confirm_password").value;

                            errorDiv.style.display = "none";
                            successDiv.style.display = "none";

                            if (newPassword.length < 8) {
                                errorDiv.textContent = "Password must be at least 8 characters.";
                                errorDiv.style.display = "block";
                                return;
                            }

                            if (newPassword !== confirmPassword) {
                                errorDiv.textContent = "Passwords do not match.";
                                errorDiv.style.display = "block";
                                return;
                            }

                            const formData = new FormData(form);

                            fetch("../control/AuthController.php", {
                                method: "POST",
                                body: formData
                            })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === "success") {
                                        successDiv.textContent = data.message;
                                        successDiv.style.display = "block";
                                        form.reset();
                                        
                                        setTimeout(() => {
                                            window.location.href = "login.php";
                                        }, 2000);
                                    } else {
                                        errorDiv.textContent = data.message;
                                        errorDiv.style.display = "block";
                                    }
                                })
                                .catch(error => {
                                    console.error(error);
                                    errorDiv.textContent = "An error occurred. Please try again.";
                                    errorDiv.style.display = "block";
                                });
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>

</html>
