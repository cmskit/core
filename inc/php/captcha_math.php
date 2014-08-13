<?php
//super-simple captcha to protect against brute-force password-guessing
require __DIR__ . '/session.php';
$n = array(mt_rand(5,100), mt_rand(0,10), mt_rand(0,10));
$_SESSION['captcha_answer'] = $n[0] + $n[1] - $n[2];
$s = $n[0] .' + '. $n[1] .' - '. $n[2];
$f  = 5;
$w  = imagefontwidth($f) * strlen($s);
$h = imagefontheight($f);
$i = imagecreatetruecolor ($w, $h);
$c1 = imagecolorallocate ($i, mt_rand(150,255), mt_rand(150,255), mt_rand(150,255));
$c2 = imagecolorallocate ($i, mt_rand(0,100), mt_rand(0,100), mt_rand(0,100));
imagefill($i,0,0,$c1);
imagestring($i,$f,0,0,$s,$c2);
$i = imagerotate($i, mt_rand(-5,5), $c1);
header('Content-type: image/png');
imagepng($i);
imagedestroy($i);
?>
