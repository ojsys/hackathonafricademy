# HackathonAfrica LMS

A Learning Management System built for HackathonAfrica (AfricaPlan Foundation) to help African youth learn web development skills and become eligible for hackathon opportunities.

## Features

### For Students
- **Beginner-friendly courses** in HTML, CSS, and JavaScript
- **Rich content** with analogies, annotated code examples, and common mistakes to avoid
- **Interactive code exercises** with live preview
- **Progress tracking** through lessons and modules
- **Quiz assessments** after each module
- **Final exams** with MCQs and coding exercises
- **Eligibility tracking** for HackathonAfrica program

### For Admins
- **Analytics dashboard** with completion rates, scores, and trends
- **Candidate review system** to evaluate program eligibility
- **User management** and course administration
- **Quiz and exam management**

## Tech Stack

- **Backend:** PHP 8.x
- **Database:** SQLite (easy deployment, no MySQL setup required)
- **Frontend:** HTML5, CSS3, Bootstrap 5.3
- **Fonts:** Outfit, IBM Plex Sans, JetBrains Mono

## Installation (cPanel)

### 1. Upload Files
Upload all project files to your cPanel `public_html` directory (or subdirectory).

### 2. Initialize Database
Visit `https://yourdomain.com/database/setup_enhanced.php` in your browser OR run via SSH:
```bash
php database/setup_enhanced.php
```

### 3. Set Permissions
Ensure the `database/` directory is writable:
```bash
chmod 755 database/
chmod 644 database/lms.sqlite
```

### 4. Default Credentials
- **Admin:** `admin@hackathon.africa` / `Admin@1234`
- **Demo Student:** `demo@hackathon.africa` / `Demo@1234`

## Directory Structure

```
/
├── actions/           # Form action handlers
├── admin/             # Admin dashboard pages
│   ├── analytics.php  # Analytics dashboard
│   ├── candidates.php # Candidate review
│   └── ...
├── config/            # Configuration files
├── database/          # SQLite database & setup
├── includes/          # Shared PHP includes
├── pages/             # Student-facing pages
├── public/            # Static assets (CSS, JS)
└── index.php          # Landing page
```

## Course Content

The LMS includes comprehensive beginner-friendly content for:

1. **HTML Fundamentals** (8 hours)
   - How the Web Works
   - Setting Up Your Environment
   - Document Structure
   - Headings, Paragraphs, and Text
   - And more...

2. **CSS Fundamentals** (10 hours)
   - Introduction to CSS
   - Selectors and Properties
   - Box Model
   - Flexbox and Grid
   - Responsive Design

3. **JavaScript Fundamentals** (15 hours)
   - What is JavaScript?
   - Variables and Data Types
   - Functions and Scope
   - DOM Manipulation
   - And more...

## Eligibility Criteria

Candidates become eligible for HackathonAfrica when they:
1. Complete all 3 courses (HTML, CSS, JavaScript)
2. Pass all module quizzes
3. Pass final exams with 70%+ score
4. Receive admin approval (optional additional review)

## License

Built for AfricaPlan Foundation (africaplanfoundation.org).

## Support

For questions or issues, contact the HackathonAfrica team.
