# HackathonAfrica LMS — Deployment Guide

## Quick Start (SQLite — No DB config needed)

1. Upload the ZIP contents to your cPanel `public_html/` (or a subdomain folder)
2. Visit `https://yourdomain.com/database/setup.php`
3. Click **"Initialize Database"**
4. Login with `admin@hackathon.africa` / `Admin@1234`

> SQLite is the default driver. It creates a file-based database at `database/lms.sqlite`. Good for small-to-medium traffic.

---

## Production Deployment (MySQL — Recommended for cPanel)

### Step 1: Create MySQL Database in cPanel

1. Login to your cPanel
2. Go to **MySQL Databases**
3. Create a new database (e.g. `yourusername_lms`)
4. Create a new database user (e.g. `yourusername_admin`) with a strong password
5. Click **"Add User to Database"** and grant **ALL PRIVILEGES**

### Step 2: Upload Files

1. Download the ZIP and extract it
2. Upload all files to `public_html/` (or your desired directory)
3. Make sure `public/img/uploads/` directory has write permissions: 
   ```
   chmod 755 public/img/uploads/
   ```

### Step 3: Configure Database

Edit `config/database.php` and update these lines:

```php
// Change 'sqlite' to 'mysql'
define('DB_DRIVER', 'mysql');

// Fill in your cPanel MySQL credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'yourusername_lms');        // The database you created
define('DB_USER', 'yourusername_admin');       // The user you created  
define('DB_PASS', 'your_strong_password');     // The password you set
define('DB_CHARSET', 'utf8mb4');
```

### Step 4: Initialize Database

Visit `https://yourdomain.com/database/setup.php` in your browser and click **"Initialize Database"**.

### Step 5: Secure the Setup File

After setup, either:
- Delete `database/setup.php` from your server, OR
- Add password protection via `.htaccess`:
  ```apache
  # database/.htaccess
  <Files "setup.php">
      AuthType Basic
      AuthName "Admin Only"
      AuthUserFile /home/yourusername/.htpasswd
      Require valid-user
  </Files>
  ```

---

## File Permissions

```
public_html/
├── config/          → 644 (read-only is fine)
├── database/        → 755 (needs write for SQLite file)
├── includes/        → 644
├── pages/           → 644
├── admin/           → 644
├── actions/         → 644
├── public/
│   ├── css/         → 644
│   ├── js/          → 644
│   └── img/
│       └── uploads/ → 755 (writable for logo/favicon uploads)
```

---

## Admin Settings

After deployment, login as admin and go to **Admin > Settings** to:
- Change the site name
- Upload a custom logo (PNG, JPG, SVG, WebP — max 2MB)
- Upload a custom favicon
- Reset branding to defaults

---

## Credentials

| Role    | Email                    | Password   |
|---------|--------------------------|------------|
| Admin   | admin@hackathon.africa   | Admin@1234 |
| Student | demo@hackathon.africa    | Demo@1234  |

**Change these passwords immediately after first login!**

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "Database connection error" | Check `config/database.php` credentials match your cPanel MySQL settings |
| "FOREIGN KEY constraint failed" | Run setup from the browser (`/database/setup.php`), not directly as PHP include |
| No courses showing | Run `/database/setup.php` — it seeds all 60 lessons automatically |
| Logo upload fails | Ensure `public/img/uploads/` directory has write permissions (755) |
| 500 Internal Server Error | Check PHP version is 8.0+ in cPanel > Select PHP Version |
