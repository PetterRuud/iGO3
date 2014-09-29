<?php
	/**
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @version			$LastChangedDate: 2009-08-26 19:19:41 +0200 (Mi, 26 Aug 2009) $
	 * @package			pm
	 * @copyright		2007-2009 IPBWI development team
	 * @link			http://ipbwi.com/examples/pm.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */
	class ipbwi_pm extends ipbwi {
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
		 * @desc			Moves a personal message to another folder.
		 * @param	int		$messageID Message ID to be moved
		 * @param	int		$targetID Target folder ID.
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->move(5,4);
		 * </code>
		 * @since			2.0
		 */
		public function move($messageID, $targetID){
			if($this->ipbwi->member->isLoggedIn()){
				// Grab PM Info
				if($info = $this->info($messageID, 0)){
					// Check the Dest Folder Exists
					if($this->folderExists($targetID)){
						self::$ips->DB->query('UPDATE ibf_message_topics SET mt_vid_folder="'.$targetID.'" WHERE mt_id="'.$messageID.'" AND mt_owner_id="'.self::$ips->member['id'].'" LIMIT 1');
						if(self::$ips->DB->get_affected_rows()){
							// If you move an unread message from inbox
							if($info['vid'] == 'in' AND $info['read_state'] == '0'){
								self::$ips->DB->query('UPDATE ibf_members SET new_msg = new_msg - 1 WHERE id="'.self::$ips->member['id'].'"');
							// And if you move a unread message to the inbox
							}else if($targetID == 'in' AND $info['read_state'] == '0'){
								self::$ips->DB->query('UPDATE ibf_members SET new_msg = new_msg + 1 WHERE id="'.self::$ips->member['id'].'"');
							}
							return true;
						}else{
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMsgNoMove'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
					}else{
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmFolderNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMsgNoMove'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Marks a message read/unread.
		 * @param	int		$messageID Message ID to be marked
		 * @param	int		$isRead Default: 1=mark read, 0=mark unread
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->mark(55,0);
		 * </code>
		 * @since			2.0
		 */
		public function mark($messageID, $isRead = 1){
			if($this->ipbwi->member->isLoggedIn()){
				$pm = $this->info($messageID);
				if($isRead && $pm['mt_read'] != 1){
					self::$ips->DB->query('UPDATE ibf_members SET new_msg = new_msg-1 WHERE id="'.self::$ips->member['id'].'" AND new_msg > 0');
					self::$ips->DB->query('UPDATE ibf_message_topics SET mt_read="1" WHERE mt_owner_id="'.self::$ips->member['id'].'" AND mt_id="'.intval($messageID).'"');
					// Return success
					if(self::$ips->DB->get_affected_rows()){
						return true;
					}else{
						return false;
					}
				}elseif(!$isRead && $pm['mt_read'] == 1){
					self::$ips->DB->query('UPDATE ibf_members SET new_msg = new_msg+1 WHERE id="'.self::$ips->member['id'].'"');
					self::$ips->DB->query('UPDATE ibf_message_topics SET mt_read="0" WHERE mt_owner_id="'.self::$ips->member['id'].'" AND mt_id="'.intval($messageID).'"');
					// Return success
					if(self::$ips->DB->get_affected_rows()){
						return true;
					}else{
						return false;
					}
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Removes a personal message folder.
		 * @param	int		$folderID folder ID
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->removeFolder(55);
		 * </code>
		 * @since			2.0
		 */
		public function removeFolder($folderID){
			if($this->ipbwi->member->isLoggedIn()){
				$folders = $this->getFolders();
				$foldersi = array();
				if($this->folderExists($folderID)){
					// Check if it's Inbox or Sent Items
					if($folderID != 'in' AND $folderID != 'sent'){
						// Good. Now, try and delete the messages firstly.
						$this->emptyFolder($folderID, 0);
						// Now Delete the Folder
						foreach($folders as $m => $i){
							if($i['id'] != $folderID){
								$cur = $i['id'].':'.$i['name'].';'.$i['count'];
								$foldersi[$i['id']] = $cur;
							}
						}
						$newvids = implode('|', $foldersi);
						self::$ips->DB->query('UPDATE ibf_member_extra SET vdirs="'.$newvids.'" WHERE id="'.self::$ips->member['id'].'" LIMIT 1');
						return true;
					}else{
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmFolderNoRem'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmFolderNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Empties PMs in a personal message folder.
		 * @param	int		$folderID folder ID
		 * @param	int		$keepunread Default: 0=also delete unread msgs, 1=keep unread messages
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->emptyFolder(55,1);
		 * </code>
		 * @since			2.0
		 */
		public function emptyFolder($folderID, $keepUnread = 0){
			if($this->ipbwi->member->isLoggedIn()){
				if($this->folderExists($folderID)){
					if($keepUnread) $sql_keep_unread = ' AND mt_read="1"';
					// Just so we can decrement total
					self::$ips->DB->query('SELECT COUNT(mt_id) AS messagescount FROM ibf_message_topics WHERE mt_vid_folder="'.$folderID.'" AND mt_owner_id="'.self::$ips->member['id'].'"'.$sql_keep_unread);
					$row = self::$ips->DB->fetch_row();
					$del = $row['messagescount'];
					// Get message text ids...
					self::$ips->DB->query('SELECT mt_msg_id FROM ibf_message_topics WHERE mt_vid_folder="'.$folderID.'" AND mt_owner_id="'.self::$ips->member['id'].'"'.$sql_keep_unread);
					// Delete from text
					while($row = self::$ips->DB->fetch_row()){
						self::$ips->DB->query('DELETE FROM ibf_message_text WHERE msg_id = "'.$row['mt_msg_id'].'"');
					}
					// Delete from topics
					self::$ips->DB->query('DELETE FROM ibf_message_topics WHERE mt_vid_folder="'.$folderID.'" AND mt_owner_id="'.self::$ips->member['id'].'"'.$sql_keep_unread);
					// Update Total
					self::$ips->DB->query('UPDATE ibf_members SET msg_total=msg_total-'.intval($del).' WHERE id="'.self::$ips->member['id'].'" LIMIT 1');
					// Update Cache
					$this->cache->updatePM(self::$ips->member['id']);
					return $del;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Renames a personal message folder.
		 * @param	int		$folderID folder ID
		 * @param	string	$newName New folder name
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->renameFolder(55,'new folder name');
		 * </code>
		 * @since			2.0
		 */
		public function renameFolder($folderID, $newName){
			if($this->ipbwi->member->isLoggedIn()){
				// Get Folders
				$folders = $this->getFolders();
				$info = $this->ipbwi->member->info();
				$foldersi = array();
				foreach($folders as $i){
					$foldersi[$i['id']] = $i['name'];
				}
				// Check it exists
				if($foldersi[$folderID]){
					$foldersi[$folderID] = $newName;
					$newf = array();
					foreach($folders as $i => $m){
						$newf[] = $m['id'].':'.$foldersi[$m['id']].';'.$m['count'];
					}
					$newFolders = implode ('|', $newf);
					// Rename the Folder
					self::$ips->DB->query('UPDATE ibf_member_extra SET vdirs="'.$newFolders.'" WHERE id="'.self::$ips->member['id'].'"');
					$this->cache->updatePM(self::$ips->member['id']);
					return true;
				}else{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmFolderNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Creates a personal message folder.
		 * @param	int		$folderID folder ID
		 * @param	string	$newName folder name
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->addFolder('folder name');
		 * </code>
		 * @since			2.0
		 */
		public function addFolder($name){
			if($this->ipbwi->member->isLoggedIn()){
				// Get Folders
				$folders = $this->getFolders();
				$info = $this->ipbwi->member->info();
				$foldersi = array();
				foreach($folders as $i){
					if(isset($i[0]) && isset($i[1])){
						$foldersi[$i[0]] = $i[1];
					}
				}
				$foldersno = count($folders);
				// Just to check
				if(empty($foldersi['dir_'.$foldersno])){
					$newFolders = $info['vdirs'].'|dir_'.$foldersno.':'.$name;
					self::$ips->DB->query('UPDATE ibf_member_extra SET vdirs="'.$newFolders.'" WHERE id="'.self::$ips->member['id'].'" LIMIT 1');
					return 'dir_'.$foldersno;
				}else{
					// Just incase
					while($foldersno < 100){
						if(!$foldersi['dir_'.$foldersno]){
							$newFolders = $info['vdirs'] . '|dir_'.$foldersno.':'.$name;
							self::$ips->DB->query('UPDATE ibf_member_extra SET vdirs="'.$newFolders.'" WHERE id="'.self::$ips->member['id'].'" LIMIT 1');
							return 'dir_'.$foldersno;
						}
						++$foldersno;
					}
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns folder name associated with folder id of a member.
		 * @param	int		$folderID folder ID
		 * @param	int		$userID If $userID is ommited, the last known member is used.
		 * @return	string	Folder Name associated with id
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->folderid2name('folder name');
		 * </code>
		 * @since			2.0
		 */
		public function folderid2name($folderID, $userID = false){
			$memberInfo = $this->ipbwi->member->info($userID);
			$folders = $memberInfo['vdirs'];
			$list = explode ('|', $folders);
			foreach($list as $i){
				$j = explode (':', $i);
				$foldersinfo[$j['0']] = $j['1'];
			}
			if($foldersinfo[$folderID]){
				$name = explode(';',$foldersinfo[$folderID]);
				return $name[0];
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns whether a PM folder exists for a given member.
		 * @param	int		$folderID folder ID
		 * @param	int		$userID If $userID is ommited, the last known member is used.
		 * @return	bool	Folder Existance Status
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->folderExists(3,55);
		 * </code>
		 * @since			2.0
		 */
		public function folderExists($folderID, $userID = false){
			// Inbox and Sent Items are Good
			if($folderID == 'in' OR $folderID == 'sent'){
				return true;
			}
			// 'unsent' should be an bad folder name anyway, but put this so as not to screw up other functions
			if($folderID == 'unsent'){
				return false;
			}
			$folderIDs = array();
			$memberInfo = $this->ipbwi->member->info($userID);
			$folders = $memberInfo['vdirs'];
			$folderslist = explode ('|', $folders);
			foreach($folderslist as $i){
				$j = explode (':', $i);
				$folderIDs[] = $j['0'];
			}
			if(in_array($folderID, $folderIDs)){
				return true;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns the current user's PM folders.
		 * @return	array	Current user's PM System Folders
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->getFolders();
		 * </code>
		 * @since			2.0
		 */
		public function getFolders(){
			if($this->ipbwi->member->isLoggedIn() AND $this->ipbwi->permissions->has('g_use_pm')){
				$folders = array();
				self::$ips->DB->query('SELECT vdirs FROM ibf_member_extra WHERE id="'.self::$ips->member['id'].'"');
				if($row = self::$ips->DB->fetch_row()){
					$row['vdirs'] = $row['vdirs'] ? $row['vdirs'] : 'in:Inbox|sent:Sent Items';
					$i = explode ('|', $row['vdirs']);
					foreach($i as $j){
						$folder = array();
						$k = explode (':', $j);
						$l = explode(';', $k[1]);
						$folder['id'] = $k[0];
						$folder['name'] = $l[0];
						if(isset($l[1])) $folder['count'] = $l[1];
						$folders[] = $folder;
					}
					return $folders;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns PM space usage in percentage.
		 * @return	int		PM Space Usage in Percent
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->spaceUsage();
		 * </code>
		 * @since			2.0
		 */
		public function spaceUsage(){
			$PMs = $this->numTotalPms();
			$maximumPMs = $this->ipbwi->permissions->best('g_max_messages');
			// Remove possible division by zero...
			if($maximumPMs == 0){
				return 0;
			}
			$percent = round(($PMs / $maximumPMs) * 100);
			return $percent;
		}
		/**
		 * @desc			Returns number of unread PMs in a folder.
		 * @param	int		$folderID Folder ID
		 * @return	int		Number of unread PMs in Folder
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->numFolderUnreadPMs(55);
		 * </code>
		 * @since			2.0
		 */
		public function numFolderUnreadPMs($folderID){
			if($cache = $this->ipbwi->cache->get('numFolderUnreadPMs', $folderID)){
				return $cache;
			}
			if(!$this->ipbwi->member->isLoggedIn() AND !$this->ipbwi->permissions->has('g_use_pm')){
				$this->sdkerror($this->lang['sdk_membersOnly']);
				return false;
			}
			self::$ips->DB->query('SELECT COUNT(mt_msg_id) AS messages FROM ibf_message_topics WHERE mt_owner_id="'.self::$ips->member['id'].'" AND mt_vid_folder="'.$folderID.'" AND mt_read="0"');
			if($messages = self::$ips->DB->fetch_row()){
				// Save In Cache and Return
				$this->ipbwi->cache->save('numFolderUnreadPMs', $folderID, $messages['messages']);
				return $messages['messages'];
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns number of PMs in a folder.
		 * @param	int		$folderID Folder ID
		 * @return	int		Number of PMs in Folder
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->numFolderPMs(55);
		 * </code>
		 * @since			2.0
		 */
		public function numFolderPMs($folderID){
			if(!$this->ipbwi->member->isLoggedIn() AND !$this->permissions->has('g_use_pm')){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			self::$ips->DB->query('SELECT COUNT(mt_msg_id) AS messages FROM ibf_message_topics WHERE mt_owner_id="'.self::$ips->member['id'].'" AND mt_vid_folder="'.$folderID.'"');
			if($messages = self::$ips->DB->fetch_row()){
				return $messages['messages'];
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns whether a member has blocked another member.
		 * @param	int		$by Member ID of receiver (the one who blocked)
		 * @param	int		$blocked Member ID of sender (the one who is blocked)
		 * @return	bool		Block Status
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->isBlocked(55,77);
		 * </code>
		 * @since			2.0
		 */
		public function isBlocked($by, $blocked){
			self::$ips->DB->query('SELECT id, allow_msg FROM ibf_contacts WHERE contact_id="'.$blocked.'" AND member_id="'.$by.'"');
			if($cando = self::$ips->DB->fetch_row()){
				if($cando['allow_msg'] == 0){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Deletes a Personal Message.
		 * @param	int		$messageID Message to be deleted
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->delete(55);
		 * </code>
		 * @since			2.0
		 */
		public function delete($messageID){
			if(!$this->member->isLoggedIn()){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			self::$ips->DB->query('SELECT * FROM ibf_message_topics WHERE mt_owner_id="'.self::$ips->member['id'].'" AND mt_id="'.$messageID.'"');
			if($row = self::$ips->DB->fetch_row()){
				self::$ips->DB->query('DELETE FROM ibf_message_text WHERE msg_id="'.$row['mt_msg_id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_message_topics WHERE mt_id="'.$messageID.'" AND mt_owner_id="'.self::$ips->member['id'].'" LIMIT 1');
				if($row['mt_vid_folder'] != 'unsent'){
					self::$ips->DB->query('UPDATE ibf_members SET msg_total = msg_total - 1 WHERE id="'.self::$ips->member['id'].'"');
				}
				$this->ipbwi->cache->updatePM(self::$ips->member['id']);
				return true;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Saves a PM to the sent folder without sending it.
		 * @param	int		$toID Member ID to receive the message
		 * @param	string	$title Message title
		 * @param	string	$message Message body
		 * @param	array	$cc Array of ID for carbon copies (CC)
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->save(5,'message title','message content,array('55','77'));
		 * </code>
		 * @since			2.0
		 */
		public function save($toID, $title, $message, $cc = array()){
			// Similar to Write PM but code modified for saving
			if(!$this->ipbwi->member->isLoggedIn()){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(!$toID){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmNoRecipient'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(!$title OR strlen($title) < 2){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmTitle'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(!$message OR strlen($message) < 2){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMessage'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$sendto = array();
			self::$ips->DB->query('SELECT m.name, m.id, m.view_pop, m.mgroup, m.email_pm, m.language, m.email, m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, ibf_members m WHERE m.id="'.intval($toID).'" AND g.g_id=m.mgroup');
			if($row = self::$ips->DB->fetch_row()){
				// Just incase
				if(!$row['id']){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// Permissions Check
				if(!$this->ipbwi->permissions->has('g_use_pm',$row['id'])){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemDisAllowed'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// Space Check
				$space = $this->ipbwi->permissions->best('g_max_messages',$row['id']);
				if($row['msg_total'] >= $space AND $space > 0){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemFull'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// Block Check
				if($this->isBlocked(self::$ips->member['id'], intval($toID))){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemBlocked'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// CC Users
				$ccusers = array();
				$max = $this->ipbwi->permissions->has('g_max_mass_pm','',false);
				if($max){
					if(is_array($cc) AND count($cc) > 0){
						if(count($cc) > $max){
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmCClimit'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
						foreach($cc AS $i){
							// Check CC user stuff
							// I really should clean up the code here, it uses alot of queries in some cases, which isn't good. Should really merge this with the main sending message code instead of replicating stuff for CCs.
							self::$ips->DB->query('SELECT m.name, m.id, m.view_pop, m.mgroup, m.email_pm, m.language, m.email, m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, ibf_members m WHERE m.id="'.intval($toID).'" AND g.g_id=m.mgroup');
							if($ccrow = self::$ips->DB->fetch_row()){
								// Permissions Check
								if(!$this->ipbwi->permissions->hast('g_use_pm',$ccrow['id'])){
									$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmRecDisallowed'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
									return false;
								}
								// Space Check
								$space = $this->ipbwi->permissions->best('g_max_messages',$ccrow['id']);
								if($ccrow['msg_total'] >= $space AND $space > 0){
									$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmRecFull'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
									return false;
								}
								// Block Check
								if($this->isBlocked(self::$ips->member['id'], intval($ccrow['id']))){
									$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmRecBlocked'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
									return false;
								}
							}
							$ccusers[] = intval($i);
						}
					}
				}
				// IPB is a total pain in the butt, hence we need to now change the IDs to names, and stick some <br> in it.
				if(is_array($ccusers) AND count($ccusers) > 1){
					$ccsql = implode('\n', $this->ipbwi->member->id2name($ccusers));
				}elseif(is_array($ccusers) AND count($ccusers) == '1'){
					$ccsql = $this->ipbwi->member->id2name($ccusers['0']);
				}else{
					$ccsql = '';
				}
				$msgtxtstring = self::$ips->DB->compile_db_insert_string(array('msg_author_id' => self::$ips->member['id'],
						'msg_date' => time(),
						'msg_post' => self::$ips->remove_tags($message),
						'msg_sent_to_count' => count($ccusers) + 1,
						'msg_deleted_count' => 0,
						'msg_post_key' => 0,
						'msg_cc_users' => $ccsql
						));
				// Insert
				self::$ips->DB->query('INSERT INTO ibf_message_text (' . $msgtxtstring['FIELD_NAMES'] . ') VALUES (' . $msgtxtstring['FIELD_VALUES'] . ')');
				$c = self::$ips->DB->get_insert_id();
				$DBstring = self::$ips->DB->compile_db_insert_string(array('mt_owner_id' => self::$ips->member['id'],
						'mt_date' => time(),
						'mt_read' => '0',
						'mt_title' => $title,
						'mt_from_id' => self::$ips->member['id'],
						'mt_vid_folder' => 'unsent',
						'mt_to_id' => $toID,
						'mt_tracking' => '0',
						'mt_msg_id' => $c
						));
				self::$ips->DB->query('INSERT INTO ibf_message_topics (' . $DBstring['FIELD_NAMES'] . ') VALUES (' . $DBstring['FIELD_VALUES'] . ')');
				$this->ipbwi->cache->updatePM(self::$ips->member['id']);
				return true;
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Sends a PM.
		 * @param	int		$toID Member ID to receive the message
		 * @param	string	$title Message title
		 * @param	string	$message Message body
		 * @param	array	$cc Array of ID for carbon copies (CC)
		 * @param	int		$sentfolder Default: 0=do not save message in Sent folder, 1=save message
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->send(5,'message title','message content,array('55','77'),3);
		 * </code>
		 * @since			2.0
		 */
		public function send($toID, $title, $message, $cc = array(), $sentfolder = '0'){
			if(!$this->ipbwi->member->isLoggedIn()){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(!$toID){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmNoRecipient'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(!$title OR strlen($title) < 2){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmTitle'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(!$message OR strlen($message) < 2){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMessage'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			self::$ips->DB->query('SELECT m.name, m.id, m.view_pop, m.mgroup, m.email_pm, m.language, m.email, m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, ibf_members m WHERE m.id="'.intval($toID).'" AND g.g_id=m.mgroup');
			if($row = self::$ips->DB->fetch_row()){
				// Just incase
				if(!$row['id']){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// Permissions Check
				if(!$this->ipbwi->permissions->has('g_use_pm',$row['id'])){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemDisallowed'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// Space Check
				$space = $this->ipbwi->permissions->best('g_max_messages',$row['id']);
				if($row['msg_total'] >= $space AND $space > 0){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemFull'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// Block Check
				if($this->isBlocked(self::$ips->member['id'], intval($toID))){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemBlocked'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// CC Users
				$ccusers = array();
				$max = $this->ipbwi->permissions->best('g_max_mass_pm','',false);
				if($max){
					if(is_array($cc) AND count($cc) > 0){
						if(count($cc) > $max){
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmCClimit'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
						foreach($cc AS $i){
							// Check CC user stuff
							// I really should clean up the code here, it uses alot of queries in some cases, which isn't good. Should really merge this with the main sending message code instead of replicating stuff for CCs.
							self::$ips->DB->query('SELECT m.name, m.id, m.view_pop, m.mgroup, m.email_pm, m.language, m.email, m.msg_total, g.g_use_pm, g.g_max_messages FROM ibf_groups g, ibf_members m WHERE m.id="'.intval($toID).'" AND g.g_id=m.mgroup');
							if($ccrow = self::$ips->DB->fetch_row()){
								// Permissions Check
								if(!$this->ipbwi->permissions->has('g_use_pm',$ccrow['id'])){
									$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmRecDisallowed'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
									return false;
								}
								// Space Check
								$space = $this->ipbwi->permissions->best('g_max_messages',$ccrow['id']);
								if($ccrow['msg_total'] >= $space AND $space > 0){
									$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmRecFull'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
									return false;
								}
								// Block Check
								if($this->isBlocked(self::$ips->member['id'], intval($ccrow['id']))){
									$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmRecBlocked'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
									return false;
								}
							}
							$ccusers[] = intval($i);
						}
					}
				}
				// Actually send it
				// IPB is a total pain in the butt, hence we need to now change the IDs to names, and stick some <br> in it.
				if(is_array($ccusers) AND count($ccusers) > 1){
					$ccsql = implode('<br />', strtolower($this->ipbwi->member->id2name($ccusers)));
				}elseif(is_array($ccusers) AND count($ccusers) == '1'){
					$ccsql = strtolower($this->ipbwi->member->id2name($ccusers['0']));
				}else{
					$ccsql = '';
				}
				$ccusers[] = intval($toID);
				$msgtxtstring = self::$ips->DB->compile_db_insert_string(
					array(
						'msg_author_id'			=> self::$ips->member['id'],
						'msg_date'				=> time(),
						'msg_post'				=> self::$ips->remove_tags($message),
						'msg_sent_to_count'		=> count($ccusers),
						'msg_deleted_count'		=> 0,
						'msg_post_key'			=> md5(microtime()),
						'msg_cc_users'			=> $ccsql
					)
				);
				// Insert singular text entry
				self::$ips->DB->query('INSERT INTO ibf_message_text ('.$msgtxtstring['FIELD_NAMES'].') VALUES ('.$msgtxtstring['FIELD_VALUES'].')');
				$c = self::$ips->DB->get_insert_id();
				foreach($ccusers as $recipient){
					$DBstring = self::$ips->DB->compile_db_insert_string(
						array(
							'mt_owner_id'		=> $recipient,
							'mt_date'			=> time(),
							'mt_read'			=> '0',
							'mt_title'			=> $title,
							'mt_from_id'		=> self::$ips->member['id'],
							'mt_vid_folder'		=> 'in',
							'mt_to_id'			=> $recipient,
							'mt_tracking'		=> '0',
							'mt_msg_id'			=> $c
						)
					);
					self::$ips->DB->query('INSERT INTO ibf_message_topics ('.$DBstring['FIELD_NAMES'].') VALUES ('.$DBstring['FIELD_VALUES'].')');
					self::$ips->DB->query('UPDATE ibf_members SET msg_total = msg_total + 1, new_msg = new_msg + 1, show_popup="1" WHERE id="'.$recipient.'"');
				}
				if($sentfolder){
					$DBstring = self::$ips->DB->compile_db_insert_string(array('mt_owner_id' => self::$ips->member['id'],
							'mt_date' => time(),
							'mt_read' => '0',
							'mt_title' => 'Sent: ' . $title,
							'mt_from_id' => self::$ips->member['id'],
							'mt_vid_folder' => 'sent',
							'mt_to_id' => $recipient,
							'mt_tracking' => '0',
							'mt_msg_id' => $c
							));
					self::$ips->DB->query('INSERT INTO ibf_message_topics ('.$DBstring['FIELD_NAMES'].') VALUES ('.$DBstring['FIELD_VALUES'].')');
				}
				$this->cache->updatePM(self::$ips->member['id']);
				return true;
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('pmMemNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Returns information on a Personal Message.
		 * @param	int		$ID PM record ID
		 * @param	int		$markRead Default: 0=keep unread, 1=mark read
		 * @param	int		$convert Default: 1 convert BBCode to HTML
		 * @return	array	Information of a PM
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->info(5,true,false);
		 * </code>
		 * @since			2.0
		 */
		public function info($ID, $markRead = false, $convert = true){
			if(!$this->ipbwi->member->isLoggedIn() AND !$this->ipbwi->permissions->has('g_use_pm')){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			self::$ips->DB->query('SELECT m.*, t.*, s.name, r.name AS recipient_name FROM ibf_message_topics t LEFT JOIN ibf_message_text m ON (t.mt_msg_id=m.msg_id) LEFT JOIN ibf_members s ON (t.mt_from_id=s.id) LEFT JOIN ibf_members r ON (t.mt_to_id=r.id) WHERE mt_owner_id="'.self::$ips->member['id'].'" AND mt_id="'.intval($ID).'"');
			if(self::$ips->DB->get_num_rows()){
				if($row = self::$ips->DB->fetch_row()){
					if($markRead AND !$row['mt_read']){
						self::$ips->DB->query('UPDATE ibf_message_topics SET mt_read="1", read_date="'.time().'" WHERE mt_msg_id="'.$ID.'" AND mt_owner_id="'.self::$ips->member['id'].'" LIMIT 1');
						if($row['vid'] == 'in'){
							self::$ips->DB->query('UPDATE ibf_members SET new_msg=new_msg-1 WHERE id="'.self::$ips->member['id'].'" AND new_msg > 0');
						}
					}
					if($convert){
						self::$ips->parser->parse_smilies	= 1;
						self::$ips->parser->parse_html		= 0;
						self::$ips->parser->parse_bbcode	= 1;
						self::$ips->parser->strip_quotes	= 1;
						self::$ips->parser->parse_nl2br		= 1;
						// make proper XHTML
						$row['msg_post_bbcode']		= $this->ipbwi->properXHTML($this->ipbwi->bbcode->html2bbcode($row['msg_post']));
						$row['msg_post']			= self::$ips->parser->pre_display_parse($row['msg_post']);
						$row['msg_post']			= $this->ipbwi->properXHTML($row['msg_post']);
						$row['mt_title']			= $this->ipbwi->properXHTML($row['mt_title']);
						$row['name']				= $this->ipbwi->properXHTML($row['name']);
						$row['mt_vid_folder']		= $this->ipbwi->properXHTML($row['mt_vid_folder']);
						$row['recipient_name']		= $this->ipbwi->properXHTML($row['recipient_name']);
					}
					return $row;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Lists PMs in a folder.
		 * @param	string	$folder Keyname of Inbox folder, 'in', 'sent'
		 * @return	array	Information of PMs in folder.
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->getList(5);
		 * </code>
		 * @since			2.0
		 */
		public function getList($folder = 'in'){
			if(!$this->ipbwi->member->isLoggedIn() AND !$this->ipbwi->permissions->has('g_use_pm')){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$PMs = array();
			self::$ips->DB->query('SELECT m.*, t.*, s.name, r.name AS recipient_name FROM ibf_message_topics t LEFT JOIN ibf_message_text m ON (t.mt_msg_id=m.msg_id) LEFT JOIN ibf_members s ON (t.mt_from_id=s.id) LEFT JOIN ibf_members r ON (t.mt_to_id=r.id) WHERE mt_owner_id="'.self::$ips->member['id'].'" AND mt_vid_folder="'.$folder.'" ORDER BY mt_date DESC');
			if(self::$ips->DB->get_num_rows()){
				self::$ips->parser->parse_smilies	= 1;
				self::$ips->parser->parse_html		= 0;
				self::$ips->parser->parse_bbcode	= 1;
				self::$ips->parser->strip_quotes 	= 1;
				self::$ips->parser->parse_nl2br		= 1;
				while($row = self::$ips->DB->fetch_row()){
					// make proper XHTML
					$row['msg_post_bbcode']		= $this->ipbwi->properXHTML($this->ipbwi->bbcode->html2bbcode($row['msg_post']));
					$row['msg_post']			= self::$ips->parser->pre_display_parse($row['msg_post']);
					$row['msg_post']			= $this->ipbwi->properXHTML($row['msg_post']);
					$row['mt_title']			= $this->ipbwi->properXHTML($row['mt_title']);
					$row['name']				= $this->ipbwi->properXHTML($row['name']);
					$row['mt_vid_folder']		= $this->ipbwi->properXHTML($row['mt_vid_folder']);
					$row['recipient_name']		= $this->ipbwi->properXHTML($row['recipient_name']);
					$PMs[] = $row;
				}
				return $PMs;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Gets number of new PMs.
		 * @return	int		New Unread Messages Count
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->numNewPMs();
		 * </code>
		 * @since			2.0
		 */
		public function numNewPMs(){
			if(!$this->ipbwi->member->isLoggedIn() AND !$this->ipbwi->permissions->has('g_use_pm')){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			self::$ips->DB->query('SELECT new_msg FROM ibf_members WHERE id="'.self::$ips->member['id'].'"');
			if($messages = self::$ips->DB->fetch_row()){
				return (int)$messages['new_msg'];
			}else{
				return false;
			}
		}
		/**
		 * @desc			Gets total number of PMs.
		 * @return	int		Total Messages Count
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->numTotalPMs();
		 * </code>
		 * @since			2.0
		 */
		public function numTotalPMs(){
			if(!$this->ipbwi->member->isLoggedIn() AND !$this->ipbwi->permissions->has('g_use_pm')){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			self::$ips->DB->query('SELECT msg_total FROM ibf_members WHERE id="'.self::$ips->member['id'].'"');
			if($messages = self::$ips->DB->fetch_row()){
				return $messages['msg_total'];
			}else{
				return false;
			}
		}
		/**
		 * @desc			Blocks a contact.
		 * @param	int		$userID Member ID to be added
		 * @param	string	$description Description for the 'Buddy'
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->blockContact(55,'do not bother me');
		 * </code>
		 * @since			2.0
		 */
		public function blockContact($userID, $description = false){
			if($this->isLoggedIn()){
				// Check user exists
				if(!$userID OR !$this->info(intval($userID))){
					return false;
				}
				// o_O. Firstly check if there is already an entry.
				self::$ips->DB->query('SELECT * FROM ibf_contacts WHERE contact_id="'.intval($userID).'" AND member_id="'.self::$ips->member['id'].'"');
				if($row = self::$ips->DB->fetch_row()){
					if($row['allow_msg'] == '0' AND $row['contact_desc'] == $description){
						// Clearly no point of doing anything.
						return true;
					}else{
						// Update record
						self::$ips->DB->query('UPDATE ibf_contacts SET allow_msg="0", contact_desc="'.$description.'" WHERE contact_id="'.intval($userID).'" AND member_id="'.self::$ips->member['id'].'"');
						return true;
					}
				}else{
					// We can just add an entry because theres nothing there.
					self::$ips->DB->query('INSERT INTO ibf_contacts VALUES ("", "'.intval($userID).'", "'.self::$ips->member['id'].'", "'.$this->id2name(intval($userID)).'", "1", "'.$description .'")');
					return true;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns blocked members information.
		 * @return	array	Blocked Members Information
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->pm->blockedList();
		 * </code>
		 * @since			2.0
		 */
		public function blockedList(){
			if($this->ipbwi->member->isLoggedIn()){
				self::$ips->DB->query('SELECT contact_id, contact_desc, contact_name FROM ibf_contacts WHERE member_id="'.self::$ips->member['id'].'" AND allow_msg="0"');
				$blocked = array();
				while($row = self::$ips->DB->fetch_row()){
					$blocked[$row['contact_id']] = $row;
				}
				return $blocked;
			}else{
				return false;
			}
		}
	}
?>