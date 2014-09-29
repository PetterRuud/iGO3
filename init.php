<?php
if (!defined('ROOT')) {
	define ('ROOT', dirname(__FILE__) . '/');
}
//======================================================//
//				 MAIN FRAMEWORK
//======================================================//
require_once(ROOT."ipbwi/config.inc.php");
require_once(ROOT."ipbwi/ipbwi.inc.php");
// allow custom mysql queries
$ipbwi->DB->allow_sub_select=1;
// check login
function check_login() {
	global $ipbwi;
	if($_GET['action'] == 'login'){
		if(empty($_POST['username'])){
			$ipbwi->addSystemMessage('Error', 'You have to type an username.');
		}elseif(empty($_POST['password'])){
			$ipbwi->addSystemMessage('Error', 'You have to type a password.');
		}else{
			$setCookie	= true;
			$ipbwi->member->login($_POST['username'],$_POST['password'],$setCookie);
			header('location: '.$_SERVER['PHP_SELF']);
		}
	}
	if($_GET['action'] == 'logout'){
		$ipbwi->member->logout();
		//header('location: '.$_SERVER['PHP_SELF']);
		$ipbwi->boink_it($_SERVER['PHP_SELF']);
	}
}
// Loads stats
$stats = $ipbwi->stats->board();
// load member info
$member = $ipbwi->member->info();
// load group info
$group = $ipbwi->group->info();
// load settings
$ipbwi->DB->query("SELECT * FROM ipbwi_site_settings");
$r = $ipbwi->DB->fetch_row($query);
$num_affiliates = $r['num_affiliates'];
$site_url = $r['site_url'];
$site_name = $r['site_name'];
$site_ads = $r['site_ads'];
$onlinelist = $ipbwi->member->listOnlineMembers(true,true,true,'member_name','ASC',', ');
//======================================================//
//					Classes
//======================================================//
require(ROOT.'ipbwi/lib/affiliates.inc.php');
$affiliates = new class_affiliates();
require(ROOT.'ipbwi/lib/cheats.inc.php');
$cheats = new class_cheats();
require(ROOT.'ipbwi/lib/news.inc.php');
$news = new class_news();
require(ROOT.'ipbwi/lib/contact.inc.php');
$contact = new class_contact();
//===========================================================================
// DEBUG
//===========================================================================
class Timer {
	function startTimer(){
    global $starttime;
    $mtime = microtime ();
    $mtime = explode (' ', $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $starttime = $mtime;
	}
	function endTimer(){
    global $starttime;
    $mtime = microtime ();
    $mtime = explode (' ', $mtime);
    $mtime = $mtime[1] + $mtime[0];
    $endtime = $mtime;
    $totaltime = round (($endtime - $starttime), 5);
    return $totaltime;
	}
}
?>