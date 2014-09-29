<div><!-- RIGHT CONTENT BLOCK -->
<?php
switch($_GET['act']) {
	case 'affiliates' :
	$ad_affiliates->auto_run();
	break;
	case 'settings' :
	$ad_settings->auto_run();
	break;
	case 'cheats' :
	$ad_cheats->auto_run();
	break;
	default :
	$ad_info->auto_run();
	break;
}
?>
</div><!-- / RIGHT CONTENT BLOCK -->