<?php
$pageTitle = "Contact Us";
include '../includes/header.php';
?>

<!-- Contact Hero -->
<section class="hero" style="padding: 5rem 2rem 3rem 2rem;">
    <div class="container text-center">
        <div class="hero-badge">
            <span class="badge-icon">💬</span>
            Get in Touch
        </div>
        
        <h1 class="hero-title" style="font-size: 3rem;">
            We'd Love to <span class="highlight">Hear From You</span>
        </h1>
        
        <p class="hero-description">
            Have questions, feedback, or need support? Our team is here to help. 
            Reach out to us and we'll get back to you as soon as possible.
        </p>
    </div>
</section>

<!-- Contact Form Section -->
<section class="section" style="background: white;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; max-width: 1200px; margin: 0 auto;">
            <!-- Contact Form -->
            <div>
                <h2 style="margin-bottom: 1.5rem;">Send us a Message</h2>
                
                <form action="process-contact.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Name -->
                    <div>
                        <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">Full Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; transition: all var(--transition-base);"
                            placeholder="John Doe"
                        >
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">College Email *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; transition: all var(--transition-base);"
                            placeholder="you@college.edu"
                        >
                    </div>
                    
                    <!-- Subject -->
                    <div>
                        <label for="subject" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">Subject *</label>
                        <select 
                            id="subject" 
                            name="subject" 
                            required
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; background: white; transition: all var(--transition-base);"
                        >
                            <option value="">Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="support">Technical Support</option>
                            <option value="feedback">Feedback</option>
                            <option value="partnership">Partnership</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <!-- Message -->
                    <div>
                        <label for="message" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--gray-700);">Message *</label>
                        <textarea 
                            id="message" 
                            name="message" 
                            rows="6" 
                            required
                            style="width: 100%; padding: 0.875rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-md); font-size: 1rem; resize: vertical; transition: all var(--transition-base);"
                            placeholder="Tell us how we can help you..."
                        ></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" style="width: fit-content;">
                        Send Message
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h2 style="margin-bottom: 1.5rem;">Contact Information</h2>
                
                <!-- Info Cards -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Email -->
                    <div style="padding: 1.5rem; background: linear-gradient(135deg, #F7FAFC, #E6FFFA); border-radius: var(--radius-lg);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h4 style="margin-bottom: 0.25rem;">Email Us</h4>
                                <a href="mailto:support@skillupnow.com" style="color: var(--primary-teal); font-weight: 500;">support@skillupnow.com</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Location -->
                    <div style="padding: 1.5rem; background: linear-gradient(135deg, #FFF5F7, #FED7E2); border-radius: var(--radius-lg);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h4 style="margin-bottom: 0.25rem;">Visit Us</h4>
                                <p style="margin: 0; color: var(--gray-600);">GVP College of Engineering<br>Visakhapatnam, India</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Phone -->
                    <div style="padding: 1.5rem; background: linear-gradient(135deg, #FFFAF0, #FEEBC8); border-radius: var(--radius-lg);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--accent-orange), #DD6B20); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem;">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h4 style="margin-bottom: 0.25rem;">Call Us</h4>
                                <a href="tel:+919876543210" style="color: var(--accent-orange); font-weight: 500;">+91 98765 43210</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div style="margin-top: 2rem;">
                    <h3 style="margin-bottom: 1rem;">Follow Us</h3>
                    <div style="display: flex; gap: 1rem;">
                        <a href="#" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; transition: all var(--transition-base);">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; transition: all var(--transition-base);">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; transition: all var(--transition-base);">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; transition: all var(--transition-base);">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section">
    <div class="container">
        <h2 class="text-center mb-4">Frequently Asked Questions</h2>
        <p class="text-center mb-5" style="max-width: 700px; margin: 0 auto 3rem auto; color: var(--gray-600);">
            Find quick answers to common questions about SkillUp Now.
        </p>
        
        <div style="max-width: 800px; margin: 0 auto;">
            <!-- FAQ Item 1 -->
            <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 1rem; box-shadow: var(--shadow-sm);">
                <h4 style="margin-bottom: 0.5rem;">How do I register on SkillUp Now?</h4>
                <p style="margin: 0; color: var(--gray-600);">You can register using your college-issued email ID. Simply click on "Get Started" and follow the registration process.</p>
            </div>
            
            <!-- FAQ Item 2 -->
            <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 1rem; box-shadow: var(--shadow-sm);">
                <h4 style="margin-bottom: 0.5rem;">Is SkillUp Now free to use?</h4>
                <p style="margin: 0; color: var(--gray-600);">Yes! The basic platform features are completely free for all verified college students.</p>
            </div>
            
            <!-- FAQ Item 3 -->
            <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); margin-bottom: 1rem; box-shadow: var(--shadow-sm);">
                <h4 style="margin-bottom: 0.5rem;">How do I become a tutor?</h4>
                <p style="margin: 0; color: var(--gray-600);">After registering as a learner, you can apply to become a tutor by completing your profile and submitting your expertise areas for verification.</p>
            </div>
        </div>
    </div>
</section>

<style>
input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: var(--primary-cyan);
    box-shadow: 0 0 0 3px rgba(79, 209, 197, 0.1);
}

@media (max-width: 768px) {
    section > div > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php include '../includes/footer.php'; ?>