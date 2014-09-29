	<!-- start leftbar -->
	<div class="column" id="leftbar">
	    <!-- latest tutorials -->
	    <div class="latesttuts">
	    	<ul>
	    <?php
 	    $latest_tutorials = $ipbwi->topic->getList(14, array('order' => 'DESC', 'orderby' => 'pid', 'limit' => 5, 'allsubs' => true), $bypassPerms = true);
    	foreach($latest_tutorials as $tutorial){

    	 	if(is_array($latest_tutorials) && count($latest_tutorials)>0){
 	    ?>
	    		<li>
	    		<a href="<?=$ipbwi->getBoardVar('url');?>index.php?showtopic=<?=$tutorial['tid'];?>">
	    			<img src="<?=$ipbwi->getBoardVar('url');?>style_images/iGo3/folder_post_icons/icon<?=$tutorial['icon_id'];?>.gif" alt="" class="number" />
	    			<strong><?=$ipbwi->shorten($tutorial['title'],22); ?></strong>
	    		</a>
	    		</li>

	    <?php
	     	}
	     }
	     ?>
	    	</ul>
	    </div><!-- end latest tuts -->
	    <!-- latest resources -->
	    <div class="latestres">
	    	<ul>
	    <?php
 	    $latest_resources = $ipbwi->topic->getList(array(19,20), array('order' => 'DESC', 'orderby' => 'pid', 'limit' => 5, 'allsubs' => true), $bypassPerms = true);
    	foreach($latest_resources as $resource){
    	 	if(is_array($latest_resources) && count($latest_resources)>0){
 	    ?>
	    		<li>
	    		<a href="<?=$ipbwi->getBoardVar('url');?>index.php?showtopic=<?=$resource['tid'];?>">
	    			<img src="<?=$ipbwi->getBoardVar('url');?>style_images/iGo3/folder_post_icons/icon<?=$tutorial['icon_id'];?>.gif" alt="" class="number" />
	    			<strong><?=$ipbwi->shorten($resource['title'],22); ?></strong>
	    		</a>
	    		</li>
	    <?php
	     	}
	     }
	     ?>
	    	</ul>
	    </div><!-- end resources -->
	    <!-- recent posts -->
	    <div class="recentposts">
	    	<div class="newestuser">
	    		<a href="<?=$ipbwi->getBoardVar('url');?>index.php?showuser=<?=$ipbwi->member->displayname2id($stats['last_mem_name']);?>"><strong><?=$stats['last_mem_name'];?></strong></a>
	    	</div>
	    	<ul>
	    		<?php
 	    		$newest_posts = $ipbwi->topic->getList('*',array('limit' => 5));
 	    		foreach($newest_posts as $post){
    	 			if(is_array($newest_posts) && count($newest_posts)>0){
 	    		?>
	    			<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?showtopic=<?=$post['tid']; ?>"><strong><?=$ipbwi->shorten($post['title'],20);?></strong> <img src="images/newpost.png" alt="p" class="newposts"/></a></li>
	    		<?php
	     			}
	     		}
	     		?>
	    	</ul>
	    	<a href="#all"><img src="images/bt_all.png" alt="All" style="float:right;margin-right: 10px;" /></a>
	    </div><!-- end recents posts -->
	    <!-- bottom box -->
	    <div class="leftbottom">
	    	<?=$affiliates->shownum($num_affiliates);?>
	    </div>
	</div>