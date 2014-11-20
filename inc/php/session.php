<?php
/**
 * THIS FILE IS INCLUDED IN (NEARLY) ALL PHP INTERFACES!
 * It is the central place to manage session handling
 *
 * http://phpsec.org/projects/guide/4.html
 *
 * if you need higher security level you can follow some guides presented at
 * http://www.wikihow.com/Create-a-Secure-Session-Managment-System-in-PHP-and-MySQL
 */

// Force the session to only use cookies, not URL variables.
ini_set('session.use_only_cookies', 1);

// simple session start (suppressing E_NOTICE if session was started before)
@session_start();

// prevent session fixation
if (!isset($_SESSION['__initiated__']))
{
    session_regenerate_id();
    $_SESSION['__initiated__'] = true;
}

// prevent session hijacking
if (isset($_SESSION['__HTTP_USER_AGENT__']))
{
    if ($_SESSION['__HTTP_USER_AGENT__'] != md5($_SERVER['HTTP_USER_AGENT']))
    {
        exit('your browser changed the user agent');
    }
}
else
{
    $_SESSION['__HTTP_USER_AGENT__'] = md5($_SERVER['HTTP_USER_AGENT']);
}

