<?php

$ad_settings = new ad_settings;

class ad_settings {
/*
/********************************************************
/
/						site
/
/********************************************************
*/
function auto_run() {
	global $ipbwi;
	
	switch($ipbwi->makesafe($_REQUEST['code'])) {
		case 'settings':
		$this->settings();
		break;
		case 'update_settings':
		$this->update_settings();
		break;
		case 'offline':
		$this->turn_site_offline();
		break;
		case 'do_offline':
		$this->do_offline();
		break;
		case 'ads':
		$this->ads();
		break;
		case 'do_ads':
		$this->do_ads();
		break;
		case 'cheats':
		$this->cheats();
		break;
		case 'do_cheats':
		$this->do_cheats();
		break;
		default:
		$this->settings();
		break;
	}
}

function settings() {
	global $ipbwi;
	$ipbwi->DB->query("SELECT * FROM ipbwi_site_settings");
	$r = $ipbwi->DB->fetch_row($query);
$html .= <<<EOF
<form action='{$ipbwi->getBoardVar('home_url')}admin/index.php?act=settings&amp;code=update_settings' method='post' name='theAdminForm'>
<div class='tableborder'>
<div class='tableheaderalt'>
<table cellpadding='0' cellspacing='0' border='0' width='100%'>
<tr>
<tr>
<td style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>General Settings</td>
</tr>
</table>
</div>
<div style='background-color:#EEF2F7;padding:5px'>
<div class='tableborder'>
<div class='tablesubheader'>Names &amp; Addresses</div>
<table cellpadding='5' cellspacing='0' border='0' width='100%'>
<tr>
<td width='30%' class='tablerow1'><b>Site Name</b><div style='color:gray'>This is the name of the site. It is used as the first link in the navigation menu, etc.</div></td>
<td width='55%' class='tablerow2'>
<div align='left' style='width:auto;'><input type='text' name='site_name' value="{$r['site_name']}" size='30' class='textinput'></div></td>
</tr>
</table>
<table cellpadding='5' cellspacing='0' border='0' width='100%'>
<tr>
<td width='30%' class='tablerow1'><b>Site Address</b>&nbsp;&nbsp;<div style='color:gray'>This is the URL to your website. If entered, it'll appear on the board above the header by default.</div></td>
<td width='55%' class='tablerow2'>
<div align='left' style='width:auto;'>
<input type='text' name='site_url' value="{$r['site_url']}" size='30' class='textinput'>
</div></td>
</tr>
</table>


</div>
</div>

<div style='background-color:#EEF2F7;padding:5px'>
<div class='tableborder'>
<div class='tablesubheader'>Affiliates</div>
<table cellpadding='5' cellspacing='0' border='0' width='100%'>
<tr>
<td width='30%' class='tablerow1'><b>Number of affiliates</b><div style='color:gray'>Number of affiliates to show on the frontpage.</div></td>
<td width='55%' class='tablerow2'>
<div align='left' style='width:auto;'><input type='text' name='num_affiliates' value="{$r['num_affiliates']}" size='30' class='textinput'></div></td>
</tr>
</table>


</div>
</div>

<div class='tablesubheader' align='center'>
<input type='submit' value='Update Settings' class='realdarkbutton' />
</div>
</div>
</form>
EOF;
echo $html;
}

function update_settings() {
	global $ipbwi;
	
	$site_url = $ipbwi->makesafe($_REQUEST['site_url']);
	$site_name = $ipbwi->makesafe($_REQUEST['site_name']);
	$num_affiliates = $ipbwi->makesafe($_REQUEST['num_affiliates']);
	$ipbwi->DB->query("UPDATE ipbwi_site_settings SET site_name='$site_name', site_url='$site_url', num_affiliates='$num_affiliates'");
	$ipbwi->boink_it($url="?act=settings",$msg="Thanks");
}

function turn_site_offline() {
	global $ipbwi;

$ipbwi->DB->query("SELECT site_offline, offline_msg FROM ipbwi_site_settings");
$r = $ipbwi->DB->fetch_row($query);

if ($r['site_offline'] == 1) {
	$off = "checked=\"\"";
}
else {
	$on= "checked=\"\"";
}

$html .= <<<EOF
<form action='{$ipbwi->getBoardVar('home_url')}admin/index.php?act=settings&amp;code=do_offline' method='post'>
<div class='tableborder'>
<div class='tableheaderalt'>Site Offline / Online</div>
<table cellpadding='0' cellspacing='0' border='0' width='100%'>
	<tr>
	<td class='tablerow1'  width='30%' valign='middle'><b>Turn the site offline?</b></td>
	<td class='tablerow2'  width='55%' valign='middle'>
	<div align="left" style="width: auto;">
	Yes   
	<input type="radio" id="green" {$off} value="1" name="site_offline"/>   
	<input type="radio" id="red" {$on} value="0" name="site_offline"/>   
	No
	</div>
	</td>
	</tr>
	<tr>
	<td class='tablerow1' width='30%' valign='middle'><b>The offline message to display</b></td>
	<td class='tablerow2' width='55%' valign='middle'>
	<textarea name="offline_msg" cols='80' rows='10' class='tinymce'>{$r['offline_msg']}</textarea>
	</td>
	</tr>
</table>
<div align='center' class='tablefooter'>
				 	<div class='formbutton-wrap'>
				 		<div id='button-save'>
				<input type="submit" name="submit" value="UPDATE" class='realbutton' />
				</div>
					</div>
				</div>
</div>
</form>
EOF;
echo $html;
}

function do_offline() {
	global $ipbwi;
	$offline_msg = $ipbwi->makesafe($_REQUEST['offline_msg']);
	$site_offline = $ipbwi->makesafe($_REQUEST['site_offline']);
	$ipbwi->DB->query("UPDATE ipbwi_site_settings SET offline_msg='$offline_msg', site_offline='$site_offline'");
	$ipbwi->boink_it($url="?act=settings&amp;code=offline",$msg="Thanks");

}

function ads() {
	global $ipbwi;

$ipbwi->DB->query("SELECT site_ads FROM ipbwi_site_settings");
$r = $ipbwi->DB->fetch_row($query);

$html .= <<<EOF
<form action='{$ipbwi->getBoardVar('home_url')}admin/index.php?act=settings&amp;code=do_ads' method='post' name='theAdminForm'>
<div class='tableborder'>
<div class='tableheaderalt'>Ad Code</div>
<table cellpadding='0' cellspacing='0' border='0' width='100%'>
	<tr>
	<td class='tablerow1' width='20%' valign='middle'><b>Ad Code</b><br />Max width: 180px (<em>160px</em>)<br />Max height: 600px (<em>600px</em>)</td>
	<td class='tablerow2' width='80%' valign='middle'>
	<textarea name="site_ads" cols='80' rows='15' wrap='soft' >{$r['site_ads']}</textarea>
	</td>
	</tr>
</table>
<div align='center' class='tablefooter'>
				 	<div class='formbutton-wrap'>
				 		<div id='button-save'>
				<input type="submit" name="submit" value="UPDATE" class='realbutton' />
				</div>
					</div>
				</div>
</div>
</form>
EOF;
echo $html;
}

function do_ads() {
	global $ipbwi;
	$site_ads = $ipbwi->makesafe($_REQUEST['site_ads']);
	$ipbwi->DB->query("UPDATE ipbwi_site_settings SET site_ads='$site_ads'");
	$ipbwi->boink_it($url="?act=settings&amp;code=ads",$msg="Thanks");

}

function cheats() {
	global $ipbwi;

$ipbwi->DB->query("SELECT comments_offline,cheats_display FROM ipbwi_site_settings");
$r = $ipbwi->DB->fetch_row($query);

if ($r['comments_offline'] == 1) {
	$off = "checked=\"\"";
}
else {
	$on= "checked=\"\"";
}
	$cheats_display = $r['cheats_display'];

$html .= <<<EOF
<form action='{$ipbwi->getBoardVar('home_url')}admin/index.php?act=settings&amp;code=do_cheats' method='post' name='theAdminForm'>
<div class='tableborder'>
<div class='tableheaderalt'>
<table cellpadding='0' cellspacing='0' border='0' width='100%'>
<tr>
<tr>
<td style='font-size:12px; vertical-align:middle;font-weight:bold; color:#FFF;'>Cheat &amp; Guide Settings</td>
</tr>
</table>
</div>
<div style='background-color:#EEF2F7;padding:5px'>
<div class='tableborder'>
<div class='tablesubheader'>General Settings</div>
<table cellpadding='5' cellspacing='0' border='0' width='100%'>
<tr>
<td width='30%' class='tablerow1'><b>Number of Cheats / Guides to display for each page</b><div style='color:gray'>Default: 10</div></td>
<td width='55%' class='tablerow2'>
<div align='left' style='width:auto;'>
<input type="text" name="cheats_display" value="{$cheats_display}" />
</div></td>
</tr>
</table>
</div>
</div>

<div style='background-color:#EEF2F7;padding:5px'>
<div class='tableborder'>
<div class='tablesubheader'>Comments</div>
<table cellpadding='5' cellspacing='0' border='0' width='100%'>
<tr>
<td width='30%' class='tablerow1'><b>Comments On / Off</b></td>
<td width='55%' class='tablerow2'>
<div align='left' style='width:auto;'>
				On <input type="radio" {$on} name="comments_offline" value="0" />
				<input type="radio" {$off} name="comments_offline" value="1" /> Off
				</div></td>
</tr>
</table>


</div>
</div>
<div class='tablesubheader' align='center'>
<input type='submit' value='Update Settings' class='realdarkbutton' />
</div>
</div>
</form>
EOF;
echo $html;
}

function do_cheats() {
	global $ipbwi;
	$comments_offline = $ipbwi->makesafe($_REQUEST['comments_offline']);
	$cheats_display = $ipbwi->makesafe($_REQUEST['cheats_display']);
	$ipbwi->DB->query("UPDATE ipbwi_site_settings SET comments_offline='$comments_offline',cheats_display='$cheats_display'");
	$ipbwi->boink_it($url="?act=settings&amp;code=cheats",$msg="Thanks");

}



}