<?php
/**
 * ---------------------------------------------------------------
 *  xml_view.php  -  XML PAGE  (Module 4)
 * ---------------------------------------------------------------
 *  Reads student records from the XML document xml/students.xml using
 *  SimpleXML and displays them. The XML file is regenerated from MySQL
 *  automatically after every Add / Edit / Delete, and can also be
 *  regenerated manually using the button on this page.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

// Manual "Regenerate XML" button
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        set_flash('danger', 'Invalid request.');
    } else {
        try {
            $n = regenerate_students_xml();
            set_flash('success', "students.xml regenerated from the database ($n record(s)).");
        } catch (Throwable $e) {
            set_flash('danger', 'Could not generate XML: ' . $e->getMessage());
        }
    }
    redirect('xml_view.php');
}

// Create the XML file the first time if it does not exist
if (!is_file(XML_FILE)) {
    sync_xml_silently();
}

$xmlData   = read_students_xml();
$isValid   = validate_students_xml();
$dbCount   = (int) db()->query('SELECT COUNT(*) FROM students')->fetchColumn();
$xmlSource = is_file(XML_FILE) ? file_get_contents(XML_FILE) : '';
$inSync    = $xmlData && count($xmlData['students']) === $dbCount;

$pageTitle  = 'XML Records';
$activePage = 'xml';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>XML Student Records</h1>
        <p class="muted">Student details stored in and displayed from the XML document
            <code>xml/students.xml</code>.</p>
    </div>
    <div class="btn-group">
        <a href="xml/students.xml" target="_blank" class="btn btn-light">Open students.xml &#8599;</a>
        <a href="xml/students.xml" download class="btn btn-light">Download</a>
        <form method="post" class="inline-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">&#8635; Regenerate from MySQL</button>
        </form>
    </div>
</div>

<?php if (!$xmlData): ?>
    <div class="alert alert-warning">The XML document could not be read. Click <strong>Regenerate from MySQL</strong> to create it.</div>
<?php else: ?>

<section class="xml-meta">
    <div class="meta-item"><span>Root element</span><strong>&lt;university&gt;</strong></div>
    <div class="meta-item"><span>Records in XML</span><strong><?= count($xmlData['students']) ?></strong></div>
    <div class="meta-item"><span>Generated on</span><strong><?= e($xmlData['meta']['generated']) ?></strong></div>
    <div class="meta-item"><span>File size</span><strong><?= number_format($xmlData['meta']['size'] / 1024, 2) ?> KB</strong></div>
    <div class="meta-item">
        <span>DTD validation</span>
        <strong class="<?= $isValid ? 'text-success' : 'text-danger' ?>"><?= $isValid ? '&#10004; Valid' : '&#10008; Invalid' ?></strong>
    </div>
    <div class="meta-item">
        <span>Sync with MySQL</span>
        <strong class="<?= $inSync ? 'text-success' : 'text-warning' ?>"><?= $inSync ? '&#10004; In sync' : '&#9888; Out of sync' ?></strong>
    </div>
</section>

<div class="tabs" data-tabs>
    <button class="tab active" data-tab="tab-table">Table View</button>
    <button class="tab" data-tab="tab-source">XML Source</button>
    <button class="tab" data-tab="tab-structure">Structure</button>
</div>

<section class="card tab-panel active" id="tab-table">
    <div class="card-header">
        <h2>Students (read from XML using SimpleXML)</h2>
        <input type="search" id="xmlSearch" class="input-sm" placeholder="Filter rows...">
    </div>
    <div class="table-responsive">
        <table class="table" id="xmlTable">
            <thead>
                <tr><th>#</th><th>Register No.</th><th>Name</th><th>Course</th><th>Semester</th><th>Email ID</th><th>Contact No.</th></tr>
            </thead>
            <tbody>
            <?php foreach ($xmlData['students'] as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= e($s['reg_no']) ?></strong></td>
                    <td><?= e($s['name']) ?></td>
                    <td><span class="badge"><?= e($s['course']) ?></span></td>
                    <td><?= e($s['semester']) ?></td>
                    <td><?= e($s['email']) ?></td>
                    <td><?= e($s['contact_number']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$xmlData['students']): ?>
                <tr><td colspan="7" class="empty">The XML document has no student records.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card tab-panel" id="tab-source">
    <div class="card-header">
        <h2>xml/students.xml</h2>
        <button type="button" class="btn btn-sm btn-light" id="copyXml">Copy</button>
    </div>
    <pre class="code-block" id="xmlSource"><?= e($xmlSource) ?></pre>
</section>

<section class="card tab-panel" id="tab-structure">
    <div class="card-header"><h2>XML Document Structure</h2></div>
    <div class="card-body">
        <pre class="code-block light">&lt;university name="..." generated="..."&gt;
  &lt;students count="N"&gt;
    &lt;student id="1"&gt;
      &lt;regNo&gt;U03BC24S0001&lt;/regNo&gt;
      &lt;name&gt;Student Name&lt;/name&gt;
      &lt;course&gt;BCA&lt;/course&gt;
      &lt;semester&gt;5&lt;/semester&gt;
      &lt;email&gt;student@example.com&lt;/email&gt;
      &lt;contactNumber&gt;9876543210&lt;/contactNumber&gt;
    &lt;/student&gt;
    ...
  &lt;/students&gt;
&lt;/university&gt;</pre>
        <ul class="info-list">
            <li><strong>students.xml</strong> &ndash; the XML document holding the student records.</li>
            <li><strong>students.dtd</strong> &ndash; Document Type Definition describing the allowed elements; used to validate the XML.</li>
            <li><strong>students.xsl</strong> &ndash; XSLT stylesheet; when <a href="xml/students.xml" target="_blank">students.xml</a> is opened directly, the browser renders it as a formatted table.</li>
            <li>The file is regenerated automatically by PHP (<code>DOMDocument</code>) after every Add, Edit and Delete operation, and read back here using <code>SimpleXML</code>.</li>
        </ul>
    </div>
</section>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
