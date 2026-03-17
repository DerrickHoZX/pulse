document.addEventListener("DOMContentLoaded", function () {
    registerEventListeners();
    activateMenu();
    initScrollEffects();
    initCategoryPills();
});

/* ---- Nav scroll class ---- */
function registerEventListeners() {
    window.addEventListener("scroll", function () {
        const nav = document.getElementById("pulseNav");
        if (nav) {
            nav.classList.toggle("scrolled", window.scrollY > 60);
        }
    });
}

/* ---- Active nav link ---- */
function activateMenu() {
    const navLinks = document.querySelectorAll("nav a.nav-link");
    navLinks.forEach(link => {
        if (link.href === location.href) {
            navLinks.forEach(l => l.classList.remove("active"));
            link.classList.add("active");
        }
    });
}

/* ---- Fade-up on scroll (IntersectionObserver) ---- */
function initScrollEffects() {
    const targets = document.querySelectorAll(".fade-up");
    if (!targets.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
            }
        });
    }, { threshold: 0.12 });

    targets.forEach(el => observer.observe(el));
}

/* ---- Category pill toggle ---- */
function initCategoryPills() {
    const pills = document.querySelectorAll(".cat-pill");
    pills.forEach(pill => {
        pill.addEventListener("click", function () {
            pills.forEach(p => p.classList.remove("active"));
            this.classList.add("active");
        });
    });
}

/* ---- Sync Bootstrap carousel dots to .hero-dot active state ---- */
document.addEventListener("DOMContentLoaded", function () {
    const carousel = document.getElementById("heroCarousel");
    if (!carousel) return;

    carousel.addEventListener("slide.bs.carousel", function (e) {
        const dots = document.querySelectorAll(".hero-dot");
        dots.forEach((d, i) => d.classList.toggle("active", i === e.to));
    });
});
