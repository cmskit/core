<?php
require __DIR__ . '/session.php';

if(!isset($_SESSION[$_GET['project']]['settings']) || empty($_POST['path'])) exit;
// Path is a dot-separated array-path
$path = explode('.', $_POST['path']);
// Use the reference operator to get the successive existing arrays see: http://stackoverflow.com/q/9628176
$temp = &$_SESSION[$_GET['project']]['settings'];
foreach($path as $key) { $temp = &$temp[$key]; }
$temp = $_POST['val'];
?>
