<?php
$ad_affiliates = new ad_affiliates;
class ad_affiliates {

/*
/********************************************************
/
/						AFFILIATES
/
/********************************************************
*/
function auto_run() {
	global $ipbwi;
	
		
	switch($ipbwi->makesafe($_REQUEST['code'])) {
		case 'view' :
		$this->mod_aff();
		break;
		case "delete": 
		$this->delete_aff();
		break;
		case "edit": 
		$this->edit_aff();
		break;
		case "add": 
		$this->add();
		break;
		case "do_add": 
		$this->do_add();
		break;
		default :
		$this->mod_aff();
		break;
	}
}

//--------------------------------------------
//				DELETE AFFILIATE
//--------------------------------------------

function delete_aff () {
		global $ipbwi;
		if(isset($_REQUEST['submit'])) {
		$affiliate_id = $ipbwi->makesafe($_REQUEST['affiliate_id']);
		$ipbwi->DB->query("DELETE from ipbwi_affiliates where affiliate_id='$affiliate_id'");
		print "Button deleted. <a href='?act=affiliates'>back</a>";
		}else{
		   $affiliate_id = $ipbwi->makesafe($_REQUEST['id']); //gets the id from URL
		   $html .= <<<EOF
			<div class='tableborder'>
			<div class='tableheaderalt'>Delete Affiliate</div>
			<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
				<tr>
				<form action='?act=affiliates&code=delete' method='post'>
				<input type='hidden' name='affiliate_id' value='$affiliate_id'>
				  <td class='tablesubheader'>Are you sure you want to delete this affiliate?</td>
				 <td colspan="6" align='center' class="tablesubheader">
				<input type="submit" name="submit" class='realbutton' value="DELETE">
				</td>
				</tr>
				</form>
				 </table>
				</div>
EOF;
echo $html;
		}
}

//--------------------------------------------
//				EDIT AFFILIATE
//--------------------------------------------

function edit_aff () {
		global $ipbwi;
		
		if(isset($_REQUEST['submit']))	{
		   $affiliate_url = $ipbwi->makesafe($_REQUEST['affiliate_url']);
		   $affiliate_button = $ipbwi->makesafe($_REQUEST['affiliate_button']);
		   $affiliate_validated = $ipbwi->makesafe($_REQUEST['affiliate_validated']);
		   $affiliate_id = $ipbwi->makesafe($_REQUEST['affiliate_id']);
		   if(strlen($affiliate_url)<1) { 
		      $html .= "You did not enter a url.";
		   } else if(strlen($affiliate_button)<1) { 
		      $html .= "You did not enter an image.";
		    }  else {
				$ipbwi->DB->query("UPDATE ipbwi_affiliates SET affiliate_url='$affiliate_url', affiliate_button='$affiliate_button', affiliate_validated='$affiliate_validated' where affiliate_id='$affiliate_id'");
		      $ipbwi->boink_it($url="?act=affiliates",$msg="Affiliate Edited...");
		    } } else {
		   $affiliate_id = $ipbwi->makesafe($_REQUEST['id']); //gets the id from URL
		$ipbwi->DB->query("SELECT * from ipbwi_affiliates where affiliate_id='$affiliate_id'");
		$r = $ipbwi->DB->fetch_row($query);		
$html .= <<<EOF
	<div class='tableborder'>
	<div class='tableheaderalt'>Edit Affiliate</div>
		       	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
		<tr>
		<form action="?act=affiliates&code=edit" method="post">
		   <input type="hidden" name="affiliate_id" value="{$affiliate_id}">
			         <td class='tablesubheader'>ID</td>
						<td class='tablesubheader'>Link</td>
						<td class='tablesubheader'>Button</td>
						
							<td class='tablesubheader'>Button link</td>
						<td class='tablesubheader'>Click</td>
						<td class='tablesubheader'>Validated</td>
						</tr>
						<tr>
						<td class='tablerow1'>{$r['affiliate_id']}</td>
						<td class='tablerow1'><input type="text" name="affiliate_url" value="{$r['affiliate_url']}" size="40"></td>
<td class='tablerow1'><img src="{$r['affiliate_button']}"></td>
						<td class='tablerow1' align="center"><input type="text" name="affiliate_button" value="{$r['affiliate_button']}" size="40"></td>
						<td class='tablerow1'>{$r['affiliate_hits']}</td>
						<td class='tablerow1'>
							<select name="affiliate_validated">
EOF;
							
if ($r['affiliate_validated'] == 1) {
$html .= <<<EOF
<option value="1" selected="selected">Yes</option>
<option value="0">No</option>
EOF;
}
else {
$html .= <<<EOF
<option value="1">Yes</option>
<option value="0" selected="selected">No</option>
EOF;
}
$html .= <<<EOF
							</select>
							</td>
						</tr>
						<tr>
		   <td colspan="6" align='center' class="tablesubheader">
		<input type="submit" name="submit" class='realbutton' value="UPDATE">
		</td>
		</tr>
		</form>
		 </table>
		</div>
		<br />
		
EOF;
echo $html;
		 } 
	}
	
//--------------------------------------------
//				MOD AFFILIATE
//--------------------------------------------

	function mod_aff() {
		global $ipbwi;
		$aff = $ipbwi->DB->query("SELECT * from ipbwi_affiliates WHERE affiliate_validated='1' order by affiliate_hits desc");
		$waiting = $ipbwi->DB->query("SELECT * from ipbwi_affiliates WHERE affiliate_validated='0'");
		$html .= <<<EOF
			<div class='tableborder'>
		
			<div class='tableheaderalt'>Waiting For Approval</div>
			<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
				   <tr>
				   <td class='tablesubheader'>ID</td>
					<td class='tablesubheader'>Image</td>
					<td class='tablesubheader'>Link</td>
					<td class='tablesubheader'>Edit</td>
					<td class='tablesubheader'>Delete</td>
					</tr>
EOF;
		while($w = $ipbwi->DB->fetch_row($wating)){ 
$html .= <<<EOF
	<tr>
	<td class='tablerow2'>{$w['affiliate_id']}</td>
	<td class='tablerow2' align="center">
	<img src="{$w['affiliate_button']}" width="88px" height="31px" alt="No Image"></td>
	<td class='tablerow2'>{$w['affiliate_url']}</td>
	<td class='tablerow2'><a href='?act=affiliates&code=edit&id={$w['affiliate_id']}'>Edit</a></td>
	<td class='tablerow2'><a href='?act=affiliates&code=delete&id={$w['affiliate_id']}'>Delete</a></td>
	</tr>
EOF;
		}
		$html .= <<<EOF
			 </table>
			</div>
			<br />
EOF;
$html .= <<<EOF
<div class='tableborder'>
<div class='tableheaderalt'>Current Affiliates</div>
	       <table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>

	        <tr>
	         <td class='tablesubheader'>ID</td>
			<td class='tablesubheader'>Image</td>
			<td class='tablesubheader'>Link</td>
			<td class='tablesubheader'>Hits</td>
			<td class='tablesubheader'>Edit</td>
			<td class='tablesubheader'>Delete</td>
			</tr>
EOF;
	while($r = $ipbwi->DB->fetch_row($aff)){ 
		
$html .= <<<EOF

	<tr>
	<td class='tablerow2'>{$r['affiliate_id']}</td>
	<td class='tablerow2' align="center">
	<img src='{$r['affiliate_button']}' border='0' width="88px" height="31px" alt="No Image"></td>
	<td class='tablerow2'>{$r['affiliate_url']}</td>
	<td class='tablerow2'>{$r['affiliate_hits']}</td>
	<td class='tablerow2'><a href='?act=affiliates&code=edit&id={$r['affiliate_id']}'>Edit</a></td>
	<td class='tablerow2'><a href='?act=affiliates&code=delete&id={$r['affiliate_id']}'>Delete</a></td>
	</tr>

EOF;
		 }
$html .= <<<EOF
	 </table>
	</div>
	<br />
EOF;
echo $html;
	}
	
	function add() {
		global $ipbwi;
		$HTML = <<<EOF
		<div class='tableborder'>
		<form action="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=affiliates&amp;code=do_add" method="post">
			<div class='tableheaderalt'>Add Affiliate</div>
				<table cellpadding='4' cellspacing='0' width='100%'>
					<tr>
						<td class='tablerow1' valign="top">Url <em>Include http://</em></td>
						<td class='tablerow2'><input type="text" name="url" /></td>
					</tr>
					<tr>
						<td class='tablerow1' valign="top">Button Code</td>
						<td class='tablerow2' valign="top"><input type="text" name="button"/></td>
					</tr>
					<tr>
						<td colspan="6" align='center' class="tablesubheader">
							<input type="submit" name="submit" class="realdarkbutton" />
						</td>
					</tr>
			</table>
		</form>
	</div>
<br />
					
EOF;
echo $HTML;
	}


	/**
	 *
	 */
	function do_add() {
		global $ipbwi;
		$affiliate_url = $ipbwi->makesafe($_REQUEST['url']);
		$affiliate_button = $ipbwi->makesafe($_REQUEST['button']);
		if (strlen($affiliate_url)<1) {
			echo "You did not enter a URL.";
			return;
		}
		if (strlen($affiliate_button)<1) {
			echo "You did not enter a button url.";
			return;
		}
		$ipbwi->DB->query("INSERT into ipbwi_affiliates (affiliate_url, affiliate_button,affiliate_validated) values('$affiliate_url','$affiliate_button','1')");
		$ipbwi->boink_it($url='?act=affiliates', $msg="Affiliate added.");
	}

}
?>