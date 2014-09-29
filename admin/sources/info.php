<?php
$ad_info = new ad_info;
class ad_info {
/*
/********************************************************
/
/						INFO
/
/********************************************************
*/
function auto_run() {
	global $ipbwi;
	
	//$ipbwi->acp->nav[] = array( $url,'Affiliates' );
		
	switch($ipbwi->makesafe($_REQUEST['code'])) {
		case 'view' :
		$this->view();
		break;
		case 'view_info' :
		$this->view_info();
		break;
		case 'save_notes' :
		$this->save_notes();
		break;
		case 'signature':
		$this->signature();
		break;
		default :
		$this->view();
		break;
	}
}
//-----------------------------------------------//
//				VIEW info
//-----------------------------------------------//

function ipbwi_version () {
global $ipbwi;
$site = '2.07';
return $site;
}
function mysql_version() {
	global $ipbwi;
	$ipbwi->DB->query('SELECT VERSION() AS version');
	if (!$row = $ipbwi->DB->fetch_row()) {
		$ipbwi->DB->query("SHOW VARIABLES LIKE 'version'");
		$row = $ipbwi->DB->fetch_row();
	}
	$version =  $row['version'];
	return $version;
}
function php_version() {
	return phpversion();
}
function members() {
	global $ipbwi;
	$members = count($ipbwi->member->getList());
	return $members;
}
function affiliates() {
	global $ipbwi;
	$ipbwi->DB->query("SELECT COUNT(*) as aff FROM ipbwi_affiliates ");
	$a = $ipbwi->DB->fetch_row( $query );
	return $a['aff'];
}

function affiliates_waiting() {
	global $ipbwi;
	$ipbwi->DB->query("SELECT COUNT(*) as aff FROM ipbwi_affiliates WHERE affiliate_validated = '0'");
	$aw = $ipbwi->DB->fetch_row( $query );
	return $aw['aff'];
}

function online() {
	global $ipbwi;
	$online = count($ipbwi->member->listOnlineMembers());
	return $online;
}

function notes() {
	global $ipbwi;
	$ipbwi->DB->query("SELECT cs_value FROM ibf_cache_store WHERE cs_key = 'adminnotes'");
	$r = $ipbwi->DB->fetch_row($query);
	$notes = $r['cs_value'];
	return $notes;
}


function view() {
	global $ipbwi;	
$html .= <<<EOF
<table border=0 width=100%>
	<div style="border-bottom: 1px solid rgb(237, 237, 237); font-size: 30px; padding-left: 7px; letter-spacing: -2px;">
 		Welcome to itsGAMEOVER
	</div>
	</table>
<br />

	<table border=0 width=100%>
	<td width=49% valign='top'>
	<div class='tableborder'>
	<div class='tableheaderalt'>Stats</div>
	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
	<tr>
	<td class='tablerow1' valign='middle'><strong>Members</strong></td>
	<td class='tablerow2' valign='middle'><a href="{$ipbwi->getBoardVar('url')}admin/?section=content&act=mem&code=search" target="_blank">Manage</a> (<strong>{$this->members()}</strong>)</td>
	</tr>
	<tr>
	<td class='tablerow1' valign='middle'>&nbsp;&nbsp;&#124;-<strong>Online Users</strong></td>
	<td class='tablerow2' valign='middle'><a href="{$ipbwi->getBoardVar('url')}?act=online" target='_blank'>View Online List</a> (<strong>{$this->online()}</strong>)</td>
	</tr>
	<tr>
	<td class='tablerow1' valign='middle'><strong>Affiliates</strong></td>
	<td class='tablerow2' valign='middle'><strong>{$this->affiliates()}</strong></td>
	</tr>
	<tr>
	<td class='tablerow1' valign='middle'>&nbsp;&nbsp;&#124;- <strong>Awaiting Validation</strong></td>
	<td class='tablerow2' valign='middle'><a href="?act=affiliates">View List</a> <strong>({$this->affiliates_waiting()})</strong></td>
	</tr>
	</table>
	</div>
	</td>
	<td width=2%><!-- --></td>
	<td width=49% valign='top'>
	<div class='tableborder'>
	<div class='tableheaderalt'>Info</div>

	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'><tr>
	<td class='tablerow1' width='40%' valign='middle'><strong>IPB Version</strong></td>
	<td class='tablerow2' width='60%' valign='middle'><font color="red"><strong>{$ipbwi->getBoardVar('version')}</strong> (ID: {$ipbwi->getBoardVar('version_long')})</font></td>
	</tr>
	<tr>
	<td class='tablerow1' width='40%' valign='middle'><strong>IPBWI Version</strong></td>
	<td class='tablerow2' width='60%' valign='middle'><a href="?act=info&code=view_info">More info</a> (<strong>{$this->ipbwi_version()}</strong>)</td>
	</tr>
	<tr>
	<td class='tablerow1' width='40%' valign='middle'><strong>PHP Version</strong></td>
	<td class='tablerow2' width='60%' valign='middle'><strong>{$this->php_version()}</strong></td>
	</tr>
	<tr>
	<td class='tablerow1' width='40%' valign='middle'><strong>MySQL Version</strong></td>
	<td class='tablerow2' width='60%' valign='middle'><strong>{$this->mysql_version()}</strong></td>
	</tr>
	</table>
	</div>
	</td>
	</tr>
	</table>
	
<table border=0 width=100%>
<tr>

EOF;
if($ipbwi->site_offline() == 1) {
$html .= <<<EOF
<td width="49%" valign="top">
	<div class="tableborder" style="border: 1px solid #66343E;">
 		<div class="tableheaderalt" style="background: #66343E url(skin/tabs_main/table_title_gradient2.gif) repeat-x;">Site Offline</div>
 		<div class='tablerow1' style="background: #F3E2E0;" valign='middle'>
 			Your site is currently offline
 			<br/><br/>
 			&raquo; <a href="?act=settings&amp;code=offline">Turn Site Online</a>
 		</div>
	</div>
</td>
<td width=2%><!-- --></td>
EOF;
}
$html .= <<<EOF


<td width="49%">
	<div class="tableborder">
		<div class="tableheaderalt">Admin Notes</div>
		<div class='tablerow1' valign='middle'>
		<div clas="center">
			<form action="?act=info&amp;code=save_notes" method="POST">
			<textarea cols="25" rows="8" style="border: 1px solid rgb(204, 204, 204); background-color: rgb(249, 255, 162); width: 95%; font-family: verdana; font-size: 10px;" name="notes">{$this->notes()}</textarea>
			<input type="submit" class="realbutton" value="Save Admin Notes"/>
			</form>
			</div>
			<div>
		</div>
	</div>
</td>

</tr>
	</table>
	<br />
EOF;
echo $html;
}
function view_info() {
	global $ipbwi;	
		echo $ipbwi->info();		
	}
	
	function save_notes() {
	global $ipbwi;	
		$notes = $ipbwi->makesafe($ipbwi->makesafe($_REQUEST['notes']));
		$ipbwi->DB->query("UPDATE ibf_cache_store SET cs_value = '$notes' WHERE cs_key = 'adminnotes'");
		$ipbwi->boink_it($url="?act=info",$msg="Admin notes updated...");
	}
	
function signature() {
$html .= <<<EOF
<iframe src ="sources/signature.php" width="100%" height="100" frameborder="0">
  <p>Your browser does not support iframes.</p>
</iframe>
Right click and save as
EOF;
echo $html;
}

}