<?php
session_start();
$pageTitle = "Find Classes";
include 'dashboard-header.php';

// Redirect tutors
if ($_SESSION['user_type'] !== 'learner') {
    header("Location: tutor-dashboard.php");
    exit();
}
?>

<div style="max-width: 1400px; margin: 0 auto;">
    <h1 style="margin-bottom: 1.5rem; color: var(--gray-900);">Find Classes</h1>
    
    <!-- Search and Filter Bar -->
    <div style="background: white; border-radius: 1rem; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="display: grid; grid-template-columns: 1fr 200px 200px 150px; gap: 1rem;">
            <input type="text" placeholder="Search for skills, tutors..." 
                style="padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: 0.5rem;">
            
            <select style="padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: 0.5rem; background: white;">
                <option value="">All Categories</option>
                <option value="programming">Programming</option>
                <option value="design">Design</option>
                <option value="data">Data Science</option>
                <option value="languages">Languages</option>
            </select>
            
            <select style="padding: 0.75rem; border: 1px solid var(--gray-300); border-radius: 0.5rem; background: white;">
                <option value="">Price Range</option>
                <option value="0-500">₹0 - ₹500</option>
                <option value="500-1000">₹500 - ₹1000</option>
                <option value="1000+">₹1000+</option>
            </select>
            
            <button class="btn btn-primary" style="justify-content: center;">
                <i class="fas fa-search"></i> Search
            </button>
        </div>
    </div>
    
    <!-- Available Classes Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
        
        <!-- Class Card 1 -->
        <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="height: 180px; background: linear-gradient(135deg, #4F46E5, #7C3AED); display: flex; align-items: center; justify-content: center; font-size: 4rem;">
                🐍
            </div>
            <div style="padding: 1.5rem;">
                <div style="display: flex; justify-content: between; align-items: start; margin-bottom: 1rem;">
                    <span style="padding: 0.25rem 0.75rem; background: #DBEAFE; color: #1E40AF; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
                        Programming
                    </span>
                    <div style="margin-left: auto; color: #F59E0B;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <span style="color: var(--gray-600); font-size: 0.875rem; margin-left: 0.25rem;">(24)</span>
                    </div>
                </div>
                
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem;">Python Programming</h3>
                <p style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 1rem;">
                    Learn Python from basics to advanced with hands-on projects and real-world applications.
                </p>
                
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-cyan), var(--primary-teal)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                        JS
                    </div>
                    <div>
                        <p style="margin: 0; font-weight: 600; font-size: 0.875rem;">John Smith</p>
                        <p style="margin: 0; color: var(--gray-500); font-size: 0.75rem;">2 years experience</p>
                    </div>
                </div>
                
                <div style="border-top: 1px solid var(--gray-200); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="margin: 0; font-size: 0.875rem; color: var(--gray-600);">Starting from</p>
                        <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--primary-teal);">₹500<span style="font-size: 0.875rem; font-weight: 400;">/hr</span></p>
                    </div>
                    <button class="btn btn-primary" style="padding: 0.5rem 1.5rem;">
                        Book Now
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Class Card 2 -->
        <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="height: 180px; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); display: flex; align-items: center; justify-content: center; font-size: 4rem;">
                ⚛️
            </div>
            <div style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <span style="padding: 0.25rem 0.75rem; background: #DBEAFE; color: #1E40AF; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
                        Programming
                    </span>
                    <div style="color: #F59E0B;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span style="color: var(--gray-600); font-size: 0.875rem; margin-left: 0.25rem;">(18)</span>
                    </div>
                </div>
                
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem;">React Development</h3>
                <p style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 1rem;">
                    Master modern React with hooks, context API, and best practices for building web apps.
                </p>
                
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                        SJ
                    </div>
                    <div>
                        <p style="margin: 0; font-weight: 600; font-size: 0.875rem;">Sarah Johnson</p>
                        <p style="margin: 0; color: var(--gray-500); font-size: 0.75rem;">3 years experience</p>
                    </div>
                </div>
                
                <div style="border-top: 1px solid var(--gray-200); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="margin: 0; font-size: 0.875rem; color: var(--gray-600);">Starting from</p>
                        <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--primary-teal);">₹750<span style="font-size: 0.875rem; font-weight: 400;">/hr</span></p>
                    </div>
                    <button class="btn btn-primary" style="padding: 0.5rem 1.5rem;">
                        Book Now
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Class Card 3 -->
        <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
            <div style="height: 180px; background: linear-gradient(135deg, var(--accent-orange), #DD6B20); display: flex; align-items: center; justify-content: center; font-size: 4rem;">
                🎨
            </div>
            <div style="padding: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <span style="padding: 0.25rem 0.75rem; background: #FCE7F3; color: #9F1239; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
                        Design
                    </span>
                    <div style="color: #F59E0B;">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                        <span style="color: var(--gray-600); font-size: 0.875rem; margin-left: 0.25rem;">(12)</span>
                    </div>
                </div>
                
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem;">UI/UX Design</h3>
                <p style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 1rem;">
                    Create beautiful and user-friendly interfaces with Figma and modern design principles.
                </p>
                
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-orange), #DD6B20); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                        ML
                    </div>
                    <div>
                        <p style="margin: 0; font-weight: 600; font-size: 0.875rem;">Mike Lee</p>
                        <p style="margin: 0; color: var(--gray-500); font-size: 0.75rem;">4 years experience</p>
                    </div>
                </div>
                
                <div style="border-top: 1px solid var(--gray-200); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="margin: 0; font-size: 0.875rem; color: var(--gray-600);">Starting from</p>
                        <p style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--primary-teal);">₹600<span style="font-size: 0.875rem; font-weight: 400;">/hr</span></p>
                    </div>
                    <button class="btn btn-primary" style="padding: 0.5rem 1.5rem;">
                        Book Now
                    </button>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php include 'dashboard-footer.php'; ?>