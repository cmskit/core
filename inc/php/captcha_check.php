<?php
require __DIR__ . '/session.php';
//echo $_GET['c'];
$o = (isset($_SESSION['captcha_answer']) && $_SESSION['captcha_answer'] == $_GET['c']) ? 'ok' : 'nope';
if($o == 'nope') unset($_SESSION['captcha_answer']);
echo $o;
?>
