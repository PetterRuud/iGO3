
<?php 
require('init.php');
$timer = new Timer;
$timer->startTimer();

if($ipbwi->member->isAdmin() OR $ipbwi->site_offline() == 0) {
	// header
	include(ROOT."template/header.php");
	//left sidebar
	include(ROOT."template/leftsidebar.php");
	include(ROOT."template/middletop.php");

	// Error Output
	echo $ipbwi->printSystemMessages();

	if ($ipbwi->makesafe($_REQUEST['act']) == "" OR $ipbwi->makesafe($_REQUEST['act']) == "home") {
		$news->auto_load();
	}

	switch ($ipbwi->makesafe($_REQUEST['act'])){
		case "affiliates"		:							
			$affiliates->auto_load();				
		break;
		case "news"		:
			$news->auto_load();
		break;
		case "cheats"		:
			$cheats->auto_load();	
		break;
		case "contact"		:
			$contact->auto_load();	
		break;
	}
	//right sidebar
	include(ROOT."template/rightsidebar.php");
	// footer
	include(ROOT."template/footer.php");
}
else {
	// offline
	include(ROOT."template/offline.php");
}
?>