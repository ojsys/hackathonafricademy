</main>

<footer class="lms-footer py-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php $footerSettings = get_site_settings(); ?>
                    <img src="<?= h($footerSettings['logo_path'] ?? '/public/img/logo.png') ?>" alt="AfricaPlan Foundation" style="height: 32px; width: auto;">
                </div>
                <p class="small mb-0" style="color: var(--text-muted);">
                    Empowering African youth through technology education. Learn HTML, CSS, and JavaScript to build the future.
                </p>
            </div>
            <div class="col-md-2">
                <h6 class="fw-700 mb-3 text-white">Learn</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="/pages/courses.php" class="text-muted">All Courses</a></li>
                    <li class="mb-2"><a href="/pages/dashboard.php" class="text-muted">Dashboard</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6 class="fw-700 mb-3 text-white">About</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="https://africaplanfoundation.org" target="_blank" class="text-muted">AfricaPlan Foundation</a></li>
                    <li class="mb-2"><a href="#" class="text-muted">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-700 mb-3 text-white">Ready to Code?</h6>
                <p class="small text-muted mb-3">Start your journey to becoming a developer today.</p>
                <a href="/pages/register.php" class="btn btn-primary btn-sm" data-testid="footer-get-started">
                    Get Started <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
        <hr style="border-color: var(--border); margin: 2rem 0;">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <p class="small text-muted mb-0">&copy; <?= date('Y') ?> HackathonAfrica LMS. Part of AfricaPlan Foundation.</p>
            <p class="small text-muted mb-0">Building Africa's tech future, one developer at a time.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/public/js/main.js"></script>
</body>
</html>
