<?php
class class_cheats {
	
function auto_load() {
	global $ipbwi;
	switch ($ipbwi->makesafe($_REQUEST['code']))
	{		
		case "categories":	
		$this->categories();					
		break;
	  	case "category": 	
		$this->category();		
		break;
		case "search":
		$this->search();
		break;
	  	case "add": 	
		$this->add();							
		break;
		case "doadd":
		$this->doadd();
	  	case "edit": 	
		$this->edit();				
		break;
		case 'wub':
		$this->wub();
		break;
		case 'doaddcomment':
		$this->doaddcomment();
		break;
	  	case "view": 	
		$this->view();				
		break;
		default:
		$this->categories();
		break;
	}
}
//-----------------------------------------------//
//				VIEW ARTICLE CATEGORIES
//-----------------------------------------------//
function categories() {
	global $ipbwi;
	
	$html .= <<<EOF
					<div class="top">
						<div class="search">
							<form action="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=search" method="post">
								<div class="searchbg">
									<input type="text" name="cheat_keyword" class="searchinput swap" value="SEARCH"/>
									<img src="images/bt_down.png" alt="" style="margin: -5px 5px 0px -10px;" />
								</div>
								<input type="submit" value="" class="searchsubmit" />
							</form>
						</div>
						<div align="right" style="float:right;margin:6px 15px 0 0;">
							<a href="{$ipbwi->getBoardVar('home_url')}?act=cheats&amp;code=add"><img src="images/bt_submityours.png" alt="submit yours"/></a>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="categories">
					
EOF;

	$query = $ipbwi->DB->query("SELECT * FROM ipbwi_categories WHERE cat_parentid = '0' ORDER BY cat_order");
	
	while ($parent = $ipbwi->DB->fetch_row($query)) {
		$parent_cat = $parent['catid'];
		$html .= <<<EOF
		<h2>{$parent['cat_name']}</h2>
		<ul class="hlist {$parent['cat_name']}">
EOF;
		$query_sub = $ipbwi->DB->query("SELECT * FROM ipbwi_categories WHERE cat_parentid = '$parent_cat' ORDER BY cat_order");
		if($parent_cat != 0) {
			while ($sub = $ipbwi->DB->fetch_row($query_sub)) {
				$html .= <<<EOF
				<li><a href="{$ipbwi->getBoardVar('home_url')}?act=cheats&amp;code=category&amp;catid={$sub['catid']}&amp;page=0" title="{$sub['cat_name']}">{$sub['cat_name']}</a>, </li>
EOF;
			}
		}//if
		$html .= <<<EOF
		</ul>
EOF;
	}
	$html .= <<<EOF
					</ul>
					<div class="featured">		
						<div class="box left">
							<h3>Most popular cheats</h3>					
							<div class="cell">
								<ul>
EOF;
									$html .= $this->most_wubbed("cheats");
$html .= <<<EOF
								</ul>
							</div>
						</div>
						
						<div class="box right">
							<h3>Most popular Guides</h3>					
							<div class="cell">
								<ul>
EOF;
									$html .= $this->most_wubbed("guides");
$html .= <<<EOF
								</ul>
							</div>
						</div>
						<div class="spacer"></div>
											<div class="box left">
							<h3>Latest Cheats</h3>					
							<div class="cell">
								<ul>
EOF;
									$html .= $this->latest("cheats");
$html .= <<<EOF
								</ul>
							</div>
						</div>
						
						<div class="box right">
							<h3>Latest Guides</h3>					
							<div class="cell">
								<ul>
EOF;
									$html .= $this->latest("guides");
$html .= <<<EOF
								</ul>
							</div>
						</div>
					</div>
					<div class="clearfix"></div>
					<div align="right" style="float:right;">
							<a href="{$ipbwi->getBoardVar('home_url')}?act=cheats&amp;code=add"><img src="images/bt_submityours.png" alt="submit yours"/></a>
						</div>
						<div class="clearfix"></div>
				</div>
EOF;
echo $html;
}

function search() {
	global $ipbwi;
	$html .= <<<EOF
					<div class="top">
						<div class="search">
							<form action="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=search" method="post">
								<div class="searchbg">
									<input type="text" name="cheat_keyword" class="searchinput swap" value="SEARCH" />
									<img src="images/bt_down.png" alt="" style="margin: -5px 5px 0px -10px;" />
								</div>
								<input type="submit" value="" class="searchsubmit" />
							</form>
						</div>
						<div align="right" style="float:right;margin:6px 15px 0 0;">
							<a href="{$ipbwi->getBoardVar('home_url')}?act=cheats&amp;code=add"><img src="images/bt_submityours.png" alt="submit yours"/></a>
						</div>
					</div>
					<div class="clearfix"></div>
EOF;
	$page = $ipbwi->makesafe($_REQUEST['page']);
	if ($start == '') {
		$start = 0;
	}
	$per_row = 10;
	$pre = $start - $per_row;
	$next = $start + $per_row;
	if($pre < 0) {
		$pre = 0;
	}
	$page = ceil(($start/$per_row)+1);
	
	$cheat_keyword = $ipbwi->makesafe($_REQUEST['cheat_keyword']);
	$cheat_keyword = trim($cheat_keyword);
	$cheat_keyword_array = explode(" ",$cheat_keyword);
	
	if ($cheat_keyword == "") {
  		$html .=  "<p>Search Error</p><p>Please enter a keyword...</p>" ;
  }
  
  foreach  ($cheat_keyword_array as $keyword){
     $query = "SELECT * FROM ipbwi_cheats WHERE cheat_title LIKE '%$keyword%' OR cheat_desc like '%$keyword%' OR cheat_body like '%$keyword%' ORDER BY cheat_title DESC"; 
     // Execute the query to  get number of rows that contain search kewords
     $numresults = $ipbwi->DB->query($query);
     $last = $ipbwi->DB->get_num_rows($numresults);
          
   // now let's get results.
      $query .= ' LIMIT ' .($start).', ' .($start + $per_row);
            
      $numresults = $ipbwi->DB->query($query) or die ( "Couldn't execute query" );
      $row = $ipbwi->DB->fetch_row($numresults);

      //store record id of every item that contains the keyword in the array we need to do this to avoid display of duplicate search result.
      do{
          $adid_array[] = $row[ 'cheat_id' ];
      }while( $row = $ipbwi->DB->fetch_row($numresults));
 } //end foreach

if($last == 0 && $row_set_num == 0){
   $searchedfor .= "<h2>Search results for: ". $cheat_keyword."</p><p>Sorry, your search returned zero results</h2>" ;
}
   //delete duplicate record id's from the array. To do this we will use array_unique function
   $tmparr = array_unique($adid_array);
   $i=0;
   foreach ($tmparr as $v) {
       $newarr[$i] = $v; 
       $i++;
   } 
   
   // display what the person searched for.
 if( isset ($searchedfor)){
  $html .= $searchedfor;
  exit();
 }else{
  $html .= <<<EOF
  <h2>Search results for: {$cheat_keyword}</h2>
EOF;
 }
 
foreach($newarr as $value){

 $query_value = "SELECT * FROM ipbwi_cheats WHERE cheat_id = '$value' ";
 $num_value = $ipbwi->DB->query($query_value);
 $row_linkcat = $ipbwi->DB->fetch_row($num_value);
 $row_num_links = $ipbwi->DB->get_num_rows($num_value);

//now let's make the keywods bold. To do that we will use preg_replace function.
//Replace field
  $titlehigh = preg_replace ( "'($cheat_keyword)'" , "<b>$keyword</b>" , $row_linkcat[ 'cheat_title' ] );
  $deschigh = preg_replace ( "'($cheat_keyword)'" , "<b>$keyword</b>" , $row_linkcat[ 'cheat_desc' ] );
  $bodyhigh = preg_replace ( "'($cheat_keyword)'" , "<b>$keyword</b>" , $row_linkcat[ 'cheat_body' ] );

foreach($cheat_keyword_array as $keyword){
    if($keyword != 'b' ){
        $titlehigh = preg_replace( "'$keyword'" ,  "<b>$keyword</b>" , $titlehigh);
        $deschigh = preg_replace( "'$keyword'" , "<b>$keyword</b>" , $deschigh);
        $bodyhigh = preg_replace( "'$keyword'" ,  "<b>$keyword</b>" , $bodyhigh); 
     }
//end highlight
	if($row_linkcat['cheat_img'] == "") {
		$row_linkcat['cheat_img'] = "/images/noimg.png";
	}
$html .= <<<EOF
	<!-- cheat id {$cheat_id} -->
	<div class="cheat">
		<div class="cheat_img"><img src="{$row_linkcat['cheat_img']}" alt="" /></div>
		<div class="cheat_main">
			<h2><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=view&amp;id={$row_linkcat['cheat_id']}">{$titlehigh}</a></h2>
			<ul class="cheat_info">
				<li class="cheat_wubs">{$row_linkcat['cheat_wubs']} <span class="smalltext">Wubs</span></li>
				<li class="cheat_comments">{$row_linkcat['cheat_comments']} <span class="smalltext">Comments</span></li>
				<li class="cheat_views">{$row_linkcat['cheat_views']} <span class="smalltext">Views</span></li>
			</ul>
			{$deschigh}
		</div>
		<div class="clearfix"></div>
	</div><!-- end cheat id {$cheat_id}-->
	<div class="commentdivider"></div>
EOF;


	}//end foreach $trimmed_array 

}//end foreach $newarr

if ($page > 1) {
$html .= <<<EOF
&nbsp;
<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}/index.php?act=cheats&code=search&amp;start={$pre}&amp;cheat_keyword=$cheat_keyword">Previous {$per_row}</a></span>
EOF;
}

if ($last > ($start + $per_row)) {
$html .= <<<EOF
&nbsp;
<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}/index.php?act=cheats&code=search&amp;start={$next}&amp;cheat_keyword=$cheat_keyword">Next {$per_row}</a></span>
EOF;
}

	echo $html;
}

function displaylatest($latest) {
	if( empty($latest)) {
		$latest = "Nothing to show";
	}
	return $latest;
}


//-----------------------------------------------//
//				VIEW CATEGORY
//-----------------------------------------------//
function category() {
	global $ipbwi;
	
		$html .= <<<EOF
						<div class="top">
						<div class="search">
							<form action="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=search" method="post">
								<div class="searchbg">
									<input type="text" name="cheat_keyword" class="searchinput swap" value="SEARCH"/>
									<img src="images/bt_down.png" alt="" style="margin: -5px 5px 0px -10px;" />
								</div>
								<input type="submit" value="" class="searchsubmit" />
							</form>
						</div>
						<div align="right" style="float:right;margin:6px 15px 0 0;">
							<a href="{$ipbwi->getBoardVar('home_url')}?act=cheats&amp;code=add"><img src="images/bt_submityours.png" alt="submit yours"/></a>
						</div>
					</div>
					<div class="clearfix"></div>
					<br />
					<div class="categories">
EOF;
	$catid = $ipbwi->makesafe($_REQUEST['catid']);
	
	//This is the number of results displayed per page 
	$limit = $ipbwi->cheats_display();
	if($limit == "")
	{
		$limit = 10;
	}
	//get the thing
  	$query_num = $ipbwi->DB->query("SELECT COUNT(*) AS num FROM ipbwi_cheats WHERE cheat_catid='$catid' AND cheat_validated='1'");
  	//Here we count the number of results 
	$total_pages = $ipbwi->DB->fetch_row($query_num);
	$total_pages = $total_pages['num'];
	
	$stages = 3;
  	
  	$page = $ipbwi->makesafe($_REQUEST['page']);
  	
  	// pagination starts
	if($page){
		$start = ($page - 1) * $limit; 
	}else{
		$start = 0;	
	}
	
	//query with limit
	$query_row = $ipbwi->DB->query("SELECT * FROM ipbwi_cheats WHERE cheat_catid='$catid' AND cheat_validated='1' ORDER BY cheat_posted DESC LIMIT $start, $limit");
	
	$r = $ipbwi->DB->fetch_row($query_row);
	
	// Initial page num setup
	if ($page == 0){
		$page = 1;
	}
	$prev = $page - 1;	
	$next = $page + 1;							
	$lastpage = ceil($total_pages/$limit);		
	$LastPagem1 = $lastpage - 1;	
	
	if($lastpage > 1)
	{
		if ($page > 1)
		{
		$html .= <<<EOF
		&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}/index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$prev}" title="Previous">&lt;</a></span>
EOF;
		}
		else
		{
		$html .= <<<EOF
		&nbsp;<span class="pagelink disabled">&lt;</span>
EOF;
		}
		// Pages	
		if ($lastpage < 7 + ($stages * 2))
		{	
			for ($counter = 1; $counter <= $lastpage; $counter++)
			{
				if ($counter == $page)
				{
					$html .= <<<EOF
		&nbsp;<span class="pagecurrent">{$counter}</span>
EOF;
				}
				else
				{
				$html .= <<<EOF
		&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}/index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$counter}" title="Previous">$counter</a></span>
EOF;
				}
			}
		}
		elseif($lastpage > 5 + ($stages * 2))
		{
			// Beginning only hide later pages
			if($page < 1 + ($stages * 2))
			{
				for ($counter = 1; $counter < 4 + ($stages * 2); $counter++)
				{
					if ($counter == $page)
					{
						$html .= <<<EOF
		&nbsp;<span class="pagecurrent">{$counter}</span>
EOF;
					}
					else
					{
						$html .= <<<EOF
		&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}/index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$counter}" title="Previous">$counter</a></span>
EOF;
					}
				}
				$html .= <<<EOF
				...
				&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$LastPagem1}" title="Next">$LastPagem1</a></span>
				&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$lastpage}" title="Next">$lastpage</a></span>			
EOF;
			}
			// Middle hide some front and some back
			elseif($lastpage - ($stages * 2) > $page && $page > ($stages * 2))
			{
$html .= <<<EOF
				&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page=1" title="Next">1</a></span>
				&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page=2" title="Next">2</a></span>
EOF;
				$html .= <<<EOF
		...
EOF;
				for ($counter = $page - $stages; $counter <= $page + $stages; $counter++)
				{
					if ($counter == $page)
					{
						$html .= <<<EOF
		&nbsp;<span class="pagecurrent">{$counter}</span>
EOF;
					}
					else
					{
						$html .= <<<EOF
		&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}/index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$counter}" title="Previous">$counter</a></span>
EOF;
					}					
				}
				$html .= <<<EOF
				...
				&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$LastPagem1}" title="Next">$LastPagem1</a></span>
				&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$lastpage}" title="Next">$lastpage</a></span>
EOF;
			}
			else
			{
			$html .= <<<EOF
				&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page=1" title="Next">1</a></span>
				&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page=2" title="Next">2</a></span>
EOF;
				$html .= <<<EOF
		...
EOF;
				for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++)
				{
					if ($counter == $page)
					{
						$html .= <<<EOF
		&nbsp;<span class="pagecurrent">{$counter}</span>
EOF;
					}
					else
					{
						$html .= <<<EOF
		&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}/index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$counter}" title="Previous">$counter</a></span>
EOF;
					}					
				}
			}
		}
					
		// Next
		if ($page < $counter - 1){ 
			$html .= <<<EOF
		&nbsp;<span class="pagelink"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=category&amp;catid={$catid}&amp;page={$next}" title="Next">&gt;</a></span>
EOF;
		}else{
			$html .= <<<EOF
		&nbsp;<span class="pagelink disabled">&gt;</span>
EOF;
		}
	}

	$html .= <<<EOF
	<div class="clearfix"></div>
	<br /><br />
EOF;
	//render
	while ($r = $ipbwi->DB->fetch_row($query_row)) {
		$cheat_img = $r['cheat_img'];
		if($cheat_img == "") {
			$cheat_img = "/images/noimg.png";
		}
		$cheat_id = $r['cheat_id'];
		$cheat_title = $r['cheat_title'];
		$cheat_catid = $r['cheat_catid'];
		$cheat_author = $r['cheat_author'];
		$cheat_authorid = $r['cheat_authorid'];
		$cheat_desc = $r['cheat_desc'];
		$views = $r['cheat_views'];
		$wubs = $this->displaywubs($r['cheat_wubs']);
		$comments = $r['cheat_comments'];
		$cheat_posted = $ipbwi->date($r['cheat_posted'],'%d. %B %Y');
	$html .= <<<EOF
	<!-- cheat id {$cheat_id} -->
	<div class="cheat">
		<div class="cheat_img"><img src="{$cheat_img}" alt="" />
		</div>
		<div class="cheat_main">
			<h2><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=view&amp;id={$cheat_id}">{$cheat_title}</a></h2>
			<ul class="cheat_info">
				<li class="cheat_wubs">{$wubs} <span class="smalltext">Wubs</span></li>
				<li class="cheat_comments">{$comments} <span class="smalltext">Comments</span></li>
				<li class="cheat_views">{$views} <span class="smalltext">Views</span></li>
			</ul>
			{$cheat_desc}
		</div>
		<div class="clearfix"></div>
	</div><!-- end cheat id {$cheat_id}-->
	<div class="commentdivider"></div>
EOF;
	}
	//submit button
	$html .= <<<EOF
	<div align="right" style="float:right;">
		<a href="{$ipbwi->getBoardVar('home_url')}?act=cheats&amp;code=add"><img src="images/bt_submityours.png" alt="submit yours" /></a>
	</div>
	<div class="clearfix"></div>
	</div>
	
EOF;
echo $html;
}

//-----------------------------------------------//
//				SHOW LATEST ARTICLES
//-----------------------------------------------//
function latest($rel) {
	global $ipbwi;
	
	$ipbwi->DB->query("SELECT * FROM ipbwi_cheats WHERE cheat_validated='1' AND cheat_rel='$rel' order by cheat_posted DESC LIMIT 0,5");
	while($r = $ipbwi->DB->fetch_row($query)) {
	
	$cheat_id = $r['cheat_id'];
	$cheat_title = $r['cheat_title'];
	$cheat_game = $r['cheat_game'];
	
	$html .= <<<EOF
	
	<li><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=view&amp;id={$cheat_id}">{$cheat_title}</a><span>{$cheat_game}</span></li>
EOF;

	}
	return $html;
}

//-----------------------------------------------//
//				SHOW LATEST ARTICLES
//-----------------------------------------------//
function most_wubbed($rel) {
	global $ipbwi;
	
	$ipbwi->DB->query("SELECT * FROM ipbwi_cheats WHERE cheat_validated='1' AND cheat_rel='$rel' order by cheat_wubs DESC LIMIT 0,5");
	while($r = $ipbwi->DB->fetch_row($query)) {
		$cheat_id = $r['cheat_id'];
		$cheat_title = $r['cheat_title'];
		$cheat_game = $r['cheat_game'];
		$cheat_wubs = $r['cheat_wubs'];
	$html .= <<<EOF
	<li><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=view&amp;id={$cheat_id}">{$cheat_title}</a> (<img src="images/heart.png" alt="" /> {$cheat_wubs})<span>{$cheat_game}</span></li>
EOF;

	}
	return $html;
}

//-----------------------------------------------//
//				VIEW ARTICLE
//-----------------------------------------------//
function view() {
	global $ipbwi;
	$html .= <<<EOF
	<div class="top">
		<div class="search">
			<form action="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=search" method="post">
				<div class="searchbg">
					<input type="text" name="cheat_keyword" class="searchinput swap" value="SEARCH"/>
					<img src="images/bt_down.png" alt="" style="margin: -5px 5px 0px -10px;" />
				</div>
				<input type="submit" value="" class="searchsubmit" />
			</form>
		</div>
		<div align="right" style="float:right;margin:6px 15px 0 0;">
			<a href="{$ipbwi->getBoardVar('home_url')}?act=cheats&amp;code=add"><img src="images/bt_submityours.png" alt="submit yours"/></a>
		</div>
	</div>
	<div class="clearfix"></div>
EOF;

	if (empty($id)) {
		$cheat_id = $ipbwi->makesafe($_REQUEST['id']);
		}
$ipbwi->DB->query("UPDATE ipbwi_cheats SET cheat_views = cheat_views + 1 WHERE cheat_id = '$cheat_id'");
$ipbwi->DB->query("SELECT c.*,a.* FROM ipbwi_cheats a, ipbwi_categories c 
	WHERE a.cheat_validated = '1' 
	AND a.cheat_id = '$cheat_id' AND a.cheat_catid = c.catid");
	$r = $ipbwi->DB->fetch_row($query);
			$cheat_author = $r['cheat_author'];
			$cheat_title = $r['cheat_title'];
			$cheat_img = $r['cheat_img'];
			if($cheat_img == "") {
				$cheat_img = "/images/noimg.png";
			}
			$cheat_authorid = $r['cheat_authorid'];
			$cat_name = $r['cat_name'];
			$catid = $r['catid'];
			$cheat_posted = $ipbwi->date($r['cheat_posted'],'%d. %B %Y');
			$views = $r['cheat_views'];
			$cheat_desc = $r['cheat_desc'];
			$cheat_body = $r['cheat_body'];
			$cheat_id = $cheat_id;
			$wubs = $this->displaywubs($r['cheat_wubs']);
			
		$html .= <<<EOF
		<!-- cheat id {$cheat_id} -->
		<div class="cheat">
			<div class="cheat_img"><img src="{$cheat_img}" alt="" /></div>
			<div class="cheat_main">
				<h2>{$cheat_title}</h2>
				
				<ul class="cheat_info">
					<li class="cheat_wubs">{$wubs} <span class="smalltext">Wubs</span></li>
					<li class="cheat_comments">{$comments} <span class="smalltext">Comments</span></li>
					<li class="cheat_views">{$views} <span class="smalltext">Views</span></li>
				</ul>
				<ul class="tools">
					<li class="tool_wub"><a href="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=wub&amp;id={$cheat_id}"><img src="/images/wubit.png" alt="WUB IT" /></a></li>
					<li><script type="text/javascript" src="http://w.sharethis.com/button/sharethis.js#publisher=38059036-7da9-4424-afd9-fefc22a5273f&amp;type=website&amp;buttonText=Share%20This"></script></li>
				
EOF;
if($ipbwi->member->isAdmin()){
$html .= <<<EOF
<li class="tool_edit"><a href="{$ipbwi->getBoardVar('home_url')}admin/index.php?act=cheats&amp;code=edit_cheat&amp;cheat_id={$cheat_id}&amp;catid={$catid}" target="_blank"><img src="/images/edit.png" alt="EDIT IT" /></a></li>
EOF;
}
$html .= <<<EOF
				</ul><!-- end tools -->
				<br />
				
			</div>
			<div class="clearfix"></div>
			
			<div class="byline">
				<h1>$cheat_desc</h1>
				<span class="author">Posted by <strong><a href="{$ipbwi->getBoardVar('url')}index.php?showuser={$cheat_authorid}">{$cheat_author}</a></strong></span>
        		<div class="clearfix"></div>
        	</div>
        		{$cheat_body}
			<div class="clearfix"></div>
		</div><!-- end cheat id {$cheat_id}-->
EOF;
	if($r['cheat_hassub'] == 1) {
		$ipbwi->DB->query("SELECT * FROM ipbwi_sub_cheats WHERE subcheat_cheatid = '$cheat_id' ");
		while($s = $ipbwi->DB->fetch_row($query)) {
			$subcheat_author = $s['subcheat_author'];
			$subcheat_title = $s['subcheat_title'];
			$subcheat_img = $s['subcheat_img'];
			$subcheat_authorid = $s['subcheat_authorid'];
			$subcheat_posted = $ipbwi->date($s['subcheat_posted'],'%d. %B %Y');
			$subcheat_desc = $s['subcheat_desc'];
			$subcheat_body = $s['subcheat_body'];
			$subcheat_id =  $s['subcheat_cheatid'];
			
		$html .= <<<EOF
		<!-- id {$subcheat_id} -->
		<div class="cheat">
			<div class="byline">
				<h1>{$subcheat_desc}</h1>
				<!-- <span class="posted">&nbsp;{$subcheat_posted}</span> -->
				<span class="author">Posted by <strong><a href="{$ipbwi->getBoardVar('url')}index.php?showuser={$subcheat_authorid}">{$subcheat_author}</a></strong></span>
        		<div class="clearfix"></div>
        	</div>
        		{$subcheat_body}
			<div class="clearfix"></div>
		</div><!-- end cheat -->
EOF;
		}
	}
	if ($ipbwi->comments_offline() == 0) {
		$html .= $this->comments();
		$html .= $this->addcomment();
	}
	
	echo $html;
}
	
function displaywubs($wubs)
{
		global $ipbwi; 
		if ($wubs != 0)
		{
			$wubs = $wubs;
		}
		else
		{
			$wubs = '<em>0</em>';
		}
		return $wubs;
}
function wub(){
	global $ipbwi;
		$wub = 1;
		$cheat_id = $ipbwi->makesafe($_REQUEST['id']);
		if(!$cheat_id){
			$ipbwi->boink_it($url="?act=cheats");
		}
		$this->updatewubs($cheat_id, $wub);
		$ipbwi->boink_it($url="?act=cheats&amp;code=view&amp;id=$cheat_id", $msg="Thanks for wubbing, <br />redirecting back");
}
function updatewubs($cheat_id, $newwub)
	{
		global $ipbwi;
		$ipbwi->DB->query("SELECT wubid from ipbwi_wubs where wubid = '$cheat_id' ");
		if($ipbwi->DB->get_num_rows())
		{
			$ipbwi->boink_it($url="?act=cheats&amp;code=view&amp;id=$cheat_id", $msg="You have already wubbed.");
			return;
		}
		$ipbwi->DB->query("SELECT cheat_wubs FROM ipbwi_cheats WHERE cheat_id = '$cheat_id' ");
		$r = $ipbwi->DB->fetch_row($query);
		$new_wubs = $r['cheat_wubs'] + 1;
		
		$ipbwi->DB->query("UPDATE ipbwi_cheats
		SET cheat_wubs = '$new_wubs'WHERE cheat_id = '$cheat_id'");

		$member_info = $ipbwi->member->info();
		$member_id = $member_info['id'];
		$member_name = $ipbwi->member->id2displayname($member_id);
		$ipbwi->DB->query("INSERT INTO ipbwi_wubs 
		(wub_member, wub_memberid, wub, wub_rid) VALUES ('$member_name','$member_id','$new_wubs','$cheat_id')");
	}
//-----------------------------------------------//
//				POST COMMENT
//-----------------------------------------------//
function addcomment() {
	global $ipbwi;
	
	if($ipbwi->member->isLoggedIn()) { 
	$html .= <<<EOF
	<div class="commentdivider"></div>
	<div class="addcomment">
		<h3>Add Comment</h3>
		<form id="form" method="post" action="{$ipbwi->getBoardVar('home_url')}index.php?act=cheats&amp;code=doaddcomment">
			<input type="hidden" name="cheat_id" value="{$ipbwi->makesafe($_REQUEST['id'])}" />
			<label for="title">Title</label>
			<input id="title" type="text" name="comment_title" size="35"/>
			<label for="comment">Comment</label>
			<textarea id="comment" class="required" name="comment" cols="68"></textarea>
			<input type="submit" value="&nbsp;&nbsp;Add Comment!&nbsp;&nbsp;" />
		</form>
	</div>
EOF;
} 		
else { 
		$html .= <<<EOF
		<div class="addcomment">
			<p class="error">{$ipbwi->getLibLang('membersOnly')}</p>
		</div>
EOF;
		}
return $html;		
		
}

function doaddcomment() {
	global $ipbwi;
	
$cheat_id = $ipbwi->makesafe($_REQUEST['cheat_id']);
if(empty($cheat_id)) {
$cheat_id = $ipbwi->makesafe($_REQUEST['id']);
}
	if ($ipbwi->makesafe($_REQUEST['comment']) == '') {
		return;
	}
	$posted = time();
	$member_info = $ipbwi->member->info();
	$commment_authorid = $memberinfo['id'];
	$comment = $ipbwi->makesafe($_REQUEST['comment']);
	$title = $ipbwi->makesafe($_REQUEST['comment_title']);
	$comment_author = $ipbwi->member->id2displayname($comment_authorid);
	$comment_ip = getenv(REMOTE_ADDR);
	$ipbwi->DB->query("INSERT INTO ipbwi_comments (
	comment_authorid,
	comment_author,
	comment_cheatid,
	comment_posted,
	comment,comment_title,
	comment_ip
	) VALUES (
	'$comment_authorid',
	'$comment_author',
	'$cheat_id',
	'$posted',
	'$comment',
	'$title',
	'$comment_ip'
	)");
	$ipbwi->DB->query("UPDATE ipbwi_cheats SET cheat_comments = cheat_comments + 1 where cheat_id = '$cheat_id'");
	$ipbwi->boink_it($url="?act=cheats&code=view&id=$cheat_id",$msg="Comment has been added...");
}
//-----------------------------------------------//
//				VIEW COMMENT
//-----------------------------------------------//

function comments() {
	global $ipbwi;
	$cheat_id = $ipbwi->makesafe($_REQUEST['id']);
	$ipbwi->DB->query("SELECT * FROM ipbwi_comments WHERE comment_cheatid = '$cheat_id' ORDER BY comment_posted DESC");
	while ($r = $ipbwi->DB->fetch_row($query)) {
		$commentid = $r['commentid'];
		$comment_author = $r['comment_author'];
		$comment_title = $r['comment_title'];
		$comment = $r['comment'];
		$comment_posted = $ipbwi->date($r['comment_posted'],'%d. %B');
		$comment_author_avatar = $ipbwi->member->avatar($r['comment_authorid']);
		
		$html .= <<<EOF
		<div class="commentdivider"></div>
		<!-- comment {$commentid} -->
		<div class="comment">
			<div class="comment_info">
				{$comment_author_avatar}<br />
				<span class="comment_author"><strong><a href="{$ipbwi->getBoardVar('url')}index.php?showuser={$ipbwi->member->displayname2id($comment_author)}">{$comment_author}</a></strong</span><br />	
				<span class="smalltext">{$comment_posted}</span>
			</div>
			<div class="comment_area">
				<h3>{$comment_title}</h3>
				{$comment}
			</div>
		</div>
		<div class="clearfix"></div>
EOF;
	}
	return $html;
}

//-----------------------------------------------//
//				POST ARTICLE
//-----------------------------------------------//
function add() {
	global $ipbwi;
	$ipbwi->DB->query("SELECT * FROM ipbwi_categories ORDER BY cat_rel DESC");
	$member = $ipbwi->member->info();
	$cats = '';
	while($r = $ipbwi->DB->fetch_row()){
		if($r['cat_parentid']==0) {
			$cats .= <<<EOF
		 	<optgroup label="{$r['cat_name']}">
EOF;
		}
		if($r['cat_parentid']!=0) {
			$cats .= <<< EOF
			<option value="{$r['catid']}">{$r['cat_name']}</option>
EOF;
		}
	}
		
$html .= <<<EOF
	<div class="add_cheat">
	<h2>Add</h2>
	<form id="form" action="{$ipbwi->getBoardVar('home_url')}?act=cheats&code=doadd" method="post">
		<input type="hidden" name="cheat_author" value="{$member['members_display_name']}">
		<input type="hidden" name="cheat_authorid" value="{$member['id']}">
		<label for="author">Author</label>
		<input class="inputdisabled" type="text" value="{$member['members_display_name']}" disabled />
		<label for="category">Category:</label>
		<select id="category" name="cheat_catid" class="required">
		<option value="">{$ipbwi->getLibLang('selectCategory')}</option>
			{$cats}
		</select><br />
		<label for="title">Title:</label>
		<input id="title" type="text" name="cheat_title" size="35" class="required" /><br />
		<label for="thumb">Thumbnail Image:</label>
		<input type="text" id="thumb" name="cheat_img" size="35" class="required"/><br />
		<label for="desc">Description:</label>
		<textarea id="desc" name="cheat_desc" style='padding:4px;width:90%;height:100px' class="required"/></textarea><br />
		<textarea style='padding:4px;width:90%;height:300px' name="cheat_body" class="tinymce"></textarea><br />
		<input class="button" type="submit" name="submit" value="Submit!"/>
	</form>
</div>
EOF;
echo $html;
}

function doadd() {
	global $ipbwi;
	if (!isset($_POST['cheat_catid']) OR $_POST['cheat_catid'] == ''){
		$ipbwi->Error("");
		return;
	}
	$cheat_authorid = $ipbwi->makesafe($_REQUEST['cheat_authorid']);
	$cheat_author = $ipbwi->makesafe($_REQUEST['cheat_author']);
	$cheat_catid = $ipbwi->makesafe($_REQUEST['cheat_catid']);
	$cheat_posted = time();
	$cheat_img = $ipbwi->makesafe($_REQUEST['cheat_img']);
	$cheat_body = $ipbwi->makesafe($_REQUEST['cheat_body']);
	$cheat_title = $ipbwi->makesafe($_REQUEST['cheat_title']);
	$cheat_desc = $ipbwi->makesafe($_REQUEST['cheat_desc']);
		
	$ipbwi->DB->query("SELECT cat_rel FROM ipbwi_categories WHERE catid = '$cheat_catid'");
	$c = $ipbwi->DB->fetch_row();
	$cheat_rel = $c['cat_rel'];
		
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
		$ipbwi->boink_it($url="?act=cheats&amp;code=categories",$msg=" Merging...");
		return;
	}
	else {
		$ipbwi->DB->query("INSERT INTO ipbwi_cheats( 
		cheat_authorid, 
		cheat_author, 
		cheat_catid, 
		cheat_posted, 
		cheat_img, 
		cheat_title, 
		cheat_desc, 
		cheat_body,
		cheat_validated,
		cheat_rel
		) VALUES ( 
		'$cheat_authorid',
		'$cheat_author',
		'$cheat_catid',
		'$cheat_posted',
		'$cheat_img',
		'$cheat_title',
		'$cheat_desc',
		'$cheat_body',
		'0',
		'$cheat_rel'
		)");
		$ipbwi->boink_it($url="?act=cheats&amp;code=categories",$msg="Posted...");

	return;
	}	
}

} // EOC
?>