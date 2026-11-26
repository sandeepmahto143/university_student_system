<?php
/**
 * Common page header + navigation bar for logged-in pages.
 * Set $pageTitle and $activePage before including this file.
 */
$pageTitle  = $pageTitle  ?? 'Dashboard';
$activePage = $activePage ?? '';
$flash      = get_flash();

$navItems = [
    'dashboard' => ['dashboard.php',    'Dashboard',    '&#9638;'],
    'students'  => ['students.php',     'Students',     '&#9776;'],
    'add'       => ['add_student.php',  'Add Student',  '&#43;'],
    'xml'       => ['xml_view.php',     'XML Records',  '&lt;/&gt;'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(APP_SHORT) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="navbar">
    <div class="container navbar-inner">
        <a href="dashboard.php" class="brand">
            <span class="brand-logo">U</span>
            <span class="brand-text"><?= e(APP_SHORT) ?><small>Student Information System</small></span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">&#9776;</button>

        <nav class="nav-links" id="navLinks">
            <?php foreach ($navItems as $key => [$href, $label, $icon]): ?>
                <a href="<?= $href ?>" class="<?= $activePage === $key ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $icon ?></span><?= $label ?>
                </a>
            <?php endforeach; ?>
            <span class="nav-user" title="Logged in as <?= e($_SESSION['username'] ?? '') ?>">
                <span class="avatar"><?= e(strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1))) ?></span>
                <?= e($_SESSION['full_name'] ?? '') ?>
            </span>
            <a href="logout.php" class="btn btn-sm btn-outline-light">Logout</a>
        </nav>
    </div>
</header>

<main class="container page">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" data-autohide>
            <?= e($flash['message']) ?>
            <button type="button" class="alert-close" aria-label="Close">&times;</button>
        </div>
    <?php endif; ?>
