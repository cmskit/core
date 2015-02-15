<?php
/**
 * super simple routines to register user as superroot if not available
 */


require_once 'inc/php/session.php';
require_once 'inc/login/loginfunctions.php';

// redirect back to login if the project is not valid
if (!file_exists($projectPath . '/objects/__database.php')) {
    goToIndex($projectName, 'project_unknown');
}

// start verification
$_SESSION[$projectName] = verifyUser(array(), $_POST, $projectPath, $projectName);

//print_r($_SESSION[$projectName]);exit;

// relocate this Page to kill $_POST
if (!isset($_SESSION[$projectName]['objectOptions'])) {
    $_SESSION[$projectName] = collectObjects($_SESSION[$projectName], $lang);
}

header('location: backend.php?project=' . $projectName);

exit();