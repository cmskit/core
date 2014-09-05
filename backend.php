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
/**
 * Backend functions
 */


// if a login-request is detected
if (isset($_POST['project'])) {
    foreach($_POST as $k=>$v){ $_POST[$k] = preg_replace('/\W,@\{\}"/', '', $v); }
    //catch the projectname submitted via $_POST
    $projectName = $_POST['project'];
    $projectPath = '../projects/' . $projectName;
	require 'inc/php/session.php';
    unset($_SESSION[$projectName]);
} else {
    require 'inc/php/header.php';
}

$post = $_POST;
$objects = null;
$action = null;

require_once 'inc/php/functions.php';

/**
 * Shorthand to redirect to the login page with an error message
 * @param $projectName
 * @param $errormsg
 */
function goToIndex($projectName, $errormsg)
{
    unset($_SESSION[$projectName]);
    header('location: index.php?error=' . $errormsg . '&project=' . $projectName);
    exit();
}

// back to login if the projectname is not valid
if (!file_exists($projectPath . '/objects/__database.php')) {
    $errormsg = 'project_unknown';
    goToIndex($projectName, $errormsg);
}


/**
 * @param $projectPath
 * @param $projectName
 */
function verifyUser($mysession, $post, $projectPath, $projectName)
{

    global $log, $objects, $action, $mysession;

    // Array containing Hook-Names to be processed (should be filled in hooks.php)
    $loginHooks = array();

    // set the Check-Variable to false
    $log = false;

    // dummy
    $filter = '';

    $super = array();

    // includes
    $includes = array(
        // required, path
        array(true, 'inc/super.php'), // load the Credentials
        array(true, $projectPath . '/objects/__model.php'), // load Model + Database
        array(true, $projectPath . '/objects/__filter.php'),
        array(true, $projectPath . '/objects/__database.php'),
        array(false, 'extensions/default/hooks.php'),
        array(false, $projectPath . '/extensions/default/hooks.php'),
    );

    foreach ($includes as $a) {
        if ($a[0] && file_exists($a[1]) != $a[0]) {
            exit('"' . $a[1] . '" is missing');
        }
        //echo $a[1].'<br>';
        @include($a[1]);
    }
    $mainConfig = $config;

    $_CONF = $projectName . '\\Configuration';

    // define/reset the main Session-Array
    $mysession = array(
        'sys_secret' => md5($_CONF::SECRET), // set a project-specific secret
        'usr_secret' => md5($post['pass']), // set a user-specific secret
        'login' => time(),
        'special' => array(),
        'lang' => $post['lang'],
        'client' => json_decode(stripcslashes($post['client']), true),
        'filter' => $filter,
        'sort' => array(),
        'fields' => array(),
        'messages' => array(),
        'settings' => array(
            'interface' => array(
                'theme' => $mainConfig['theme'],
                'default' => $mainConfig['template'],
            ),
            'templates' => array(
                'default' => array('columns' => array(55, 200, 200, 20, 1))
            ),
            'objects' => array('sort' => array(),),
        ),
    );


    //$projectConfiguration = new $i();
    //$_SESSION[$projectName]['projectConfiguration'] = ;

    // collect global backend templates from backend and -optional- project
    $templateFolders = glob('{templates/*,../projects/' . $projectName . '/templates/*}', GLOB_ONLYDIR | GLOB_BRACE);
    $mainConfig['templates'] = array();
    foreach ($templateFolders as $templatePath) {
        if (file_exists($templatePath . '/backend.php')) {
            $mainConfig['templates'][basename($templatePath)] = $templatePath;
        }
    }



    // load configs
    foreach (array('extensions/default/config/config.php', $projectPath . '/extensions/default/config/config.php') as $configFile) {
        if (include($configFile)) {
            $mainConfig = array_merge_recursive($mainConfig, json_decode($config, true));
            // print_r($mainConfig);exit(); // test if config is merged correctly (swap $configFiles above)
        }
    }

    $mysession['config'] = $mainConfig;


    // check for superroot //////////////////////////////////////////////////////////////
    if ((
            crpt(substr($post['pass'], 0, 200), $super[0]) === $super[0] . ':' . $super[1]
            &&
            (
                in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1')) // no need for captchas on localhost
                ||
                isset($_SESSION['captcha_answer']) && $post['name'] == $_SESSION['captcha_answer']
            )
        )
        ||
        end($mainConfig['autolog']) === 1 // login is disabled at all
    ) {

        // try to load a previously saved configuration
        @include $projectPath . '/extensions/default/config/superroot.php';

        // define User as "Super-Root" and put some infos into the user-array
        $mysession['root'] = md5($_SERVER['REMOTE_ADDR'] . $super[1]);

        // if the super-password is older than 2 months
        if ((time() - filemtime('inc/super.php')) > 5259487) {
            $mysession['messages'][] = 'Hello admin: please refresh your super-password!';
        }

        // settings
        $mysession['settings'][0] = array('default' => array('objects' => array()));

        $mysession['special']['user'] =
            array(
                'id' => 0,
                'username' => 'superroot',
                'prename' => 'superroot',
                'lastname' => 'superroot',
                'profiles' => array(0 => 'superroot'),
                'lastlogin' => 0,
                'logintime' => time(),
                'wizards' => array(),
                'fileaccess' => array(array(
                    'driver' => 'LocalFileSystem',
                    'path' => 'files/',
                    'tmbPath' => 'files/.tmb',
                )),
            );

        $log = true;

    } // super-root END

    // (try to) call Login-Hooks
    foreach ($loginHooks as $hook) {

        if (function_exists($hook)) {
            $mysession = call_user_func($hook, $mysession);
        }
    }
//

    // collect Admin-Wizards from backend and -optional- project
    if (isset($mysession['root'])) {
        $mysession['adminfolders'] = array();
        foreach (glob('{admin/*,../projects/' . $projectName . '/admin/*}', GLOB_ONLYDIR | GLOB_BRACE) as $f) {
            $bf = basename($f);
            // Admin-Wizards beginning with "_" are for Super-Admins only
            if ($mysession['root'] != 1 || substr($bf, 0, 1) != '_') {
                $mysession['special']['user']['wizards'][] =
                    array(
                        'name' => $bf,
                        'url' => $f . '/index.php?project=' . $projectName
                    );
            }
        }
    }

    // login failed
    if (!$log) {
        $errormsg = 'please_log_in';
        goToIndex($projectName, $errormsg);
    }
    else {// Login-Check was successful

        $mysession['objects'] = $objects;
        $mysession['loginTime'] = time();

        // create Check to prevent Session-Hijacking in crud.php
        $mysession['user_fingerprint'] = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . date('z'));
    }


    return $mysession;
}



// start verification
if (!isset($_SESSION[$projectName])) {

    $_SESSION[$projectName] = verifyUser(array(), $_POST, $projectPath, $projectName);
    //print_r($_SESSION[$projectName]);
    //exit;
    // relocate this Page to kill $_POST
    header('location: backend.php?project=' . $projectName);

    exit();
}


if (!isset($_SESSION[$projectName]['special']['user'])) {

    $errormsg = 'please_log_in_user';
    goToIndex($projectName, $errormsg);
}

// reset Captcha-Answer if exists
if (isset($_SESSION['captcha_answer'])) unset($_SESSION['captcha_answer']);


// Objects
$els = array();
$entries = array();
$objectOptions = array();
$objects = $_SESSION[$projectName]['objects'];
$lang = $_SESSION[$projectName]['lang'];



/**
 * collect Objects (only once
 *
 * @param $objects
 * @param $lang
 * @param $objectOptions
 * @param $projectName
 */
function collectObjects($mysession, $objects, $lang, $objectOptions, $projectName)
{

    foreach ($objects as $objectKey => $objectValues) {
        $option = array(
            'name' => $objectKey,
            'label' => ((isset($objectValues['lang']) && isset($objectValues['lang'][$lang])) ? $objectValues['lang'][$lang] : $objectKey),
            'htype' => (isset($objectValues['ttype']) ? $objectValues['ttype'] : '')
        );

        // collect Objects in Tag-Groups
        if (isset($objectValues['tags'][$lang])) {
            foreach ($objectValues['tags'][$lang] as $t) {
                if (!isset($objectOptions[$t[0]])) {
                    $objectOptions[$t[0]] = array();
                }
                $objectOptions[$t[0]][] = $option;
            }
        } else {
            $objectOptions[0][] = $option;
        }

        if (!isset($_SESSION[$projectName]['settings']['labels'][$objectKey])) {
            $mysession['settings']['labels'][$objectKey] = array('id'); // default

            // define Field-Labels shown in Lists (Fallback id)
            if (isset($_SESSION[$projectName]['objects'][$objectKey]['config']['list']['labels'])) {
                $mysession['settings']['labels'][$objectKey] = $_SESSION[$projectName]['objects'][$objectKey]['config']['list']['labels'];
            } else {
                foreach ($objectValues['col'] as $fieldKey => $fieldValues) {
                    if (
                        substr($fieldKey, -2) != 'id' && // ignore id-Fields
                        !in_array(substr($fieldKey, 0, 2), array('__', 'c_', 'e_')) && // ignore Fields beginning with...
                        strpos($fieldValues['type'], 'CHAR') // take only (Var)char-Fields
                    ) {
                        $mysession['settings']['labels'][$objectKey] = array($fieldKey);
                        break;
                    }
                }
            }
        }

        // save available Backend-Templates for later use
        $mysession['templates'][$objectKey] = (isset($objectValues['templates']) ? explode(',', $objectValues['templates']) : $_SESSION[$projectName]['config']['template']);

        if (!isset($_SESSION[$projectName]['settings']['sort'][$objectKey])) {
            $mysession['settings']['sort'][$objectKey] = ($option['htype'] == 'Tree') ? array('treeleft' => 'asc') : array('id' => 'asc');
        }

    }

    // ????
    ksort($objectOptions, SORT_LOCALE_STRING);
    $mysession['objectOptions'] = $objectOptions;

    return $mysession;
}// collect Objects END

if (is_array($objects) && !isset($_SESSION[$projectName]['objectOptions'])) {
    $_SESSION[$projectName] = collectObjects($_SESSION[$projectName], $objects, $lang, $objectOptions, $projectName);
}


$w = isset($_SESSION[$projectName]['config']['wizards']) ? $_SESSION[$projectName]['config']['wizards'] : array();
@$user_wizards = array_merge($w, $_SESSION[$projectName]['special']['user']['wizards']);

$objectName = (!empty($_GET['object']) ? $_GET['object'] : false);

// define actual Template. Fallback-Order: 1. GET, 2. SESSION, 3. default
$_SESSION[$projectName]['template'][$objectName] = (!empty($_GET['template']) ? $_GET['template'] : (isset($_SESSION[$projectName]['template'][$objectName]) ? $_SESSION[$projectName]['template'][$objectName] : end($_SESSION[$projectName]['config']['template'])));
$template = (!empty($_GET['ttemplate'])) ? $_GET['ttemplate'] : $_SESSION[$projectName]['template'][$objectName];

// prevent caching of HTML (in addition to Meta-Tags)
header('Cache-Control: no-cache,must-revalidate', true);


unset($_SESSION['TMP__' . $projectName]);

/**
 * build HTML for the dropdowns located in the header
 *
 * @param $projectName string the project name
 * @param $objectName string the object name
 * @param $user_wizards array
 * @return array
 */
function buildDropdowns($projectName, $objectName, $user_wizards)
{
    $d = array('objectSelect' => '', 'tplSelect' => '', 'uwizSelect' => '');
// build Object-Selector
    $d['objectSelect'] = '<select id="objectSelect">' .
        '<option value="" data-htype=""> ' . L('availabe_Sections') . " </option>\n";

    foreach ($_SESSION[$projectName]['objectOptions'] as $group => $arr) {
        $d['objectSelect'] .= '<optgroup label="' . (($group != '0') ? ' ' . $group . '' : '') . '">';
        foreach ($arr as $option) {
            $opt_state = ($option['name'] == $objectName) ? ' selected="selected"' : '';
            if (substr($option['label'], 0, 1) !== '.') {
                $d['objectSelect'] .= '	<option' . $opt_state . ' value="' . $option['name'] . '" data-htype="' . $option['htype'] . '"> ' . $option['label'] . '</option>';
            }
        }
        $d['objectSelect'] .= '</optgroup>';
    }
    $d['objectSelect'] .= '</select>';

// build Template-Selector if needed
    $d['tplSelect'] = '';
    if ($objectName && count($_SESSION[$projectName]['templates'][$objectName]) > 1) {
        $d['tplSelect'] .= '<select id="templateSelect">' .
            '<option value="">' . L('availabe_Templates') . "</option>\n";
        foreach ($_SESSION[$projectName]['templates'][$objectName] as $templatename) {
            $d['tplSelect'] .= '<option value="' . $templatename . '">' . L($templatename) . '</option>';
        }
        $d['tplSelect'] .= '</select>';
    }

// build user wizard selector

    if (isset($user_wizards)) {
        $d['uwizSelect'] = '
    <span>
    <select id="globalWizard" onchange="openGlobalWizard(this)">
        <option value=""> ' . L('Wizards') . ' </option>
        <optgroup label="' . L('global_wizards') . '">
        ';
        foreach ($user_wizards as $w) {
            if (isset($w['url']) && isset($w['name'])) {
                $d['uwizSelect'] .= '		<option value="' . $w['url'] . '"> ' . L($w['name']) . ' </option>';
            }
        }
        $d['uwizSelect'] .= '</optgroup>
        <optgroup id="objectWizards" label="' . L('object_wizards') . '">
        </optgroup>
    </select>
    </span>';
    }
    return $d;
}


$templatePath = 	$_SESSION[$projectName]['config']['templates'][$template];

// load language-labels
@include $templatePath . '/locales/' . $lang . '.php';
// load the Template
include $templatePath . '/backend.php';

