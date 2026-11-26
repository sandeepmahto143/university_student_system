<?php
/**
 * Shared student form used by add_student.php and edit_student.php.
 * Expects: $student (array), $errors (array), $submitLabel (string)
 */
function student_field_error(array $errors, string $field): string
{
    return isset($errors[$field]) ? e($errors[$field]) : '';
}
function student_group_class(array $errors, string $field): string
{
    return 'form-group' . (isset($errors[$field]) ? ' has-error' : '');
}
?>
<form method="post" id="studentForm" class="card form-card" novalidate data-validate="student">
    <?= csrf_field() ?>
    <div class="card-body">
        <?php if (isset($errors['form'])): ?>
            <div class="alert alert-danger"><?= e($errors['form']) ?></div>
        <?php endif; ?>

        <div class="form-row">
            <div class="<?= student_group_class($errors, 'reg_no') ?>">
                <label for="reg_no">Register Number <span class="req">*</span></label>
                <input type="text" id="reg_no" name="reg_no" maxlength="20"
                       value="<?= e($student['reg_no']) ?>" placeholder="e.g. U03BC24S0009">
                <small class="hint">5-20 capital letters and digits.</small>
                <small class="error-text"><?= student_field_error($errors, 'reg_no') ?></small>
            </div>
            <div class="<?= student_group_class($errors, 'name') ?>">
                <label for="name">Student Name <span class="req">*</span></label>
                <input type="text" id="name" name="name" maxlength="100"
                       value="<?= e($student['name']) ?>" placeholder="e.g. Priya Sharma">
                <small class="error-text"><?= student_field_error($errors, 'name') ?></small>
            </div>
        </div>

        <div class="form-row">
            <div class="<?= student_group_class($errors, 'course') ?>">
                <label for="course">Course <span class="req">*</span></label>
                <select id="course" name="course">
                    <option value="">-- Select course --</option>
                    <?php foreach ($COURSES as $c): ?>
                        <option value="<?= e($c) ?>" <?= $student['course'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="error-text"><?= student_field_error($errors, 'course') ?></small>
            </div>
            <div class="<?= student_group_class($errors, 'semester') ?>">
                <label for="semester">Semester <span class="req">*</span></label>
                <select id="semester" name="semester">
                    <option value="">-- Select semester --</option>
                    <?php foreach ($SEMESTERS as $s): ?>
                        <option value="<?= $s ?>" <?= (string) $student['semester'] === (string) $s ? 'selected' : '' ?>>Semester <?= $s ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="error-text"><?= student_field_error($errors, 'semester') ?></small>
            </div>
        </div>

        <div class="form-row">
            <div class="<?= student_group_class($errors, 'email') ?>">
                <label for="email">Email ID <span class="req">*</span></label>
                <input type="email" id="email" name="email" maxlength="100"
                       value="<?= e($student['email']) ?>" placeholder="student@example.com">
                <small class="error-text"><?= student_field_error($errors, 'email') ?></small>
            </div>
            <div class="<?= student_group_class($errors, 'contact_number') ?>">
                <label for="contact_number">Contact Number <span class="req">*</span></label>
                <input type="tel" id="contact_number" name="contact_number" maxlength="10"
                       value="<?= e($student['contact_number']) ?>" placeholder="10-digit mobile number">
                <small class="error-text"><?= student_field_error($errors, 'contact_number') ?></small>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <a href="students.php" class="btn btn-light">Cancel</a>
        <button type="reset" class="btn btn-light">Reset</button>
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    </div>
</form>
