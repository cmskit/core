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


// STEP 1: define variables & load basic functions

require_once 'inc/php/session.php';
require_once 'inc/php/functions.php';
//require_once 'inc/global_configuration.php';

// prevent malicious userinputs
foreach ($_POST as $k => $v) {
    $_POST[$k] = htmlspecialchars($v);
}
foreach ($_GET as $k => $v) {
    $_GET[$k] = htmlspecialchars($v);
}

// define some variables
$objects = null;
$action = null;
$projectName = preg_replace('/[^-a-zA-Z0-9_]/', '', $_REQUEST['project']);
$projectPath = '../projects/' . $projectName;



// STEP 2: determine if login-procedure should be loaded (missing session)
if (!isset($_SESSION[$projectName])) {
    include 'inc/login/login_backend.inc';
} else {
    //
    //exit($projectName);
    require_once 'inc/php/header.php';


// STEP 3: define some variables & load template

    // Objects
    $els = array();
    $entries = array();
    $objectOptions = array();
    $objects = $_SESSION[$projectName]['objects'];
    $lang = $_SESSION[$projectName]['lang'];


    $wizards = $_SESSION[$projectName]['config']['wizards'];

    @$user_wizards = array_merge($wizards, $_SESSION[$projectName]['special']['user']['wizards']);

    $objectName = (!empty($_GET['object']) ? $_GET['object'] : false);

    // define actual Template. Fallback-Order: 1. GET, 2. SESSION, 3. default
    $_SESSION[$projectName]['template'][$objectName] = (!empty($_GET['template'])
        ? $_GET['template']
        : (isset($_SESSION[$projectName]['template'][$objectName])
            ? $_SESSION[$projectName]['template'][$objectName]
            : end($_SESSION[$projectName]['config']['template'])));

    $template = (!empty($_GET['ttemplate'])) ? $_GET['ttemplate'] : $_SESSION[$projectName]['template'][$objectName];

    // prevent caching of HTML (in addition to Meta-Tags)
    header('Cache-Control: no-cache,must-revalidate', true);


    //@unset($_SESSION['TMP__' . $projectName]);

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
            foreach ($user_wizards as $wizards) {
                if (isset($wizards['url']) && isset($wizards['name'])) {
                    $d['uwizSelect'] .= '		<option value="' . $wizards['url'] . '"> ' . L($wizards['name']) . ' </option>';
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


    $templatePath = $_SESSION[$projectName]['config']['templates'][$template];

    // load language-labels
    @include $templatePath . '/locales/' . $lang . '.php';

    // load the template
    if (file_exists($templatePath . '/backend.php')) {
        // load the Template
        include $templatePath . '/backend.php';
    } else {
        exit('template does not exist');
    }

}


