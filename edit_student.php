<?php
/**
 * ---------------------------------------------------------------
 *  edit_student.php  -  EDIT STUDENT DETAILS  (UPDATE)
 * ---------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);

// Fetch the existing record
$stmt = db()->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$existing = $stmt->fetch();

if (!$existing) {
    set_flash('danger', 'Student record not found.');
    redirect('students.php');
}

$student = $existing;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student = student_from_post();

    if (!csrf_verify()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    } else {
        $errors = validate_student($student);
        if (!$errors) {
            $errors = check_student_duplicates($student, $id);
        }
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare(
                'UPDATE students
                    SET reg_no = :reg_no, name = :name, course = :course, semester = :semester,
                        email = :email, contact_number = :contact_number,
                        updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id'
            );
            $stmt->execute([
                ':reg_no'         => $student['reg_no'],
                ':name'           => $student['name'],
                ':course'         => $student['course'],
                ':semester'       => (int) $student['semester'],
                ':email'          => $student['email'],
                ':contact_number' => $student['contact_number'],
                ':id'             => $id,
            ]);

            sync_xml_silently();
            set_flash('success', "Student {$student['name']} ({$student['reg_no']}) updated successfully.");
            redirect('students.php');
        } catch (PDOException $e) {
            $errors['form'] = $e->getCode() === '23000'
                ? 'Another student already uses this register number or email.'
                : 'Could not update the student. Please try again.';
        }
    }
}

$pageTitle   = 'Edit Student';
$activePage  = 'students';
$submitLabel = 'Update Student';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Edit Student Details</h1>
        <p class="muted">Editing <strong><?= e($existing['name']) ?></strong> (<?= e($existing['reg_no']) ?>)
           &middot; last updated <?= e($existing['updated_at']) ?></p>
    </div>
    <a href="students.php" class="btn btn-light">&larr; Back to list</a>
</div>

<?php require __DIR__ . '/includes/student_form.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
