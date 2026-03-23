</main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <!-- Footer Top -->
            <div class="footer-top">
                <!-- About Section -->
                <div class="footer-about">
                    <h3>SkillUp Now</h3>
                    <p>An exclusive digital skill learning platform for college students. Connect with verified peers, exchange knowledge, and grow together in a supportive learning community.</p>
                    
                    <!-- Social Links -->
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-links">
                    <h4>Platform</h4>
                    <ul>
                        <li><a href="<?= $BASE_URL ?>/pages/how-it-works.php">How it Works</a></li>
                        <li><a href="<?= $BASE_URL ?>/pages/browse.php">Browse Skills</a></li>
                        <li><a href="<?= $BASE_URL ?>/pages/become-tutor.php">Become a Tutor</a></li>
                        <li><a href="<?= $BASE_URL ?>/pages/pricing.php">Pricing</a></li>
                    </ul>
                </div>
                
                <!-- Company Links -->
                <div class="footer-links">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="<?= $BASE_URL ?>/pages/about.php">About Us</a></li>
                        <li><a href="<?= $BASE_URL ?>/pages/team.php">Our Team</a></li>
                        <li><a href="<?= $BASE_URL ?>/pages/careers.php">Careers</a></li>
                        <li><a href="<?= $BASE_URL ?>/pages/blog.php">Blog</a></li>
                    </ul>
                </div>
                
                <!-- Support Links -->
                <div class="footer-links">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="<?= $BASE_URL ?>/pages/contact.php">Contact Us</a></li>
                        <li><a href="<?= $BASE_URL ?>/pages/faq.php">FAQ</a></li>
                        <li><a href="<?= $BASE_URL ?>/pages/privacy.php">Privacy Policy</a></li>
                        <li><a href="<?= $BASE_URL ?>/pages/terms.php">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> SkillUp Now. Developed by students of Gayatri Vidya Parishad College of Engineering (Autonomous). All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
    // SIMPLE TOGGLE SCRIPT - GUARANTEED TO WORK
    (function() {
        console.log('Toggle script starting...');
        
        // Wait for DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initToggle);
        } else {
            initToggle();
        }
        
        function initToggle() {
            const roleOptions = document.querySelectorAll('.role-option');
            const roleSlider = document.querySelector('.role-toggle-slider');
            
            console.log('Found options:', roleOptions.length);
            console.log('Found slider:', !!roleSlider);
            
            if (roleOptions.length === 0 || !roleSlider) {
                console.error('Toggle elements not found!');
                return;
            }
            
            // Click handler
            roleOptions.forEach(function(option) {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const role = this.getAttribute('data-role');
                    console.log('Switching to:', role);
                    
                    // Update active state
                    roleOptions.forEach(function(opt) {
                        opt.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Move slider
                    if (role === 'tutors') {
                        roleSlider.style.transform = 'translateX(100%)';
                    } else {
                        roleSlider.style.transform = 'translateX(0)';
                    }
                    
                    // Toggle content
                    const studentContent = document.querySelectorAll('.student-content');
                    const tutorContent = document.querySelectorAll('.tutor-content');
                    
                    if (role === 'tutors') {
                        studentContent.forEach(function(el) { el.style.display = 'none'; });
                        tutorContent.forEach(function(el) { el.style.display = 'block'; });
                    } else {
                        studentContent.forEach(function(el) { el.style.display = 'block'; });
                        tutorContent.forEach(function(el) { el.style.display = 'none'; });
                    }
                    
                    // Save preference
                    try {
                        localStorage.setItem('selectedRole', role);
                    } catch(e) {
                        console.warn('Could not save to localStorage:', e);
                    }
                });
            });
            
            // Load saved preference
            let savedRole = 'students';
            try {
                savedRole = localStorage.getItem('selectedRole') || 'students';
            } catch(e) {
                console.warn('Could not read from localStorage:', e);
            }
            
            console.log('Loading saved role:', savedRole);
            
            // Find and click the saved option
            const savedOption = document.querySelector('.role-option[data-role="' + savedRole + '"]');
            if (savedOption) {
                // Trigger the click programmatically
                setTimeout(function() {
                    savedOption.click();
                }, 100);
            }
            
            console.log('Toggle initialized successfully!');
        }
        
        // Mobile menu
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                
                const spans = menuToggle.querySelectorAll('span');
                if (navMenu.classList.contains('active')) {
                    spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                    spans[1].style.opacity = '0';
                    spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
                } else {
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                }
            });
        }
        
        // Header scroll
        const header = document.querySelector('.header');
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }
    })();
    </script>
</body>
</html>