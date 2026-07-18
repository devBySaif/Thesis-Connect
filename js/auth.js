document.addEventListener("DOMContentLoaded", () => {

    // =====================
    // Forgot Password Modal
    // =====================
    
    const modal = document.getElementById("forgotPasswordModal");
    const forgotPasswordLink = document.getElementById("forgotPasswordLink");
    const modalCloseBtn = document.getElementById("modalCloseBtn");
    const forgotPasswordForm = document.getElementById("forgotPasswordForm");

    if (forgotPasswordLink && modal) {
        forgotPasswordLink.addEventListener("click", (e) => {
            e.preventDefault();
            modal.classList.add("show");
        });
    }

    if (modalCloseBtn && modal) {
        modalCloseBtn.addEventListener("click", () => {
            modal.classList.remove("show");
            resetForgotPasswordForm();
        });
    }

    if (modal) {
        window.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.classList.remove("show");
                resetForgotPasswordForm();
            }
        });
    }

    const resetForgotPasswordForm = () => {
        if (forgotPasswordForm) {
            forgotPasswordForm.reset();
            document.getElementById("forgotErrorMessage").style.display = "none";
            document.getElementById("forgotSuccessMessage").style.display = "none";
        }
    };

    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener("submit", (e) => {
            e.preventDefault();

            const email = document.getElementById("forgotEmail").value.trim();
            const errorDiv = document.getElementById("forgotErrorMessage");
            const successDiv = document.getElementById("forgotSuccessMessage");

            if (!email) {
                errorDiv.textContent = "Please enter your email address.";
                errorDiv.style.display = "block";
                successDiv.style.display = "none";
                return;
            }

            errorDiv.style.display = "none";
            successDiv.style.display = "none";

            const formData = new FormData(forgotPasswordForm);

            fetch("../control/AuthController.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        successDiv.textContent = data.message;
                        successDiv.style.display = "block";
                        forgotPasswordForm.reset();
                        
                        setTimeout(() => {
                            modal.classList.remove("show");
                            resetForgotPasswordForm();
                        }, 3000);
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
    }

    // =====================
    // Registration Forms
    // =====================

    const forms = [
        {
            id: "studentRegisterForm",
            emailPattern: /^[0-9]{2}-[0-9]{5}-[0-9]@student\.aiub\.edu$/i,
            emailMessage: "Enter a valid AIUB student email, e.g. 23-50952-1@student.aiub.edu.",
            requiredIds: [
                "full_name",
                "student_id",
                "department",
                "semester",
                "phone",
                "email",
                "password",
                "confirm_password",
                "terms"
            ],
            optionalNumberId: "cgpa"
        },
        {
            id: "teacherRegisterForm",
            emailPattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            emailMessage: "Enter a valid university email address.",
            requiredIds: [
                "full_name",
                "teacher_id",
                "designation",
                "department",
                "office",
                "phone",
                "email",
                "password",
                "confirm_password",
                "terms"
            ]
        }
    ];

    const showError = (field, message) => {
        let error = field.parentElement.querySelector(".error-message");
        if (!error) {
            error = document.createElement("span");
            error.className = "error-message";
            field.parentElement.appendChild(error);
        }
        field.classList.add("input-error");
        error.textContent = message;
    };

    const clearAllErrors = (form) => {
        form.querySelectorAll(".error-message").forEach(el => el.remove());
        form.querySelectorAll(".input-error").forEach(el => el.classList.remove("input-error"));
    };

    const validateForm = (form, config) => {
        clearAllErrors(form);

        let isValid = true;
        let firstInvalidField = null;

        config.requiredIds.forEach(id => {
            const field = form.querySelector(`#${id}`);
            if (!field) return;

            if (field.type === "checkbox") {
                if (!field.checked) {
                    showError(field, "This field is required.");
                    isValid = false;
                    firstInvalidField = firstInvalidField || field;
                }
                return;
            }

            if (!field.value.trim()) {
                showError(field, "This field is required.");
                isValid = false;
                firstInvalidField = firstInvalidField || field;
            }
        });

        const emailField = form.querySelector("#email");
        if (emailField && emailField.value.trim()) {
            if (!config.emailPattern.test(emailField.value.trim())) {
                showError(emailField, config.emailMessage);
                isValid = false;
                firstInvalidField = firstInvalidField || emailField;
            }
        }

        const passwordField = form.querySelector("#password");
        const confirmPasswordField = form.querySelector("#confirm_password");
        if (passwordField && passwordField.value && passwordField.value.length < 8) {
            showError(passwordField, "Password must be at least 8 characters.");
            isValid = false;
            firstInvalidField = firstInvalidField || passwordField;
        }

        if (passwordField && confirmPasswordField && passwordField.value && confirmPasswordField.value && passwordField.value !== confirmPasswordField.value) {
            showError(confirmPasswordField, "Passwords do not match.");
            isValid = false;
            firstInvalidField = firstInvalidField || confirmPasswordField;
        }

        const phoneField = form.querySelector("#phone");
        const phonePattern = /^01[3-9]\d{8}$/;
        if (phoneField && phoneField.value.trim() && !phonePattern.test(phoneField.value.trim())) {
            showError(phoneField, "Enter a valid Bangladeshi phone number.");
            isValid = false;
            firstInvalidField = firstInvalidField || phoneField;
        }

        if (config.optionalNumberId) {
            const cgpaField = form.querySelector(`#${config.optionalNumberId}`);
            if (cgpaField && cgpaField.value.trim()) {
                const cgpaValue = parseFloat(cgpaField.value);
                if (isNaN(cgpaValue) || cgpaValue < 0 || cgpaValue > 4) {
                    showError(cgpaField, "CGPA must be between 0 and 4.");
                    isValid = false;
                    firstInvalidField = firstInvalidField || cgpaField;
                }
            }
        }

        if (firstInvalidField) {
            firstInvalidField.focus();
        }

        return isValid;
    };

    const submitForm = (form) => {
        const formData = new FormData(form);

        fetch("../control/AuthController.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Registration Successful",
                        text: data.message
                    }).then(() => {
                        window.location.href = "login.php";
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Registration Failed",
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "Server Error",
                    text: "Something went wrong."
                });
                console.error(error);
            });
    };

    forms.forEach(config => {
        const form = document.getElementById(config.id);
        if (!form) return;

        form.addEventListener("submit", function (e) {
            e.preventDefault();

            if (validateForm(form, config)) {
                submitForm(form);
            }
        });
    });
});