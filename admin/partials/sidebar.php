<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar d-none d-lg-block" data-testid="admin-sidebar">
    <div class="sidebar-section">Dashboard</div>
    <a href="/admin/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>" data-testid="sidebar-dashboard">
        <i class="bi bi-speedometer2"></i> Overview
    </a>
    <a href="/admin/analytics.php" class="<?= $currentPage === 'analytics.php' ? 'active' : '' ?>" data-testid="sidebar-analytics">
        <i class="bi bi-graph-up"></i> Analytics
    </a>
    
    <div class="sidebar-section">Candidates</div>
    <a href="/admin/candidates.php" class="<?= $currentPage === 'candidates.php' ? 'active' : '' ?>" data-testid="sidebar-candidates">
        <i class="bi bi-person-check"></i> Review Candidates
    </a>
    <a href="/admin/users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>" data-testid="sidebar-users">
        <i class="bi bi-people"></i> All Users
    </a>
    
    <div class="sidebar-section">Content</div>
    <a href="/admin/courses.php" class="<?= $currentPage === 'courses.php' ? 'active' : '' ?>" data-testid="sidebar-courses">
        <i class="bi bi-collection"></i> Courses
    </a>
    <a href="/admin/lessons.php" class="<?= $currentPage === 'lessons.php' ? 'active' : '' ?>" data-testid="sidebar-lessons">
        <i class="bi bi-file-text"></i> Lessons
    </a>
    <a href="/admin/quizzes.php" class="<?= $currentPage === 'quizzes.php' ? 'active' : '' ?>" data-testid="sidebar-quizzes">
        <i class="bi bi-question-circle"></i> Quizzes
    </a>
    <a href="/admin/qualifying_exam.php" class="<?= $currentPage === 'qualifying_exam.php' ? 'active' : '' ?>">
        <i class="bi bi-shield-check"></i> Final Exam
    </a>
    <a href="/admin/proctor_images.php" class="<?= $currentPage === 'proctor_images.php' ? 'active' : '' ?>">
        <i class="bi bi-camera"></i> Proctor Images
    </a>
    
    <div class="sidebar-section">Quick Links</div>
    <a href="/admin/settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>" data-testid="sidebar-settings">
        <i class="bi bi-gear"></i> Settings
    </a>
    <a href="/pages/dashboard.php" data-testid="sidebar-student-view">
        <i class="bi bi-box-arrow-up-right"></i> Student View
    </a>
    <a href="https://africaplanfoundation.org" target="_blank">
        <i class="bi bi-globe"></i> Main Website
    </a>
</aside>
