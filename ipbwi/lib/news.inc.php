<?php
class class_news {
/*********************************************************
						NEWS
/*********************************************************/

	function auto_load() {
		global $ipbwi;
		switch ($ipbwi->makesafe($_REQUEST['code'])) {
		case "showall":
			$this->showall();
			break;
		case "show":
			$this->show();
			break;
		default:
			$this->showall();
			break;
		}

	}
	function showall() {
		global $ipbwi;
		$html .= <<<EOF
					<div class="top">
						<div class="search">
							<form action="{$ipbwi->getBoardVar('url')}index.php?act=Search&amp;CODE=01" method="post">
							<input type="hidden" name="forums" id="gbl-search-forums" value="2" />
								<div class="searchbg">
									<input type="text" id="ipb-tl-search-box" name="keywords" class="searchinput swap" value="SEARCH"/>
									<img src="images/bt_down.png" alt="" style="margin: -5px 5px 0px -10px;" />
								</div>
								<input type="submit" value="" class="searchsubmit" />
							</form>
						</div>
						<div class="pagination">
							Site selection 
							<a href="#prev">&laquo;</a>
							<span><a href="#1">1</a></span>
							<span><a href="#2">2</a></span>
							<span><a href="#3">3</a></span>
							<span><a href="#4">4</a></span>
							<span><a href="#5">5</a></span>
							<a href="#next">&raquo;</a>
						</div>
					</div>
EOF;

//$limit = $ipbwi->news_display();
	if($limit == "")
	{
		$limit = 2;
	}
	
	$start = 0;
					
	$page = $ipbwi->makesafe($_REQUEST['page']);
					
					$posts = $ipbwi->topic->getList(2,array('order' => 'DESC', 'orderby' => 'pid', 'start' => $start, 'limit' => $limit),true);
					
					
    				if(isset($posts) && is_array($posts) && count($posts) > 0){
        				foreach($posts as $post){
        					$attachments = $ipbwi->attachment->getList($post['topic_firstpost'],array('type' => 'post'));
        					if ($attachments['boardURL'] != NULL) {
        						$attachments['boardURL'] = <<<EOF
        						<img src="{$attachments['boardURL']}" alt="" class="center newsimg" />
EOF;
        					}
        					
$html .= <<<EOF
    				<!-- article {$post['pid']} start -->
    				<div class="article">
    					{$attachments['boardURL']}
						<div class="byline">
						<h1>{$post['title']}</h1>
							<span class="author">Posted by <strong><a href="{$ipbwi->getBoardVar('url')}index.php?showuser={$post['author_id']}">
        					{$ipbwi->member->id2displayname($post['author_id'])}</a></strong></span>
        					<div class="clearfix"></div>
						</div>
        					{$post['post']}
						<div class="cleared"></div>
						<a href="{$ipbwi->getBoardVar('url')}index.php?showtopic={$post['tid']}"><img src="images/bt_more2.png" alt="more" style="float:right;" /></a>
						<!-- <a href="#comments"><img src="images/bt_comments.png" alt="comments" style="float:right;" /></a> -->	
					</div><!-- end article -->
					<div class="cleared"></div>
EOF;
        			}
    			}
    			echo $html;
}

function show() {
	global $ipbwi;

	$post = $ipbwi->topic->info($ipbwi->makesafe($_REQUEST['id']));
	$attachments = $ipbwi->attachment->getList($post['pid'],array('type' => 'post'));
	if ($attachments['boardURL'] != NULL) {
        						$attachments['boardURL'] = <<<EOF
        						<img src="{$attachments['boardURL']}" alt="" class="center newsimg" />
EOF;
        					}

	$html .= <<<EOF
    				<!-- article {$post['pid']} start -->
    				<div class="article">
    					{$attachments['boardURL']}
						<div class="byline">
						<h1>{$post['title']}</h1>
							<span class="author">Posted by <strong><a href="{$ipbwi->getBoardVar('url')}index.php?showuser={$post['author_id']}">
        					{$ipbwi->member->id2displayname($post['author_id'])}</a></strong></span>
						</div>
        				{$post['post']}
						<div class="cleared"></div>
						<a href="{$ipbwi->getBoardVar('url')}index.php?showtopic={$post['tid']}"><img src="images/bt_more2.png" alt="more" style="float:right;" /></a>
						<!-- <a href="#comments"><img src="images/bt_comments.png" alt="comments" style="float:right;" /></a> -->	
					</div><!-- end article -->
					<div class="cleared"></div>
EOF;
echo $html;

}


} //eoc
?>