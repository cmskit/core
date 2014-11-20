<?php
//super-simple captcha to protect against brute-force attacks
require __DIR__ . '/session.php';
$ch = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';// 0/1 look like O/l, so we remove both
$s = '';
for($i=0; $i < 5; $i++) $s .= $ch[mt_rand(0, strlen($ch)-1)];
$_SESSION['captcha_answer'] = $s;
$s = ' '.$s.' ';
$f = 5;
$w = imagefontwidth($f) * strlen($s);
$h = imagefontheight($f)+3;
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
