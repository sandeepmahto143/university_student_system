<?php
/**
 * ---------------------------------------------------------------
 *  students.php  -  DISPLAY STUDENT DETAILS  (SELECT)
 * ---------------------------------------------------------------
 *  Retrieves and displays all student records, with search and filters.
 *  Each row has Edit and Delete actions.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$search   = trim($_GET['q'] ?? '');
$course   = trim($_GET['course'] ?? '');
$semester = (int) ($_GET['semester'] ?? 0);

// Allowed sort columns (whitelist - never put user input directly in SQL)
$sortable = ['reg_no' => 'reg_no', 'name' => 'name', 'course' => 'course', 'semester' => 'semester'];
$sort     = $sortable[$_GET['sort'] ?? ''] ?? 'reg_no';
$dir      = (($_GET['dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';

// Build the WHERE clause dynamically with placeholders
$where  = [];
$params = [];
if ($search !== '') {
    $where[]  = '(name LIKE ? OR reg_no LIKE ? OR email LIKE ?)';
    $like     = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}
if ($course !== '' && in_array($course, $COURSES, true)) {
    $where[]  = 'course = ?';
    $params[] = $course;
}
if (in_array($semester, $SEMESTERS, true)) {
    $where[]  = 'semester = ?';
    $params[] = $semester;
}

$sql = 'SELECT id, reg_no, name, course, semester, email, contact_number FROM students'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . " ORDER BY $sort $dir";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
$total    = (int) db()->query('SELECT COUNT(*) FROM students')->fetchColumn();

/** Build a column-header link that toggles sort direction. */
function sort_link(string $col, string $label, string $sort, string $dir): string
{
    $params        = $_GET;
    $params['sort'] = $col;
    $params['dir']  = ($sort === $col && $dir === 'ASC') ? 'desc' : 'asc';
    $arrow = $sort === $col ? ($dir === 'ASC' ? ' &#9650;' : ' &#9660;') : '';
    return '<a href="students.php?' . e(http_build_query($params)) . '">' . e($label) . $arrow . '</a>';
}

$pageTitle  = 'Students';
$activePage = 'students';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Student Details</h1>
        <p class="muted">Showing <?= count($students) ?> of <?= $total ?> student record(s).</p>
    </div>
    <a href="add_student.php" class="btn btn-primary">+ Add New Student</a>
</div>

<form class="card filter-bar" method="get" action="students.php">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search by name, register no. or email...">
    <select name="course">
        <option value="">All courses</option>
        <?php foreach ($COURSES as $c): ?>
            <option value="<?= e($c) ?>" <?= $course === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="semester">
        <option value="">All semesters</option>
        <?php foreach ($SEMESTERS as $s): ?>
            <option value="<?= $s ?>" <?= $semester === $s ? 'selected' : '' ?>>Semester <?= $s ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Filter</button>
    <?php if ($search !== '' || $course !== '' || $semester): ?>
        <a href="students.php" class="btn btn-light">Clear</a>
    <?php endif; ?>
</form>

<section class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= sort_link('reg_no', 'Register No.', $sort, $dir) ?></th>
                    <th><?= sort_link('name', 'Student Name', $sort, $dir) ?></th>
                    <th><?= sort_link('course', 'Course', $sort, $dir) ?></th>
                    <th><?= sort_link('semester', 'Semester', $sort, $dir) ?></th>
                    <th>Email ID</th>
                    <th>Contact No.</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= e($s['reg_no']) ?></strong></td>
                    <td><?= e($s['name']) ?></td>
                    <td><span class="badge"><?= e($s['course']) ?></span></td>
                    <td><?= (int) $s['semester'] ?></td>
                    <td><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></td>
                    <td><?= e($s['contact_number']) ?></td>
                    <td class="actions">
                        <a href="edit_student.php?id=<?= (int) $s['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form method="post" action="delete_student.php" class="inline-form delete-form"
                              data-name="<?= e($s['name']) ?>" data-reg="<?= e($s['reg_no']) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$students): ?>
                <tr><td colspan="8" class="empty">
                    No student records found.
                    <?php if ($total === 0): ?><a href="add_student.php">Add the first student</a>.<?php endif; ?>
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
