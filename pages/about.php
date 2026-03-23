<?php
$pageTitle = "About Us";
include '../includes/header.php';
?>

<!-- About Hero Section -->
<section class="hero" style="padding: 5rem 2rem 3rem 2rem;">
    <div class="container">
        <div class="hero-badge">
            <span class="badge-icon">📚</span>
            Our Story
        </div>
        
        <h1 class="hero-title" style="font-size: 3rem;">
            Empowering Students Through <span class="highlight">Peer Learning</span>
        </h1>
        
        <p class="hero-description">
            SkillUp Now was created by students of Gayatri Vidya Parishad College of Engineering 
            to bridge the gap between academic education and real-world skill development.
        </p>
    </div>
</section>

<!-- Mission Section -->
<section class="section" style="background: white;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;">
            <div>
                <h2 style="margin-bottom: 1.5rem;">Our Mission</h2>
                <p style="color: var(--gray-600); font-size: 1.125rem; line-height: 1.8;">
                    We believe that the best learning happens when students teach students. 
                    Our mission is to create an exclusive, verified platform where college students 
                    can connect, share knowledge, and grow together in a supportive community.
                </p>
                <p style="color: var(--gray-600); font-size: 1.125rem; line-height: 1.8;">
                    By restricting access to verified college students only, we ensure a safe, 
                    authentic, and focused learning environment that promotes accountability, 
                    trust, and meaningful skill development.
                </p>
            </div>
            <div style="background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); height: 400px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem;">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="section">
    <div class="container">
        <h2 class="text-center mb-5">Our Core Values</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
            <!-- Value 1 -->
            <div style="padding: 2rem; background: white; border-radius: 1rem; box-shadow: var(--shadow-md);">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎯</div>
                <h3 style="margin-bottom: 1rem;">Excellence</h3>
                <p style="color: var(--gray-600);">We strive for the highest quality in education, mentorship, and platform experience.</p>
            </div>
            
            <!-- Value 2 -->
            <div style="padding: 2rem; background: white; border-radius: 1rem; box-shadow: var(--shadow-md);">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🤝</div>
                <h3 style="margin-bottom: 1rem;">Community</h3>
                <p style="color: var(--gray-600);">Building a supportive network where students help each other succeed.</p>
            </div>
            
            <!-- Value 3 -->
            <div style="padding: 2rem; background: white; border-radius: 1rem; box-shadow: var(--shadow-md);">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🔒</div>
                <h3 style="margin-bottom: 1rem;">Trust</h3>
                <p style="color: var(--gray-600);">Maintaining a verified, secure platform through institutional authentication.</p>
            </div>
            
            <!-- Value 4 -->
            <div style="padding: 2rem; background: white; border-radius: 1rem; box-shadow: var(--shadow-md);">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">🚀</div>
                <h3 style="margin-bottom: 1rem;">Growth</h3>
                <p style="color: var(--gray-600);">Empowering continuous learning and skill development for career success.</p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="section" style="background: white;">
    <div class="container text-center">
        <h2 class="mb-4">Meet Our Team</h2>
        <p class="mb-5" style="max-width: 700px; margin: 0 auto 3rem auto; color: var(--gray-600);">
            A dedicated team of students passionate about transforming education through technology.
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; max-width: 1000px; margin: 0 auto;">
            <!-- Team Member 1 -->
            <div style="text-align: center;">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; margin: 0 auto 1rem auto; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 700;">
                    IP
                </div>
                <h4 style="margin-bottom: 0.5rem;">Indira Praneetha</h4>
                <p style="color: var(--gray-600); margin-bottom: 0;">Developer</p>
            </div>
            
            <!-- Team Member 2 -->
            <div style="text-align: center;">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); border-radius: 50%; margin: 0 auto 1rem auto; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 700;">
                    SJ
                </div>
                <h4 style="margin-bottom: 0.5rem;">Sai Jyostna</h4>
                <p style="color: var(--gray-600); margin-bottom: 0;">Developer</p>
            </div>
            
            <!-- Team Member 3 -->
            <div style="text-align: center;">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--accent-orange), #DD6B20); border-radius: 50%; margin: 0 auto 1rem auto; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 700;">
                    VP
                </div>
                <h4 style="margin-bottom: 0.5rem;">Vegi Prasanthi</h4>
                <p style="color: var(--gray-600); margin-bottom: 0;">Developer</p>
            </div>
            
            <!-- Team Member 4 -->
            <div style="text-align: center;">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #667EEA, #764BA2); border-radius: 50%; margin: 0 auto 1rem auto; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 700;">
                    AK
                </div>
                <h4 style="margin-bottom: 0.5rem;">Aishwarya Kamakshi</h4>
                <p style="color: var(--gray-600); margin-bottom: 0;">Developer</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section" style="background: linear-gradient(135deg, var(--gray-900), var(--gray-800)); color: white; text-align: center;">
    <div class="container">
        <h2 style="color: white; font-size: 2.5rem; margin-bottom: 1rem;">Join Our Community</h2>
        <p style="font-size: 1.25rem; margin-bottom: 2rem; color: rgba(255, 255, 255, 0.8);">
            Become part of a thriving community of learners and educators.
        </p>
        <a href="../pages/signup.php" class="btn btn-primary">
            Get Started Today
            <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

<?php include '../includes/footer.php'; ?>