// SkillUp Now - Main JavaScript File

document.addEventListener('DOMContentLoaded', () => {

    /* =========================
       ROLE TOGGLE (STUDENT / TUTOR)
    ========================== */

    const roleOptions = document.querySelectorAll('.role-option');
    const roleSlider = document.querySelector('.role-toggle-slider');

    const studentContent = document.querySelectorAll('.student-content');
    const tutorContent = document.querySelectorAll('.tutor-content');

    function applyRole(role) {
        console.log(`🔄 Applying role: ${role}`);
        
        // Slider position
        if (roleSlider) {
            roleSlider.style.transform = role === 'tutors' ? 'translateX(100%)' : 'translateX(0)';
            console.log(`📍 Slider moved to: ${role === 'tutors' ? '100%' : '0%'}`);
        } else {
            console.warn('⚠️ Slider element not found!');
        }

        // Active button
        roleOptions.forEach(opt => {
            opt.classList.toggle('active', opt.dataset.role === role);
        });

        // Content toggle
        studentContent.forEach(el => {
            el.style.display = role === 'tutors' ? 'none' : 'block';
        });

        tutorContent.forEach(el => {
            el.style.display = role === 'tutors' ? 'block' : 'none';
        });

        console.log(`📄 Content visibility: ${role === 'tutors' ? 'Tutor content shown' : 'Student content shown'}`);

        // Persist role
        try {
            localStorage.setItem('selectedRole', role);
            console.log(`💾 Role saved: ${role}`);
        } catch (e) {
            console.warn('⚠️ Could not save to localStorage:', e);
        }
    }

    // Click handlers
    if (roleOptions.length > 0) {
        roleOptions.forEach(option => {
            option.addEventListener('click', () => {
                const role = option.dataset.role;
                console.log(`🖱️ Button clicked: ${role}`);
                applyRole(role);
            });
        });

        // Initial load
        let savedRole = 'students';
        try {
            savedRole = localStorage.getItem('selectedRole') || 'students';
        } catch (e) {
            console.warn('⚠️ Could not read from localStorage:', e);
        }
        
        console.log(`🎬 Initial role: ${savedRole}`);
        applyRole(savedRole);
    } else {
        console.warn('⚠️ No role toggle buttons found');
    }

    /* =========================
       MOBILE MENU
    ========================== */

    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');

            const spans = menuToggle.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans.forEach(span => {
                    span.style.transform = '';
                    span.style.opacity = '';
                });
            }
        });
    }

    /* =========================
       HEADER SCROLL EFFECT
    ========================== */

    const header = document.querySelector('.header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    /* =========================
       ACTIVE NAV LINK
    ========================== */

    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-menu a').forEach(link => {
        const linkHref = link.getAttribute('href');
        if (linkHref && linkHref.includes(currentPage)) {
            link.classList.add('active');
        }
    });

    /* =========================
       SMOOTH SCROLL
    ========================== */

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', e => {
            const href = anchor.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (!target) return;

            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Close mobile menu if open
            if (navMenu) {
                navMenu.classList.remove('active');
            }
        });
    });

    /* =========================
       STATS ANIMATION
    ========================== */

    const statsSection = document.querySelector('.stats-section');

    if (statsSection) {
        const observer = new IntersectionObserver(entries => {
            if (entries[0].isIntersecting) {
                animateStats();
                observer.disconnect();
            }
        }, { threshold: 0.5 });

        observer.observe(statsSection);
    }

    function animateStats() {
        document.querySelectorAll('.stat-value').forEach(stat => {
            const raw = stat.textContent;
            const value = parseInt(raw.replace(/\D/g, ''), 10);
            if (isNaN(value)) return;
            
            const suffix = raw.replace(/[0-9]/g, '');
            let current = 0;

            const step = value / 40;
            const timer = setInterval(() => {
                current += step;
                if (current >= value) {
                    stat.textContent = value + suffix;
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current) + suffix;
                }
            }, 30);
        });
    }

    /* =========================
       PASSWORD TOGGLE
    ========================== */

    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (!input) return;
            
            const type = input.getAttribute('type');
            if (type === 'password') {
                input.setAttribute('type', 'text');
                this.textContent = '👁️';
            } else {
                input.setAttribute('type', 'password');
                this.textContent = '👁️‍🗨️';
            }
        });
    });

    /* =========================
       AUTO-HIDE ALERTS
    ========================== */

    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    console.log('✨ SkillUp Now Initialized Successfully!');
});

/* =========================
   UTILITY FUNCTIONS
========================= */

window.validateEmail = email =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

window.showNotification = function(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
};

window.formatDate = function(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('en-US', options);
};