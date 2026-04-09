<?php
require_once __DIR__ . '/../includes/functions.php';
start_session();
$user = current_user();
$isAdmin = $user && in_array($user['role'], ['admin', 'superadmin']);

// Load site settings (logo, favicon)
$siteSettings = get_site_settings();
$logoPath = $siteSettings['logo_path'] ?? '/public/img/logo.png';
$faviconPath = $siteSettings['favicon_path'] ?? '/public/img/favicon.png';
$siteName = $siteSettings['site_name'] ?? 'HackathonAfrica LMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= h($siteName) ?></title>
    <meta name="description" content="Learn HTML, CSS, and JavaScript with HackathonAfrica's free learning platform. Become eligible for hackathons and tech opportunities across Africa.">
    
    <!-- Fonts: Outfit, IBM Plex Sans, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="/public/css/style.css">
    
    <!-- Dynamic Theme Color -->
    <?php $primaryColor = $siteSettings['primary_color'] ?? '#F8B526'; ?>
    <?php if ($primaryColor !== '#F8B526'): ?>
    <style>
    :root {
        --primary: <?= htmlspecialchars($primaryColor) ?>;
        --primary-dark: <?= htmlspecialchars($primaryColor) ?>;
        --primary-glow: <?= htmlspecialchars($primaryColor) ?>18;
    }
    </style>
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= h($faviconPath) ?>">
    <link rel="apple-touch-icon" href="<?= h($faviconPath) ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg lms-navbar" data-testid="main-navbar">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/index.php" data-testid="brand-logo">
            <img src="<?= h($logoPath) ?>" alt="<?= h($siteName) ?>" class="navbar-brand-logo" data-testid="site-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" data-testid="navbar-toggle">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <?php if ($user): ?>
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/dashboard') !== false ? 'active' : '' ?>" href="/pages/dashboard.php" data-testid="nav-dashboard">
                        <i class="bi bi-grid-1x2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/courses') !== false ? 'active' : '' ?>" href="/pages/courses.php" data-testid="nav-courses">
                        <i class="bi bi-book"></i> Courses
                    </a>
                </li>
                <?php if ($isAdmin): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= strpos($_SERVER['REQUEST_URI'], '/admin') !== false ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown" data-testid="nav-admin-dropdown">
                        <i class="bi bi-shield-check"></i> Admin
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin/index.php" data-testid="nav-admin-dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="/admin/analytics.php" data-testid="nav-admin-analytics"><i class="bi bi-graph-up me-2"></i>Analytics</a></li>
                        <li><a class="dropdown-item" href="/admin/candidates.php" data-testid="nav-admin-candidates"><i class="bi bi-person-check me-2"></i>Candidates</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin/courses.php" data-testid="nav-admin-courses"><i class="bi bi-collection me-2"></i>Courses</a></li>
                        <li><a class="dropdown-item" href="/admin/users.php" data-testid="nav-admin-users"><i class="bi bi-people me-2"></i>Users</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/admin/settings.php" data-testid="nav-admin-settings"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown" data-testid="user-menu">
                        <span class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                        <span class="d-none d-md-inline"><?= h($user['name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/pages/profile.php" data-testid="nav-profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/actions/logout.php" data-testid="nav-logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
            <?php else: ?>
            <ul class="navbar-nav ms-auto gap-2">
                <li class="nav-item">
                    <a class="nav-link" href="/pages/login.php" data-testid="nav-login">Login</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm" href="/pages/register.php" data-testid="nav-register">Get Started</a>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="lms-main">
