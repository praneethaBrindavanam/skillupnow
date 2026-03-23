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
    
    <!-- JavaScript - INLINE VERSION -->
    <script>
    // SkillUp Now - Inline Script
    document.addEventListener('DOMContentLoaded', function() {
        
        // Role Toggle Functionality
        const roleOptions = document.querySelectorAll('.role-option');
        const roleSlider = document.querySelector('.role-toggle-slider');
        
        if (roleOptions.length > 0 && roleSlider) {
            roleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove active class from all options
                    roleOptions.forEach(opt => opt.classList.remove('active'));
                    
                    // Add active class to clicked option
                    this.classList.add('active');
                    
                    // Get selected role
                    const selectedRole = this.getAttribute('data-role');
                    
                    // Move slider based on role
                    if (selectedRole === 'tutors') {
                        roleSlider.style.transform = 'translateX(100%)';
                    } else {
                        roleSlider.style.transform = 'translateX(0)';
                    }
                    
                    // Store selected role in localStorage
                    localStorage.setItem('selectedRole', selectedRole);
                    
                    // Show/hide content
                    const studentContent = document.querySelectorAll('.student-content');
                    const tutorContent = document.querySelectorAll('.tutor-content');
                    
                    if (selectedRole === 'tutors') {
                        studentContent.forEach(el => el.style.display = 'none');
                        tutorContent.forEach(el => el.style.display = 'block');
                    } else {
                        studentContent.forEach(el => el.style.display = 'block');
                        tutorContent.forEach(el => el.style.display = 'none');
                    }
                });
            });
            
            // Load saved role preference
            const savedRole = localStorage.getItem('selectedRole') || 'students';
            const savedOption = document.querySelector('[data-role="' + savedRole + '"]');
            if (savedOption) {
                roleOptions.forEach(opt => opt.classList.remove('active'));
                savedOption.classList.add('active');
                
                // Set initial slider position
                if (savedRole === 'tutors') {
                    roleSlider.style.transform = 'translateX(100%)';
                } else {
                    roleSlider.style.transform = 'translateX(0)';
                }
                
                // Set initial content visibility
                const studentContent = document.querySelectorAll('.student-content');
                const tutorContent = document.querySelectorAll('.tutor-content');
                
                if (savedRole === 'tutors') {
                    studentContent.forEach(el => el.style.display = 'none');
                    tutorContent.forEach(el => el.style.display = 'block');
                } else {
                    studentContent.forEach(el => el.style.display = 'block');
                    tutorContent.forEach(el => el.style.display = 'none');
                }
            }
        }
        
        // Mobile Menu Toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (menuToggle) {
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
        
        // Header scroll effect
        const header = document.querySelector('.header');
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        
        // Active navigation link
        const currentPage = window.location.pathname.split('/').pop() || 'index.php';
        const navLinks = document.querySelectorAll('.nav-menu a');
        
        navLinks.forEach(link => {
            const linkPage = link.getAttribute('href');
            if (linkPage === currentPage) {
                link.classList.add('active');
            }
        });
        
        console.log('SkillUp Now - Platform Initialized ✓');
    });
    </script>
</body>
</html>