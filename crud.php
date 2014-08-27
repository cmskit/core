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

/**
* main CRUD-Functions
* this File calls 
* * the base Crud-Class in inc/php/class.crud.php
* * the Template specific CRUD-Class in template/TPLNAME/crud.php
* * and Hooks if required
*/

require 'inc/php/header.php';

$output = '';
$TMP = false; // variable to store temporarily global accessible things
$action = preg_replace('/\W/', '', $_REQUEST['action']);

$_CONF = $projectName.'\\Configuration';
$_DB = $projectName.'\\DB';

//if (!isset($_SESSION[$projectName]['objects'])) exit(''.$projectName.' is not active');

include_once('extensions/default/hooks.php');
@include_once($projectPath . '/extensions/default/hooks.php');

// get the class of the base-object
require_once 		$projectPath.'/objects/__database.php';

// load base crud-functions
require_once 		'inc/php/class.crud.php';

// now load Template-related crud-functions + translations
$actTemplate = 		(!empty($_GET['actTemplate'])) 
					? $_GET['actTemplate'] 
					: $_SESSION[$projectName]['template'][$objectName];
					
$templatePath = 	$_SESSION[$projectName]['config']['templates'][$actTemplate];
@include			$templatePath . '/locales/' . $lang . '.php';
require_once		$templatePath . '/crud.php';

// DB number
$objectDB = intval($objects[$objectName]['db']);

$objectId 			 = 	isset($_GET['objectId']) 
						? $_GET['objectId']
						: 0;
$referenceName 		 =	(isset($_GET['referenceName']) && strlen(trim($_GET['referenceName']))>0)
						? trim($_GET['referenceName'])
						: null;
$referenceId 		 =	isset($_GET['referenceId']) 
						? $_GET['referenceId']
						: null;
$referenceFields 	 =	isset($_GET['referenceName']) 
						? $_SESSION[$projectName]['settings']['labels'][$referenceName] 
						: array('id'=>1);
$objectFields 		 =	$_SESSION[$projectName]['settings']['labels'][$objectName];


foreach ($_POST as $k => $v)
{
	switch (substr($k, 0, 2))
	{
		// base64-encode Content 
		case 'e_':
			$_POST[$k] = base64_encode($v);
		break;
	
		// encrypt Content (Blowfish) OR prevent replacing encrypted Content
		case 'c_':
			if (isset($_SESSION[$projectName]['config']['crypt'][$objectName][$k]))
			{
				require_once('inc/php/crypt.php');

				// the Key is buid  MD5( projectname + objectname + fieldname + entry_id + password )
				$key  = md5($projectName . $objectName . $k . $objectId . $_SESSION[$projectName]['config']['crypt'][$objectName][$k]);

                $_POST[$k] = Blowfish::encrypt($v, $key, md5($_CONF::$DB_PASSWORD[$objectDB]));
			}
			else
			{
				unset ($_POST[$k]);
			}
		break;
		
	}
	
}


$objectHooks = $objects[$objectName]['hooks'];


$c->lang = $lang;
$c->LL = $LL;
$c->projectName = $projectName;
$c->ppath = $projectPath;
$c->objects = $objects;
$c->objectName = $objectName;
$c->objectId = $objectId;
$c->objectFields = $objectFields;
$c->actTemplate = $actTemplate;
$c->dbi = $objectDB;
$c->referenceName = $referenceName;
$c->referenceId = $referenceId;
$c->referenceFields = $referenceFields;
$c->limit  = (isset($_GET['limit'])  ? intval($_GET['limit']) : 0);
$c->offset = (isset($_GET['offset']) ? intval($_GET['offset']) : 0);
$c->mobile = (isset($_GET['mobile']) ? intval($_GET['mobile']) : 0);
$c->sortBy = $_SESSION[$projectName]['settings']['sort'][$objectName];



/**
* call PRE-/PST-Hooks if available
* @param $when string PRE or PST
* @param $what string the Object-Name
* @param $why string the Action
*/
function callHooks ($when, $what, $why)
{
	global $objects, $objectName, $referenceName, $TMP;
	
	if (@is_array($objects[$what]['hooks'][$when]))
	{
		foreach ($objects[$what]['hooks'][$when] as $hookarr)
		{
			if (function_exists($hookarr[0]))
			{
				call_user_func( $hookarr[0], (isset($hookarr[1]) ? explode(',', $hookarr[1]) : null) );
			}
			else
			{
				trigger_error('Hook '.$hookarr[0].' is called from '.$what.' but the Function do not exist', E_USER_ERROR);
			}
		}
	}
	
	// recall this Function if we have to check/call the Reference-Hooks
	if ($what === $objectName)
	{
		if ($why == 'getReferences' && !empty($referenceName))
		{
			$TMP = $referenceName;
			callHooks ($when, $referenceName, $why);
		}
		if ($why == 'getConnectedReferences' && isset($objects[$objectName]['rel']))
		{
			foreach ($objects[$objectName]['rel'] as $rname => $rarr)
			{
				$TMP = $rname;
				callHooks ($when, $rname, $why);
			}
		}
		$TMP = false;
	}
}

callHooks('PRE', $objectName, $action);


if (method_exists($c, $action))
{
	$output = $c->$action();
}
else
{
	$output = $action . ' is not supported!';
}

callHooks('PST', $objectName, $action);

echo $output;

// rough Tests
//print_r($objects->$objectName->hooks);
//print_r($c->disallow);
//print_r($objects->$objectName->acl);
//print_r($_SESSION);
//echo memory_get_peak_usage();
?>
