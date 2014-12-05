<?php
/**
 * general Settings + Functions included by all Admin-Wizards
 *
 *
 */
require_once dirname(__DIR__) . '/inc/php/session.php';
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
//error_reporting(0);

require_once dirname(__DIR__) . '/inc/php/autoload.php';

// disabling magic quotes at runtime
if (get_magic_quotes_gpc()) {
    function stripslashes_gpc(&$value)
    {
        $value = stripslashes($value);
    }

    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    //array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    //array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

$projectName = preg_replace('/\W/', '', $_GET['project']);
if (!isset($_SESSION[$projectName])) exit('not logged in');

$backend = dirname(__DIR__); // backend-path
$frontend = dirname($backend) . '/projects/' . $projectName; // project-path
$projectPath = $frontend . '/objects/';
$wizard = dirname($_SERVER['SCRIPT_FILENAME']); //path of actual wizard

$LL = array();
$lang = (!empty($_GET['lang'])) ? substr($_GET['lang'], 0, 2) : $_SESSION[$projectName]['lang'];

@include $wizard . '/locales/' . $lang . '.php';

$HTML = '';

// Access-Control BEGIN
require_once $backend . '/inc/global_configuration.php';

// check if user is superroot
$superroot = ($_SESSION[$projectName]['root'] == md5($_SERVER['REMOTE_ADDR'] . $super[1]));

if (
    !$_SESSION[$projectName]['root']
    ||
    (
        substr(basename($wizard), 0, 1) == '_' // Admin-Wizards beginning with "_" are for Super-Admins only!
        &&
        !$superroot
    )
) {
    exit('You are not allowed to access this service!');
}
// Access-Control END

/**
 *
 */
function relativePath($from, $to, $ps = DIRECTORY_SEPARATOR)
{
    $arFrom = explode($ps, rtrim($from, $ps));
    $arTo = explode($ps, rtrim($to, $ps));
    while (count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0])) {
        array_shift($arFrom);
        array_shift($arTo);
    }
    return str_pad('', count($arFrom) * 3, '..' . $ps) . implode($ps, $arTo);
}

/**
 *
 */
if (!function_exists('L')) {
    function L($str)
    {
        global $LL, $wizard;

        if (isset($LL[$str])) {
            return $LL[$str];
        } else {
            //file_put_contents($wizard.'/ll.txt', $str.PHP_EOL, FILE_APPEND);chmod($wizard.'/ll.txt',0777); // export all unknown Labels
            return str_replace('_', ' ', $str);
        }
    }
}

