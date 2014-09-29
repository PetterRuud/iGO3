<?php
class class_affiliates {
	/*
	/********************************************************
	/
	/						AFFILIATES
	/
	/********************************************************
	*/
	function auto_load() {
		global $ipbwi;

		switch ($ipbwi->makesafe($_REQUEST['code'])) {
		case "add" :
			$this->addlink();
			break;
		case "doadd" :
			$this->doadd();
			break;
		case "view":
			$this->viewall();
			break;
		case "out":
			$this->out();
			break;
		}

	}


	function add() {
		global $ipbwi;
		$HTML = <<<EOF
<div class="article">
	<div class="byline">
		<h1 style="float:none;">Our buttons</h1>
	</div>
	<p>			
		<b>88x31px buttons</b><br />
		<img src="{$ipbwi->getBoardVar('home_url')}/images/buttons/igobt88x31.gif" alt="itsGAMEOVER" />
				
		<div>
			<textarea cols="50" rows="3"><a href="http://itsgameover.com" title="itsGAMEOVER"><img src="http://itsgameover.com/images/buttons/igobt88x31.gif" alt="itsGAMEOVER" /></a></textarea>
		</div>
			
		<br />
		<b>468x60px banners</b><br />
		<img src="{$ipbwi->getBoardVar('home_url')}/images/buttons/igobt468x60.gif" alt="itsGAMEOVER" />
						
		<div>
			<textarea cols="50" rows="3"><a href="http://itsgameover.com" title="itsGAMEOVER"><img src="http://itsgameover.com/images/buttons/igobt468x60.gif" alt="itsGAMEOVER" /></a></textarea>
		</div>
		<br /><br />
	</p>

	<h2>Submit yours</h2>
	<div>
		<div class="byline">
			<h1>Affiliation Rules</h1>
		</div>
		<div class="add_cheat">
			<p>We welcome all sites to advertise in our affiliate system, but we do have some guidelines!</p>
			<ul>
				<li>88 x 31 Affiliate banner</li>
				<li>A well typed description of your website</li>
				<li>No sexual content</li>
			</ul>
			<p>We <b><u>do not</u></b> affiliate with InvisionFree sites, sites on free hosting, or illegal boards.</p>
			<p>We're always open to help other sites grow, but we want to get something in return. Contact us to see what sort of offer we can work out between the two sites for further affiliation.</p>
			<p>
				<form id="form" action='{$ipbwi->getBoardVar('home_url')}index.php?act=affiliates&amp;code=doadd' method='post'>
					<label for="url">URL(include http://):</label>
					<input id="url" type='text' name='url' size='40' class="required" /><br />
					<label for="button">Image(button URL):</label>
					<input type='text' id="button" name='button' size='40' class="required" /><br />
					<input type='submit' name='submit' value='Submit'>
				</form>	
			</p>
		</div>
	</div>
</div>				
EOF;
echo $HTML;
	}


	/**
	 *
	 */
	function doadd() {
		global $ipbwi;
		$affiliate_url = $ipbwi->makesafe($_REQUEST['url']);
		$affiliate_button = $ipbwi->makesafe($_REQUEST['button']);
		if (strlen($affiliate_url)<1) {
			$html .= <<<EOF
        <div style="margin: 3px 15px;" class="errorwrap">
			<h4>The error returned was:</h4>
			<p>You did not fill in a valid URL</p>
		</div>
EOF;
echo $html;
			return;
		}
		if (strlen($affiliate_button)<1) {
$html .= <<<EOF
        <div style="margin: 3px 15px;" class="errorwrap">
			<h4>The error returned was:</h4>
			<p>You did not fill a valid button URL</p>
		</div>
EOF;
echo $html;
			return;
		}
		$ipbwi->DB->query("INSERT into ipbwi_affiliates (affiliate_url, affiliate_button,affiliate_validated) values('$affiliate_url','$affiliate_button','0')");
		$ipbwi->boink_it($ipbwi->getBoardVar('home_url'), $msg="Your Request has been submitted, the request must be validated by a high staff before your button will show.");
	}


	/**
	 *
	 */
	function viewall() {
		global $ipbwi;
		
		$html .= <<<EOF
		<p><table cellpadding="3" cellspacing="0" border="0" width="100%">
EOF;
		$per_row  = 3;
		$td_width = ceil(100 / $per_row);
		$count    = 0;
		 $html .= <<<EOF
		 <tr align="center">
EOF;

		$ipbwi->DB->query("SELECT * FROM ipbwi_affiliates WHERE affiliate_validated='1' ORDER BY affiliate_hits desc");
		while ($r = $ipbwi->DB->fetch_row($query)) {
				$count++;
				$button = $r['affiliate_button'];
				$id=$r['affiliate_id'];
				$hits=$r['affiliate_hits'];
				$td_witdh = $td_width;
				$html .= <<<EOF
				<td width="{$td_width}%" align="left" style="padding:6px;">
					<a href="{$ipbwi->getBoardVar('home_url')}/?act=affiliates&amp;code=out&amp;id={$id}"><img src="{$button}" border="0" height="31px" width="88px" alt="" /></a><br />
					<span class="desc"><strong>Hits out ({$hits})</strong></span>
				</td>
EOF;
				if ($count == $per_row ) {
					$html .= <<<EOF
					 </tr>
					 <tr align="center">
EOF;
					$count = 0;
				}
		}

		if ( $count > 0 and $count != $per_row ) {
			for ($i = $count ; $i < $per_row ; ++$i) {
				$html .= <<< EOF
				<td class="row2">&nbsp;</td>
EOF;
			}

			$html .= <<<EOF
			</tr>
EOF;
		}
		$html .= <<<EOF
		</table>
	</p><br />
EOF;
echo $html;
		$this->add();
	}


	/**
	 *
	 *
	 * @param unknown $num
	 * @return unknown
	 */
	function shownum($num) {
		global $ipbwi;
		$num=$num; // number of affiliates
		$ipbwi->DB->query("SELECT * FROM ipbwi_affiliates WHERE affiliate_validated='1' ORDER BY RAND() LIMIT $num");
		$html .= <<<EOF
		<table width="100%">
EOF;
		while ($r = $ipbwi->DB->fetch_row($query)) {
			$id = $r['affiliate_id'];
			$url = $r['affiliate_url'];
			$hits = $r['affiliate_hits'];
			$button = $r['affiliate_button'];
		$html .= <<<EOF
		<tr>
			<td align="center">
				<a href="{$ipbwi->getBoardVar('home_url')}index.php?act=affiliates&amp;code=out&amp;id={$id}"><img src="{$button}" border="0" height="31px" width="88px" alt="{$url}"/></a>
			</td>
		</tr>
EOF;
		}
		$html .= <<<EOF
	</table>
	<a href="{$ipbwi->getBoardVar('home_url')}index.php?act=affiliates&amp;code=view"><img src="images/bt_all.png" alt="All" style="float:right;" /></a>
EOF;
echo $html;
	}


	/**
	 *
	 */
	function out() {
		global $ipbwi;
		
		$id = $ipbwi->makesafe($_REQUEST['id']);
		$query = $ipbwi->DB->query("SELECT affiliate_url from ipbwi_affiliates where affiliate_id='$id'");
		$r = $ipbwi->DB->fetch_row($query);
		$ipbwi->DB->query("UPDATE ipbwi_affiliates set affiliate_hits=affiliate_hits+1 where affiliate_id='$id'");
		$ipbwi->boink_it($url=$r['affiliate_url'], $msg="You will now be transferred...");
	}



} // EOC
?>