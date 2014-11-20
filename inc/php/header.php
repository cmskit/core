<?php
/**
* first Processings for various Files
* 
*/

// include session start
require_once __DIR__ . '/session.php';

// include autoload
require_once __DIR__ . '/autoload.php';

// set the timezone
date_default_timezone_set('UTC');

// define how to handle error reporting
//error_reporting(0);
error_reporting(E_ALL ^ E_NOTICE);

// disabling magic quotes at runtime
if (get_magic_quotes_gpc())
{
	function stripslashes_gpc(&$value)
	{
		$value = stripslashes($value);
	}
	array_walk_recursive($_GET, 'stripslashes_gpc');
	array_walk_recursive($_POST, 'stripslashes_gpc');
	//array_walk_recursive($_COOKIE, 'stripslashes_gpc');
	//array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

// fix/sanitize GET-Parameter 
foreach ($_GET as $k=>$v){ $_GET[str_replace('amp;','',$k)] = preg_replace('/\W, /', '', $v); }

// additional securing of Variables (probably via filter)??
@$projectName = $_GET['project'];
@$objectName = $_GET['object'];

// prevent Session-Hijacking
if( !isset($_SESSION[$projectName]['user_fingerprint']) || $_SESSION[$projectName]['user_fingerprint'] != md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . date('z')))
{
    exit('Session expired or IP has changed');
}

// abort the script if the access is not allowed
if (!isset($_SESSION[$projectName]['objects'])) exit('not active');
if (!empty($objectName) && !isset($_SESSION[$projectName]['objects'][$objectName]) && !isset($_SESSION['TMP__'.$projectName]['objects'][$objectName])) exit('Object is not accessible!');

// absolute project-path
$projectPath = realpath( __DIR__ . '/../../../projects/' . $projectName );
//absolute backend path
$backendPath = dirname(dirname(__DIR__));


// accessible Objects
$objects = isset($_SESSION['TMP__'.$projectName]['objects'])
    ? $_SESSION['TMP__'.$projectName]['objects']
    : $_SESSION[$projectName]['objects'];

// if we have a temporary session, destroy it immediately
unset($_SESSION['TMP__'.$projectName]);

@$db = intval($objects[$objectName]['db']);
@$theme = end($_SESSION[$projectName]['settings']['interface']['theme']);


/**
 * translate Strings
 * todo: move the lang-arrays somwhere else...
 */
$lang = $_SESSION[$projectName]['lang'];
$LL = array();

/**
 * recursively bubble up the directory tree and look for translations
 * @param $path
 */
function lookForLocales($path)
{
    global $LL, $lang, $backendPath;
    if(strlen($path) < strlen($backendPath)) return;// abort if we are outside of backend
    $p = $path . '/locales/' . $lang . '.php';
    if(file_exists($p)) {
        include $p;
    }else {
        lookForLocales(dirname($path));
    }
}
//lookForLocales(dirname($_SERVER['SCRIPT_FILENAME']));

/**
 * Function to translate strings/arrays of strings
 *
 * @param $str string/array
 * @return array|mixed
 */
function L($str)
{
	// if a list of strings (array) is detected, process them one by one
    if(is_array($str)) {
        $m = array();
        foreach($str as $e) $m[$e] = L($e);
        return $m;
    }

    global $LL;
	if(isset($LL[$str])) {
		return $LL[$str];
	} else {
		// uncomment to add all untranslated labels to "ll.txt" (directory must be writable!)
		// file_put_contents(dirname($_SERVER['SCRIPT_FILENAME']) . '/ll.txt', $str.'<<<'.$_SERVER['PHP_SELF'].'>>>'. PHP_EOL, FILE_APPEND);
		// chmod(dirname($_SERVER['SCRIPT_FILENAME']) . '/ll.txt', 0777);
		return str_replace('_', ' ', $str);	
	}
}

