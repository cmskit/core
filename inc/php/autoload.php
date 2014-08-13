<?php
/**
* Special purpose autoloader for cms-kit backend
* 
* It detects some of the common libraries used in the backend by their namespaces OR class names.
* Everything else is treated as a project-class
* 
*/
spl_autoload_register(function ($class)
{
	$a = explode('\\', $class);
	if(!isset($a[1])) $a = explode('_', $class);// lookup for 5.2 namespacing
	
	switch ($a[0])
	{
		case 'Michelf': // markdown extra
			include __DIR__ . '/Markdown.php';
		break;
		case 'PclZip': // PclZip
			include __DIR__ . '/pclzip.lib.php';
		break;
		case 'JsonPatch': // JsonPatch
			include __DIR__ . '/jsonPatch.php';
		break;
		case 'PHPTAL': // phptal...
			
		break;
		
		default: // assume we have to load a project class
			if (isset($a[1]))
			{
				$path = dirname(dirname(dirname(__DIR__))) . '/projects/' . $a[0] . '/objects/class.' . strtolower($a[1]) . '.php';
				if (is_file($path))
				{
					include $path;
				}
				else
				{
					echo '<p style="color:red">' . $path . ' does not exist!</p>'; // simple debug
				}
			}
		break;
	}
});
