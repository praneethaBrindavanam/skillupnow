<?php
// Set page title
$pageTitle = "Skills";

// Include header
include '../includes/header.php';
?>

<!-- Skills Hero Section -->
<section class="hero" style="padding: var(--spacing-xl) 2rem;">
    <div class="hero-container">
        <h1 class="hero-title" style="font-size: 2.5rem; margin-bottom: var(--spacing-sm);">
            <span class="student-content">Explore <span class="highlight">Skills & Subjects</span></span>
            <span class="tutor-content" style="display: none;">Share Your <span class="highlight">Expertise</span></span>
        </h1>
        <p class="hero-description" style="font-size: 1rem;">
            <span class="student-content">Discover what you can learn - from cutting-edge tech skills to core academic subjects</span>
            <span class="tutor-content" style="display: none;">Choose subjects you want to teach and help students excel</span>
        </p>
    </div>
</section>

<!-- Search Section -->
<section class="section" style="background: white; padding-top: var(--spacing-lg); padding-bottom: var(--spacing-lg);">
    <div class="container">
        <div style="max-width: 700px; margin: 0 auto;">
            <div style="position: relative;">
                <input 
                    type="text" 
                    id="skillSearch"
                    placeholder="Search for skills or subjects..."
                    style="width: 100%; padding: 1rem 3rem 1rem 1.5rem; border: 2px solid var(--gray-200); border-radius: var(--radius-full); font-size: 1rem; outline: none; transition: border-color var(--transition-base);"
                    onfocus="this.style.borderColor='var(--primary-cyan)'"
                    onblur="this.style.borderColor='var(--gray-200)'"
                >
                <i class="fas fa-search" style="position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 1.2rem;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Main Categories Toggle -->
<section class="section" style="padding-top: 0; padding-bottom: var(--spacing-md);">
    <div class="container">
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <button class="category-toggle active" data-type="all" style="padding: 0.75rem 2rem; border: 2px solid var(--primary-cyan); background: var(--primary-cyan); color: white; border-radius: var(--radius-full); font-weight: 600; cursor: pointer; transition: all var(--transition-base); font-size: 1rem;">
                All Skills
            </button>
            <button class="category-toggle" data-type="technical" style="padding: 0.75rem 2rem; border: 2px solid var(--gray-300); background: transparent; color: var(--gray-700); border-radius: var(--radius-full); font-weight: 600; cursor: pointer; transition: all var(--transition-base); font-size: 1rem;">
                <i class="fas fa-code"></i> Technical Skills
            </button>
            <button class="category-toggle" data-type="academic" style="padding: 0.75rem 2rem; border: 2px solid var(--gray-300); background: transparent; color: var(--gray-700); border-radius: var(--radius-full); font-weight: 600; cursor: pointer; transition: all var(--transition-base); font-size: 1rem;">
                <i class="fas fa-book"></i> Academic Subjects
            </button>
        </div>
    </div>
</section>

<!-- Technical Skills Section -->
<section class="section skills-container technical-skills" style="padding-top: var(--spacing-md);">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: var(--spacing-lg); font-size: 2rem;">
            <i class="fas fa-laptop-code" style="color: var(--primary-cyan);"></i> Technical Skills
        </h2>

        <!-- Web Development -->
        <div class="skill-category" style="margin-bottom: var(--spacing-2xl);">
            <h3 style="font-size: 1.5rem; margin-bottom: var(--spacing-md); padding-bottom: 0.5rem; border-bottom: 3px solid var(--primary-cyan); display: inline-block;">
                <i class="fas fa-globe"></i> Web Development
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: var(--spacing-lg);">
                
                <!-- Frontend Development -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #61DAFB;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #61DAFB, #20232A); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-react" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Frontend Development</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">HTML, CSS, JavaScript, React, Vue, Angular</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 245 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Backend Development -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #68A063;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #68A063, #44883E); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-server" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Backend Development</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Node.js, Python, Java, PHP, Express, Django</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 198 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Full Stack -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #FF6B6B;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #FF6B6B, #C92A2A); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-layer-group" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Full Stack Development</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">MERN, MEAN, Full Stack Projects</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 167 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

            </div>
        </div>

        <!-- DevOps & Cloud -->
        <div class="skill-category" style="margin-bottom: var(--spacing-2xl);">
            <h3 style="font-size: 1.5rem; margin-bottom: var(--spacing-md); padding-bottom: 0.5rem; border-bottom: 3px solid var(--accent-purple); display: inline-block;">
                <i class="fas fa-cloud"></i> DevOps & Cloud
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: var(--spacing-lg);">
                
                <!-- DevOps -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #326CE5;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #326CE5, #1E4B8F); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-infinity" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">DevOps</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Docker, Kubernetes, Jenkins, CI/CD</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 89 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Cloud Computing -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #FF9900;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #FF9900, #CC7A00); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fab fa-aws" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Cloud Computing</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">AWS, Azure, Google Cloud Platform</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 76 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Linux & Shell -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #FCC624;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #FCC624, #E5B020); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fab fa-linux" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Linux & Shell Scripting</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Bash, Linux Admin, Shell Commands</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 52 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Data Science & AI -->
        <div class="skill-category" style="margin-bottom: var(--spacing-2xl);">
            <h3 style="font-size: 1.5rem; margin-bottom: var(--spacing-md); padding-bottom: 0.5rem; border-bottom: 3px solid var(--accent-orange); display: inline-block;">
                <i class="fas fa-brain"></i> Data Science & AI
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: var(--spacing-lg);">
                
                <!-- Machine Learning -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #FF6F00;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #FF6F00, #D15600); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-robot" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Machine Learning</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Scikit-learn, TensorFlow, PyTorch, ML Algorithms</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 134 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Data Analysis -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #3776AB;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3776AB, #2B5D8C); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-chart-bar" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Data Analysis</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Python, Pandas, NumPy, Data Visualization</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 156 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Deep Learning -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #EE4C2C;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #EE4C2C, #C43E24); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-network-wired" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Deep Learning</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Neural Networks, CNN, RNN, NLP</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 87 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Design & Creative -->
        <div class="skill-category" style="margin-bottom: var(--spacing-2xl);">
            <h3 style="font-size: 1.5rem; margin-bottom: var(--spacing-md); padding-bottom: 0.5rem; border-bottom: 3px solid var(--accent-pink); display: inline-block;">
                <i class="fas fa-palette"></i> Design & Creative
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: var(--spacing-lg);">
                
                <!-- UI/UX Design -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #F24E1E;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #F24E1E, #D43E18); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-pencil-ruler" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">UI/UX Design</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Figma, Adobe XD, User Research, Wireframing</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 178 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Graphic Design -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #FF61F6;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #FF61F6, #D14ED0); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-paint-brush" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Graphic Design</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Photoshop, Illustrator, Logo Design</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 145 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Video Editing -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #9999FF;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #9999FF, #7A7AD1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-video" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Video Editing</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Premiere Pro, After Effects, DaVinci Resolve</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 112 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- Academic Subjects Section -->
<section class="section skills-container academic-skills" style="padding-top: var(--spacing-md); background: var(--gray-50);">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: var(--spacing-lg); font-size: 2rem;">
            <i class="fas fa-graduation-cap" style="color: var(--primary-cyan);"></i> Academic Subjects
        </h2>

        <!-- Computer Science -->
        <div class="skill-category" style="margin-bottom: var(--spacing-2xl);">
            <h3 style="font-size: 1.5rem; margin-bottom: var(--spacing-md); padding-bottom: 0.5rem; border-bottom: 3px solid var(--primary-cyan); display: inline-block;">
                <i class="fas fa-laptop-code"></i> Computer Science
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: var(--spacing-lg);">
                
                <!-- OOPs -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #4FD1C5;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #4FD1C5, #38B2AC); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-cubes" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Object Oriented Programming</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Classes, Objects, Inheritance, Polymorphism</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 289 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- DBMS -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #4299E1;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #4299E1, #3182CE); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-database" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Database Management</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">SQL, Normalization, ER Diagrams, Queries</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 234 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Data Structures -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #48BB78;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #48BB78, #38A169); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-project-diagram" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Data Structures & Algorithms</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Arrays, Trees, Graphs, Sorting, Searching</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 312 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Operating Systems -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #ED8936;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #ED8936, #DD6B20); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-desktop" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Operating Systems</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Processes, Threads, Memory, Scheduling</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 187 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Computer Networks -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #9F7AEA;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #9F7AEA, #805AD5); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-network-wired" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Computer Networks</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">OSI Model, TCP/IP, Routing, Protocols</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 156 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Compiler Design -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #F687B3;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #F687B3, #ED64A6); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-cogs" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Compiler Design</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Lexical Analysis, Parsing, Code Generation</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 98 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Mathematics -->
        <div class="skill-category" style="margin-bottom: var(--spacing-2xl);">
            <h3 style="font-size: 1.5rem; margin-bottom: var(--spacing-md); padding-bottom: 0.5rem; border-bottom: 3px solid #4299E1; display: inline-block;">
                <i class="fas fa-calculator"></i> Mathematics
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: var(--spacing-lg);">
                
                <!-- Engineering Maths -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #3182CE;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #3182CE, #2C5AA0); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-square-root-alt" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Engineering Mathematics</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Calculus, Linear Algebra, Differential Equations</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 267 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Discrete Mathematics -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #805AD5;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #805AD5, #6B46C1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-cubes" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Discrete Mathematics</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Set Theory, Graph Theory, Combinatorics</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 198 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Probability & Statistics -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #38B2AC;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #38B2AC, #2C7A7B); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-chart-pie" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Probability & Statistics</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Probability Distributions, Hypothesis Testing</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 223 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Core Engineering -->
        <div class="skill-category" style="margin-bottom: var(--spacing-2xl);">
            <h3 style="font-size: 1.5rem; margin-bottom: var(--spacing-md); padding-bottom: 0.5rem; border-bottom: 3px solid #ED8936; display: inline-block;">
                <i class="fas fa-cog"></i> Core Engineering
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-top: var(--spacing-lg);">
                
                <!-- Digital Electronics -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #DD6B20;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #DD6B20, #C05621); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-microchip" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Digital Electronics</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Logic Gates, Flip-Flops, Circuits</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 176 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Signals & Systems -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #48BB78;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #48BB78, #38A169); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-wave-square" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Signals & Systems</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Fourier Transform, Laplace, Z-Transform</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 143 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

                <!-- Control Systems -->
                <div class="skill-card" style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-md); transition: all var(--transition-base); cursor: pointer; border-top: 4px solid #F687B3;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #F687B3, #ED64A6); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <i class="fas fa-sliders-h" style="color: white; font-size: 1.5rem;"></i>
                    </div>
                    <h4 style="font-size: 1.25rem; margin-bottom: 0.5rem;">Control Systems</h4>
                    <p style="color: var(--gray-600); font-size: 0.9rem; margin-bottom: 1rem;">Transfer Functions, Stability, Controllers</p>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: var(--primary-teal); font-weight: 600; font-size: 0.85rem;">
                            <i class="fas fa-user-graduate"></i> 121 Tutors
                        </span>
                        <a href="/pages/browse.php" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Explore</a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- CTA Section -->
<section class="section" style="background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); color: white; text-align: center;">
    <div class="container">
        <div class="student-content">
            <h2 style="color: white; font-size: 2rem; margin-bottom: 1rem;">Ready to Master a New Skill?</h2>
            <p style="font-size: 1.1rem; margin-bottom: 2rem; color: rgba(255, 255, 255, 0.9);">
                Connect with expert tutors and start your learning journey today!
            </p>
            <a href="/pages/browse.php" class="btn" style="background: white; color: var(--primary-teal); padding: 0.75rem 2rem;">
                Find Your Tutor
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="tutor-content" style="display: none;">
            <h2 style="color: white; font-size: 2rem; margin-bottom: 1rem;">Start Teaching Your Expertise</h2>
            <p style="font-size: 1.1rem; margin-bottom: 2rem; color: rgba(255, 255, 255, 0.9);">
                Share your knowledge and earn while helping students succeed!
            </p>
            <a href="/pages/signup.php" class="btn" style="background: white; color: var(--primary-teal); padding: 0.75rem 2rem;">
                Become a Tutor
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<style>
/* Skill card hover effects */
.skill-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-xl);
}

/* Category toggle styles */
.category-toggle:hover {
    border-color: var(--primary-cyan);
    color: var(--primary-cyan);
}

.category-toggle.active {
    background: var(--primary-cyan) !important;
    border-color: var(--primary-cyan) !important;
    color: white !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const categoryToggles = document.querySelectorAll('.category-toggle');
    const technicalSkills = document.querySelector('.technical-skills');
    const academicSkills = document.querySelector('.academic-skills');
    const searchInput = document.getElementById('skillSearch');
    const skillCards = document.querySelectorAll('.skill-card');

    // Category toggle functionality
    categoryToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            // Update active button
            categoryToggles.forEach(btn => btn.classList.remove('active'));
            toggle.classList.add('active');

            const type = toggle.dataset.type;

            if (type === 'all') {
                technicalSkills.style.display = 'block';
                academicSkills.style.display = 'block';
            } else if (type === 'technical') {
                technicalSkills.style.display = 'block';
                academicSkills.style.display = 'none';
            } else if (type === 'academic') {
                technicalSkills.style.display = 'none';
                academicSkills.style.display = 'block';
            }
        });
    });

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();

            skillCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const parentCategory = card.closest('.skill-category');
                
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            // Hide empty categories
            document.querySelectorAll('.skill-category').forEach(category => {
                const visibleCards = category.querySelectorAll('.skill-card[style*="display: block"]');
                if (visibleCards.length === 0 && searchTerm !== '') {
                    category.style.display = 'none';
                } else {
                    category.style.display = 'block';
                }
            });
        });
    }

    console.log('Skills page initialized ✓');
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?>