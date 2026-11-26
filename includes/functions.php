<?php
/**
 * ---------------------------------------------------------------
 *  Common helper functions: sessions, security, flash messages
 * ---------------------------------------------------------------
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/xml_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    /*
     * Preview-only fallback (enabled in config.local.php, never on XAMPP):
     * if the browser blocks cookies - e.g. when the app is shown inside an
     * embedded preview frame - PHP carries the session id in links and forms.
     */
    if (defined('SESSION_URL_FALLBACK') && SESSION_URL_FALLBACK) {
        ini_set('session.use_strict_mode', '1');   // reject made-up session ids
        ini_set('session.use_only_cookies', '0');
        ini_set('session.use_trans_sid', '1');
    }

    // Secure session cookie: not readable by JavaScript, sent only to this site.
    session_set_cookie_params([
        'lifetime' => 0,          // until the browser is closed
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Escape output to prevent XSS (Cross-Site Scripting). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Redirect to another page and stop the script. */
function redirect(string $url): void
{
    // Preview fallback: keep the session id in the URL when no cookie was received
    if (defined('SESSION_URL_FALLBACK') && SESSION_URL_FALLBACK
        && session_status() === PHP_SESSION_ACTIVE && !isset($_COOKIE[session_name()])) {
        $url .= (strpos($url, '?') === false ? '?' : '&')
              . urlencode(session_name()) . '=' . urlencode(session_id());
    }
    header('Location: ' . $url);
    exit;
}

/** Is a user logged in? */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/** Protect a page: only logged-in users may open it. */
function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to continue.');
        redirect('index.php');
    }
}

/** Store a one-time message to show on the next page. */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Read (and remove) the one-time message. */
function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/** CSRF token - protects forms from being submitted by other websites. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    return isset($_POST['csrf_token'])
        && hash_equals(csrf_token(), (string) $_POST['csrf_token']);
}

/**
 * Server-side validation of the student form.
 * (Client-side validation is done in assets/js/validation.js, but the
 *  server must ALWAYS validate again because JavaScript can be disabled.)
 *
 * @return array list of error messages keyed by field name
 */
function validate_student(array $data): array
{
    global $COURSES, $SEMESTERS;
    $errors = [];

    if (!preg_match('/^[A-Z0-9]{5,20}$/', $data['reg_no'])) {
        $errors['reg_no'] = 'Register number must be 5-20 capital letters/digits (e.g. U03BC24S0001).';
    }
    if (!preg_match('/^[A-Za-z][A-Za-z .\'-]{1,99}$/', $data['name'])) {
        $errors['name'] = 'Name must contain only letters, spaces, dots or hyphens (2-100 characters).';
    }
    if (!in_array($data['course'], $COURSES, true)) {
        $errors['course'] = 'Please select a valid course.';
    }
    if (!in_array((int) $data['semester'], $SEMESTERS, true)) {
        $errors['semester'] = 'Please select a semester between 1 and 8.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 100) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (!preg_match('/^[6-9][0-9]{9}$/', $data['contact_number'])) {
        $errors['contact_number'] = 'Contact number must be a valid 10-digit mobile number.';
    }

    return $errors;
}

/** Read & tidy the student form fields from $_POST. */
function student_from_post(): array
{
    return [
        'reg_no'         => strtoupper(trim($_POST['reg_no'] ?? '')),
        'name'           => preg_replace('/\s+/', ' ', trim($_POST['name'] ?? '')),
        'course'         => trim($_POST['course'] ?? ''),
        'semester'       => trim($_POST['semester'] ?? ''),
        'email'          => strtolower(trim($_POST['email'] ?? '')),
        'contact_number' => preg_replace('/\D/', '', $_POST['contact_number'] ?? ''),
    ];
}

/**
 * Check that reg_no / email are not already used by another student.
 * $ignoreId is used while editing so the record does not clash with itself.
 */
function check_student_duplicates(array $data, int $ignoreId = 0): array
{
    $errors = [];
    $stmt = db()->prepare('SELECT reg_no, email FROM students
                            WHERE (reg_no = ? OR email = ?) AND id <> ?');
    $stmt->execute([$data['reg_no'], $data['email'], $ignoreId]);
    foreach ($stmt->fetchAll() as $row) {
        if ($row['reg_no'] === $data['reg_no']) {
            $errors['reg_no'] = 'This register number already exists.';
        }
        if (strtolower($row['email']) === $data['email']) {
            $errors['email'] = 'This email is already registered to another student.';
        }
    }
    return $errors;
}
