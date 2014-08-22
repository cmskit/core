
**Attention: work in Progress!!!!!!!**

## Backend-Templates

Templates for Backend

a Template-Folder **must** contain

* *backend.php* the Template-File
* *crud.php* loadable Classes to extend the functionalities
* *doc/info.php* Informations about the Template gathered by admin/modeling/index.php

a Folder **may** contain

* *pack.json* Informations about Source-Files gathered by admin/script_manager (see below)
* *locales/LANG.php* containing Language-Labels
* *js/css/img* - Folders containing Development-Files as well as packed Files
* *doc/LANG/SOME_FANCY_INFOS.md* further Infos about the Template 
* *config/config.php* Configurations to adapt some global Settings
* *readme.md* human readable Informations


---

### Packing css/js - Files


	{
		"css": {
			"lessify": true,
			"src": [
				["TEMPLATE/css/plugins/foldertree.css", true],
				["TEMPLATE/css/styles.css", true]
			],
			"out": "TEMPLATE/css/packed_UI.css"
		},
		"js": {
			"src": [
				["TEMPLATE/js/cmskit.core.js", true, true, true],
				["TEMPLATE/js/cmskit.desktop.js", true, true, false],
				["TEMPLATE/js/jquery.autosize.min.js", true, false, false],
				["BACKEND/../vendor/cmskit/jquery-ui/jquery.foldertree.js", true, false, true]
			],
			"out": "TEMPLATE/js/packed_LANG.js"
		}
	}

#### CSS
	"lessify": false/true

	["TEMPLATE/css/plugins/foldertree.css", true]
	[filepath, compress_code]

#### Javascript

	["TEMPLATE/js/cmskit.core.js", true, true, true]
	[filepath, compress_code, translate_labels, restore_commenthead]
