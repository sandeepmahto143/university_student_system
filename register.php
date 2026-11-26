<?php
/**
 * ---------------------------------------------------------------
 *  register.php  -  SIGN UP PAGE
 * ---------------------------------------------------------------
 *  Module 1: New users create an account. The password is stored as a
 *  secure hash using password_hash() - never as plain text.
 */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$old    = ['full_name' => '', 'username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['full_name'] = preg_replace('/\s+/', ' ', trim($_POST['full_name'] ?? ''));
    $old['username']  = trim($_POST['username'] ?? '');
    $old['email']     = strtolower(trim($_POST['email'] ?? ''));
    $password         = $_POST['password'] ?? '';
    $confirm          = $_POST['confirm_password'] ?? '';

    // ---------- server-side validation ----------
    if (!csrf_verify()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }
    if (!preg_match('/^[A-Za-z][A-Za-z .\'-]{1,99}$/', $old['full_name'])) {
        $errors['full_name'] = 'Enter your full name (letters and spaces only).';
    }
    if (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $old['username'])) {
        $errors['username'] = 'Username must be 4-30 characters: letters, digits or underscore.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (strlen($password) < 6 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Password must be at least 6 characters with a letter and a number.';
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // ---------- uniqueness check ----------
    if (!$errors) {
        $stmt = db()->prepare('SELECT username, email FROM users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = ?');
        $stmt->execute([$old['username'], $old['email']]);
        foreach ($stmt->fetchAll() as $row) {
            if (strcasecmp($row['username'], $old['username']) === 0) {
                $errors['username'] = 'This username is already taken.';
            }
            if (strcasecmp($row['email'], $old['email']) === 0) {
                $errors['email'] = 'An account with this email already exists.';
            }
        }
    }

    // ---------- INSERT the new user ----------
    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (full_name, username, email, password)
                               VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $old['full_name'],
            $old['username'],
            $old['email'],
            password_hash($password, PASSWORD_DEFAULT),
        ]);
        set_flash('success', 'Account created successfully! Please log in.');
        redirect('index.php');
    }
}

/** Small helper to print a field error. */
function field_error(array $errors, string $field): string
{
    return isset($errors[$field]) ? e($errors[$field]) : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | <?= e(APP_SHORT) ?></title>
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
            <h1>Create your account</h1>
            <p>Register once to access the student management dashboard. Your password is
               encrypted with a secure one-way hash before it is stored in the database.</p>
            <ul class="feature-list">
                <li>Takes less than a minute</li>
                <li>Passwords stored using bcrypt hashing</li>
                <li>Access all student records instantly</li>
            </ul>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-card">
            <h2>Sign up</h2>
            <p class="muted">Fill in the details below to register.</p>

            <?php if (isset($errors['form'])): ?>
                <div class="alert alert-danger"><?= e($errors['form']) ?></div>
            <?php endif; ?>

            <form method="post" action="register.php" id="registerForm" novalidate data-validate="register">
                <?= csrf_field() ?>
                <div class="form-group <?= isset($errors['full_name']) ? 'has-error' : '' ?>">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>"
                           placeholder="e.g. Priya Sharma" autocomplete="name">
                    <small class="error-text"><?= field_error($errors, 'full_name') ?></small>
                </div>

                <div class="form-row">
                    <div class="form-group <?= isset($errors['username']) ? 'has-error' : '' ?>">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?= e($old['username']) ?>"
                               placeholder="e.g. priya_s" autocomplete="username">
                        <small class="error-text"><?= field_error($errors, 'username') ?></small>
                    </div>
                    <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= e($old['email']) ?>"
                               placeholder="you@example.com" autocomplete="email">
                        <small class="error-text"><?= field_error($errors, 'email') ?></small>
                    </div>
                </div>

                <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input type="password" id="password" name="password"
                               placeholder="At least 6 characters" autocomplete="new-password">
                        <button type="button" class="toggle-password" data-target="password">Show</button>
                    </div>
                    <div class="strength" id="strength"><span></span></div>
                    <small class="error-text"><?= field_error($errors, 'password') ?></small>
                </div>

                <div class="form-group <?= isset($errors['confirm_password']) ? 'has-error' : '' ?>">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="Re-enter password" autocomplete="new-password">
                    <small class="error-text"><?= field_error($errors, 'confirm_password') ?></small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-switch">Already have an account? <a href="index.php">Log in</a></p>
        </div>
    </section>
</div>
<script src="assets/js/validation.js"></script>
</body>
</html>
