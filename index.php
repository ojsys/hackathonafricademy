<?php
require_once __DIR__ . '/includes/functions.php';

// Redirect logged-in users to dashboard
if (is_logged_in()) {
    header('Location: /pages/dashboard.php');
    exit;
}

$courses = get_all_published_courses();
$totalStudents = db()->query('SELECT COUNT(*) FROM users WHERE role = "student"')->fetchColumn() ?: 0;
$totalLessons = db()->query('SELECT COUNT(*) FROM lessons')->fetchColumn() ?: 0;

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="hero-badge" data-testid="hero-badge">
                    <i class="bi bi-mortarboard-fill"></i>
                    FREE CODING COURSES
                </span>
                <h1 data-testid="hero-title">
                    Learn to Code.<br>
                    <span style="color: var(--primary);">Build Africa's Future.</span>
                </h1>
                <p class="lead mt-4">
                    Master HTML, CSS, and JavaScript through hands-on lessons, practical exercises, and real projects. Become eligible for HackathonAfrica and unlock tech opportunities across the continent.
                </p>
                
                <div class="d-flex gap-3 mt-4 flex-wrap">
                    <a href="/pages/register.php" class="btn btn-primary btn-lg" data-testid="hero-cta-register">
                        Start Learning Free <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                    <a href="/pages/courses.php" class="btn btn-outline-secondary btn-lg" data-testid="hero-cta-courses">
                        View Courses
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <strong><?= $totalStudents ?>+</strong>
                        <span>Students</span>
                    </div>
                    <div class="hero-stat">
                        <strong>3</strong>
                        <span>Courses</span>
                    </div>
                    <div class="hero-stat">
                        <strong><?= $totalLessons ?>+</strong>
                        <span>Lessons</span>
                    </div>
                    <div class="hero-stat">
                        <strong>100%</strong>
                        <span>Free</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div style="position: relative;">
                    <img src="https://images.unsplash.com/photo-1710770563074-6d9cc0d3e338?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTY2NjZ8MHwxfHNlYXJjaHwxfHxhZnJpY2FuJTIwcHJvZ3JhbW1lcnxlbnwwfHx8fDE3NzU3NTMzMDJ8MA&ixlib=rb-4.1.0&q=85&w=800" 
                         alt="African developer coding" 
                         class="img-fluid" 
                         style="border-radius: var(--radius); border: 1px solid var(--border);">
                    
                    <!-- Floating Code Card -->
                    <div class="d-none d-md-block" style="position: absolute; bottom: -20px; left: -20px; background: var(--surface); border: 1px solid var(--border); padding: 1rem; border-radius: var(--radius); max-width: 300px;">
                        <code style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-secondary);">
                            <span style="color: var(--primary);">const</span> developer = {<br>
                            &nbsp;&nbsp;name: <span style="color: var(--warning);">"You"</span>,<br>
                            &nbsp;&nbsp;ready: <span style="color: var(--primary);">true</span><br>
                            };
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Courses Section -->
<section class="py-5" style="background: var(--surface);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="overline mb-2 d-block">CURRICULUM</span>
            <h2>Three Courses to Web Mastery</h2>
            <p class="text-muted mt-2 mx-auto" style="max-width: 600px;">
                Progress through our structured curriculum. Each course builds on the last, taking you from complete beginner to job-ready developer.
            </p>
        </div>

        <div class="row g-4">
            <?php 
            $icons = ['bi-filetype-html', 'bi-filetype-css', 'bi-filetype-js'];
            $iconClasses = ['icon-html', 'icon-css', 'icon-js'];
            $colors = ['#e34f26', '#1572b6', '#f7df1e'];
            foreach ($courses as $i => $course): 
                $idx = $i % 3;
            ?>
            <div class="col-md-4">
                <div class="card h-100" data-testid="course-card-<?= $course['id'] ?>">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="course-icon <?= $iconClasses[$idx] ?>">
                                <i class="bi <?= $icons[$idx] ?>"></i>
                            </div>
                            <div>
                                <span class="overline" style="color: <?= $colors[$idx] ?>;">COURSE <?= $i + 1 ?></span>
                                <h4 class="mb-0"><?= h($course['title']) ?></h4>
                            </div>
                        </div>
                        <p class="text-muted mb-4"><?= h($course['description']) ?></p>
                        
                        <?php 
                        $modules = get_modules_for_course($course['id']);
                        $lessonCount = 0;
                        foreach ($modules as $m) {
                            $lessonCount += count(get_lessons_for_module($m['id']));
                        }
                        ?>
                        
                        <div class="d-flex gap-3 mb-4 small">
                            <span class="text-muted">
                                <i class="bi bi-collection me-1"></i> <?= count($modules) ?> Modules
                            </span>
                            <span class="text-muted">
                                <i class="bi bi-file-text me-1"></i> <?= $lessonCount ?> Lessons
                            </span>
                        </div>
                        
                        <a href="/pages/course.php?id=<?= $course['id'] ?>" class="btn btn-outline-primary w-100">
                            View Course <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container py-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="overline mb-2 d-block">WHY HACKATHON AFRICA LMS</span>
                <h2>Everything You Need to Succeed</h2>
                <p class="text-muted mt-3">
                    Our platform is built specifically for African learners. Clear explanations, practical examples, and a supportive path to becoming job-ready.
                </p>
                
                <div class="d-flex flex-column gap-4 mt-4">
                    <div class="d-flex gap-3">
                        <div class="stat-icon green flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-book"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Beginner-Friendly Content</h5>
                            <p class="text-muted mb-0 small">Clear explanations, real-world analogies, annotated code examples, and common mistakes to avoid.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3">
                        <div class="stat-icon orange flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-code-slash"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Hands-On Exercises</h5>
                            <p class="text-muted mb-0 small">Practice with interactive coding exercises. Write real code and see instant results.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3">
                        <div class="stat-icon purple flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-trophy"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Hackathon Eligibility</h5>
                            <p class="text-muted mb-0 small">Complete all courses and pass assessments to become eligible for HackathonAfrica events.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-3">
                        <div class="stat-icon blue flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Join the Community</h5>
                            <p class="text-muted mb-0 small">Connect with fellow learners and access opportunities across Africa.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1515879218367-8466d910aaa4?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTY2OTV8MHwxfHNlYXJjaHwxfHxjb2RlJTIwc2NyZWVufGVufDB8fHx8MTc3NTc1MzMwMnww&ixlib=rb-4.1.0&q=85&w=800" 
                     alt="Code on screen" 
                     class="img-fluid" 
                     style="border-radius: var(--radius); border: 1px solid var(--border);">
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container py-4">
        <div class="text-center">
            <h2>Ready to Start Your Coding Journey?</h2>
            <p class="text-muted mt-2 mb-4 mx-auto" style="max-width: 500px;">
                Join thousands of African developers learning to code. It's completely free.
            </p>
            <a href="/pages/register.php" class="btn btn-primary btn-lg" data-testid="cta-register">
                Create Free Account <i class="bi bi-arrow-right ms-2"></i>
            </a>
            <p class="small text-muted mt-3">
                Already have an account? <a href="/pages/login.php">Sign in</a>
            </p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
