<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" /> 
<title><?=$site_name;?> Site ACP</title>
<link rel="shortcut icon" href="/images/favicon.ico" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?=$acp->skin_acp_url;?>/acp_css.css" media="screen" />
<script type="text/javascript" src="/jscript/tiny_mce/tiny_mce.js"></script>
    
<script type="text/javascript">
tinyMCE.init({
// General options
mode : "textareas",
editor_selector : "tinymce",
theme : "advanced",
plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,phpimage",
// Theme options
theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,code,|,backcolor",
theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,charmap,emotions,media,|,fullscreen",
theme_advanced_buttons4 : "insertdate,inserttime,|,preview",
theme_advanced_toolbar_location : "top",
theme_advanced_toolbar_align : "left",
theme_advanced_statusbar_location : "bottom",
theme_advanced_resizing : true,
// Example content CSS (should be your site CSS)
content_css : "/css/site.css",
});

</script>
</head>
<body>
<div id="ipdwrapper"><!-- WRAPPER -->
	<div class="tabwrap-main">
<?php
	function build_tabs($act) {
	global $ipbwi;
	$onoff['info'] = 'taboff-main';
	$onoff['settings'] = 'taboff-main';
	$onoff['affiliates']   = 'taboff-main';
	$onoff['cheats']  = 'taboff-main';
	$onoff[ $act ] = 'tabon-main';
	if($act == NULL){
		$onoff['info'] = 'tabon-main';
	}
	echo <<<EOF
		<div class='{$onoff['info']}'>
		<img src="skin/tabs_main/dashboard.png" style="vertical-align:middle;" width="24" height="24" alt="" /> 
		<a href="?act=info">DASHBOARD</a></div>
		
		<div class="{$onoff['settings']}">
		<img src="skin/tabs_main/tools.png" style="vertical-align:middle;" width="24" height="24" alt="" /> 
		<a href="?act=settings">SETTINGS</a></div>
		
		<div class="{$onoff['affiliates']}">
		<img src="skin/tabs_main/validating.png" style="vertical-align:middle;" width="24" height="24" alt="" /> 
		<a href="?act=affiliates">AFFILIATES</a></div>
		
		<div class="{$onoff['cheats']}">
		<img src="skin/tabs_main/emos.png" style="vertical-align:middle;" width="24" height="24" alt="" /> 
		<a href="?act=cheats">CHEATS &amp; GUIDES</a></div>
EOF;

}

build_tabs($_GET['act']);
?>
			<div class="logoright"><img src="acp-logo.png" alt="Portal ACP" border="0" /></div>
		</div>
		<!-- / TOP TABS -->
		<div class="sub-tab-strip">
			<div class="global-memberbar">
		 		Welcome <strong><?=$member['members_display_name'];?></strong> [
		 		<a href="<?=$ipbwi->getBoardVar('home_url');?>" target="_blank">Site</a> &middot;
		 		<a href="<?=$ipbwi->getBoardVar('url');?>" target="_blank">Forum</a> &middot;
		 		<a href="?action=logout">Log Out</a>]
			</div>
			Site ACP Home <?=acpnav();?>
		</div>
		<div class="outerdiv" id="global-outerdiv"><!-- OUTERDIV -->
			<table cellpadding="0" cellspacing="8" width="100%" id="tablewrap">
				<tr>
		 			<td width="15%" valign="top" id="leftblock">
		 				<div>
		 					<?php include("leftmenu.php");?>
		 				</div>
				 	</td>
		 			<td width="85%" valign="top" id="rightblock">
						<?php include("rightcontent.php");?>
				 	</td>
				</tr>
			</table>
		</div><!-- / OUTERDIV -->
	</div><!-- / WRAPPER -->
	<div align="center"><br/>Time: <?=$timer->endTimer();?></div>
	<div align="center" class="copy"><?=$site_name;?> &copy; <?=date('Y');?></div>
</div><!-- / WRAPPER -->
</body>
</html>