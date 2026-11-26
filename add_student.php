<?php
/**
 * ---------------------------------------------------------------
 *  add_student.php  -  ADD NEW STUDENT  (INSERT)
 * ---------------------------------------------------------------
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$student = ['reg_no' => '', 'name' => '', 'course' => '', 'semester' => '', 'email' => '', 'contact_number' => ''];
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student = student_from_post();

    if (!csrf_verify()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    } else {
        $errors = validate_student($student);
        if (!$errors) {
            $errors = check_student_duplicates($student);
        }
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare(
                'INSERT INTO students (reg_no, name, course, semester, email, contact_number)
                 VALUES (:reg_no, :name, :course, :semester, :email, :contact_number)'
            );
            $stmt->execute([
                ':reg_no'         => $student['reg_no'],
                ':name'           => $student['name'],
                ':course'         => $student['course'],
                ':semester'       => (int) $student['semester'],
                ':email'          => $student['email'],
                ':contact_number' => $student['contact_number'],
            ]);

            sync_xml_silently();   // keep xml/students.xml up to date
            set_flash('success', "Student {$student['name']} ({$student['reg_no']}) added successfully.");
            redirect('students.php');
        } catch (PDOException $e) {
            // 23000 = integrity constraint violation (duplicate key)
            $errors['form'] = $e->getCode() === '23000'
                ? 'A student with this register number or email already exists.'
                : 'Could not save the student. Please try again.';
        }
    }
}

$pageTitle   = 'Add Student';
$activePage  = 'add';
$submitLabel = 'Save Student';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Add New Student</h1>
        <p class="muted">Enter the student details and click <strong>Save Student</strong>. Fields marked * are required.</p>
    </div>
    <a href="students.php" class="btn btn-light">&larr; Back to list</a>
</div>

<?php require __DIR__ . '/includes/student_form.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
