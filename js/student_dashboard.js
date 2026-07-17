/*=========================================================
                DASHBOARD.JS
=========================================================*/

document.addEventListener("DOMContentLoaded", () => {

    /*=====================================================
                    ELEMENTS
    =====================================================*/

    const notificationBtn = document.getElementById("notificationBtn");

    const notificationDropdown = document.getElementById("notificationDropdown");

    const profileBtn = document.getElementById("profileBtn");

    const profileDropdown = document.getElementById("profileDropdown");

    const notificationCount = document.getElementById("notificationCount");

    const applyButtons = document.querySelectorAll(".apply-btn");

    const navLinks = document.querySelectorAll(".nav-links a");

    const showFormMessage = (form, message) => {
        let messageBox = form.querySelector(".client-error");

        if (!messageBox) {
            messageBox = document.createElement("div");
            messageBox.className = "client-error";
            form.prepend(messageBox);
        }

        messageBox.textContent = message;
    };

    const getFieldLabel = (field) => {
        return field.dataset.label || field.name || "This field";
    };

    const validatePostForm = (form) => {
        const requiredNames = ["title", "teacher_user_id", "department", "members_needed", "deadline", "description"];

        for (const name of requiredNames) {
            const field = form.querySelector(`[name="${name}"]`);

            if (!field || !field.value.trim()) {
                showFormMessage(form, `${getFieldLabel(field)} is required.`);
                field && field.focus();
                return false;
            }
        }

        const members = form.querySelector('[name="members_needed"]');
        if (members && Number(members.value) < 1) {
            showFormMessage(form, "Members needed must be at least 1.");
            members.focus();
            return false;
        }

        const deadline = form.querySelector('[name="deadline"]');
        if (deadline && deadline.value) {
            const selectedDate = new Date(`${deadline.value}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (!form.querySelector('[name="post_id"]') && selectedDate < today) {
                showFormMessage(form, "Deadline cannot be in the past.");
                deadline.focus();
                return false;
            }
        }

        return true;
    };

    document.querySelectorAll(".js-post-form").forEach(form => {
        form.addEventListener("submit", (event) => {
            if (!validatePostForm(form)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll(".js-apply-form").forEach(form => {
        form.addEventListener("submit", (event) => {
            const message = form.querySelector('[name="message"]');

            if (message && !message.value.trim()) {
                event.preventDefault();
                showFormMessage(form, "Application message is required.");
                message.focus();
            }
        });
    });

    document.querySelectorAll(".js-admin-announcement-form").forEach(form => {
        form.addEventListener("submit", (event) => {
            const title = form.querySelector('[name="title"]');
            const body = form.querySelector('[name="body"]');

            if (!title.value.trim()) {
                event.preventDefault();
                showFormMessage(form, "Title is required.");
                title.focus();
                return;
            }

            if (!body.value.trim()) {
                event.preventDefault();
                showFormMessage(form, "Details are required.");
                body.focus();
            }
        });
    });



    /*=====================================================
                NOTIFICATION DROPDOWN
    =====================================================*/

    if (notificationBtn && notificationDropdown) {

        notificationBtn.addEventListener("click", function (e) {

            e.stopPropagation();

            notificationDropdown.classList.toggle("active");

            if (profileDropdown) {

                profileDropdown.classList.remove("active");

            }

        });

    }



    /*=====================================================
                    PROFILE DROPDOWN
    =====================================================*/

    if (profileBtn && profileDropdown) {

        profileBtn.addEventListener("click", function (e) {

            e.stopPropagation();

            profileDropdown.classList.toggle("active");

            if (notificationDropdown) {

                notificationDropdown.classList.remove("active");

            }

        });

    }



    /*=====================================================
            PREVENT CLOSE WHEN CLICK INSIDE
    =====================================================*/

    if (notificationDropdown) {

        notificationDropdown.addEventListener("click", function (e) {

            e.stopPropagation();

        });

    }


    if (profileDropdown) {

        profileDropdown.addEventListener("click", function (e) {

            e.stopPropagation();

        });

    }



    /*=====================================================
                CLOSE WHEN CLICK OUTSIDE
    =====================================================*/

    document.addEventListener("click", function () {

        if (notificationDropdown) {

            notificationDropdown.classList.remove("active");

        }

        if (profileDropdown) {

            profileDropdown.classList.remove("active");

        }

    });



    /*=====================================================
                CLOSE USING ESC KEY
    =====================================================*/

    document.addEventListener("keydown", function (e) {

        if (e.key === "Escape") {

            if (notificationDropdown) {

                notificationDropdown.classList.remove("active");

            }

            if (profileDropdown) {

                profileDropdown.classList.remove("active");

            }

        }

    });



    /*=====================================================
                APPLY BUTTON EFFECT
    =====================================================*/

    applyButtons.forEach(button => {

        button.addEventListener("click", function () {

            if (this.closest("form") || this.tagName.toLowerCase() === "a") {

                return;

            }

            if (this.classList.contains("applied")) {

                return;

            }

            this.classList.add("applied");

            this.innerHTML = '<i class="fa-solid fa-check"></i> Applied';

            this.style.background = "#22C55E";

            this.disabled = true;

        });

    });



    /*=====================================================
                ACTIVE NAVIGATION
    =====================================================*/

    const currentPage = window.location.pathname.split("/").pop();

    navLinks.forEach(link => {

        const href = link.getAttribute("href");

        if (href === currentPage) {

            link.classList.add("active");

        }

    });



    /*=====================================================
            NOTIFICATION BADGE ANIMATION
    =====================================================*/

    if (notificationCount) {

        setInterval(() => {

            notificationCount.style.transform = "scale(1.25)";

            notificationCount.style.transition = ".25s";

            setTimeout(() => {

                notificationCount.style.transform = "scale(1)";

            }, 250);

        }, 2500);

    }



    /*=====================================================
            BUTTON RIPPLE EFFECT
    =====================================================*/

    document.querySelectorAll("button").forEach(button => {

        button.addEventListener("mousedown", function () {

            this.style.transform = "scale(.97)";

        });

        button.addEventListener("mouseup", function () {

            this.style.transform = "";

        });

        button.addEventListener("mouseleave", function () {

            this.style.transform = "";

        });

    });



    /*=====================================================
                SMOOTH PAGE LOAD
    =====================================================*/

    document.body.style.opacity = "0";

    setTimeout(() => {

        document.body.style.transition = ".35s";

        document.body.style.opacity = "1";

    }, 50);

});
