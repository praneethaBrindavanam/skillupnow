<?php
session_start();
require_once '../includes/config.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$pageTitle = "Help & Support";
include 'dashboard-header.php';
?>

<style>
.help-container {
    max-width: 1000px;
    margin: 0 auto;
}

.contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.contact-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px solid #F3F4F6;
    text-align: center;
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
}

.contact-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(79, 209, 197, 0.2);
    border-color: #4FD1C5;
}

.contact-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.contact-title {
    margin: 0 0 8px 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

.contact-info {
    margin: 0 0 16px 0;
    font-size: 0.938rem;
    color: #4B5563;
    word-break: break-all;
}

.contact-link {
    color: #4FD1C5;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.875rem;
}

.contact-link:hover {
    text-decoration: underline;
}

.faq-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 2px solid #F3F4F6;
    cursor: pointer;
    transition: all 0.3s ease;
}

.faq-card:hover {
    border-color: #4FD1C5;
}

.faq-question {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 1.063rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.faq-answer {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    color: #4B5563;
    font-size: 0.938rem;
    line-height: 1.6;
}

.faq-card.active .faq-answer {
    max-height: 500px;
    margin-top: 16px;
}

.faq-card.active .faq-icon {
    transform: rotate(180deg);
}

.faq-icon {
    transition: transform 0.3s ease;
    color: #4FD1C5;
}

.section-header {
    margin-bottom: 32px;
    text-align: center;
}

.section-title {
    margin: 0 0 8px 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
}

.section-subtitle {
    margin: 0;
    font-size: 1rem;
    color: #6B7280;
}
</style>

<div class="help-container">
    
    <!-- Header -->
    <div style="margin-bottom: 48px; text-align: center;">
        <h1 style="margin-bottom: 8px; font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, #4FD1C5, #38B2AC); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="fas fa-life-ring"></i> Help & Support
        </h1>
        <p style="color: #6B7280; font-size: 1.125rem; margin: 0;">
            We're here to help! Reach out anytime.
        </p>
    </div>

    <!-- Contact Methods -->
    <div class="section-header">
        <h2 class="section-title">Get in Touch</h2>
        <p class="section-subtitle">Choose your preferred way to contact us</p>
    </div>

    <div class="contact-grid">
        <!-- Email -->
        <a href="mailto:support@skillupnow.com" class="contact-card">
            <div class="contact-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h3 class="contact-title">Email Us</h3>
            <p class="contact-info">support@skillupnow.com</p>
            <span class="contact-link">Send Email →</span>
        </a>

        <!-- Phone -->
        <a href="tel:+919876543210" class="contact-card">
            <div class="contact-icon">
                <i class="fas fa-phone"></i>
            </div>
            <h3 class="contact-title">Call Us</h3>
            <p class="contact-info">+91 98765 43210</p>
            <span class="contact-link">Make Call →</span>
        </a>

        <!-- WhatsApp -->
        <a href="https://wa.me/919876543210" target="_blank" class="contact-card">
            <div class="contact-icon">
                <i class="fab fa-whatsapp"></i>
            </div>
            <h3 class="contact-title">WhatsApp</h3>
            <p class="contact-info">+91 98765 43210</p>
            <span class="contact-link">Chat Now →</span>
        </a>

        <!-- Office -->
        <a href="https://goo.gl/maps/example" target="_blank" class="contact-card">
            <div class="contact-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <h3 class="contact-title">Visit Office</h3>
            <p class="contact-info">GVP College of Engineering, Visakhapatnam</p>
            <span class="contact-link">Get Directions →</span>
        </a>
    </div>

    <!-- Social Media -->
    <div style="text-align: center; margin: 48px 0;">
        <h3 style="margin: 0 0 24px 0; font-size: 1.25rem; font-weight: 700; color: #111827;">
            Follow Us on Social Media
        </h3>
        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
            <a href="https://facebook.com/skillupnow" target="_blank" style="width: 50px; height: 50px; border-radius: 50%; background: #1877F2; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; text-decoration: none; transition: transform 0.2s;">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="https://twitter.com/skillupnow" target="_blank" style="width: 50px; height: 50px; border-radius: 50%; background: #1DA1F2; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; text-decoration: none; transition: transform 0.2s;">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="https://instagram.com/skillupnow" target="_blank" style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(45deg, #F58529, #DD2A7B, #8134AF); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; text-decoration: none; transition: transform 0.2s;">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="https://linkedin.com/company/skillupnow" target="_blank" style="width: 50px; height: 50px; border-radius: 50%; background: #0A66C2; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; text-decoration: none; transition: transform 0.2s;">
                <i class="fab fa-linkedin-in"></i>
            </a>
            <a href="https://youtube.com/@skillupnow" target="_blank" style="width: 50px; height: 50px; border-radius: 50%; background: #FF0000; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; text-decoration: none; transition: transform 0.2s;">
                <i class="fab fa-youtube"></i>
            </a>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="section-header" style="margin-top: 64px;">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <p class="section-subtitle">Quick answers to common questions</p>
    </div>

    <div class="faq-card" onclick="this.classList.toggle('active')">
        <p class="faq-question">
            How do I enroll in a class?
            <i class="fas fa-chevron-down faq-icon"></i>
        </p>
        <div class="faq-answer">
            <p>To enroll in a class: 1) Go to "Browse Tutors" and find tutors you like. 2) Click "Follow Tutor" to see their classes. 3) Visit "My Tutors" to see available classes from tutors you follow. 4) Click "Enroll" on any class you want to join. For paid classes, you'll be taken to the payment page.</p>
        </div>
    </div>

    <div class="faq-card" onclick="this.classList.toggle('active')">
        <p class="faq-question">
            How do I become a tutor?
            <i class="fas fa-chevron-down faq-icon"></i>
        </p>
        <div class="faq-answer">
            <p>If you signed up as a tutor: 1) Complete your profile with at least 3 skills. 2) Go to "Tutor Verification" from your dashboard. 3) Get verified in your skills by passing the Moodle exams (75%+ score required). 4) Once verified, you can schedule classes from your dashboard!</p>
        </div>
    </div>

    <div class="faq-card" onclick="this.classList.toggle('active')">
        <p class="faq-question">
            What payment methods do you accept?
            <i class="fas fa-chevron-down faq-icon"></i>
        </p>
        <div class="faq-answer">
            <p>We accept UPI (Google Pay, PhonePe, Paytm), Credit/Debit Cards (Visa, Mastercard, RuPay), and Net Banking from all major banks. All payments are secure and encrypted.</p>
        </div>
    </div>

    <div class="faq-card" onclick="this.classList.toggle('active')">
        <p class="faq-question">
            Can I get a refund if I can't attend a class?
            <i class="fas fa-chevron-down faq-icon"></i>
        </p>
        <div class="faq-answer">
            <p>Yes! If you cancel at least 24 hours before the class starts, you'll receive a full refund. Cancellations made less than 24 hours before the class are not eligible for refunds. Contact support@skillupnow.com for refund requests.</p>
        </div>
    </div>

    <div class="faq-card" onclick="this.classList.toggle('active')">
        <p class="faq-question">
            How do I join my enrolled class?
            <i class="fas fa-chevron-down faq-icon"></i>
        </p>
        <div class="faq-answer">
            <p>Go to "My Classes" from your dashboard. Find your upcoming class and click the "Join Class" button. This will open the meeting link (Google Meet/Zoom) provided by your tutor. Make sure to join a few minutes before the scheduled time!</p>
        </div>
    </div>

    <div class="faq-card" onclick="this.classList.toggle('active')">
        <p class="faq-question">
            What if my tutor cancels a class?
            <i class="fas fa-chevron-down faq-icon"></i>
        </p>
        <div class="faq-answer">
            <p>If a tutor cancels a class, you'll receive an email notification immediately. For paid classes, you'll receive an automatic full refund within 5-7 business days. The tutor may also reschedule the class and notify you.</p>
        </div>
    </div>

    <div class="faq-card" onclick="this.classList.toggle('active')">
        <p class="faq-question">
            How can I update my profile information?
            <i class="fas fa-chevron-down faq-icon"></i>
        </p>
        <div class="faq-answer">
            <p>Click on your name in the top-right corner and select "Profile" or "Settings". From there, you can update your personal information, bio, skills, contact details, and profile picture.</p>
        </div>
    </div>

    <div class="faq-card" onclick="this.classList.toggle('active')">
        <p class="faq-question">
            Is my personal information secure?
            <i class="fas fa-chevron-down faq-icon"></i>
        </p>
        <div class="faq-answer">
            <p>Yes! We take security seriously. All data is encrypted, payments are processed through secure gateways, and we never share your personal information with third parties without your consent. You can manage your privacy settings in Settings → Privacy.</p>
        </div>
    </div>

    <!-- Still Need Help -->
    <div style="text-align: center; margin-top: 64px; padding: 48px 24px; background: linear-gradient(135deg, #F0FDFA, #E0F2FE); border-radius: 16px;">
        <i class="fas fa-question-circle" style="font-size: 3rem; color: #4FD1C5; margin-bottom: 20px;"></i>
        <h3 style="margin: 0 0 12px 0; font-size: 1.5rem; font-weight: 700; color: #111827;">
            Still Need Help?
        </h3>
        <p style="margin: 0 0 24px 0; color: #4B5563; font-size: 1rem;">
            Can't find what you're looking for? Our support team is ready to help!
        </p>
        <a href="mailto:support@skillupnow.com" class="btn btn-primary" style="padding: 14px 32px; text-decoration: none; display: inline-block;">
            <i class="fas fa-envelope"></i> Contact Support
        </a>
    </div>

    <!-- Office Hours -->
    <div style="margin-top: 40px; padding: 24px; background: white; border-radius: 12px; border: 2px solid #F3F4F6;">
        <h4 style="margin: 0 0 16px 0; font-size: 1.125rem; font-weight: 700; color: #111827;">
            <i class="fas fa-clock"></i> Support Hours
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div>
                <p style="margin: 0 0 4px 0; font-weight: 600; color: #374151;">Monday - Friday</p>
                <p style="margin: 0; color: #6B7280; font-size: 0.875rem;">9:00 AM - 6:00 PM IST</p>
            </div>
            <div>
                <p style="margin: 0 0 4px 0; font-weight: 600; color: #374151;">Saturday</p>
                <p style="margin: 0; color: #6B7280; font-size: 0.875rem;">10:00 AM - 4:00 PM IST</p>
            </div>
            <div>
                <p style="margin: 0 0 4px 0; font-weight: 600; color: #374151;">Sunday</p>
                <p style="margin: 0; color: #6B7280; font-size: 0.875rem;">Closed</p>
            </div>
        </div>
    </div>

</div>

<script>
// Add hover effect to social media icons
document.querySelectorAll('a[href*="facebook"], a[href*="twitter"], a[href*="instagram"], a[href*="linkedin"], a[href*="youtube"]').forEach(icon => {
    icon.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.1)';
    });
    icon.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
});
</script>

<?php include 'dashboard-footer.php'; ?>