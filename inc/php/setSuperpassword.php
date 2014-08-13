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
require __DIR__ . '/session.php';
// collect styles
$styles = glob('../../../vendor/cmskit/lib-jquery-ui/themes/*', GLOB_ONLYDIR);
$sopt = '';
foreach($styles as $style)
{
	if(file_exists($style.'/preview.png'))
	{
		$name = basename($style);
		$sopt .= '<option value="'.$name.'">'.$name.'</option>';
	}
}

// collect templates
$templates = glob('../../templates/*', GLOB_ONLYDIR);
$topt = '';
foreach($templates as $template)
{
	if(file_exists($template.'/backend.php'))
	{
		$name = basename($template);
		$topt .= '<option value="'.$name.'">'.$name.'</option>';
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>set new Super-Password</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1" />
	<script type="text/javascript" src="../../../vendor/cmskit/lib-jquery-ui/jquery.min.js"></script>
	<script type="text/javascript" src="../../../vendor/cmskit/lib-jquery-ui/vendor/cmskit/lib-jquery-ui/plugins/jquery.plugin_password_strength.js"></script>
	<script type="text/javascript" src="../js/gpw.js"></script>
	<style>
		body{background:#eee; font:72.5% "Trebuchet MS", Arial, sans-serif;}
		a{text-decoration:none;color:#333;}
		#wrapper{position:absolute;top:50%;left:50%;width:170px;margin:-80px 0px 0px -80px;}
		input, select{background:#fff;border:1px solid #333;padding:5px;margin:3px 0px;border-radius:5px;}
		input[type=text]{width:158px;}
		
		h3{color: #f00;}
		
		.password_strength   {padding: 0 5px; display: inline-block;}
		.password_strength_1 {background-color: #fcb6b1;}
		.password_strength_2 {background-color: #fccab1;}
		.password_strength_3 {background-color: #fcfbb1;}
		.password_strength_4 {background-color: #dafcb1;}
		.password_strength_5 {background-color: #bcfcb1;}

	</style>

</head>
<body>

<?php

require 'functions.php';
@include '../super.php';
//echo $_SESSION[$_GET['project']]['root'].' / '.md5($_SERVER['REMOTE_ADDR'] . $super[1]);

function L($s){
	return str_replace('_',' ',$s);
}


if(
	!isset($super) 
	||
	(	isset($_GET['project'])
		&& isset($_SESSION[$_GET['project']]['root'])
		&& $_SESSION[$_GET['project']]['root'] == md5($_SERVER['REMOTE_ADDR'] . $super[1])
	)
  )
{
	if(is_writable('../'))
	{
		// save Settings to File
		if(isset($_POST['pass']))
		{
			$templates = glob('./tpl_*.php');
			$tpl = array();
			foreach($templates as $template)
			{
				$tpl[] = basename($template);
			}
			$crpt = explode(':', crpt($_POST['pass'], $_POST['salt']));
	
	// save the Settings to inc/super.php
	file_put_contents('../super.php', 
	'<?php
	// auto-generated: do not edit!
	$super = array(\''.$_POST['salt'].'\', \''.array_pop($crpt).'\');
	$config = array(
		\'theme\' => array(\''.$_POST['theme'].'\'), // default jQuery-UI-Theme
		\'template\' => array(\''.$_POST['template'].'\'), // default Backend-Template
		\'autolog\' => array('.(strlen($_POST['pass'])>0 ? '0' : '1').'), // automatic Login without Password (1/0)
	);
	');
			
			chmod('../super.php', 0776);
			echo '<div id="wrapper">
			<h2>' . L('Password_saved') . '!</h2>
			<a href="../../">' . L('Login-Page') . '</a></div>';
		}
		// show Input
		else
		{
			echo '
			<form id="wrapper" style="display:none" method="post" action="setSuperpassword.php'.(isset($_GET['project'])?'?project='.$_GET['project']:'').'">
			<h4>' . L('set_Super-Password') . '</h4>
			<input type="password" autocomplete="off" id="inputPassword" name="pass" title="' . L('leave_empty_to_enable_auto-login') . '" placeholder="' . L('leave_empty_to_enable_auto-login') . '" /> 
			<input type="hidden" name="salt" id="salt" value="' . md5(mt_rand()) . '" />
			<h4>' . L('default_Theme') . '</h4>
			<select name="theme" title="' . L('choose_the_default_UI-Stylesheet_for_Backend') . '">' .
			$sopt .
			'</select>
			<h4>' . L('default_Template') . '</h4>
			<select name="template" title="' . L('choose_the_default_Template_for_Backend') . '">' .
			$topt .
			'</select>
			
			<hr /><input type="submit" value="' . L('save_Settings') . '" />
			</form>
			';
		}
	}
	else
	{
		echo '<h3 id="wrapper">"backend/inc/"' . L('is_not_writable') . ' !</h3>';
	}
}
else
{
	echo '<h3 id="wrapper">' . L('Super-Password_already_exists') . '!</h3>';
}

?>


<script>
	
	$(function() {
		$('#wrapper').attr('autocomplete', 'off').css('display','block');
		$('#inputPassword').val('');
		var opts = {
			'minLength' : 8,
			'texts' : {
				1 : '<?php echo L('Password_extremely_weak')?>',
				2 : '<?php echo L('Password_weak')?>',
				3 : '<?php echo L('Password_ok')?>',
				4 : '<?php echo L('Password_strong')?>',
				5 : '<?php echo L('Password_very_strong')?>'
			}
		};
		$('#inputPassword').password_strength(opts);
		
		// generate a new random Salt (hopefully better than md5)
		$('#salt').val(GPW.complex(12));
	});
</script>
</body>
</html>
