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