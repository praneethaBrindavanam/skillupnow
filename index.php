<?php
// Set page title
$pageTitle = "Home";

// Include header
include 'includes/header.php';
?>

<!-- Student Content -->
<div class="student-content">
    <!-- Hero Section - Students -->
    <section class="hero">
        <div class="hero-container">
            <!-- Badge -->
            <div class="hero-badge">
                <span class="badge-icon">🔥</span>
                Join 10,000+ students already learning
            </div>
            
            <!-- Main Heading -->
            <h1 class="hero-title">
                Learn Skills From <span class="highlight">Your Peers</span><br>
                <span class="text-muted">Grow Your Knowledge</span>
            </h1>
            
            <!-- Description -->
            <p class="hero-description">
                Connect with verified college tutors who share your interests. Learn new skills, 
                build connections, and grow in a supportive learning community.
            </p>
            
            <!-- Call to Action -->
            <div class="hero-actions">
                <a href="/pages/signup.php" class="btn btn-primary">
                    Start Learning Free
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="/pages/browse.php" class="btn btn-outline">
                    Browse Tutors
                </a>
            </div>
            
            <!-- Quick Match Card -->
            <div class="quick-match">
                <div class="quick-match-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <h4>Quick Match!</h4>
                <p>Found 5 tutors nearby</p>
            </div>
            
            <!-- Popular Skills -->
            <div class="skills-section">
                <p class="skills-label">Popular skills you can learn</p>
                <div class="skills-tags">
                    <span class="skill-tag">Python</span>
                    <span class="skill-tag">React</span>
                    <span class="skill-tag">UI/UX Design</span>
                    <span class="skill-tag">Video Editing</span>
                    <span class="skill-tag">Guitar</span>
                    <span class="skill-tag">Spanish</span>
                    <span class="skill-tag">Photography</span>
                    <span class="skill-tag">Data Science</span>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="stats-section">
                <div class="stat-item">
                    <div class="stat-value">10K+</div>
                    <div class="stat-label">Active Students</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">2K+</div>
                    <div class="stat-label">Expert Tutors</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">25K+</div>
                    <div class="stat-label">Sessions Completed</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section - Students -->
    <section class="section" style="background: white;">
        <div class="container">
            <h2 class="text-center mb-4">Why Learn on SkillUp Now?</h2>
            <p class="text-center mb-5" style="max-width: 700px; margin: 0 auto 3rem auto; color: var(--gray-600);">
                Access verified tutors, structured courses, and track your progress all in one place.
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <!-- Feature 1 -->
                <div style="padding: 2rem; border-radius: 1rem; background: linear-gradient(135deg, #F7FAFC, #E6FFFA); text-align: center;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Expert Tutors</h3>
                    <p style="color: var(--gray-600);">Learn from verified college students who excel in their subjects and love to teach.</p>
                </div>
                
                <!-- Feature 2 -->
                <div style="padding: 2rem; border-radius: 1rem; background: linear-gradient(135deg, #FFF5F7, #FED7E2); text-align: center;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Personalized Learning</h3>
                    <p style="color: var(--gray-600);">Get customized learning paths tailored to your goals and learning pace.</p>
                </div>
                
                <!-- Feature 3 -->
                <div style="padding: 2rem; border-radius: 1rem; background: linear-gradient(135deg, #FFFAF0, #FEEBC8); text-align: center;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent-orange), #DD6B20); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Track Progress</h3>
                    <p style="color: var(--gray-600);">Monitor your learning journey with analytics, milestones, and certificates.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section - Students -->
    <section class="section">
        <div class="container text-center">
            <h2 class="mb-4">How to Start Learning</h2>
            <p class="mb-5" style="max-width: 700px; margin: 0 auto 3rem auto; color: var(--gray-600);">
                Begin your learning journey in three simple steps.
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 3rem; max-width: 1000px; margin: 0 auto;">
                <!-- Step 1 -->
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 2rem; font-weight: 700;">
                        1
                    </div>
                    <h3 style="margin-bottom: 1rem;">Create Account</h3>
                    <p style="color: var(--gray-600);">Sign up with your college email to join our verified student community.</p>
                </div>
                
                <!-- Step 2 -->
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 2rem; font-weight: 700;">
                        2
                    </div>
                    <h3 style="margin-bottom: 1rem;">Choose Your Tutor</h3>
                    <p style="color: var(--gray-600);">Browse verified tutors and select the one that matches your learning goals.</p>
                </div>
                
                <!-- Step 3 -->
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent-orange), #DD6B20); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 2rem; font-weight: 700;">
                        3
                    </div>
                    <h3 style="margin-bottom: 1rem;">Start Learning</h3>
                    <p style="color: var(--gray-600);">Book sessions, attend classes, and track your progress every step of the way.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section - Students -->
    <section class="section" style="background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); color: white; text-align: center;">
        <div class="container">
            <h2 style="color: white; font-size: 2.5rem; margin-bottom: 1rem;">Ready to Start Learning?</h2>
            <p style="font-size: 1.25rem; margin-bottom: 2rem; color: rgba(255, 255, 255, 0.9);">
                Join thousands of students already learning on SkillUp Now.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="/pages/signup.php" class="btn" style="background: white; color: var(--primary-teal);">
                    Start Learning Now
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="/pages/browse.php" class="btn btn-outline" style="border-color: white; color: white;">
                    Browse Tutors
                </a>
            </div>
        </div>
    </section>
</div>

<!-- Tutor Content -->
<div class="tutor-content" style="display: none;">
    <!-- Hero Section - Tutors -->
    <section class="hero">
        <div class="hero-container">
            <!-- Badge -->
            <div class="hero-badge">
                <span class="badge-icon">💼</span>
                Join 2,000+ tutors earning and teaching
            </div>
            
            <!-- Main Heading -->
            <h1 class="hero-title">
                Share Your <span class="highlight">Expertise</span><br>
                <span class="text-muted">Earn While Teaching</span>
            </h1>
            
            <!-- Description -->
            <p class="hero-description">
                Turn your knowledge into income. Connect with eager students, build your reputation, 
                and make a meaningful impact while earning money.
            </p>
            
            <!-- Call to Action -->
            <div class="hero-actions">
                <a href="/pages/signup.php" class="btn btn-primary">
                    Become a Tutor
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="/pages/how-it-works.php" class="btn btn-outline">
                    Learn More
                </a>
            </div>
            
            <!-- Quick Match Card -->
            <div class="quick-match">
                <div class="quick-match-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h4>Earning Potential</h4>
                <p>₹500-2000/hour average</p>
            </div>
            
            <!-- Popular Skills -->
            <div class="skills-section">
                <p class="skills-label">High-demand subjects to teach</p>
                <div class="skills-tags">
                    <span class="skill-tag">Python</span>
                    <span class="skill-tag">React</span>
                    <span class="skill-tag">UI/UX Design</span>
                    <span class="skill-tag">Video Editing</span>
                    <span class="skill-tag">Guitar</span>
                    <span class="skill-tag">Spanish</span>
                    <span class="skill-tag">Photography</span>
                    <span class="skill-tag">Data Science</span>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="stats-section">
                <div class="stat-item">
                    <div class="stat-value">2K+</div>
                    <div class="stat-label">Active Tutors</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">₹1.2Cr+</div>
                    <div class="stat-label">Total Earnings</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">4.8/5</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section - Tutors -->
    <section class="section" style="background: white;">
        <div class="container">
            <h2 class="text-center mb-4">Why Teach on SkillUp Now?</h2>
            <p class="text-center mb-5" style="max-width: 700px; margin: 0 auto 3rem auto; color: var(--gray-600);">
                Build your teaching career with the tools and support you need to succeed.
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <!-- Feature 1 -->
                <div style="padding: 2rem; border-radius: 1rem; background: linear-gradient(135deg, #F7FAFC, #E6FFFA); text-align: center;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Flexible Earnings</h3>
                    <p style="color: var(--gray-600);">Set your own rates and schedule. Earn money teaching what you love.</p>
                </div>
                
                <!-- Feature 2 -->
                <div style="padding: 2rem; border-radius: 1rem; background: linear-gradient(135deg, #FFF5F7, #FED7E2); text-align: center;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Ready Students</h3>
                    <p style="color: var(--gray-600);">Access thousands of verified students actively looking for tutors.</p>
                </div>
                
                <!-- Feature 3 -->
                <div style="padding: 2rem; border-radius: 1rem; background: linear-gradient(135deg, #FFFAF0, #FEEBC8); text-align: center;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent-orange), #DD6B20); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 1.5rem;">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Teaching Tools</h3>
                    <p style="color: var(--gray-600);">Use our platform's built-in tools for video sessions, assignments, and tracking.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section - Tutors -->
    <section class="section">
        <div class="container text-center">
            <h2 class="mb-4">How to Start Teaching</h2>
            <p class="mb-5" style="max-width: 700px; margin: 0 auto 3rem auto; color: var(--gray-600);">
                Begin your teaching journey in three simple steps.
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 3rem; max-width: 1000px; margin: 0 auto;">
                <!-- Step 1 -->
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 2rem; font-weight: 700;">
                        1
                    </div>
                    <h3 style="margin-bottom: 1rem;">Create Profile</h3>
                    <p style="color: var(--gray-600);">Sign up and build your tutor profile showcasing your expertise and experience.</p>
                </div>
                
                <!-- Step 2 -->
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 2rem; font-weight: 700;">
                        2
                    </div>
                    <h3 style="margin-bottom: 1rem;">Set Your Rate</h3>
                    <p style="color: var(--gray-600);">Choose your hourly rate and availability that works for your schedule.</p>
                </div>
                
                <!-- Step 3 -->
                <div style="text-align: center;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent-orange), #DD6B20); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; color: white; font-size: 2rem; font-weight: 700;">
                        3
                    </div>
                    <h3 style="margin-bottom: 1rem;">Start Teaching</h3>
                    <p style="color: var(--gray-600);">Connect with students, conduct sessions, and earn money doing what you love.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section - Tutors -->
    <section class="section" style="background: linear-gradient(135deg, var(--accent-purple), var(--accent-pink)); color: white; text-align: center;">
        <div class="container">
            <h2 style="color: white; font-size: 2.5rem; margin-bottom: 1rem;">Ready to Start Teaching?</h2>
            <p style="font-size: 1.25rem; margin-bottom: 2rem; color: rgba(255, 255, 255, 0.9);">
                Join thousands of tutors already earning on SkillUp Now.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="/pages/signup.php" class="btn" style="background: white; color: var(--accent-purple);">
                    Become a Tutor
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="/pages/contact.php" class="btn btn-outline" style="border-color: white; color: white;">
                    Contact Us
                </a>
            </div>
        </div>
    </section>
</div>
<script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
<df-messenger intent="WELCOME" chat-title="SkillUpNow" agent-id="21e4130f-3b0e-4692-ba74-6ae80af1eff1"
  language-code="en"></df-messenger>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
<?php
// Include footer
include 'includes/footer.php';
?>