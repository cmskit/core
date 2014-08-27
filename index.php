<?php
/********************************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Christoph Taubmann (info@cms-kit.org)
 *  All rights reserved
 *
 *  This script is part of cms-kit Framework.
 *  This is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License Version 3 as published by
 *  the Free Software Foundation, or (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/licenses/gpl.html
 *  A copy is found in the textfile GPL.txt and important notices to other licenses
 *  can be found found in LICENSES.txt distributed with these scripts.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *********************************************************************************/
require 'inc/php/session.php';
error_reporting(0);

// fix/sanitize GET-Parameter
foreach ($_GET as $k => $v) {
    $_GET[$k] = preg_replace('/\W/', '', $v);
}

$projects = array();
foreach(glob('../projects/*', GLOB_ONLYDIR) as $p) {
    $projects[] = basename($p);
}

// if not needed you can delete the following Actions
//if ( !file_exists('composer.json') ){ header('location: inc/php/collectExternalRequirements.php'); } // redirect to System-Setup if not set

// redirect to Superpassword-Input if not set
if (!file_exists('inc/super.php')) {
    header('location: inc/php/setSuperpassword.php');
}

// redirect to Project-Setup if no project
if (count($projects) == 0) {
    header('location: admin/project_setup/index.php');
}


$logout = false;

if (isset($_GET['project'])) {
    $projectName = $_GET['project'];
    if (isset($_SESSION[$projectName])) {
        $logout = true;
    }

    $_SESSION[$projectName] = null;
    unset($_SESSION[$projectName]);
    unset($_SESSION['TMP__' . $projectName]);
    unset($_SESSION['SetupProjectName']);
} else {
    $projectName = '';
}

require 'inc/super.php';
require 'inc/php/functions.php';

$lang = !empty($_GET['lang']) ? $_GET['lang'] : browserLang(glob('inc/login/locales/*.php'), 'en');
include 'inc/login/locales/' . $lang . '.php';

function L($arr)
{
    global $LL;
    $m = array();
    foreach($arr as $e) {
		$m[$e] = (isset($LL[$e]) ? $LL[$e] : str_replace('_', ' ', $e));
	}
    return $m;
}


$data = array(
    'projectName' => $projectName,
    'projects' => $projects,
    'projectCount' => count($projects),
    'lang' => $lang,
    'servername' => $_SERVER['SERVER_NAME'],
    'jquerypath' => '../vendor/cmskit/jquery-ui',
    'error' => $_GET['error'],
    'logout'=> 'false',
    'timestamp' => time(),
    'localhost' => in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1')),
    'L' => L(array(
            'javascript_not_activated',
            'your_version_of_internet_explorer_may_not_work',
            'please_wait',
            'login',
            'project_name',
            'user_name',
            'password',
            'register',
            'forgot_password',
            'bookmark',
        )
    ),
);

include_once 'inc/login/tpl/indexTpl/tpl.php';
$tpl = new indexTpl();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang?>">
<?php
echo $tpl->render_page($data);
?>
</html>
