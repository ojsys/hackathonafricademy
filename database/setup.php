<?php
/**
 * HackathonAfrica LMS - Browser-based Database Setup
 * Access this file in your browser to initialize the database
 * 
 * URL: https://yourdomain.com/database/setup.php
 */
require_once __DIR__ . '/../config/database.php';

$driver = DB_DRIVER;
$alreadySetup = false;

if ($driver === 'sqlite') {
    $alreadySetup = file_exists(SQLITE_PATH);
} else {
    try {
        $pdo = db();
        $count = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount();
        $alreadySetup = $count > 0;
    } catch (Exception $e) {
        // DB might not be accessible yet
    }
}

// Handle POST to run setup
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    try {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        ob_start();
        include __DIR__ . '/setup_enhanced.php';
        $output = ob_get_clean();
        
        $courseCount = db()->query('SELECT COUNT(*) FROM courses')->fetchColumn();
        
        if ($courseCount > 0) {
            $message = "Database setup completed successfully! Created {$courseCount} courses with 60 lessons.";
        } else {
            $error = "Setup completed but no courses were created. Output: " . substr($output, 0, 500);
        }
    } catch (Exception $e) {
        $error = "Setup failed: " . $e->getMessage();
    } catch (Error $e) {
        $error = "Setup error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HackathonAfrica LMS - Database Setup</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0D1117;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 540px;
            width: 100%;
            background: #151B23;
            border: 1px solid #2A3040;
            border-radius: 8px;
            padding: 40px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        .brand img { height: 40px; width: auto; }
        h1 { font-size: 22px; }
        .driver-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }
        .driver-badge.sqlite { background: rgba(248,181,38,0.15); color: #F8B526; border: 1px solid rgba(248,181,38,0.3); }
        .driver-badge.mysql { background: rgba(46,160,67,0.15); color: #2EA043; border: 1px solid rgba(46,160,67,0.3); }
        p { color: #A0AEC0; margin-bottom: 16px; line-height: 1.6; font-size: 14px; }
        .status {
            padding: 16px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
        }
        .status.success { background: rgba(46,160,67,0.1); border: 1px solid rgba(46,160,67,0.3); color: #2EA043; }
        .status.warning { background: rgba(248,181,38,0.1); border: 1px solid rgba(248,181,38,0.3); color: #F8B526; }
        .status.error { background: rgba(255,68,68,0.1); border: 1px solid rgba(255,68,68,0.3); color: #FF4444; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #F8B526;
            color: #0D1117;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
        }
        .btn:hover { background: #D49A10; }
        .btn-secondary {
            background: transparent;
            border: 1px solid #2A3040;
            color: #fff;
        }
        .btn-secondary:hover { background: #1C2333; }
        .credentials {
            background: #0D1117;
            padding: 20px;
            margin-top: 20px;
            border-radius: 6px;
            border: 1px solid #2A3040;
        }
        .credentials h3 { font-size: 12px; color: #6B7A90; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px; }
        .cred-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #1C2333; font-size: 14px; }
        .cred-item:last-child { border-bottom: none; }
        .cred-label { color: #A0AEC0; }
        .cred-value { font-family: monospace; color: #F8B526; }
        .buttons { display: flex; gap: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">
            <img src="/public/img/logo.png" alt="AfricaPlan Foundation">
        </div>
        <h1>Database Setup</h1>
        <br>
        
        <span class="driver-badge <?= $driver ?>"><?= strtoupper($driver) ?> Driver Active</span>
        
        <?php if ($message): ?>
        <div class="status success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="status error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($alreadySetup && !$message): ?>
        <div class="status warning">
            <strong>Database already exists!</strong><br>
            Running setup again will reset all data.
        </div>
        <?php endif; ?>
        
        <p>This will initialize the HackathonAfrica LMS database with 3 courses (HTML, CSS, JavaScript), 60 beginner-friendly lessons, 12 quizzes, and admin/demo accounts.</p>
        
        <?php if ($driver === 'mysql'): ?>
        <p><strong>MySQL:</strong> Make sure you have created the database <code><?= DB_NAME ?></code> and the user <code><?= DB_USER ?></code> has full privileges on it.</p>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="setup" value="1">
            <button type="submit" class="btn" onclick="return confirm('<?= $alreadySetup ? 'This will reset all existing data. Continue?' : 'Initialize the database now?' ?>')">
                <?= $alreadySetup ? 'Reset & Reinitialize Database' : 'Initialize Database' ?>
            </button>
        </form>
        
        <?php if ($message): ?>
        <div class="credentials">
            <h3>Login Credentials</h3>
            <div class="cred-item">
                <span class="cred-label">Admin Email:</span>
                <span class="cred-value">admin@hackathon.africa</span>
            </div>
            <div class="cred-item">
                <span class="cred-label">Admin Password:</span>
                <span class="cred-value">Admin@1234</span>
            </div>
            <div class="cred-item">
                <span class="cred-label">Demo Student:</span>
                <span class="cred-value">demo@hackathon.africa</span>
            </div>
            <div class="cred-item">
                <span class="cred-label">Demo Password:</span>
                <span class="cred-value">Demo@1234</span>
            </div>
        </div>
        
        <div class="buttons">
            <a href="/" class="btn">Go to Homepage</a>
            <a href="/pages/login.php" class="btn btn-secondary">Login</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
