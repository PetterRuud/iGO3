	<!-- start middle -->
	<div class="column" id="middle">
	<!-- start igo specials (slideshow)-->
	    <div class="igospecial">
	    	<div class="frame"></div>
	    	<div class="anythingSlider">
	    		<div class="wrapper">
    				<ul>
    				<?php
 	    	$recent_posts = $ipbwi->topic->getList('26',array('limit' => 4));
 	    	foreach($recent_posts as $post){
     			if(is_array($recent_posts) && count($recent_posts)>0){
     			$attachments = $ipbwi->attachment->getList($post['topic_firstpost'],array('type' => 'post'));
 	    	?>
 	    				<li>
	    					<img src="<?=$attachments['boardURL'];?>" alt="" />
	    					<div class="text"><a href="<?=$ipbwi->getBoardVar('url');?>index.php?showtopic=<?=$post['tid'];?>" title="<?=$post['title'];?>"><?=$ipbwi->shorten($post['title'],40);?></a></div>
	    				</li>
	    	<?php
	 			} 
	 		} 
	 		?>
	    			</ul>
	    		</div>
	    	</div>
	    </div><!-- end igo specials -->
	    <!-- recent news -->
	    <div class="recentnews">
	    	<ul>
	    	<?php
 	    	$recent_posts = $ipbwi->topic->getList('2',array('limit' => 5));
 	    	foreach($recent_posts as $post){
     			if(is_array($recent_posts) && count($recent_posts)>0){
 	    	?>
 	    		<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?showtopic=<?=$post['tid'];?>"><img src="images/newnews.png" alt="p" class="newposts"/> <strong><?=$ipbwi->shorten($post['title'],20);?></strong> <em><?=$ipbwi->date($post['start_date'],'%d.%m');?></em></a></li>
	    	<?php
	 			} 
	 		} 
	 		?> 	
	    	</ul>
	    	<a href="<?=$ipbwi->getBoardVar('url');?>index.php?showforum=2"><img src="images/bt_all.png" alt="All" style="float:right;margin-right: 15px;" /></a>
	    </div><!-- end recent news -->
	    <!-- start main content area -->
		<div id="contentarea">