<?php
/**
 * HackathonAfrica Mailer
 * Uses SMTP via socket — no Composer/PHPMailer required.
 * Configure SMTP credentials in Admin → Settings → Email.
 */

/**
 * Send an email using configured SMTP settings.
 * Falls back to PHP mail() if SMTP is not configured.
 */
function send_email(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    $settings = get_site_settings();
    $host    = $settings['smtp_host']       ?? '';
    $port    = (int)($settings['smtp_port'] ?? 465);
    $user    = $settings['smtp_user']       ?? '';
    $pass    = $settings['smtp_pass']       ?? '';
    $from    = $settings['smtp_from_email'] ?? $user;
    $fromName= $settings['smtp_from_name']  ?? 'HackathonAfrica';
    $enc     = $settings['smtp_encryption'] ?? 'ssl';

    // Log attempt
    error_log("MAILER: Sending '$subject' to $toEmail");

    // If SMTP not configured, fall back to PHP mail()
    if (empty($host) || empty($user) || empty($pass)) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $fromName <$from>\r\n";
        $result = mail($toEmail, $subject, $htmlBody, $headers);
        error_log("MAILER: mail() result: " . ($result ? 'ok' : 'failed'));
        return $result;
    }

    // SMTP via socket
    try {
        $prefix = ($enc === 'ssl') ? 'ssl://' : 'tls://';
        $sock = fsockopen($prefix . $host, $port, $errno, $errstr, 10);
        if (!$sock) throw new RuntimeException("Connect failed: $errstr ($errno)");

        $read = fn() => fgets($sock, 512);
        $send = function(string $cmd) use ($sock, &$read) {
            fwrite($sock, $cmd . "\r\n");
            return $read();
        };

        $read(); // 220 greeting
        $send("EHLO " . gethostname());
        $read(); $read(); $read(); $read(); $read(); // eat EHLO lines

        $send("AUTH LOGIN");
        $read();
        $send(base64_encode($user));
        $read();
        $send(base64_encode($pass));
        $authResp = $read();
        if (strpos($authResp, '235') === false) {
            throw new RuntimeException("AUTH failed: $authResp");
        }

        $send("MAIL FROM:<$from>");
        $read();
        $send("RCPT TO:<$toEmail>");
        $read();
        $send("DATA");
        $read();

        $boundary = md5(uniqid('', true));
        $message  = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$from>\r\n";
        $message .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <$toEmail>\r\n";
        $message .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "\r\n";
        $message .= chunk_split(base64_encode($htmlBody));
        $message .= "\r\n.";
        $send($message);
        $resp = $read();

        $send("QUIT");
        fclose($sock);

        $ok = strpos($resp, '250') !== false;
        error_log("MAILER: SMTP result: " . ($ok ? 'ok' : 'failed: ' . $resp));
        return $ok;

    } catch (Throwable $e) {
        error_log("MAILER ERROR: " . $e->getMessage());
        return false;
    }
}

// ── Email Templates ───────────────────────────────────────────

/**
 * Wrap content in the branded email shell.
 */
function email_wrap(string $bodyHtml, string $preheader = ''): string {
    $settings  = get_site_settings();
    $color     = $settings['primary_color']  ?? '#F8B526';
    $siteName  = $settings['site_name']      ?? 'HackathonAfrica';
    $logo      = $settings['logo_path']      ?? '/public/img/logo.png';
    $year      = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>$siteName</title>
<style>
  body{margin:0;padding:0;background:#0D1117;font-family:'Helvetica Neue',Arial,sans-serif;color:#E0E0E0}
  .shell{max-width:600px;margin:0 auto;background:#151B23;border:1px solid #30363D;border-radius:8px;overflow:hidden}
  .header{background:#0D1117;padding:28px 32px;border-bottom:3px solid $color;text-align:center}
  .header img{height:44px;width:auto}
  .body{padding:36px 32px}
  h1{color:#fff;font-size:22px;margin:0 0 12px}
  p{color:#B0B8C4;font-size:15px;line-height:1.7;margin:0 0 16px}
  .btn{display:inline-block;background:$color;color:#0D1117!important;font-weight:700;font-size:15px;padding:13px 28px;border-radius:6px;text-decoration:none;margin:8px 0}
  .highlight{background:#1C2333;border-left:4px solid $color;padding:14px 18px;border-radius:4px;margin:20px 0}
  .highlight p{margin:0;color:#E0E0E0}
  .stat{text-align:center;display:inline-block;min-width:100px;margin:8px}
  .stat .num{font-size:26px;font-weight:700;color:$color}
  .stat .lbl{font-size:12px;color:#8B949E;text-transform:uppercase;letter-spacing:0.08em}
  .divider{border:none;border-top:1px solid #30363D;margin:24px 0}
  .footer{padding:20px 32px;text-align:center;border-top:1px solid #30363D}
  .footer p{font-size:12px;color:#8B949E;margin:4px 0}
</style>
</head>
<body>
<div style="display:none;max-height:0;overflow:hidden">$preheader</div>
<table width="100%" cellpadding="0" cellspacing="0"><tr><td style="padding:24px 16px">
<div class="shell">
  <div class="header">
    <div style="font-size:20px;font-weight:800;color:#fff">$siteName</div>
    <div style="font-size:11px;color:#8B949E;letter-spacing:0.12em;margin-top:4px">HACKATHON AFRICA · LEARNING PLATFORM</div>
  </div>
  <div class="body">$bodyHtml</div>
  <div class="footer">
    <p>© $year AfricaPlan Foundation · HackathonAfrica</p>
    <p>You received this because you applied to HackathonAfrica 3.0.</p>
  </div>
</div>
</td></tr></table>
</body>
</html>
HTML;
}

/**
 * 1. Welcome / onboarding email — sent immediately after registration.
 */
function email_welcome(string $toEmail, string $name, string $loginUrl): bool {
    $body = <<<HTML
<h1>Welcome to HackathonAfrica 3.0, $name! 🎉</h1>
<p>Your application is confirmed. You have been enrolled in all three courses and can begin learning <strong>right now</strong> — no waiting, no approval needed.</p>

<div class="highlight">
  <p><strong>Your courses:</strong><br>
  1. HTML — Mastering the Web's Backbone<br>
  2. CSS — Styling the Modern Web<br>
  3. JavaScript — From Fundamentals to Mastery</p>
</div>

<p>Complete all three courses and pass the final assessments to qualify for shortlisting to the <strong>HackathonAfrica Bootcamp</strong>.</p>

<p style="text-align:center">
  <a href="$loginUrl" class="btn">Start Learning Now →</a>
</p>

<hr class="divider">
<p style="font-size:13px"><strong>Scoring system:</strong> Module Quizzes count for 30% of your score. Final Exams count for 70%. You need at least 70% per course and 75% overall to qualify.</p>
<p style="font-size:13px">Speed and consistency matter — top performers are shortlisted. Do your best!</p>
HTML;

    return send_email($toEmail, $name, 'Welcome to HackathonAfrica 3.0 — Start Learning Now', email_wrap($body, 'Your journey starts today. Click to begin your courses.'));
}

/**
 * 2. Course completion email — sent when a student passes all 3 final exams.
 */
function email_all_courses_complete(string $toEmail, string $name, float $compositeScore): bool {
    $scoreColor = $compositeScore >= 75 ? '#22c55e' : '#f59e0b';
    $qualified  = $compositeScore >= 75;
    $status     = $qualified
        ? 'You have <strong>met the qualification threshold</strong> (75%+). You are now in the candidate pool for shortlisting.'
        : 'Your score is below the 75% threshold. You may retake any final exam to improve your score before the deadline.';

    $body = <<<HTML
<h1>You completed all 3 courses, $name!</h1>
<p>Outstanding commitment. Here is your performance summary:</p>

<div style="text-align:center;margin:24px 0">
  <div class="stat"><div class="num" style="color:$scoreColor">{$compositeScore}%</div><div class="lbl">Composite Score</div></div>
</div>

<div class="highlight"><p>$status</p></div>

<p>Shortlisting happens after the application deadline. You will receive an email if you are selected for the <strong>Physical Bootcamp</strong> in Enugu.</p>
<p style="font-size:13px;color:#8B949E">Keep your contact details up to date in your profile — that is how we will reach you.</p>
HTML;

    $subject = $qualified
        ? "🎯 You qualified! HackathonAfrica 3.0 results"
        : "📊 Your HackathonAfrica 3.0 results — review your scores";

    return send_email($toEmail, $name, $subject, email_wrap($body, "Your composite score is {$compositeScore}%."));
}

/**
 * 3. Shortlist / bootcamp invitation email.
 */
function email_bootcamp_invitation(string $toEmail, string $name, array $details): bool {
    $date     = $details['dates']    ?? 'TBA';
    $location = $details['location'] ?? 'HackathonAfrica Lab, Enugu';
    $deadline = $details['rsvp_deadline'] ?? 'TBA';
    $rsvpUrl  = $details['rsvp_url'] ?? '';

    $body = <<<HTML
<h1>Congratulations, $name — you are shortlisted! 🏆</h1>
<p>Out of all applicants, you have been selected to attend the <strong>HackathonAfrica 3.0 Physical Bootcamp</strong>. Your performance earned you this opportunity.</p>

<div class="highlight">
  <p>
    <strong>📅 Dates:</strong> $date<br>
    <strong>📍 Location:</strong> $location<br>
    <strong>⏰ RSVP Deadline:</strong> $deadline
  </p>
</div>

<p>The bootcamp is <strong>3 days</strong> of intensive project work, mentorship, technical interviews, and team challenges. Come ready to build.</p>

<p style="text-align:center">
  <a href="$rsvpUrl" class="btn">Confirm Your Attendance →</a>
</p>

<hr class="divider">
<p><strong>What to bring:</strong> Laptop (charged), enthusiasm, and your best ideas.</p>
<p><strong>What to expect:</strong> Orientation and team formation on Day 1, intensive build phase on Day 2, project demos and interviews on Day 3.</p>
<p style="font-size:13px;color:#8B949E">If you cannot attend, please reply to this email as soon as possible so we can invite the next candidate.</p>
HTML;

    return send_email($toEmail, $name, '🏆 You are shortlisted — HackathonAfrica 3.0 Bootcamp Invitation', email_wrap($body, 'You earned it. Your bootcamp invitation is here.'));
}

/**
 * 4. Not shortlisted — appreciation email.
 */
function email_not_shortlisted(string $toEmail, string $name): bool {
    $body = <<<HTML
<h1>Thank you for applying, $name.</h1>
<p>We have completed our review of all applications for HackathonAfrica 3.0. While you were not selected for this cohort's bootcamp, your effort and commitment were noted.</p>

<div class="highlight">
  <p><strong>Your LMS access remains active.</strong> Keep learning, improve your scores, and apply again for the next cohort — many of our strongest alumni were not selected on their first attempt.</p>
</div>

<p>The tech ecosystem across Africa needs developers like you. Keep building.</p>
<p style="font-size:13px;color:#8B949E">If you have any questions, feel free to reach out to our team.</p>
HTML;

    return send_email($toEmail, $name, 'HackathonAfrica 3.0 — Application Update', email_wrap($body, 'A message from the HackathonAfrica team.'));
}

/**
 * 5. Password reset email — sent when a student requests a password reset.
 */
function email_password_reset(string $toEmail, string $name, string $resetUrl): bool {
    $body = <<<HTML
<h1>Reset your password, $name</h1>
<p>We received a request to reset the password for your HackathonAfrica account. Click the button below to choose a new password.</p>

<p style="text-align:center">
  <a href="$resetUrl" class="btn">Reset My Password →</a>
</p>

<div class="highlight">
  <p style="font-size:13px;margin:0"><strong>This link expires in 1 hour.</strong> If you did not request a password reset, you can safely ignore this email — your password will not change.</p>
</div>

<p style="font-size:13px;color:#8B949E">If the button above does not work, copy and paste this link into your browser:<br>$resetUrl</p>
HTML;

    return send_email($toEmail, $name, 'Reset your HackathonAfrica password', email_wrap($body, 'Click to reset your password. Link expires in 1 hour.'));
}

/**
 * 6. Exam reminder — nudge for students who haven't finished.
 */
function email_reminder(string $toEmail, string $name, int $daysLeft, string $dashboardUrl): bool {
    $urgency = $daysLeft <= 3 ? '⚠️ Urgent: ' : '';
    $body = <<<HTML
<h1>{$urgency}$daysLeft days left to complete your courses, $name.</h1>
<p>The HackathonAfrica 3.0 application deadline is approaching. Make sure you complete all three courses and pass the final exams before time runs out.</p>
<p style="text-align:center">
  <a href="$dashboardUrl" class="btn">Continue Learning →</a>
</p>
<p style="font-size:13px;color:#8B949E">Only completed, qualified applicants are considered for shortlisting. Don't let your hard work go to waste.</p>
HTML;

    return send_email($toEmail, $name, "{$urgency}Complete your HackathonAfrica courses — $daysLeft days left", email_wrap($body, "$daysLeft days left to qualify for HackathonAfrica 3.0."));
}
