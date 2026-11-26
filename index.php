<?php
/**
 * ---------------------------------------------------------------
 *  index.php  -  LOGIN PAGE (Splash page of the application)
 * ---------------------------------------------------------------
 *  Module 1: User Login. Existing users log in with username (or email)
 *  and password. New users can follow the link to register.php.
 */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_verify()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (!$errors) {
        // SELECT the user by username OR email (prepared statement)
        $stmt = db()->prepare('SELECT id, full_name, username, password
                                 FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, strtolower($username)]);
        $user = $stmt->fetch();

        // password_verify() compares the typed password with the stored hash
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);               // prevent session fixation
            $_SESSION['user_id']   = (int) $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            redirect('dashboard.php');
        }
        $errors[] = 'Invalid username or password.';
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= e(APP_SHORT) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <section class="auth-hero">
        <div class="auth-hero-content">
            <div class="brand brand-lg">
                <span class="brand-logo">U</span>
                <span class="brand-text"><?= e(APP_SHORT) ?><small>Student Information System</small></span>
            </div>
            <h1>University Student Information &amp; Academic Management System</h1>
            <p>Manage student academic records in one place &mdash; add, edit, delete and view
               student details, backed by MySQL and published as XML.</p>
            <ul class="feature-list">
                <li>Secure login &amp; registration</li>
                <li>Complete student CRUD operations</li>
                <li>MySQL database storage</li>
                <li>Live XML student records page</li>
            </ul>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <h2>Welcome back</h2>
            <p class="muted">Sign in to continue to your dashboard.</p>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php" id="loginForm" novalidate data-validate="login">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" value="<?= e($username) ?>"
                           placeholder="Enter your username" autocomplete="username" autofocus>
                    <small class="error-text"></small>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password" autocomplete="current-password">
                        <button type="button" class="toggle-password" data-target="password">Show</button>
                    </div>
                    <small class="error-text"></small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-switch">New user? <a href="register.php">Create an account</a></p>
            <p class="demo-hint">Demo login: <code>admin</code> / <code>admin123</code></p>
        </div>
    </section>
</div>
<script src="assets/js/validation.js"></script>
</body>
</html>
