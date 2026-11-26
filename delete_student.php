<?php
/**
 * ---------------------------------------------------------------
 *  delete_student.php  -  DELETE STUDENT DETAILS  (DELETE)
 * ---------------------------------------------------------------
 *  Accepts only POST requests (with a CSRF token) so that a record can
 *  never be deleted just by visiting a link. A JavaScript confirm box is
 *  shown before the form is submitted (see assets/js/validation.js).
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    set_flash('danger', 'Invalid delete request.');
    redirect('students.php');
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = db()->prepare('SELECT name, reg_no FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('danger', 'Student record not found.');
    redirect('students.php');
}

$stmt = db()->prepare('DELETE FROM students WHERE id = ?');
$stmt->execute([$id]);

sync_xml_silently();
set_flash('success', "Student {$student['name']} ({$student['reg_no']}) deleted successfully.");
redirect('students.php');
