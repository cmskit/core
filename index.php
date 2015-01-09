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

// sanitize GET-parameter (we can do it here)
foreach ($_GET as $k => $v) {
    $_GET[$k] = preg_replace('/[^-a-zA-Z0-9_]/', '', $v);

}

// redirect to Superpassword-Input if not set
if (!file_exists('inc/global_configuration.php')) {
    header('location: inc/php/setSuperpassword.php');
    exit;
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

include 'inc/global_configuration.php';


// collect project folders
$projects = array();
foreach(glob('../projects/*', GLOB_ONLYDIR) as $p) {
    $projects[] = basename($p);
}
// redirect to project-setup if no project exists
if (count($projects) == 0) {
    if(file_exists('admin/project_setup/index.php')){
        header('location: admin/project_setup/index.php');
    } else {
        echo 'please install cmskit/admin-project-setup';
    }
    exit;
}


if (!empty($config['login']) && file_exists('extensions/'.$config['login'].'/login_index.inc')) {
    include 'extensions/'.$config['login'].'/login_index.inc';
} else {
    include 'inc/login/login_index.inc';
}
