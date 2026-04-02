document.addEventListener("DOMContentLoaded", function () {
    registerEventListeners();
    activateMenu();
    initScrollEffects();
    initCategoryPills();
    initPasswordChecklist();
    initProfilePasswordChecklist();
    initPasswordMatch();
    initProfilePasswordMatch();
    initHeroCarousel();
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

/* ---- Sync Bootstrap carousel dots to .hero-dot active state ---- */
function initHeroCarousel() {
    const carousel = document.getElementById("heroCarousel");
    if (!carousel) return;

    carousel.addEventListener("slide.bs.carousel", function (e) {
        const dots = document.querySelectorAll(".hero-dot");
        dots.forEach((d, i) => d.classList.toggle("active", i === e.to));
    });
}

/* ---- Toggle Password Visibility ---- */
function togglePwd(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.querySelector('.eye-open').style.display = isHidden ? 'none' : 'block';
    btn.querySelector('.eye-off').style.display  = isHidden ? 'block' : 'none';
    btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
}

/* ---- Live Password Checklist (Register) ---- */
function initPasswordChecklist() {
    const pwdInput = document.getElementById('pwd');
    if (!pwdInput) return;

    const rules = {
        'rule-length':  (v) => v.length >= 8,
        'rule-upper':   (v) => /[A-Z]/.test(v),
        'rule-number':  (v) => /[0-9]/.test(v),
        'rule-special': (v) => /[!@#$%^&*()\-_=+\[\]{};':"\\|,.<>\/?]/.test(v),
    };

    pwdInput.addEventListener('input', function () {
        const val = this.value;
        Object.entries(rules).forEach(([id, test]) => {
            const el = document.getElementById(id);
            if (!el) return;
            const icon = el.querySelector('.pwd-rule-icon');
            if (test(val)) {
                el.classList.add('passed');
                icon.textContent = '✓';
            } else {
                el.classList.remove('passed');
                icon.textContent = '✗';
            }
        });
    });
}

/* ---- Live Password Checklist (Profile) ---- */
function initProfilePasswordChecklist() {
    const pwdInput = document.getElementById('new_pwd');
    if (!pwdInput) return;

    const rules = {
        'profile-rule-length':  (v) => v.length >= 8,
        'profile-rule-upper':   (v) => /[A-Z]/.test(v),
        'profile-rule-number':  (v) => /[0-9]/.test(v),
        'profile-rule-special': (v) => /[!@#$%^&*()\-_=+\[\]{};':"\\|,.<>\/?]/.test(v),
    };

    pwdInput.addEventListener('input', function () {
        const val = this.value;
        Object.entries(rules).forEach(([id, test]) => {
            const el = document.getElementById(id);
            if (!el) return;
            const icon = el.querySelector('.pwd-rule-icon');
            if (test(val)) {
                el.classList.add('passed');
                icon.textContent = '✓';
            } else {
                el.classList.remove('passed');
                icon.textContent = '✗';
            }
        });
    });
}

/* ---- Password Match Indicator ---- */
function initPasswordMatch() {
    const pwd        = document.getElementById('pwd');
    const pwdConfirm = document.getElementById('pwd_confirm');
    const msg        = document.getElementById('pwd-match-msg');
    if (!pwd || !pwdConfirm || !msg) return;

    function checkMatch() {
        if (!pwdConfirm.value) {
            msg.textContent = '';
            msg.className = 'pwd-match-msg mt-1';
            return;
        }
        if (pwd.value === pwdConfirm.value) {
            msg.textContent = '✓ Passwords match';
            msg.className = 'pwd-match-msg mt-1 match';
        } else {
            msg.textContent = '✗ Passwords do not match';
            msg.className = 'pwd-match-msg mt-1 nomatch';
        }
    }
    pwd.addEventListener('input', checkMatch);
    pwdConfirm.addEventListener('input', checkMatch);
}

/* ---- Password Match Indicator (Profile) ---- */
function initProfilePasswordMatch() {
    const pwd        = document.getElementById('new_pwd');
    const pwdConfirm = document.getElementById('new_pwd_confirm');
    const msg        = document.getElementById('profile-pwd-match-msg');
    if (!pwd || !pwdConfirm || !msg) return;

    function checkMatch() {
        if (!pwdConfirm.value) {
            msg.textContent = '';
            msg.className = 'pwd-match-msg mt-1';
            return;
        }
        if (pwd.value === pwdConfirm.value) {
            msg.textContent = '✓ Passwords match';
            msg.className = 'pwd-match-msg mt-1 match';
        } else {
            msg.textContent = '✗ Passwords do not match';
            msg.className = 'pwd-match-msg mt-1 nomatch';
        }
    }
    pwd.addEventListener('input', checkMatch);
    pwdConfirm.addEventListener('input', checkMatch);
}