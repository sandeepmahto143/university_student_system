<?php
/**
 * logout.php - clears the session and returns to the login page.
 */
require_once __DIR__ . '/includes/functions.php';

$_SESSION = [];               // remove all login data
session_regenerate_id(true);  // destroy the old session and issue a new session id

set_flash('success', 'You have been logged out successfully.');
redirect('index.php');
