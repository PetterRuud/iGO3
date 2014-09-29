<?php 
require('../../init.php');
//We are making a PNG image 
header ("Content-type: image/png"); 
$name = $member['members_display_name'];
$group = "". $group['g_title'];
//Here we set the font. 
$font = 'arial.ttf'; 
//This is where we set our background image 
$handle = imagecreatefrompng("bgimage.png");

//Background Color 
$bg_color = ImageColorAllocate ($handle, 135, 206, 250); 

// shadow
$shadow_name = imagecolorallocate($handle, 0, 0, 0);
$shadow_group = imagecolorallocate($handle, 0, 0, 0);

//text Color 
$color_name = ImageColorAllocate ($handle, 255, 255, 255); 
$color_group = ImageColorAllocate ($handle, 255, 255, 255); 

//This adds all the text to the signature
imagettftext($handle, 14, 0, 301, 21, $shadow_name, $font, $name);
imagettftext($handle, 14, 0, 300, 20, $color_name, $font, $name); 
imagettftext($handle, 8, 0, 421, 66, $shadow_group, $font, $group);
imagettftext($handle, 8, 0, 420, 65, $color_group, $font, $group); 
ImagePng ($handle); 
imagedestroy($handle);
?>
