<?php 
////////////////////////////////////////////////////////////////////// 
/* itsGAMEOVER's Signature Rotator*/ 
/* */ 
/* This script takes an inventory of all image files in a directory */ 
/* and displays one at random. All you need to do is save this code */ 
/* in a directory that contains your images (name it what you will, */ 
/* but make sure it has a ".php" extension). Then just link to this */ 
/* script to get your random image. */ 
/* */ 
/* I would recommend naming this file "index.php," and then you can */ 
/* just link to the directory itself (like I do in my example). */ 
/* */ 
/* Once you've read these instructions, feel free to delete them to */ 
/* cut down on the file size. I claim no rights to this code, which */ 
/* means you can do whatever you like with it. Have fun.  */ 
/* */ 
////////////////////////////////////////////////////////////////////// 

if ($dir = opendir(".")) 
{ 
$list = buildimagearray($dir); 
displayrandomimage($list); 
} 

// This function reads all the files in the current directory and adds all image files to the array $list[] 
function buildimagearray($dir) 
{ 
while (false !== ($file = readdir($dir))) 
{ 
if (!is_dir($file) && getimagesize($file)) 
{ 
$list[] = $file; 
} 
} 
return $list; 
} 

// This function selects a random image, determines the mime type, opens the file for reading, 
// and then outputs the image 
function displayrandomimage($list) 
{ 
srand ((double) microtime() * 10000000); 
$sig = array_rand ($list); 

$size = getimagesize ($list[$sig]); 
$fp = fopen($list[$sig], "rb"); 

if ($size && $fp) 
{ 
header("Content-type: {$size['mime']}"); 
fpassthru($fp); 
exit; 
} 
} 
?>