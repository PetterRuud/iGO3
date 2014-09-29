<?php

$ad_cheats = new ad_cheats;
class ad_cheats {
/*
/********************************************************
/
/						CHEATS AND GUIDES
/
/********************************************************
*/
function auto_run() {
	global $ipbwi,$acp;
	
	switch($ipbwi->makesafe($_REQUEST['code'])) {
		//cheats
		case 'view_cheats' :
		$this->view_cheats();
		break;
		case 'update_cheat':
		$this->update_cheat();
		break;
		case 'update_subcheat':
		$this->update_subcheat();
		break;
		case 'edit_cheat':
		$this->edit_cheat();
		break;
		case 'delete_cheat':
		$this->delete_cheat();
		break;
		case 'delete_subcheat':
		$this->delete_subcheat();
		break;
		case 'add_cheat':
		$this->add_cheat();
		break;
		case 'doadd_cheat':
		$this->doadd_cheat();
		break;
		//categories
		case 'view_categories':
		$this->view_categories();
		break;
		case 'edit_category':
		$this->edit_category();
		break;
		case 'update_category':
		$this->update_category();
		break;
		case 'add_category':
		$this->add_category();
		break;
		case 'doadd_category':
		$this->doadd_category();
		break;
		case 'delete_category':
		$this->delete_category();
		break;
		case 'dodelete_category':
		$this->dodelete_category();
		break;
		//dashboard
		default :
		$this->dashboard();
		break;
	}
}

//-----------------------------------------------//
//				VIEW CHEATS AND GUIDES
//-----------------------------------------------//
	function view_cheats() {
		global $ipbwi;
		
	
		$rel = $ipbwi->makesafe($_REQUEST['rel']);
		if ($rel == "cheats") {
			$reltitle = "Cheats";
		}
		if ($rel == "guides") {
			$reltitle = "Guides";
		}

$ipbwi->DB->query("SELECT c.*,a.* FROM ipbwi_cheats a, ipbwi_categories c 
	WHERE a.cheat_validated = '0' 
	AND a.cheat_catid = c.catid
	AND a.cheat_rel = '$rel'");

$html .= <<<EOF
	<div class="tableborder" style="border: 1px solid #66343E;">
		<div class="tableheaderalt" style="background: #66343E url(skin/tabs_main/table_title_gradient2.gif) repeat-x;">{$reltitle} Waiting For Approval</div>
		<table cellpadding='4' cellspacing='0' width='100%'>
EOF;

	$per_row  = 3;
	$td_width = 100 / $per_row;
	$count    = 0;
	$html   .= "<tr align='center'>\n";
		
	while ($r = $ipbwi->DB->fetch_row($query)) {
		$cheat_posted = $ipbwi->date($r['cheat_posted'],'%d. %B %Y');
		$count++;
		if($r['cheat_img'] == "") {
			$r['cheat_img'] = "/images/noimg.png";
		}
		
$html .= <<<EOF
		<td width='{$td_width}%' align='left' style='background-color:#F3E2E0;padding:6px;' valign="top">
			<div class="tableborder">
				  <div class="tablesubheader">
					<table width="100%" cellspacing="0" cellpadding="0" border="0">
						<tr>	  	 
							<td width="70%">
						  		<a href="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=edit_cheat&amp;cheat_id={$r['cheat_id']}&amp;catid={$r['catid']}" style="font-size: 12px; font-weight: bold;">{$r['cheat_title']}</a>
						  	   		<span class="graytext">(ID: {$r['cheat_id']})</span>
						  	 	</td>
						  	 	<td width="30%" align="center">
						  	 		<img src="{$ipbwi->getBoardVar('url')}skin_acp/IPB2_Standard/images/folder_components/index/view.png" alt="Edit" border="0"/>
						  	 		<a href="{$ipbwi->getBoardVar('home_url')}admin/index.php?act=cheats&amp;code=edit_cheat&amp;cheat_id={$r['cheat_id']}&amp;catid={$r['cheat_catid']}">Edit</a>&nbsp;&nbsp;
									<img src="{$ipbwi->getBoardVar('url')}skin_acp/IPB2_Standard/images/aff_cross.png" alt="Delete" border="0"/>
									<a href="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=delete_cheat&amp;cheat_id={$r['cheat_id']}&amp;catid={$r['catid']}">Delete</a>
								</td>
							</tr>
					</table>
				</div>
				</div>
				<div>
					<table width="100%" cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td width='1%' align='center' rowspan="5" class="tablerow1"><img src="{$r['cheat_img']}" width="80px" alt="" /></td> 
						<td class="tablerow1" valign="top">Posted By: <strong>{$r['cheat_author']}</strong></td>
					</tr>
					<tr>
						<td class="tablerow1" valign="top">Posted on: <strong>{$cheat_posted}</strong></td>
					</tr>
					<tr>
						<td class="tablerow1" valign="top">Category: <strong>{$r['cat_name']}</strong></td>
					</tr>
					<tr>
						<td class="tablerow1" valign="top">{$r['cheat_desc']}</td>
					</tr>
				</table>
			</div>
		</td>
EOF;
		if ($count == $per_row ){
			$html .= "</tr>\n\n<tr align='center'>";
			$count   = 0;
		}
	}
	
	if ( $count > 0 and $count != $per_row )
	{
		for ($i = $count ; $i < $per_row ; ++$i){
			$html .= "<td class='tablerow2'>&nbsp;</td>\n";
		}
		
		$html .= "</tr>";
	}

$html .= <<<EOF
	</table>
</div>
<br />
EOF;

$ipbwi->DB->query("SELECT c.*,a.* FROM ipbwi_cheats a, ipbwi_categories c 
	WHERE a.cheat_validated = '1'
	AND a.cheat_catid = c.catid 
	AND a.cheat_rel = '$rel'");
$html .= <<<EOF
<div class='tableborder'>
	<div class='tableheaderalt'>{$reltitle}</div>
	<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
EOF;
	$per_row  = 3;
	$td_width = 100 / $per_row;
	$count    = 0;
	$html   .= "<tr align='center'>\n";
		
	while ($r = $ipbwi->DB->fetch_row($query)) {
		$cheat_posted = $ipbwi->date($r['cheat_posted'],'%d. %B %Y');
		if($r['cheat_img'] == "") {
			$r['cheat_img'] = "/images/noimg.png";
		}
		$count++;
$html .= <<<EOF
	<td width='{$td_width}%' align='left' style='background-color:#F1F1F1;padding:6px;' valign="top">
		<div class="tableborder">
			<div class="tablesubheader">
				<table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="70%">
							<a href="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=edit_cheat&amp;cheat_id={$r['cheat_id']}&amp;catid={$r['catid']}">{$r['cheat_title']}</a>
						  	<span class="graytext">(ID: {$r['cheat_id']})</span>
						</td>
						<td width="30%" align="center">
							<img src="{$ipbwi->getBoardVar('url')}skin_acp/IPB2_Standard/images/folder_components/index/view.png" alt="Edit" />
							<a href="{$ipbwi->getBoardVar('home_url')}admin/index.php?act=cheats&amp;code=edit_cheat&amp;cheat_id={$r['cheat_id']}&amp;catid={$r['cheat_catid']}">Edit</a>&nbsp;&nbsp;
							<img src="{$ipbwi->getBoardVar('url')}skin_acp/IPB2_Standard/images/aff_cross.png" alt="Delete" />
							<a href="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=delete_cheat&amp;cheat_id={$r['cheat_id']}&amp;catid={$r['catid']}">Delete</a>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div>
			<table width="100%" cellspacing="0" cellpadding="2" border="0">
				<tr>
					<td width='1%' align='center' rowspan="5" class="tablerow1"><img src="{$r['cheat_img']}" width="80px" alt="" /></td> 
					<td class="tablerow1">Posted By: <strong>{$r['cheat_author']}</strong></td>
				</tr>
				<tr>
					<td class="tablerow1">Posted on: <strong>{$cheat_posted}</strong></td>
				</tr>
				<tr>
					<td class="tablerow1">Category: <strong>{$r['cat_name']}</strong></td>
				</tr>
				<tr>
					<td class="tablerow1">{$r['cheat_desc']}</td>
				</tr>
			</table>
		</div>
	</td>
EOF;
		if ($count == $per_row ){
			$html .= "</tr>\n\n<tr align='center'>";
			$count   = 0;
		}
	}
	
	if ( $count > 0 and $count != $per_row )
	{
		for ($i = $count ; $i < $per_row ; ++$i){
			$html .= "<td class='tablerow2'>&nbsp;</td>\n";
		}
		
		$html .= "</tr>";
	}

$html .= <<<EOF
	</table>
</div>
<br />
EOF;
echo $html;
}
		


//-----------------------------------------------//
//				edit ARTICLE
//-----------------------------------------------//
	function edit_cheat() {
		global $ipbwi;
		$cheat_id = $ipbwi->makesafe($_REQUEST['cheat_id']);
		$catid = $ipbwi->makesafe($_REQUEST['catid']);
		
		$ipbwi->DB->query("SELECT * FROM ipbwi_cheats a, ipbwi_categories c WHERE a.cheat_id = '$cheat_id' AND c.catid = '$catid'");
		$r = $ipbwi->DB->fetch_row($query);
		$rel = $r['cheat_rel'];
$html .= <<<EOF
	<div class='tableborder'>
		<form action="{$ipbwi->getBoardVar('home_url')}admin/index.php?act=cheats&code=update_cheat" method="post">
			<div class='tableheaderalt'>Editing: {$r['cheat_title']}</div>
				<table cellpadding='4' cellspacing='0' width='100%'>
				<input type="hidden" name="cheat_id" value="{$r['cheat_id']}" />
					<tr>
						<td class='tablerow1' width="20%" valign="middle">Title</td>
						<td class='tablerow2' width="80%"><input type="text" name="cheat_title" value="{$r['cheat_title']}" /></td>
					</tr>
					<tr>
						<td class='tablerow1' width="20%" valign="middle">Image</td>
						<td class='tablerow2' width="80%"><img src="{$r['cheat_img']}" width="50px" alt="" />
							<input type="text" name="cheat_img" value="{$r['cheat_img']}" />
						</td>
					<tr>
						<td class='tablerow1' width="20%" valign="middle">Description</td>
						<td class='tablerow2' width="80%" valign="top">
						<textarea cols="50" rows="5" name="cheat_desc">{$r['cheat_desc']}</textarea>
						</td>
					</tr>
					<tr>
						<td class='tablerow1' width="20%" valign="middle">Author</td>
						<td class='tablerow2' valign="top" width="80%"><input type="text" name="cheat_author" value="{$r['cheat_author']}" /></td>
					</tr>
					<tr>
					<td class='tablerow1' width="20%" valign="middle">Category</td>
						<td class='tablerow2' width="80%" valign="top">
							<select name="catid">
EOF;
$ipbwi->DB->query("SELECT * FROM ipbwi_categories WHERE cat_rel='$rel'");
while($c = $ipbwi->DB->fetch_row($query)) {
if ($r['catid'] == $c['catid']) {
$html .= <<<EOF
<option value="{$c['catid']}" selected="selected">{$c['cat_name']}</option>
EOF;
}
else {
$html .= <<<EOF
<option value="{$c['catid']}">{$c['cat_name']}</option>
EOF;
}


}

$html .= <<<EOF
		</select>
	</td>
	</tr>
	<tr>
	<td class='tablerow1' width="20%" valign="middle">Validated</td>
	<td class='tablerow2' width="80%">
		<select name="cheat_validated">
EOF;

if ($r['cheat_validated'] == 1) {
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
	<tr>
		<td class='tablerow1' width="20%" valign="middle">Type</td>
		<td class='tablerow2' width="80%">
			<select name="cheat_rel">
				<option value="{$r['cheat_rel']}" selected="selected">What type</option>
				<option value="cheats">Cheat</option>
				<option value="guides">Guide</option>
			</select>
		</td>
	</tr>

	<tr>
		<td class='tablerow1' width="20%" valign="middle">Body</td>
		<td class='tablerow2' width="80%">
			<textarea name="cheat_body" cols="120" rows="20" class='tinymce'>{$r['cheat_body']}</textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2" align='center' class="tablesubheader">
			<input type="submit" name="submit" value="Update" class="realdarkbutton" />
			<a href="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=delete_cheat&amp;cheat_id={$r['cheat_id']}&amp;catid={$r['catid']}">Delete</a>
		</td>
	</tr>
	</form>	
EOF;
// if subcheats
$ipbwi->DB->query("SELECT * FROM ipbwi_sub_cheats WHERE subcheat_cheatid='$cheat_id'");
if ($ipbwi->DB->get_num_rows($s) != NULL) {
	while($s = $ipbwi->DB->fetch_row($query)) {	
$html .= <<<EOF
				<form action="{$ipbwi->getBoardVar('home_url')}admin/index.php?act=cheats&code=update_subcheat" method="post">
					<input type="hidden" name="cheat_id" value="{$r['cheat_id']}" />
					<input type="hidden" name="subcheat_id" value="{$s['subcheat_id']}" />
					<input type="hidden" name="subcheat_catid" value="{$ipbwi->makesafe($_REQUEST['catid'])}" />
					<tr>
						<td class='tablerow1' width="20%" valign="middle">Description</td>
						<td class='tablerow2' width="80%" valign="top">
						<textarea cols="50" rows="5" name="subcheat_desc">{$s['subcheat_desc']}</textarea>
						</td>
					</tr>
					<tr>
						<td class='tablerow1' width="20%" valign="middle">Author</td>
						<td class='tablerow2' valign="top" width="80%"><input type="text" name="subcheat_author" value="{$s['subcheat_author']}" /></td>
					</tr>
					<tr>
						<td class='tablerow1' width="20%" valign="middle">Body</td>
						<td class='tablerow2' width="80%">
							<textarea name="subcheat_body" cols="120" rows="20" class='tinymce'>{$s['subcheat_body']}</textarea>
						</td>
					</tr>
					<tr>
						<td class='tablerow1' width="20%" valign="middle">Validated</td>
						<td class='tablerow2' width="80%">
							<select name="subcheat_validated">
EOF;

if ($s['subcheat_validated'] == 1) {
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
	<tr>
		<td colspan="2" align='center' class="tablesubheader">
			<input type="submit" name="submit" value="Update" class="realdarkbutton" />
			<a href="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=delete_subcheat&amp;subcheat_id={$s['subcheat_id']}">Delete</a>
		</td>
	</tr>
	</form>
EOF;
	}
} // end subcheats

$html .= <<<EOF
	</table>
</div>
<br />
EOF;
echo $html;
}
	
function update_cheat() {
	global $ipbwi;
		$cheat_id = $ipbwi->makesafe($_REQUEST['cheat_id']);
		$cheat_author = $ipbwi->makesafe($_REQUEST['cheat_author']);
		$cheat_img = $ipbwi->makesafe($_REQUEST['cheat_img']);
		$cheat_desc = $ipbwi->makesafe($_REQUEST['cheat_desc']);
		$cheat_body = $ipbwi->makesafe($_REQUEST['cheat_body']);
		$cheat_validated = $ipbwi->makesafe($_REQUEST['cheat_validated']);
		$cheat_title = $ipbwi->makesafe($_REQUEST['cheat_title']);
		$cheat_rel = $ipbwi->makesafe($_REQUEST['cheat_rel']);
		$catid = $ipbwi->makesafe($_REQUEST['catid']);
		if ($cheat_validated == '1'){	
			$act = "+ 1";
		} else { $act = "-1";}
		
		$ipbwi->DB->query("UPDATE ipbwi_categories c,ipbwi_cheats a SET 
		a.cheat_catid = '$catid',
		a.cheat_validated = '$cheat_validated',
		a.cheat_title = '$cheat_title',
		a.cheat_img = '$cheat_img',
		a.cheat_desc = '$cheat_desc',
		a.cheat_body = '$cheat_body',
		a.cheat_author = '$cheat_author',
		a.cheat_rel = '$cheat_rel' 
		WHERE c.catid = '$catid' AND a.cheat_id = '$cheat_id'");				
		$ipbwi->boink_it($url="?act=cheats&amp;code=edit_cheat&amp;cheat_id=".$cheat_id."&amp;catid=".$catid."",$msg="Updated...");
}

function update_subcheat() {
	global $ipbwi;
		$subcheat_id = $ipbwi->makesafe($_REQUEST['subcheat_id']);
		$subcheat_author = $ipbwi->makesafe($_REQUEST['subcheat_author']);
		//$subcheat_img = $ipbwi->makesafe($_REQUEST['subcheat_img']);
		$subcheat_desc = $ipbwi->makesafe($_REQUEST['subcheat_desc']);
		$subcheat_body = $ipbwi->makesafe($_REQUEST['subcheat_body']);
		$subcheat_validated = $ipbwi->makesafe($_REQUEST['subcheat_validated']);
		$subcheat_title = $ipbwi->makesafe($_REQUEST['subcheat_title']);
		$subcheat_catid = $ipbwi->makesafe($_REQUEST['subcheat_catid']);
		$cheat_id = $ipbwi->makesafe($_REQUEST['cheat_id']);
		if ($subcheat_validated == '1'){	
			$act = "+ 1";
		} else { $act = "-1";}
		
		$ipbwi->DB->query("UPDATE ipbwi_sub_cheats a SET 
		subcheat_validated = '$subcheat_validated',
		subcheat_title = '$subcheat_title',
		subcheat_img = '$subcheat_img',
		subcheat_desc = '$subcheat_desc',
		subcheat_body = '$subcheat_body',
		subcheat_author = '$subcheat_author'
		WHERE subcheat_id = '$subcheat_id'");				
		$ipbwi->boink_it($url="?act=cheats&amp;code=edit_cheat&amp;cheat_id=".$cheat_id."&amp;catid=".$subcheat_catid."",$msg="Updated...");
}

function delete_cheat() {
	global $ipbwi;
		$cheat_id = $ipbwi->makesafe($_REQUEST['cheat_id']);
		$catid = $ipbwi->makesafe($_REQUEST['catid']);
		$ipbwi->DB->query("DELETE FROM ipbwi_cheats WHERE cheat_id = '$cheat_id'");
		$ipbwi->DB->query("UPDATE ipbwi_categories SET cat_num_cheats = cat_num_cheats -1 WHERE catid = '$catid'");				
		$ipbwi->boink_it($url="?act=cheats",$msg="Deleted...");
}

function delete_subcheat() {
	global $ipbwi;
		$subcheat_id = $ipbwi->makesafe($_REQUEST['subcheat_id']);
		$catid = $ipbwi->makesafe($_REQUEST['catid']);
		$ipbwi->DB->query("DELETE FROM ipbwi_sub_cheats WHERE subcheat_id = '$subcheat_id'");
		$ipbwi->boink_it($url="?act=cheats",$msg="Deleted...");
}

function add_cheat() {
global $ipbwi;

$member = $ipbwi->member->info();

		$rel = $ipbwi->makesafe($_REQUEST['rel']);
		if ($rel == "cheats") {
			$reltitle = "Cheat";
		}
		if ($rel == "guides") {
			$reltitle = "Guide";
		}

$html .= <<<EOF
	<div class='tableborder'>
	<form action="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=doadd_cheat" method="POST" >
		<div class='tableheaderalt'>Add {$reltitle}</div>
		<input type="hidden" value="{$rel}" name="cheat_rel" />
		<input type="hidden" value="{$member['id']}" name="cheat_authorid" />
		<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
		<tr>
		<td class='tablerow1' width='20%' valign='middle'><b>{$reltitle} Title</b></td>
		<td class='tablerow2' width='20%' valign='middle'>
		<input type="text" name="cheat_title" size='30' class='textinput'>
		</td>
		</tr>
		<tr>
		<td class='tablerow1' width='20%' valign='middle'><b>{$reltitle} Image</b></td>
		<td class='tablerow2' width='80%' valign='middle'>
		<input type="text" name="cheat_img" size='30' class='textinput'>
		</td>
		</tr>
		<tr>
		<td class='tablerow1' width='20%' valign='middle'><b>{$reltitle} Author</b></td>
		<td class='tablerow2' width='80%' valign='middle'>
		<input type="text" name="cheat_author" size='30' class='textinput' value="{$member['members_display_name']}">
		</td>
		</tr>
		<tr>
		<td class='tablerow1' width='20%' valign='middle'><b>Category</b></td>
		<td class='tablerow2' width='80%' valign='middle'>
		<select name="catid">
		<option value="">Select Category</option>
EOF;
$ipbwi->DB->query("SELECT * FROM ipbwi_categories WHERE cat_rel='$rel' ORDER BY cat_rel");

	while($c = $ipbwi->DB->fetch_row($query)){
		if($c['cat_parentid']==0) {
			$html .= <<<EOF
EOF;
		}
		if($c['cat_parentid']!=0) {
			$html .= <<< EOF
			<option value="{$c['catid']}">{$c['cat_name']}</option>
EOF;
		}
	}
	
$html .= <<<EOF
</select>
		</td>
		</tr>	
		<tr>
		<td class='tablerow1' width='20%' valign='middle'><b>{$reltitle} Description</b><div style='color:gray;'></div></td>
		<td class='tablerow2' width='80%' valign='middle'>
		<textarea name='cheat_desc' cols='80' rows='5'></textarea></td>
		</tr>
		<tr>
		<td class='tablerow1'  width='20%' valign='middle'><b>{$reltitle} Body</b><div style='color:gray;'></div></td>
		<td class='tablerow2'  width='80%' valign='middle'>
		<textarea name='cheat_body' cols='80' rows='15' class='tinymce'></textarea></td>												
		</tr>
		</table></div><br />
		<br /><div class='tableborder'><div align='center' class='tablesubheader'>
		<input type='submit' name="submit" value='Add {$reltitle}' class='realbutton'></div></div>
	</form>
EOF;
echo $html;
}

function doadd_cheat() {
	global $ipbwi;
		$cheat_rel = $ipbwi->makesafe($_REQUEST['cheat_rel']);
		$cheat_title = $ipbwi->makesafe($_REQUEST['cheat_title']);
		$cheat_img = $ipbwi->makesafe($_REQUEST['cheat_img']);
		$cheat_desc = $ipbwi->makesafe($_REQUEST['cheat_desc']);
		$cheat_author = $ipbwi->makesafe($_REQUEST['cheat_author']);
		$cheat_authorid = $ipbwi->makesafe($_REQUEST['cheat_authorid']);
		$cheat_body = $ipbwi->makesafe($_REQUEST['cheat_body']);
		$cheat_posted = time();
		$catid = $ipbwi->makesafe($_REQUEST['catid']);
		
		// if a cheat is named the same merge them
		$ipbwi->DB->query("SELECT cheat_id, cheat_title, cheat_body FROM ipbwi_cheats WHERE cheat_title LIKE '%$cheat_title%' AND cheat_rel = '$cheat_rel'");
		if($ipbwi->DB->get_num_rows($query) >= 1 ) {			
			$r = $ipbwi->DB->fetch_row($query);
			$cheat_id = $r['cheat_id'];			
			
			$ipbwi->DB->query("UPDATE ipbwi_cheats SET cheat_hassub = '1' WHERE cheat_id = '$cheat_id'");
			$ipbwi->DB->query("INSERT INTO ipbwi_sub_cheats 
		(	
		subcheat_title,
		subcheat_img,
		subcheat_desc,
		subcheat_body,
		subcheat_author,
		subcheat_authorid,
		subcheat_posted,
		subcheat_cheatid
		) VALUES(
		'$cheat_title',
		'$cheat_img',
		'$cheat_desc',
		'$cheat_body',
		'$cheat_author',
		'$cheat_authorid',
		'$cheat_posted',
		'$cheat_id'
		)");	
			$ipbwi->boink_it($url="?act=cheats",$msg=" Merging...");
			return;
		}
		else {
		
		$ipbwi->DB->query("INSERT INTO ipbwi_cheats 
		(	
		cheat_title,
		cheat_img,
		cheat_desc,
		cheat_body,
		cheat_author,
		cheat_authorid,
		cheat_rel,
		cheat_posted,
		cheat_catid
		) VALUES(
		'$cheat_title',
		'$cheat_img',
		'$cheat_desc',
		'$cheat_body',
		'$cheat_author',
		'$cheat_authorid',
		'$cheat_rel',
		'$cheat_posted',
		'$catid'
		)");
		
		$ipbwi->boink_it($url="?act=cheats",$msg=$reltitle." Added...");
		return;
		}
}

function view_categories() {
	global $ipbwi,$acp;
$query = $ipbwi->DB->query("SELECT * FROM ipbwi_categories WHERE cat_parentid = '0'");
while($parent = $ipbwi->DB->fetch_row($query)) {

$parent_cat = $parent['catid'];
$html .= <<<EOF
<div class="tableborder">
<div class="tableheaderalt">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
 <tr>
  <td width="95%" align="left" style="font-size: 12px; vertical-align: middle; font-weight: bold; color:#FFF;">{$parent['cat_name']}
  </td>
  <td width="5%" nowrap="nowrap" align="right" >
<a href="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=edit_category&amp;catid={$parent['catid']}">
<img class="ipd" src="{$acp->skin_acp_url}/images/filebrowser_action.gif"/>
</a>
</td>
 </tr>
</table> 
</div>
EOF;
 
$query_sub = $ipbwi->DB->query("SELECT * FROM ipbwi_categories WHERE cat_parentid = '$parent_cat'");
if($parent_cat != 0) {
while($sub = $ipbwi->DB->fetch_row($query_sub)) {
$html .= <<<EOF
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
 <td width="95%" class="tablerow1">
	<strong style="font-size: 11px;">{$sub['cat_name']}</strong>
	<div class="graytext">{$sub['cat_desc']}</div> 
</td>
<td width="5%" nowrap="nowrap" align="right" class="tablerow1">
<a href="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=edit_category&amp;catid={$sub['catid']}">
<img class="ipd" src="{$acp->skin_acp_url}/images/filebrowser_action.gif"/>
</a>
</td>
</tr>
</table>

EOF;
}//1
$html .= <<<EOF
</div>
<br />
EOF;
}//if

}//2
echo $html;
}

function edit_category(){
	global $ipbwi;
	$catid = $ipbwi->makesafe($_REQUEST['catid']);
	$ipbwi->DB->query("SELECT * FROM ipbwi_categories WHERE catid = '$catid'");
	$r = $ipbwi->DB->fetch_row($query);
	$rel = $r['cat_rel'];

$html .= <<<EOF
<form action="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=update_category" method="POST" >
<input type="hidden" name="catid" value="{$catid}" />
<div class="tableborder">
 <div class="tableheaderalt">Editing category: {$r['cat_name']}</div>
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
 
 <tr>
   <td width="40%" colspan="2" class="tablerow1">
    
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
   		<td width="40%" class="tablerow1"><strong>Category Name</strong></td>
   		<td width="60%" class="tablerow2"><input type="text" class="textinput" size="30" value="{$r['cat_name']}" name="cat_name"/></td>
 	</tr> 	
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category Description</strong></td>
   		<td width="60%" class="tablerow2"><textarea rows="5" cols="60" name="cat_desc">{$r['cat_desc']}</textarea></td>
 	</tr>
 	
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category Type</strong></td>
   		<td width="60%" class="tablerow2">
   		<select name="cat_rel">
   			<option value="{$rel}">{$rel}</option>
   			<option value="cheats">Cheats</option>
   			<option value="guides">Guides</option>
   		</select>
   		</td>
 	</tr>
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category Parent</strong></td>
   		<td width="60%" class="tablerow2">
   		<select class="dropdown" name="cat_parentid">
   		<option value="0">No parent</option>
EOF;
$ipbwi->DB->query("SELECT * FROM ipbwi_categories WHERE cat_parentid = '0' AND cat_rel = '$rel'");
while($parent = $ipbwi->DB->fetch_row($query)){

$html .= <<<EOF
<option value="{$parent['catid']}">{$parent['cat_name']}</option>
EOF;
}
$html .= <<<EOF
		</select>
</td>
 	</tr>
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category State</strong></td>
   		<td width="60%" class="tablerow2">
   		<select class="dropdown" name="cat_state">
			<option selected="" value="1">Active</option>
			<option value="0">Read Only Archive</option>
		</select>
</td>
 	</tr> 	
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category Image</strong><br />
   		<img src="{$r['cat_img']}" alt="" />
   		</td>
   		<td width="60%" class="tablerow2"><input type="text" value="{$r['cat_img']}" name="cat_img"/></td>
 	</tr>
    </table>
  </td>
 </tr>
 </table>
</div>
<div class="tableborder">
 <div align="center" class="tablefooter"><input type="submit" value="Edit Category" class="realbutton"/>
 <a href="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=delete_category&amp;catid={$catid}">Delete Category</a>
 </div>
</div>
</form>
EOF;
echo $html;
}

function update_category() {
global $ipbwi;
		$catid = $ipbwi->makesafe($_REQUEST['catid']);
		$cat_name = $ipbwi->makesafe($_REQUEST['cat_name']);
		$cat_img = $ipbwi->makesafe($_REQUEST['cat_img']);
		$cat_desc = $ipbwi->makesafe($_REQUEST['cat_desc']);
		$cat_parentid = $ipbwi->makesafe($_REQUEST['cat_parentid']);
		$cat_rel = $ipbwi->makesafe($_REQUEST['cat_rel']);
		$cat_state = $ipbwi->makesafe($_REQUEST['cat_state']);

		$ipbwi->DB->query("UPDATE ipbwi_categories SET 
		cat_name = '$cat_name',
		cat_img = '$cat_img',
		cat_desc = '$cat_desc',
		cat_parentid = '$cat_parentid',
		cat_state = '$cat_state',
		cat_rel = '$cat_rel' 
		WHERE catid = '$catid'");	
		$ipbwi->boink_it($url="?act=cheats&amp;code=view_categories",$msg="Category updated...");
}

function add_category(){
	global $ipbwi;


$html .= <<<EOF
<form action="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=doadd_category" method="POST" >
<input type="hidden" name="catid" value="{$r['catid']}" />
<div class="tableborder">
 <div class="tableheaderalt">Add Category</div>
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
 <tr>
   <td width="40%" colspan="2" class="tablerow1">
    
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
   		<td width="40%" class="tablerow1"><strong>Category Name</strong></td>
   		<td width="60%" class="tablerow2"><input type="text" class="textinput" size="30" name="cat_name"/></td>
 	</tr> 	
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category Description</strong></td>
   		<td width="60%" class="tablerow2"><textarea rows="5" cols="60" name="cat_desc"></textarea></td>
 	</tr>
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category Type</strong></td>
   		<td width="60%" class="tablerow2">
   		<select name="cat_rel">
   			<option value="cheats">Cheats</option>
   			<option value="guides">Guides</option>
   		</select>
   		</td>
 	</tr>
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category Parent</strong></td>
   		<td width="60%" class="tablerow2">
   		<select class="dropdown" name="cat_parentid">
   		<option value="0">No parent</option>
EOF;

$ipbwi->DB->query("SELECT * FROM ipbwi_categories");
while($parent = $ipbwi->DB->fetch_row($query)){
$html .= <<<EOF
<option value="{$parent['catid']}">{$parent['cat_name']}</option>
EOF;
}
$html .= <<<EOF
		</select>
</td>
 	</tr>
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category State</strong></td>
   		<td width="60%" class="tablerow2">
   		<select class="dropdown" name="cat_state">
			<option selected="" value="1">Active</option>
			<option value="0">Read Only Archive</option>
		</select>

</td>
 	</tr> 	
 	<tr>
   		<td width="40%" class="tablerow1"><strong>Category Image</strong><br />
   		</td>
   		<td width="60%" class="tablerow2"><input type="text" name="cat_img"/></td>
 	</tr>
    </table>
  </td>
 </tr>
 </table>
</div>
<div class="tableborder">
 <div align="center" class="tablefooter"><input type="submit" value="Add Category" class="realbutton"/></div>
</div>
</form>
EOF;
echo $html;
}


function doadd_category() {
	global $ipbwi;
		$cat_rel = $ipbwi->makesafe($_REQUEST['cat_rel']);
		$cat_name = $ipbwi->makesafe($_REQUEST['cat_name']);
		$cat_img = $ipbwi->makesafe($_REQUEST['cat_img']);
		$cat_desc = $ipbwi->makesafe($_REQUEST['cat_desc']);
		$cat_parentid = $ipbwi->makesafe($_REQUEST['cat_parentid']);
		$cat_state = $ipbwi->makesafe($_REQUEST['cat_state']);
		$ipbwi->DB->query("INSERT INTO ipbwi_categories 
		(	
		cat_name,
		cat_img,
		cat_desc,
		cat_parentid,
		cat_rel,
		cat_state
		) VALUES(	
		'$cat_name',
		'$cat_img',
		'$cheat_desc',
		'$cat_parentid',
		'$cat_rel',
		'$cat_state'
		)");
		
		$ipbwi->boink_it($url="?act=cheats&amp;code=view_categories",$msg="Category Added...");
}


function delete_category(){
	global $ipbwi;

$catid = $ipbwi->makesafe($_REQUEST['catid']);
$html .= <<<EOF
<form action="{$ipbwi->getBoardVar('home_url')}/admin/index.php?act=cheats&amp;code=dodelete_category" method="POST" >
<input type="hidden" name="catid" value="{$catid}" />
<div class="tableborder">
 <div class="tableheaderalt">Delete Category</div>
 <table width="100%" cellspacing="0" cellpadding="0" border="0">
 <tr>
   <td width="40%" colspan="2" class="tablerow1">
    
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
 	<tr>
   		<td width="40%" class="tablerow1"><strong>New Category</strong></td>
   		<td width="60%" class="tablerow2">
   		<select class="dropdown" name="new_cat">
   		<option value="0">Select a new category</option>
EOF;

$ipbwi->DB->query("SELECT * FROM ipbwi_categories");
while($new_cat = $ipbwi->DB->fetch_row($query)){

$html .= <<<EOF
<option value="{$new_cat['catid']}">{$new_cat['cat_name']}</option>
EOF;
}
$html .= <<<EOF
		</select>
</td>
 	</tr>
    </table>
  </td>
 </tr>
 </table>
</div>
<div class="tableborder">
 <div align="center" class="tablefooter"><input type="submit" value="Delete Category" class="realbutton"/></div>
</div>
</form>
EOF;
echo $html;
}

function dodelete_category() {
	global $ipbwi;
		$catid = $ipbwi->makesafe($_REQUEST['catid']);
		$new_cat = $ipbwi->makesafe($_REQUEST['new_cat']);
		$ipbwi->DB->query("UPDATE ipbwi_cheats SET cheat_catid = '$new_cat' WHERE cheat_catid = '$catid' ");
		$ipbwi->DB->query("DELETE FROM ipbwi_categories WHERE catid='$catid'");
		$ipbwi->boink_it($url="?act=cheats&amp;code=view_categories",$msg="Category Deleted...");
}

function dashboard() {
	global $ipbwi;
	
	$cheats = $ipbwi->DB->query("SELECT * FROM ipbwi_cheats");
	$r = $ipbwi->DB->fetch_row($cheats);
	$total_cheats = $ipbwi->DB->get_num_rows($cheats);
	$wubs = $ipbwi->DB->query("SELECT * FROM ipbwi_wubs");
	$total_wubs = $ipbwi->DB->get_num_rows($wubs);
	$comments = $ipbwi->DB->query("SELECT * FROM ipbwi_comments");
	$total_comments = $ipbwi->DB->get_num_rows($comments);
	$categories = $ipbwi->DB->query("SELECT * FROM ipbwi_categories");
	$total_categories = $ipbwi->DB->get_num_rows($categories);
	
$html .= <<<EOF

<table border=0 width=100%>
	<tr>
		<td width=49% valign='top'>
			<div class='tableborder'>
				<div class='tableheaderalt'>Stats</div>
					<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
					<tr>
						<td class='tablerow1' valign='middle'><strong>Total </strong></td>
						<td class='tablerow2' valign='middle'><strong>{$total}</strong> Cheats: ({$total_cheats}) Guides: ({$guides})</td>
					</tr>
					<tr>
						<td class='tablerow1' valign='middle'>&nbsp;&nbsp;&#124;-<strong>Total wubs</strong></td>
						<td class='tablerow2' valign='middle'>{$total_wubs}</td>
					</tr>
					<tr>
						<td class='tablerow1' valign='middle'>&nbsp;&nbsp;&#124;-<strong>Total views</strong></td>
						<td class='tablerow2' valign='middle'><strong></td>
					</tr>
					<tr>
						<td class='tablerow1' valign='middle'>&nbsp;&nbsp;&#124;- <strong>Comments</strong></td>
						<td class='tablerow2' valign='middle'>{$total_comments}</td>
					</tr>
					<tr>
						<td class='tablerow1' valign='middle'>&nbsp;&nbsp;&#124;- <strong>Categories</strong></td>
						<td class='tablerow2' valign='middle'>{$total_categories}</td>
					</tr>
					<tr>
						<td class='tablerow1' valign='middle'><strong>Top Poster</strong></td>
						<td class='tablerow2' valign='middle'></td>
					</tr>
					<tr>
			</table>
		</div>
	</td>
	<td width=2%><!-- --></td>
	<td width=49% valign='top'>
		<div class='tableborder'>
			<div class='tableheaderalt'>Info</div>
				<table width='100%' cellspacing='0' cellpadding='5' align='center' border='0'>
					<tr>
						<td class='tablerow1'  width='40%' valign='middle'><strong>Awaiting validation</strong></td>
						<td class='tablerow2'  width='60%' valign='middle'></td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>
EOF;
echo $html;
}




}//endclass
?>