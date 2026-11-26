<?php
/**
 * ---------------------------------------------------------------
 *  dashboard.php  -  Home page after login
 * ---------------------------------------------------------------
 *  Shows summary statistics generated dynamically from MySQL.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$pdo = db();

$totalStudents = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$totalUsers    = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

// Course-wise count (GROUP BY)
$byCourse = $pdo->query('SELECT course, COUNT(*) AS total
                           FROM students GROUP BY course ORDER BY total DESC, course')->fetchAll();

// Five most recently added students
$recent = $pdo->query('SELECT id, reg_no, name, course, semester
                         FROM students ORDER BY id DESC LIMIT 5')->fetchAll();

$xmlData  = read_students_xml();
$xmlCount = $xmlData ? count($xmlData['students']) : 0;
$maxCount = $byCourse ? max(array_column($byCourse, 'total')) : 1;

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="muted">Hello <?= e($_SESSION['full_name']) ?>, here is an overview of the student records.</p>
    </div>
    <a href="add_student.php" class="btn btn-primary">+ Add New Student</a>
</div>

<section class="stats-grid">
    <div class="stat-card stat-blue">
        <div class="stat-icon">&#127891;</div>
        <div><span class="stat-value"><?= $totalStudents ?></span><span class="stat-label">Total Students</span></div>
    </div>
    <div class="stat-card stat-green">
        <div class="stat-icon">&#128218;</div>
        <div><span class="stat-value"><?= count($byCourse) ?></span><span class="stat-label">Active Courses</span></div>
    </div>
    <div class="stat-card stat-purple">
        <div class="stat-icon">&#128100;</div>
        <div><span class="stat-value"><?= $totalUsers ?></span><span class="stat-label">Registered Users</span></div>
    </div>
    <div class="stat-card stat-orange">
        <div class="stat-icon">&#128196;</div>
        <div><span class="stat-value"><?= $xmlCount ?></span><span class="stat-label">Records in XML</span></div>
    </div>
</section>

<div class="grid-2">
    <section class="card">
        <div class="card-header">
            <h2>Students by Course</h2>
        </div>
        <div class="card-body">
            <?php if (!$byCourse): ?>
                <p class="muted">No students yet.</p>
            <?php else: ?>
                <ul class="bar-chart">
                    <?php foreach ($byCourse as $row): ?>
                        <li>
                            <span class="bar-label"><?= e($row['course']) ?></span>
                            <span class="bar-track">
                                <span class="bar-fill" style="width: <?= round($row['total'] / $maxCount * 100) ?>%"></span>
                            </span>
                            <span class="bar-value"><?= (int) $row['total'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2>Recently Added</h2>
            <a href="students.php" class="link">View all &rarr;</a>
        </div>
        <div class="card-body no-pad">
            <table class="table table-compact">
                <thead><tr><th>Reg. No.</th><th>Name</th><th>Course</th><th>Sem</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $s): ?>
                    <tr>
                        <td><strong><?= e($s['reg_no']) ?></strong></td>
                        <td><?= e($s['name']) ?></td>
                        <td><span class="badge"><?= e($s['course']) ?></span></td>
                        <td><?= (int) $s['semester'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recent): ?>
                    <tr><td colspan="4" class="empty">No students yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<section class="quick-actions">
    <a href="add_student.php" class="action-tile"><span>&#10133;</span>Add Student</a>
    <a href="students.php" class="action-tile"><span>&#128203;</span>View / Edit / Delete</a>
    <a href="xml_view.php" class="action-tile"><span>&lt;/&gt;</span>XML Student Records</a>
    <a href="xml/students.xml" target="_blank" class="action-tile"><span>&#128462;</span>Open students.xml</a>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
