<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Registration | ThesisConnect</title>

    <link rel="stylesheet" href="../css/student_register.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>

<body>

<div class="container">

    <div class="register-card">

        <div class="logo">

            <i class="fa-solid fa-user-graduate"></i>

        </div>

        <h2>Create Student Account</h2>

        <p class="subtitle">
            Join ThesisConnect and start your thesis journey.
        </p>

        <div id="registerMessage"></div>

        <form
            id="studentRegisterForm"
            method="POST"
            enctype="multipart/form-data">

            <input type="hidden" name="action" value="student_register">

            <div class="grid">

                <!-- Full Name -->

                <div class="input-box">

                    <label for="full_name">Full Name</label>

                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        autocomplete="name"
                        placeholder="Enter Full Name">

                </div>

                <!-- Student ID -->

                <div class="input-box">

                    <label for="student_id">Student ID</label>

                    <input
                        type="text"
                        id="student_id"
                        name="student_id"
                        placeholder="221-15-XXXX">

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

                <!-- Semester -->

                <div class="input-box">

                    <label for="semester">Semester</label>

                    <select
                        id="semester"
                        name="semester">

                        <option value="">Select Semester</option>

                        <option>1st Semester</option>
                        <option>2nd Semester</option>
                        <option>3rd Semester</option>
                        <option>4th Semester</option>
                        <option>5th Semester</option>
                        <option>6th Semester</option>
                        <option>7th Semester</option>
                        <option>8th Semester</option>

                    </select>

                </div>

                <!-- CGPA -->

                <div class="input-box">

                    <label for="cgpa">CGPA</label>

                    <input
                        type="number"
                        id="cgpa"
                        name="cgpa"
                        min="0"
                        max="4"
                        step="0.01"
                        placeholder="Current CGPA">

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

            <!-- Email -->

            <div class="input-box">

                <label for="email">University Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    autocomplete="email"
                    placeholder="example@university.edu">

            </div>

            <!-- Password -->

            <div class="grid">

                <div class="input-box">

                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8">

                </div>

                <div class="input-box">

                    <label for="confirm_password">Confirm Password</label>

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
                    placeholder="Tell us about yourself..."></textarea>

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

                Create Student Account

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