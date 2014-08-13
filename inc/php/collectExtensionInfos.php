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
************************************************************************************/

/**
* collect embed-codes for Wizards an Hooks from available Wizards and Extensions
* 
* @param string Project-Name
* @return mixed array with Embed-Codes
*/
function collectExtensionInfos($project)
{
	global $lang;
	$backendPath = dirname(dirname(dirname(__FILE__)));
	// fill File-List
	$dirs = 					glob($backendPath . '/wizards/*',	GLOB_ONLYDIR);
	$dirs = array_merge($dirs, 	glob($backendPath . '/extensions/*',	GLOB_ONLYDIR));
	$dirs = array_merge($dirs, 	glob(dirname($backendPath) . '/projects/' . $project . '/extensions/*',	GLOB_ONLYDIR));

	$embeds = array('w' => array(), 'h' => array());

	// collect Informations from Extensions & Wizards
	foreach($dirs as $dir)
	{
		if(@$estr = file_get_contents($dir.'/doc/info.php'))
		{
			
			//
			$arr = explode('EOD', $estr);
			if(@$eson = json_decode($arr[1], true))
			{
				if(isset($eson['system']['inputs']))
				{
					foreach($eson['system']['inputs'] as $i)
					{
						if(isset($eson['system']['include']))
						{
							if(!isset($embeds['w'][$i])) $embeds['w'][$i] = array();
							
							$embeds['w'][$i][] = 	str_replace(array(' ','-'),'_', $eson['info']['name']) . 
													':[\'' . implode('#OR#', $eson['system']['include']) . '\',\'' .
													(	
														(isset($eson['info']['description'][$lang])) ?
															$eson['info']['description'][$lang] :
															$eson['info']['description']['en']
													).
													'\']';
							
						}
					}
				}
				
				// fill hook-embeds
				if(isset($eson['system']['hooks']))
				{
					foreach($eson['system']['hooks'] as $k=>$i)
					{
						//if(!$embeds['h'][$k]) $embeds['h'][$k] = array();
						$i['name'] = $k;
						$embeds['h'][$k] = json_encode($i);
					}
				}
				
			}
		}
	}// collect END
	
	return $embeds;

}
