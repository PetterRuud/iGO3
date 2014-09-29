<?php 
require('../init.php');
// ACP

$timer = new Timer;
$timer->startTimer();

$acp->skin_acp_url = $ipbwi->getBoardVar('url')."/skin_acp/IPB2_Standard";

if($ipbwi->member->isAdmin()){

	function acpnav() {
	
		switch($_GET['act']) {
			case 'affiliates' :
				$nav = "> Manage Affiliates";
			break;
			case 'settings' :
				$nav = "> Manage Settings";
			break;
			case 'cheats' :
				$nav = "> Manage Cheats";
			break;
		}
		return $nav;
	}
	include("skin/global.php");
}
else {
	include("skin/loginform.php");
}
?>