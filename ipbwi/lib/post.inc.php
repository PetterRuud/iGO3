<?php
	/**
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @version			$LastChangedDate: 2009-08-26 19:19:41 +0200 (Mi, 26 Aug 2009) $
	 * @package			post
	 * @copyright		2007-2009 IPBWI development team
	 * @link			http://ipbwi.com/examples/post.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */
	class ipbwi_post extends ipbwi {
		private $ipbwi			= null;
		/**
		 * @desc			Loads and checks different vars when class is initiating
		 * @author			Matthias Reuter
		 * @since			2.0
		 * @ignore
		 */
		public function __construct($ipbwi){
			// loads common classes
			$this->ipbwi = $ipbwi;
		}
		/**
		 * @desc			Adds a new post.
		 * @param	int		$topicID Topic ID of the Post
		 * @param	string	$post Message body
		 * @param	bool	$disableemos Default: false = disable emoticons, true = enable
		 * @param	bool	$disablesig Default: false = disable signatures, true = enable
		 * @param	bool	$bypassPerms Default: false = repect board permission, true = bypass permissions
		 * @param	string	$guestname Name for Guest user, Default: false
		 * @return	int		New post ID or false on failure
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->post->create(55,'[b]post[/b]');
		 * $ipbwi->post->create(77,'[i]post[/i]', true, true, true, 'Mr. Guest');
		 * </code>
		 * @since			2.0
		 */
		public function create($topicid, $post, $useEmo = false, $useSig = false, $bypassPerms = false, $guestname = false){
			if($this->ipbwi->member->isLoggedIn()){
				$postname = self::$ips->member['members_display_name'];
			}elseif($guestname){
				$postname = self::$ips->vars['guest_name_pre'].$this->makeSafe($guestname).self::$ips->vars['guest_name_suf'];
			}else{
				$postname = self::$ips->member['members_display_name'];
			}
			// No Posting
			if(self::$ips->member['restrict_post']){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Flooding
			if(self::$ips->vars['flood_control'] AND !$this->ipbwi->permissions->has('g_avoid_flood')){
				if((time() - self::$ips->member['last_post']) < self::$ips->vars['flood_control']){
					$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('floodControl'), self::$ips->vars['flood_control']),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}
			// Check some Topic Stuff
			self::$ips->DB->query('SELECT t.*, f.* FROM ibf_topics t LEFT JOIN ibf_forums f ON (t.forum_id=f.id) WHERE t.tid="'.intval($topicid).'"');
			if($row = self::$ips->DB->fetch_row()){
				// Check User can Post to Forum
				if($this->ipbwi->forum->isPostable($row['forum_id']) OR $bypassPerms){
					// Post Queue
					if($row['preview_posts'] OR self::$ips->member['mod_posts']){
						$preview = 1;
					}else{
						$preview = 0;
					}
					// What if the topic is locked
					if($row['state'] != 'open' AND !$this->ipbwi->permissions->has('g_post_closed')){
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
					// Check they can reply
					if($row['starter_id'] == self::$ips->member['id'] && !$this->ipbwi->permissions->has('g_reply_own_topics')){
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}elseif(!$this->ipbwi->permissions->has('g_reply_other_topics')){
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
					$time = time();
					// If we're still here, we should be ok to add the post
					self::$ips->parser->parse_bbcode	= $row['use_ibc'];
					self::$ips->parser->strip_quotes	= 1;
					self::$ips->parser->parse_nl2br		= 1;
					self::$ips->parser->parse_html		= $row['use_html'];
					self::$ips->parser->parse_smilies	= ($useEmo ? 1 : 0);
					$post = self::$ips->parser->pre_db_parse($post);
					$post = $this->ipbwi->makeSafe($post);
					// POST KEY!
					self::$ips->DB->query('INSERT INTO ibf_posts (author_id, author_name, use_emo, use_sig, ip_address, post_date, post, queued, topic_id, post_key) VALUES ("'.self::$ips->member['id'].'", "'.$postname.'", "'.($useEmo ? 1 : 0).'", "'.($useSig ? 1 : 0).'", "'.$_SERVER['REMOTE_ADDR'].'", "'.$time.'", "'.$post.'", "'.$preview.'", "'.$row['tid'].'", "'.md5(microtime()).'")');
					$postID = self::$ips->DB->get_insert_id();
					// Update the Topics
					self::$ips->DB->query('UPDATE ibf_topics SET last_poster_id="'.self::$ips->member['id'].'", last_poster_name="'.$postname.'", posts=posts+1, last_post="'.$time.'" WHERE tid="'.intval($topicid).'"');
					// Finally update the forums
					self::$ips->DB->query('UPDATE ibf_forums SET last_poster_id="'.self::$ips->member['id'].'", last_poster_name="'.$postname.'", posts=posts+1, last_post="'.$time.'", last_title="'.addslashes($row['title']).'", last_id="'.intval($topicid).'" WHERE id="'.intval($row['forum_id']).'"');
					// Oh yes, any update the post count for the user
					if(self::$ips->member['id'] != '0' && $row['inc_postcount']){
						self::$ips->DB->query('UPDATE ibf_members SET posts=posts+1, last_post="'.time().'" WHERE id="'.self::$ips->member['id'].'" LIMIT 1');
					}elseif(self::$ips->member['id'] != '0'){
						self::$ips->DB->query('UPDATE ibf_members SET last_post="'.time().'" WHERE id="'.self::$ips->member['id'].'" LIMIT 1');
					}
					self::$ips->cache['stats']['total_replies']	+= 1;
					self::$ips->update_forum_cache();
					self::$ips->update_cache(array( 'name' => 'stats', 'value' => false, 'donow' => false, 'array' => 1, 'deletefirst' => 0 ) );
					return $postID;
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('topicNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Deletes Topic-Post contains delivered post_id
		 * @param	int		$postID ID of the Post
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->post->delete(55);
		 * </code>
		 * @since			2.0
		 */
		public function delete($postID){
			$pInfo = $this->info($postID);
			self::$ips->DB->query('DELETE FROM ibf_posts WHERE pid = "'.intval($postID).'"');
			// Update the Topics
			self::$ips->DB->query('UPDATE ibf_topics SET posts=posts-1 WHERE tid="'.$pInfo['topic_id'].'"');
			// Finally update the forums
			if(self::$ips->update_forum_cache($pInfo['forum_id'],array('posts' => -1))){
				return true;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Edits a post (adapted from add_post)
		 * @param	int		$postID ID of the Post
		 * @param	string	$post Message body
		 * @param	bool	$disableemos Default: false = disable emoticons, true = enable
		 * @param	bool	$disablesig Default: false = disable signatures, true = enable
		 * @param	bool	$bypassPerms Default: false = repect board permission, true=bypass permissions
		 * @param	bool	$appendedit Default: true = adds the 'edited' line afer the post, false = doesn't add
		 * @return	bool	true on success, false on failure
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @sample
		 * <code>
		 * $ipbwi->post->edit(55,'[b]post[/b]');
		 * $ipbwi->post->edit(77,'[i]post[/i]', true, true, false, true);
		 * </code>
		 * @since			2.0
		 */
		public function edit($postID, $post, $useEmo = false, $useSig = false, $bypassPerms = false, $appendedit = true){
			if(!$this->ipbwi->member->isLoggedIn()){
				// Oh dear... not sure you can go around having guests editing posts...
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// No Posting
			if(self::$ips->member['restrict_post']){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Flooding
			if(self::$ips->vars['flood_control'] AND !$this->ipbwi->permissions->has('g_avoid_flood') && ((time() - self::$ips->member['last_post']) < self::$ips->vars['flood_control'])){
				$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('floodControl'), $this->ips->vars['flood_control']),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Check some Topic Stuff
			self::$ips->DB->query('SELECT  f.*,p.*,t.* FROM ibf_topics t LEFT JOIN ibf_forums f ON (t.forum_id=f.id) LEFT JOIN ibf_posts p ON(p.topic_id=t.tid) WHERE p.pid="'.intval($postID).'"');
			if($row = self::$ips->DB->fetch_row()){
				// Check User can Post to Forum
				if($this->ipbwi->forum->isPostable($row['forum_id']) OR $bypassPerms){
					// Post Queue
					if($row['preview_posts'] OR self::$ips->member['mod_posts']){
						$preview = 1;
					}else{
						$preview = 0;
					}
					// What if the topic is locked
					if($row['state'] != 'open' AND !$this->ipbwi->permissions->has('g_post_closed')){
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
					// Check they can edit posts
					if($row['author_id'] == self::$ips->member['id'] && !$this->ipbwi->permissions->has('g_edit_posts')){
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}elseif($row['author_id'] != self::$ips->member['id'] && !$this->ipbwi->permissions->has('g_is_supmod')){
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
					// Append_Edit?
					if(!$bypassPerms && !$appendedit){
						$appendedit = $this->ipbwi->permissions->has('g_append_edit') ? 0 : 1;
					}
					$time = time();
					self::$ips->parser->parse_bbcode	= $row['use_ibc'];
					self::$ips->parser->strip_quotes	= 1;
					self::$ips->parser->parse_nl2br		= 1;
					self::$ips->parser->parse_html		= $row['use_html'];
					self::$ips->parser->parse_smilies	= ($useEmo ? 1 : 0);
					$post	= self::$ips->parser->pre_db_parse($post);
					$post	= $this->ipbwi->makeSafe($post);
					self::$ips->DB->query('REPLACE INTO ibf_posts (pid, author_id, author_name, use_emo, use_sig, ip_address, edit_time, post, queued, topic_id, append_edit, edit_name, post_date,post_parent,post_key,post_htmlstate,new_topic,icon_id) VALUES ("'.$row['pid'].'", "'.$row['author_id'].'", "'.$row['author_name'].'", "'.($useEmo ? 1 : 0).'", "'.($useSig ? 1 : 0).'", "'.$_SERVER['REMOTE_ADDR'].'", "'.$time.'", "'.$post.'", "'.$preview.'", "'.$row['tid'].'", "'.$appendedit.'", "'.self::$ips->member['name'].'", "'.$row['post_date'].'", "'.$row['post_parent'].'", "'.$row['post_key'].'", 0, "'.$row['new_topic'].'", "'.$row['icon_id'].'")');
					return true;
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('postNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Returns information on a post.
		 * @param	int		$postID ID of the Post
		 * @return	array	Post Information
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->post->info(55);
		 * </code>
		 * @since			2.0
		 */
		public function info($postID){
			// Check for Post Cache
			if($cache = $this->ipbwi->cache->get('postInfo', $postID)){
				return $cache;
			}else{
				self::$ips->DB->query('SELECT p.*, t.forum_id, t.title AS topic_name, g.g_dohtml AS usedohtml FROM ibf_posts p LEFT JOIN ibf_topics t ON (p.topic_id=t.tid) LEFT JOIN ibf_members m ON (p.author_id=m.id) LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) WHERE p.pid="'.$postID.'"');
				if($row = self::$ips->DB->fetch_row()){
					// Parse [doHTML] taggy
					$mem = $this->ipbwi->member->info($row['author_id']);
					$row = array_merge($row,$mem);
					self::$ips->parser->parse_nl2br = true;
					$row['post_bbcode']			= $this->ipbwi->properXHTML($this->ipbwi->bbcode->html2bbcode($row['post']));
					$row['post'] 				= self::$ips->parser->pre_display_parse($row['post']);
					$row['post']				= $this->ipbwi->properXHTML($row['post']);
					$row['post_title']			= $this->ipbwi->properXHTML($row['post_title']);
					$row['topic_name']			= $this->ipbwi->properXHTML($row['topic_name']);
					$row['post_edit_reason']	= $this->ipbwi->properXHTML($row['post_edit_reason']);
					$row['attachments']			= $this->ipbwi->attachment->getList('post',$row['pid']);
					$this->ipbwi->cache->save('postInfo', $postID, $row);
					return $row;
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			array with post IDs of the given Topics
		 * @param	int		$topicIDs The topic IDs where the post IDs should be retrieved from
		 * @return	array	Post IDs
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->post->getListIDs(array(55,22,77,99));
		 * </code>
		 * @since			2.0
		 */
		public function getListIDs($topicIDs){
			// posts
			if(is_array($topicIDs)){
				$topics = '';
				foreach($topicIDs as $topicID){
					if($topics){
						$topics .= '" OR "'.intval($topicID).'"';
					}else{
						$topics = '"'.intval($topicID).'"';
					}
				}
			}else{
				$topics = '"'.$topicIDs.'"';
			}
			$query = self::$ips->DB->query('SELECT pid FROM ibf_posts WHERE (topic_id = '.$topics.')');
			if(self::$ips->DB->get_num_rows() == 0){
				return false;
			}
			while($row = self::$ips->DB->fetch_row($query)){
				$postIDs[] = $row['pid'];
			}
			return $postIDs;
		}
		/**
		 * @desc			Lists posts in a topic.
		 * @param	mixed	$topicID The topic ID (array-list, int or '*' for all board topics)
		 * @param	array	$settings optional query settings. Settings allowed: order, orderby limit and start
		 * + string order = ASC or DESC, default ASC
		 * + string orderby = pid, author_id, author_name, post_date, post or random. Default: post_date
		 * + int start = Default: 0
		 * + int limit = Default: 15
		 * @param	bool	$bypassPerms Default: false = respect board permission, true = bypass permissions
		 * @param	bool	$countView Default: false = do not add view count, true = add the view count
		 * @return	array	Topic Posts
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->post->getList(55);
		 * $ipbwi->post->getList(array(55,22,77,99));
		 * $ipbwi->post->getList('*');
		 * $ipbwi->post->getList(55,array('order' => 'DESC', 'orderby' => 'pid', 'start' => 10, 'limit' => 20),true,true);
		 * </code>
		 * @since			2.0
		 */
		public function getList($topicID, $settings = array(), $bypassPerms = false, $countView = false){
			if(empty($settings['order'])){
				$settings['order'] = 'asc';
			}else{
				$settings['order'] = strtolower($settings['order']);
			}
			if(empty($settings['limit'])){
				$settings['limit'] = 15;
			}
			if(empty($settings['start'])){
				$settings['start'] = 0;
			}
			if(empty($settings['orderby'])){
				$settings['orderby'] = 'post_date';
			}
			if(empty($settings['memberid'])){
				$settings['memberid'] = false;
			}
			// get data from a specific user
			if($settings['memberid']){
				$specificMember = 'p.author_id = "'.intval($settings['memberid']).'" AND ';
			}else{
				$specificMember = false;
			}
			$sqlwhere = '';
			if(is_array($topicID)){
				// get_topic_info() is too inefficent when we have alot of topic ids.
				$topics = '';
				foreach($topicID as $i){
					$i = intval($i);
					if($topics){
						$topics .= ' OR tid="'.$i.'"';
					}else{
						$topics = ' tid="'.$i.'"';
					}
				}
				// Query
				$getfid = self::$ips->DB->query('SELECT tid, forum_id FROM ibf_topics WHERE '.$topics);
				// Now we should how topic ids and their forum ids.
				while($row = self::$ips->DB->fetch_row($getfid)){
					if($this->ipbwi->forum->isReadable($row['forum_id']) OR $bypassPerms){
						if(!$sqlwhere){
							$sqlwhere .= '(topic_id="'. $row['tid'].'"';
						}else{
							$sqlwhere .= ' OR topic_id="'.$row['tid'].'"';
						}
					}
				}
				if($sqlwhere){
					$sqlwhere .= ') AND ';
					$cando = 1;
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}elseif($topicID == '*'){
				if($bypassPerms){
					// Grab posts from the whole board
					$sqlwhere = false;
					$cando = 1;
				}else{
					// All topics. So we can grab them from all readable forums.
					$readable = $this->ipbwi->forum->getReadable();
					foreach($readable as $j => $k){
						if(!$sqlwhere){
							$sqlwhere .= '(forum_id="'.$j.'"';
						}else{
							$sqlwhere .= ' OR forum_id="'.$j.'"';
						}
					}
					if($sqlwhere OR isset($cando)){
						$sqlwhere .= ') AND ';
						$cando = 1;
					}else{
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
				}
			}else{
				// Classic Posts from Topic Export
				// Grab Topic Info then check whether forum is readable.
				$topicinfo = $this->ipbwi->topic->info($topicID,$countView);
				if($this->ipbwi->forum->isReadable($topicinfo['forum_id']) OR $bypassPerms){
					$sqlwhere = 'topic_id="'.intval($topicID).'" AND ';
					$cando = 1;
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}
			if($cando){
				// What shall I order it by guv?
				$allowedorder = array('pid', 'author_id', 'author_name', 'post_date', 'post');
				if(in_array($settings['orderby'], $allowedorder)){
					$order = $settings['orderby'].' '.(($settings['order'] == 'desc') ? 'DESC' : 'ASC');
				}elseif($settings['orderby'] == 'random'){
					$order = 'RAND()';
				}else{
					$order = 'post_date '.(($settings['order'] == 'desc') ? 'DESC' : 'ASC');
				}
				// Grab Posts
				$limit = $settings['limit'] ? intval($settings['limit']) : 15;
				$start = $settings['start'] ? intval($settings['start']) : 0;
				self::$ips->DB->query('SELECT p.*, t.forum_id, g.g_dohtml AS usedohtml FROM ibf_posts p LEFT JOIN ibf_members m ON (p.author_id=m.id) LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) LEFT JOIN ibf_topics t ON(p.topic_id=t.tid) WHERE p.pid != topic_firstpost AND '.$specificMember.$sqlwhere.'p.queued="0" ORDER BY '.$order.' LIMIT '.$start.','.$limit);
				$return = array();
				self::$ips->parser->parse_bbcode	= 1;
				self::$ips->parser->strip_quotes	= 1;
				self::$ips->parser->parse_nl2br		= 1;
				while($row = self::$ips->DB->fetch_row()){
					$row['post_bbcode']				= $this->ipbwi->properXHTML($this->ipbwi->bbcode->html2bbcode($row['post']));
					$row['post'] 					= self::$ips->parser->pre_display_parse($row['post']);
					$row['post']					= $this->ipbwi->properXHTML($row['post']);
					$row['post_title']				= $this->ipbwi->properXHTML($row['post_title']);
					$row['post_edit_reason']		= $this->ipbwi->properXHTML($row['post_edit_reason']);
					// Add to return array
					$return[] = $row;
				}
				return $return;
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
	}
?>