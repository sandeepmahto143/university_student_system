<?php
/**
 * ---------------------------------------------------------------
 *  XML Component (Module 4)
 * ---------------------------------------------------------------
 *  - regenerate_students_xml(): writes every student row from MySQL into
 *    xml/students.xml (called automatically after Add / Edit / Delete).
 *  - read_students_xml():       reads xml/students.xml back with SimpleXML
 *    so that xml_view.php can display it.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/**
 * Build xml/students.xml from the students table using DOMDocument.
 * Returns the number of student records written.
 */
function regenerate_students_xml(): int
{
    $students = db()->query(
        'SELECT id, reg_no, name, course, semester, email, contact_number
           FROM students ORDER BY id'
    )->fetchAll();

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;

    // <!DOCTYPE university SYSTEM "students.dtd">  -> structure rules
    $impl    = new DOMImplementation();
    $doctype = $impl->createDocumentType('university', '', 'students.dtd');
    $dom->appendChild($doctype);

    // xml-stylesheet processing instruction -> lets a browser show the raw XML as a styled table
    $dom->appendChild($dom->createProcessingInstruction(
        'xml-stylesheet', 'type="text/xsl" href="students.xsl"'
    ));

    $root = $dom->createElement('university');
    $root->setAttribute('name', APP_NAME);
    $root->setAttribute('generated', date('Y-m-d H:i:s'));
    $dom->appendChild($root);

    $list = $dom->createElement('students');
    $list->setAttribute('count', (string) count($students));
    $root->appendChild($list);

    foreach ($students as $s) {
        $node = $dom->createElement('student');
        $node->setAttribute('id', (string) $s['id']);

        $fields = [
            'regNo'         => $s['reg_no'],
            'name'          => $s['name'],
            'course'        => $s['course'],
            'semester'      => $s['semester'],
            'email'         => $s['email'],
            'contactNumber' => $s['contact_number'],
        ];
        foreach ($fields as $tag => $value) {
            $child = $dom->createElement($tag);
            $child->appendChild($dom->createTextNode((string) $value)); // auto-escapes &, <, >
            $node->appendChild($child);
        }
        $list->appendChild($node);
    }

    if ($dom->save(XML_FILE) === false) {
        throw new RuntimeException('Could not write ' . XML_FILE . ' - check folder permissions.');
    }
    return count($students);
}

/**
 * Read xml/students.xml with SimpleXML.
 * Returns ['meta' => [...], 'students' => [...]] or null if the file is missing/invalid.
 */
function read_students_xml(): ?array
{
    if (!is_file(XML_FILE)) {
        return null;
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_file(XML_FILE);
    if ($xml === false) {
        return null;
    }

    $students = [];
    foreach ($xml->students->student as $s) {
        $students[] = [
            'id'             => (string) $s['id'],
            'reg_no'         => (string) $s->regNo,
            'name'           => (string) $s->name,
            'course'         => (string) $s->course,
            'semester'       => (string) $s->semester,
            'email'          => (string) $s->email,
            'contact_number' => (string) $s->contactNumber,
        ];
    }

    return [
        'meta' => [
            'name'      => (string) $xml['name'],
            'generated' => (string) $xml['generated'],
            'count'     => (string) $xml->students['count'],
            'size'      => filesize(XML_FILE),
        ],
        'students' => $students,
    ];
}

/** Validate xml/students.xml against xml/students.dtd. */
function validate_students_xml(): bool
{
    if (!is_file(XML_FILE)) {
        return false;
    }
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->load(XML_FILE, LIBXML_DTDLOAD);
    $valid = $dom->validate();
    libxml_clear_errors();
    return $valid;
}

/** Safe wrapper used after CRUD operations - never breaks the page. */
function sync_xml_silently(): void
{
    try {
        regenerate_students_xml();
    } catch (Throwable $e) {
        error_log('XML sync failed: ' . $e->getMessage());
    }
}
