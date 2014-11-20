<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 08.11.14
 * Time: 15:47
 */


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
        array(true, 'inc/global_configuration.php'), // load the Credentials
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
        'client' => json_decode(stripcslashes(htmlspecialchars_decode($post['client'])), true),
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
        if ((time() - filemtime('inc/global_configuration.php')) > 5259487) {
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




    // reset Captcha-Answer if exists
    if (isset($_SESSION['captcha_answer'])) unset($_SESSION['captcha_answer']);

    return $mysession;
}


/**
 * collect Objects
 *
 * @param $mysession
 * @param $lang
 */
function collectObjects($mysession, $lang)
{

    $objectOptions = array();
    $objects = $mysession['objects'];
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

        if (!isset($mysession['settings']['labels'][$objectKey])) {
            $mysession['settings']['labels'][$objectKey] = array('id'); // default

            // define Field-Labels shown in Lists (Fallback id)
            if (isset($mysession['objects'][$objectKey]['config']['list']['labels'])) {
                $mysession['settings']['labels'][$objectKey] = $mysession['objects'][$objectKey]['config']['list']['labels'];
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
        $mysession['templates'][$objectKey] = (isset($objectValues['templates']) ? explode(',', $objectValues['templates']) : $mysession['config']['template']);

        if (!isset($mysession['settings']['sort'][$objectKey])) {
            $mysession['settings']['sort'][$objectKey] = ($option['htype'] == 'Tree') ? array('treeleft' => 'asc') : array('id' => 'asc');
        }

    }

    // ????
    ksort($objectOptions, SORT_LOCALE_STRING);
    $mysession['objectOptions'] = $objectOptions;

    return $mysession;
}// collect Objects END

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