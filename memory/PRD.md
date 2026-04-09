# HackathonAfrica LMS - Product Requirements Document

## Project Overview
A Learning Management System for HackathonAfrica (AfricaPlan Foundation) designed to help African youth learn web development skills and become eligible for hackathon opportunities.

## Original Problem Statement
Build an LMS with:
1. Beginner-friendly courses — introductions, analogies, annotated code examples, common mistakes, more explanations, code exercises, and preview sections
2. Applicants can go through courses (HTML, CSS, JS), take final exams with MCQs + coding exercises
3. Robust admin dashboard with analytics and candidate review for eligibility determination
4. Branding must match AfricaPlan Foundation website (africaplanfoundation.org)
5. Admin should be able to change logo and favicon
6. MySQL support for production deployment on cPanel

## Constraint
Must maintain Vanilla PHP structure. Supports both SQLite (dev) and MySQL (production/cPanel).

## Technical Stack
- **Backend:** PHP 8.x (Vanilla)
- **Database:** SQLite (dev) / MySQL (production) — driver toggle in config/database.php
- **Frontend:** HTML5, CSS3, Bootstrap 5.3
- **Deployment:** cPanel compatible (.zip packaging)

## What's Been Implemented

### Phase 1: Core Platform (Complete)
- [x] Dual-driver DB support (SQLite + MySQL) with auto-adapting schema
- [x] Authentication: login, register, session management, CSRF protection
- [x] Role-based access (student, admin, superadmin)
- [x] Course listing, enrollment, progress tracking
- [x] Lesson viewer with sidebar navigation
- [x] Module-based quiz gating (70% pass required)
- [x] Code exercise editor with live preview
- [x] Admin dashboard with analytics
- [x] Candidate review system

### Phase 2: Content (Complete)
- [x] 3 courses: HTML, CSS, JavaScript
- [x] 4 modules per course, 5 lessons per module (60 lessons total)
- [x] All 60 lessons include: analogies, annotated code, common mistakes, key takeaways
- [x] 12 quizzes with 27 questions

### Phase 3: Branding & Settings (Complete)
- [x] AfricaPlan Foundation branding: Gold/Amber (#F8B526) on Dark Navy (#0D1117)
- [x] Real APF logo and favicon from africaplanfoundation.org
- [x] Admin Settings page with logo/favicon upload (file validation, max 2MB)
- [x] Dynamic logo/favicon across all pages
- [x] site_settings DB table for persistent configuration
- [x] Reset to defaults option

### Phase 4: Form UX Fix (Complete)
- [x] Fixed dropdown icon overlapping form fields (separated .form-control and .form-select CSS)
- [x] Proper padding for select elements (2.5rem right) vs text inputs (1rem)
- [x] Explicit background-image: none on .form-control

### Phase 5: MySQL Production Support (Complete)
- [x] config/database.php supports both SQLite and MySQL drivers
- [x] setup_enhanced.php auto-adapts SQL (AUTOINCREMENT vs AUTO_INCREMENT, CHECK constraints)
- [x] setup.php browser UI shows active driver
- [x] DEPLOYMENT.md with step-by-step cPanel MySQL guide

## Database Schema
- users, courses, modules, lessons, quizzes, quiz_questions, quiz_options
- quiz_attempts, user_lesson_progress, user_enrollments
- coding_exercises, code_submissions
- final_exams, final_exam_questions, final_exam_attempts
- candidate_reviews, admin_notes, activity_log
- site_settings

## Key Files
- `/config/database.php` — Driver toggle (sqlite/mysql) + credentials
- `/database/setup.php` — Browser-based setup UI
- `/database/setup_enhanced.php` — Schema creation (dual-driver)
- `/database/seed_full_content.php` — 60 lessons content seeder
- `/includes/header.php` — Dynamic logo/favicon
- `/includes/functions.php` — Helpers incl. get_site_settings()
- `/admin/settings.php` — Admin settings page
- `/DEPLOYMENT.md` — Production deployment guide

## Prioritized Backlog

### P0 - Critical
- [ ] Implement full final exam functionality (MCQ + coding)

### P1 - High
- [ ] Implement admin review actions (approve/reject candidates)
- [ ] Add more quiz questions per module
- [ ] Code submission storage

### P2 - Medium
- [ ] Advanced code editor (Monaco Editor)
- [ ] CSV export for candidate data
- [ ] Progress certificates
- [ ] Theme customizer (admin color picker)

### P3 - Nice to Have
- [ ] Email notifications
- [ ] Discussion forum

## Test Credentials
- Admin: admin@hackathon.africa / Admin@1234
- Demo Student: demo@hackathon.africa / Demo@1234
