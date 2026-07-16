<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Teacher Registration | ThesisConnect</title>

    <link rel="stylesheet"
          href="../css/student_register.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>

<body>

<div class="container">

    <div class="register-card">

        <div class="logo">

            <i class="fa-solid fa-chalkboard-user"></i>

        </div>

        <h2>Create Teacher Account</h2>

        <p class="subtitle">

            Register as a faculty member of ThesisConnect.

        </p>

        <div id="registerMessage"></div>

        <form
            id="teacherRegisterForm"
            method="POST"
            enctype="multipart/form-data">

            <input type="hidden" name="action" value="teacher_register">

            <div class="grid">

                <!-- Full Name -->

                <div class="input-box">

                    <label for="full_name">Full Name</label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        placeholder="Enter Full Name"
                        autocomplete="name">

                </div>

                <!-- Teacher ID -->

                <div class="input-box">

                    <label for="teacher_id">Teacher ID</label>

                    <input
                        type="text"
                        id="teacher_id"
                        name="teacher_id"
                        placeholder="Enter Teacher ID">

                </div>

                <!-- Designation -->

                <div class="input-box">

                    <label for="designation">Designation</label>

                    <select
                        id="designation"
                        name="designation">

                        <option value="">Select Designation</option>

                        <option>Lecturer</option>

                        <option>Senior Lecturer</option>

                        <option>Assistant Professor</option>

                        <option>Associate Professor</option>

                        <option>Professor</option>

                    </select>

                </div>

                <!-- Department -->

                <div class="input-box">

                    <label for="department">Department</label>

                    <select
                        id="department"
                        name="department">

                        <option value="">Select Department</option>

                        <option>CSE</option>

                        <option>Software Engineering</option>

                        <option>Information Technology</option>

                    </select>

                </div>

                <!-- Office -->

                <div class="input-box">

                    <label for="office">Office</label>

                    <input
                        type="text"
                        id="office"
                        name="office"
                        placeholder="Office Room">

                </div>

                <!-- Phone -->

                <div class="input-box">

                    <label for="phone">Phone Number</label>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="01XXXXXXXXX">

                </div>

            </div>

            <!-- University Email -->

            <div class="input-box">

                <label for="email">

                    University Email

                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="faculty@university.edu"
                    autocomplete="email">

            </div>

            <!-- Password -->

            <div class="grid">

                <div class="input-box">

                    <label for="password">

                        Password

                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password">

                </div>

                <div class="input-box">

                    <label for="confirm_password">

                        Confirm Password

                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password">

                </div>

            </div>

            <!-- Profile -->

            <div class="input-box">

                <label for="profile_picture">

                    Profile Picture (Optional)

                </label>

                <input
                    type="file"
                    id="profile_picture"
                    name="profile_picture"
                    accept="image/*">

            </div>

            <!-- Bio -->

            <div class="input-box">

                <label for="bio">

                    Bio (Optional)

                </label>

                <textarea
                    id="bio"
                    name="bio"
                    rows="4"
                    placeholder="Write a short professional bio..."></textarea>

            </div>

            <!-- Terms -->

            <div class="terms">

                <label>

                    <input
                        type="checkbox"
                        id="terms"
                        name="terms">

                    I agree to the Terms & Conditions

                </label>

            </div>

            <button
                type="submit"
                id="registerBtn"
                name="registerBtn">

                Create Teacher Account

            </button>

        </form>

        <div class="bottom-link">

            Already have an account?

            <a href="login.php">

                Login

            </a>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/auth.js"></script>
</body>

</html>