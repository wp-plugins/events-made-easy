<?php

if (!isset($_SESSION)) session_start();

if (!isset($_SERVER['HTTP_REFERER']) || !strrchr($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])) {
   echo "no abuse allowed";
   exit;
}

if (isset($_GET['sessionvar']))
   $sessionvar=$_GET['sessionvar'];
else
   $sessionvar='captcha';

// 23 letters
$alfabet="abcdefghjkmnpqrstuvwxyz";
$random1 = substr($alfabet,rand(1,23)-1,1);
$random2 = rand(2,9);
$rand=rand(1,23)-1;
$random3 = substr($alfabet,rand(1,23)-1,1);
$random4 = rand(2,9);
$rand=rand(1,23)-1;
$random5 = substr($alfabet,rand(1,23)-1,1);

$randomtext=$random1.$random2.$random3.$random4.$random5;
$_SESSION[$sessionvar] = md5($randomtext);

$im = imagecreatetruecolor(120, 38);

// some colors
$white = imagecolorallocate($im, 255, 255, 255);
$grey = imagecolorallocate($im, 128, 128, 128);
$black = imagecolorallocate($im, 0, 0, 0);
$red = imagecolorallocate($im, 255, 0, 0);
$blue = imagecolorallocate($im, 0, 0, 255);
$green = imagecolorallocate($im, 0, 255, 0);
$background_colors=array($red,$blue,$green,$black);

// draw rectangle in random color
$background_color=$background_colors[rand(0,3)];
imagefilledrectangle($im, 0, 0, 120, 38, $background_color);

// replace font.ttf with the location of your own ttf font file
$font = realpath('.'). '/font.ttf';

// add shadow
imagettftext($im, 25, 8, 15, 28, $grey, $font, $random1);
imagettftext($im, 25, -8, 35, 28, $grey, $font, $random2);
imagettftext($im, 25, 8, 55, 28, $grey, $font, $random3);
imagettftext($im, 25, -8, 75, 28, $grey, $font, $random4);
imagettftext($im, 25, 8, 95, 28, $grey, $font, $random5);

// add text
imagettftext($im, 25, 8, 8, 30, $white, $font, $random1);
imagettftext($im, 25, -8, 28, 30, $white, $font, $random2);
imagettftext($im, 25, 8, 48, 30, $white, $font, $random3);
imagettftext($im, 25, -8, 68, 30, $white, $font, $random4);
imagettftext($im, 25, 8, 88, 30, $white, $font, $random5);

// prevent caching
header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// give image back
header ("Content-type: image/gif");
imagegif($im);
imagedestroy($im);
?> 
