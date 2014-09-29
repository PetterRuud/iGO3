<!-- LEFT CONTEXT SENSITIVE MENU -->
<?php
$icon = "<img src='".$acp->skin_acp_url."/images/item_bullet.gif' border='0' alt='' valign='absmiddle'>";
switch($_GET['act']) {

	// affiliates
	case 'affiliates' :
	?>
	<div class="menuouterwrap">
	  	<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Affiliates</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=affiliates&amp;code=view'>View All Affiliates</a></div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=affiliates&amp;code=add'>Add Affiliates</a></div>
	</div>
	<br />
	
	<?php
	require( ROOT.'admin/sources/affiliates.php');
	break;
	
	// settings
	case 'settings' :
	?>
	<div class="menuouterwrap">
	  	<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Settings</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=settings&amp;code=settings'>General Settings</a></div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=settings&amp;code=offline'>Turn Site On / Off</a></div>
	</div>
	<br />
	<div class="menuouterwrap">
	  	<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Cheat &amp; Guides</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=settings&amp;code=cheats'>Cheat &amp; Guides Settings</a></div>
	</div>
	<br />
	<div class="menuouterwrap">
	  	<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Ads Settings</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=settings&amp;code=ads'>Ads Code</a></div>
	</div>
	<br />
	<?php
	require( ROOT.'admin/sources/settings.php');
	break;
	
	// info
	case 'info' :
	?>
	<div class="menuouterwrap">
		<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Dashboard</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=info&amp;code=view'>System Overview</a></div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=info&amp;code=view_info'>System Info</a></div>
	</div>
	<br />
	<div class="menuouterwrap">
		<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Other stuff</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=info&amp;code=signature'>Generate Signature</a></div>
	</div>
	<br />
	<?php
	require( ROOT.'admin/sources/info.php');
	break;
	
	// cheats
	case 'cheats' :
	?>
	<div class="menuouterwrap">
	  	<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Settings</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=settings&amp;code=cheats'>General Settings</a></div>
	</div>
	<br />
	<div class="menuouterwrap">
		<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Cheats</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=cheats&amp;code=view_cheats&amp;rel=cheats'>View All Cheats</a></div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=cheats&amp;code=add_cheat&amp;rel=cheats'>Add Cheat</a></div>
	</div>
	<br />
	<div class="menuouterwrap">
		<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Guides</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=cheats&amp;code=view_cheats&amp;rel=guides'>View All Guides</a></div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=cheats&amp;code=add_cheat&amp;rel=guides'>Add Guide</a></div>
	</div>
	<br />
	<div class="menuouterwrap">
		<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Categories</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=cheats&amp;code=view_categories'>View All Categories</a></div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=cheats&amp;code=add_category'>Add Category</a></div>
	</div>
	<br />
	<?php
	require( ROOT.'admin/sources/cheats.php');
	break;
	
	// default
	default :
	?>
	<div class="menuouterwrap">
		<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom' border='0' /> Dashboard</div>
	 	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=info&amp;code=view'>System Overview</a></div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=info&amp;code=view_info'>System Info</a></div>
	</div>
	<br />
	<div class="menuouterwrap">
		<div class="menucatwrap"><img src='<?php echo $acp->skin_acp_url;?>/images/menu_title_bullet.gif' style='vertical-align:bottom'/> Other stuff</div>
	  	<div class="menulinkwrap">&nbsp;<?=$icon;?>&nbsp;<a href='?act=info&amp;code=signature'>Generate Signature</a></div>
	</div>
	<br />
	<?php
	require( ROOT.'admin/sources/info.php');
	break;
}
?>
<!-- / LEFT CONTEXT SENSITIVE MENU -->