<?php
	/**
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @version			$LastChangedDate: 2009-08-26 19:19:41 +0200 (Mi, 26 Aug 2009) $
	 * @package			member
	 * @copyright		2007-2009 IPBWI development team
	 * @link			http://ipbwi.com/examples/member.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */
	class ipbwi_member extends ipbwi {
		private $ipbwi			= null;
		private $loggedIn		= null;
		/**
		 * @desc			Loads and checks different vars when class is initiating
		 * @author			Matthias Reuter
		 * @since			2.0
		 * @ignore
		 */
		public function __construct($ipbwi){
			// loads common classes
			$this->ipbwi = $ipbwi;

			// checks if the current user is logged in
			if(empty(self::$ips->member['mgroup']) OR self::$ips->member['mgroup'] == self::$ips->vars['guest_group'] OR self::$ips->member['id'] == '0'){
				$this->loggedIn = false;
			}else{
				$this->loggedIn = true;
			}
		}
		/**
		 * @desc			Returns whether a member can access the board's Admin CP.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	bool	Whether currently logged in member can access ACP
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->isAdmin(5);
		 * </code>
		 * @since			2.0
		 */
		public function isAdmin($userID=false){
			return $this->ipbwi->permissions->has('g_access_cp',$userID);
		}
		/**
		 * @desc			Returns whether a member is a super moderator.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	bool	Whether currently logged in member is a Super Moderator
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->isSuperMod(5);
		 * </code>
		 * @since			2.0
		 */
		public function isSuperMod($userID=false){
			return $this->ipbwi->permissions->has('g_is_supmod',$userID);
		}
		/**
		 * @desc			Returns whether a member is logged in.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	bool	Whether currently logged in member is a Super Moderator
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->isLoggedIn(5);
		 * </code>
		 * @since			2.0
		 */
		public function isLoggedIn($userID=false){
			if($userID){
				if(in_array($userID,$this->listOnlineMembers())){
					return true;
				}else{
					return false;
				}
			}else{
				return $this->loggedIn;
			}
		}
		/**
		 * @desc			Grabs detailed information of a member.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	array	Member Information, or false on failure
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->info(5);
		 * </code>
		 * @since			2.0
		 */
		public function info($userID = false){
			if(!$userID){
				if($this->isLoggedIn()){
					// No UID? Return current user info
					$userID = self::$ips->member['id'];
				}else{
					// Return guest group info
					self::$ips->DB->query('SELECT * FROM ibf_groups WHERE g_id="2"');
					if(self::$ips->DB->get_num_rows()){
						$info = self::$ips->DB->fetch_row();
						$this->ipbwi->cache->save('memberInfo', $userID, $info);
						return $info;
					}else{
						return false;
					}
				}
			}
			// Check for cache - if exists don't bother getting it again
			if($cache = $this->ipbwi->cache->get('memberInfo', $userID)){
				return $cache;
			}else{
				// Return user info if UID given
				self::$ips->DB->query('SELECT * FROM ibf_members m LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) LEFT JOIN ibf_pfields_content cf ON (cf.member_id=m.id) LEFT JOIN ibf_member_extra me ON (me.id=m.id) LEFT JOIN ibf_profile_portal pp ON(pp.pp_member_id=m.id) WHERE m.id="'.intval($userID).'"');
				if(self::$ips->DB->get_num_rows()){
					$info = self::$ips->DB->fetch_row();
					$info['signature'] = $this->ipbwi->properXHTML($info['signature']);
					$info['members_display_name'] = $this->ipbwi->properXHTML($info['members_display_name']);
					$info['name'] = $this->ipbwi->properXHTML($info['name']);
					if($info['icq_number'] == 0){
						$info['icq_number'] = '';
					}
					$this->ipbwi->cache->save('memberInfo', $userID, $info);
					return $info;
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Returns the HTML code to show a member's avatar.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @return	string	HTML Code for member's avatar, or false on failure
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->avatar(5);
		 * </code>
		 * @since			2.0
		 */
		public function avatar($member = false){
			// No Member ID specified? Go for the current users UID.
			if(!$member){
				$member = self::$ips->member['id'];
			}
			// Get Avatar Info
			if($row = $this->info($member)){
				$avatar = self::$ips->get_avatar ($row['avatar_location'], 1, $row['avatar_size'], $row['avatar_type']);
				if($row['avatar_type'] == 'local' && $row['avatar_location'] != 'noavatar'){
					$avatar = str_replace('<img src="','<img src="'.$this->ipbwi->getBoardVar('url').'style_avatars',$avatar);
				}
				$avatar = $this->ipbwi->properXHTML($avatar);
				return $avatar;
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('badMemID'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Returns HTML code for member's photo.
		 * @param	int		$userID User ID. If $userID is ommited, the last known member id is used.
		 * @param	bool	$thumb true to activate thumbnail, otherwise false (default)
		 * @return	string	HTML code for member photo
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->photo(5,true);
		 * </code>
		 * @since			2.0
		 */
		public function photo($userID = false,$thumb = false){
			if(!$userID){
				$userID = self::$ips->member['id'];
			}
			self::$ips->DB->query('SELECT pp_main_photo, pp_main_width, pp_main_height, pp_thumb_photo, pp_thumb_width, pp_thumb_height FROM ibf_profile_portal WHERE pp_member_id="'.intval($userID).'"');
			if($row = self::$ips->DB->fetch_row()){
				if($row['pp_main_photo']){
					if($thumb === true && $row['pp_thumb_photo']){
						$photo = '<a href="'.$this->ipbwi->getBoardVar('upload_url').'/'.$row['pp_main_photo'].'"><img src="'.$this->ipbwi->getBoardVar('upload_url').'/'.$row['pp_thumb_photo'].'" width="'.$row['pp_thumb_width'].'" height="'.$row['pp_thumb_height'].'" alt="'.$this->id2displayname($userID).'" /></a>';
					}else{
						$photo = '<img src="'.$this->ipbwi->getBoardVar('upload_url').'/'.$row['pp_main_photo'].'" width="'.$row['pp_main_width'].'" height="'.$row['pp_main_height'].'" alt="'.$this->id2displayname($userID).'" />';
					}
					return $photo;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Gets the Member ID associated with a Member Name.
		 * @param	mixed	$names If you pass an array with names, the function also returns an array with each name beeing the key and the ID as its value. If a member name could not be found, the value will be set to false.
		 * @return	mixed	Single Member ID, assoc. array with id/name pairs, or false if the name(s) could not be found
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->name2id('name');
		 * $ipbwi->member->name2id(array('name1','name2'));
		 * </code>
		 * @since			2.0
		 */
		public function name2id($names){
			if(is_array($names)){
				foreach($names as $i => $j){
					self::$ips->DB->query('SELECT id FROM ibf_members WHERE LOWER(name)="'.$this->ipbwi->makeSafe(strtolower(trim($j))).'"');
					if($row = self::$ips->DB->fetch_row()){
						$ids[$i] = $row['id'];
					}else{
						$ids[$i] = false;
					}
				}
				return $ids;
			}else{
				self::$ips->DB->query('SELECT id FROM ibf_members WHERE LOWER(name)="'.$this->ipbwi->makeSafe(strtolower(trim($names))).'"');
				if($row = self::$ips->DB->fetch_row()){
					return $row['id'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Creates a new account and returns the member ID for further processing.
		 * @param	string	$userName Username
		 * @param	string	$password In plain text. Will be encrypted with md5()
		 * @param	string	$email Mail
		 * @param	array	$customFields Optional values for the (existing) custom profile fields.
		 * @param	boolean	$validate Whether to put the user in the validation group
		 * @param	string	$displayName Display name
		 * @return	long	New Member ID or false on failure
		 * @author			Jan Ecker <info@jan-ecker.eu>
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->create('name', 'password', 'email@foo.com');
		 * $ipbwi->member->create('name', 'password', 'email@foo.com', array('1' => 'content of field1', '2' => 'content of field2'), true, 'displayname', true);
		 * </code>
		 * @since			2.0
		 */
		public function create($userName, $password, $email, $customFields = array(), $validate = false, $displayName = '', $useBanFilter = false){
			// Display name?
			if($displayName == ''){
				$displayName = $userName;
			}
			$cfields = array();
			// Custom Profile Stuff
			self::$ips->DB->query('SELECT * from ibf_pfields_data WHERE pf_member_edit="1"');
			while($row = self::$ips->DB->fetch_row()){
				// Required and No Field Specified? return false
				if($row['pf_not_null'] AND !$customFields[$row['pf_id']]){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('cfMissing'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// Is it too long?
				if($row['pf_max_input'] > 0){
					if(strlen($customFields[$row['pf_id']]) > $row['pf_max_input']){
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('cfLength'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
				}
				$cfields['field_' . $row['pf_id']] = str_replace('<br>', "\n", $customFields[$row['pf_id']]);
			}
			// Check and Clean Username, Password & Email
			$userName		= trim(str_replace('|', '&#124;' , $userName));
			$displayName	= trim(str_replace('|', '&#124;' , $displayName));
			$password		= trim($password);
			$email			= strtolower(trim($email));
			// Strip Multiple Spaces
			$userName = preg_replace('/\s{2,}/', ' ', $userName);
			// Check Ban Filter
			if($useBanFilter){
				$bannedInfo = array();
				$bantypes = array();
				self::$ips->DB->query('SELECT * FROM ibf_banfilters');
				while($r = self::$ips->DB->fetch_row()){
					$bantypes[$r['ban_type']][] = $r['ban_content'];
				}
				
				// Are they banned?
				foreach($bantypes as $bantype => $banfilters){
					if($bantype == 'email') $checkData = $email;
					elseif($bantype == 'name') $checkData = $userName;
					elseif($bantype == 'ip') $checkData = $_SERVER['REMOTE_ADDR'];
					
					if(is_array($banfilters) && count($banfilters)){
						foreach($banfilters as $bannedEntry){
							$bannedEntry = str_replace('\*', '.*' , preg_quote($bannedEntry, '/'));
							if(preg_match("/^{$bannedEntry}$/i", $checkData)){
								$bannedInfo[$bantype][] = self::$ips->lang['reg_error_email_taken'];
								break;
							}
						}
					}
				}
				
				if(count($bannedInfo) > 0){
					$this->banned_info = $bannedInfo;
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accBanned'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}
			if(empty($userName) OR strlen($userName) < 3 OR strlen($userName) > 32){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accUser'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(empty($displayName) OR strlen($displayName) < 3 OR strlen($displayName) > 32){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accUser'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(empty($password) OR strlen($password) < 3 OR strlen($password) > 32){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accPass'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$email = self::$ips->clean_email($email);
			if(empty($email) OR strlen($email) < 6){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accEmail'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Already taken?
			self::$ips->DB->query('SELECT id FROM ibf_members WHERE LOWER(name)="'.strtolower($userName).'" OR email="'.$email.'" OR LOWER(members_display_name)="'.strtolower($displayName).'"');
			if(self::$ips->DB->get_num_rows() OR strtolower($userName) == 'guest'){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accTaken'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Reserved?
			if(isset(self::$ips->vars['ban_names'])){
				$reserved = explode ('|', self::$ips->vars['ban_names']);
				foreach($reserved as $i){
					if($i != ''){
						if(preg_match('/'.preg_quote($i, '/').'/i',$userName)){
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accUser'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
					}
				}
			}
			// Is validation required?
			// If so, let's put them in the validation group...
			$mGroup = $validate ? self::$ips->vars['auth_group'] : self::$ips->vars['member_group'];
			$member = array(
				'name'						=> $userName,
				'member_login_key'			=> self::$ips->converge->generate_auto_log_in_key(),
				'email'						=> $email,
				'mgroup'					=> $mGroup,
				'posts'						=> 0,
				'joined'					=> time(),
				'ip_address'				=> self::$ips->input['IP_ADDRESS'],
				'time_offset'				=> 0,
				'view_sigs'					=> 1,
				'email_pm'					=> 1,
				'view_img'					=> 1,
				'view_avs'					=> 1,
				'restrict_post'				=> 0,
				'view_pop'					=> 1,
				'msg_total'					=> 0,
				'new_msg'					=> 0,
				'coppa_user'				=> 0,
				'language'					=> self::$ips->vars['default_language'],
				'dst_in_use'				=> 0,
				'allow_admin_mails'			=> 1,
				'hide_email'				=> 0,
				'subs_pkg_chosen'			=> 0,
				'members_display_name'		=> $displayName,
				'members_l_display_name'	=> $displayName,
				'members_l_username'		=> $userName
			);
			$salt	= self::$ips->converge->generate_password_salt(5);
			while(!(strpos($salt, "'") === false) || !(strpos($salt, '"') === false)){
				$salt = self::$ips->converge->generate_password_salt(5);
			}
			$passhash	= self::$ips->converge->generate_compiled_passhash($salt, md5($password));
			$converge	= array(
				'converge_email'		=> $email,
				'converge_joined'		=> time(),
				'converge_pass_hash'	=> $passhash,
				'converge_pass_salt'	=> str_replace('\\','\\\\', $salt )
			);
			// Insert: CONVERGE
			self::$ips->DB->force_data_type = self::$ips->DB->no_escape_fields = array(
				'converge_email'		=> false,
	   			'converge_joined'		=> false,
				'converge_pass_hash'	=> false,
				'converge_pass_salt'	=> false
	 		);
			self::$ips->DB->do_insert( 'members_converge', $converge);
			// Get converges auto_increment user_id
			$memberID		= self::$ips->DB->get_insert_id();
			$member['id']	= $memberID;
			// Insert: MEMBERS
			self::$ips->DB->force_data_type = self::$ips->DB->no_escape_fields = array(
				 'id'					=> false,
				 'name'					=> false,
				 'member_login_key'		=> false,
				 'email'				=> false,
				 'mgroup'				=> false,
				 'posts'				=> false,
				 'joined'				=> false,
				 'ip_address'			=> false,
				 'time_offset'			=> false,
				 'view_sigs'			=> false,
				 'email_pm'				=> false,
				 'view_img'				=> false,
				 'view_avs'				=> false,
				 'restrict_post'		=> false,
				 'view_pop'				=> false,
				 'msg_total'			=> false,
				 'new_msg'				=> false,
				 'coppa_user'			=> false,
				 'language'				=> false,
				 'dst_in_use'			=> false,
				 'allow_admin_mails'	=> false,
				 'hide_email'			=> false,
				 'subs_pkg_chosen'		=> false,
				 'members_display_name'	=> false
			);
			self::$ips->DB->do_insert('members', $member);
			// Insert: MEMBER EXTRA
			self::$ips->DB->force_data_type = self::$ips->DB->no_escape_fields = array('id' => false,'vdirs' => false);
			self::$ips->DB->do_insert('member_extra', array('id' => $memberID, 'vdirs' => 'in:Inbox|sent:Sent Items'));
			// Insert into the custom profile fields DB
			unset($this->DB_string);
			// Custom Fields
			self::$ips->DB->query('DELETE FROM ibf_pfields_content WHERE member_id="'.$member['id'].'"');
			$this->DB_string	= self::$ips->DB->compile_db_insert_string($cfields);
			$fields				= $this->DB_string['FIELD_NAMES'] ? ', ' . $this->DB_string['FIELD_NAMES'] : '';
			$values				= $this->DB_string['FIELD_VALUES'] ? ', ' . $this->DB_string['FIELD_VALUES'] : '';
			self::$ips->DB->query('INSERT INTO ibf_pfields_content (member_id'.$fields.') VALUES('.$memberID.$values.')');
			unset($this->DB_string);
			if($validate){
				$validateKey	= md5(self::$ips->make_password().time());
				$time			= time();
				self::$ips->DB->do_insert(
					'validating',
					array(
						'vid'			=> $validateKey,
						'member_id'		=> $memberID,
						'real_group'	=> self::$ips->vars['member_group'],
						'temp_group'	=> self::$ips->vars['auth_group'],
						'entry_date'	=> $time,
						'coppa_user'	=> 0,
						'new_reg'		=> 1,
						'ip_address'	=> $_SERVER['REMOTE_ADDR']
					)
				);
				require_once(ipbwi_BOARD_PATH.'sources/classes/class_email.php');
				// Require verification email class
				$this->email = new emailer(ipbwi_BOARD_PATH);
				$this->email->ipsclass = self::$ips;
				$this->email->email_init();
				// Build email message
				$this->email->get_template('reg_validate');
				$this->email->build_message(
					array(
						'THE_LINK'	=> self::$ips->vars['board_url'].'/index.php?act=Reg&amp;CODE=03&amp;uid='.urlencode($memberID).'&amp;aid='.urlencode($validateKey),
						'NAME'		=> $displayName,
						'MAN_LINK'	=> self::$ips->vars['board_url'].'/index.php?act=Reg&amp;CODE=05',
						'EMAIL'		=> $email,
						'ID'		=> $memberID,
						'CODE'		=> $validateKey,
					)
				);
				// For this single tiny insignificant line, we need to load a language file.
				$lang = false;
				require_once(ipbwi_BOARD_PATH.'cache/lang_cache/'.self::$ips->vars['default_language'].'/lang_register.php');
				$this->email->subject = $lang['new_registration_email1'].self::$ips->vars['board_name'];
				$this->email->to	  = $email;
				// Send email message
				$this->email->send_mail();
			}else{
				self::$ips->cache['stats']['last_mem_name']	= $member['name'];
				self::$ips->cache['stats']['last_mem_id']	= $member['id'];
				self::$ips->cache['stats']['mem_count']		+= 1;
			}
			self::$ips->DB->force_data_type = $this->DB->no_escape_fields = array('id' => false,'cs_array' => false,'cs_value' => false);
			self::$ips->update_cache(array( 'name' => 'stats', 'array' => 1, 'deletefirst' => 0, 'value' => false, 'donow' => false));
			// Finally
			return $memberID;
		}
		/**
		 * @desc			Deletes a Member.
		 * @param	mixed	$userIDs Member(s) to be deleted. int for single member id, or array for a list of ids
		 * @param	string	$password Plaintext password of currently logged in member for more security
		 * @return	bool	true on success, false on failure
		 * @author			Jan Ecker <info@jan-ecker.eu>
		 * @author			Matthias Reuter
		 * @author			Tamahome <thakid6583@gmail.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->delete(55);
		 * $ipbwi->member->delete(array(55,22,77));
		 * </code>
		 * @since			2.0
		 */
		public function delete($userIDs,$password=false){
			// Are there more than one ID's?
			if(is_array($userIDs)){
				foreach($userIDs as $k => $v){
					if(!is_numeric($v)){
						unset($userIDs[$k]);
					}
				}
				$loggedinUser = $this->info();
			}elseif(is_numeric($userIDs)){
				// The ID's gotta be numeric...
				$loggedinUser = $this->info();
			}else{
				return false;
			}
			// Are YOU logged in?
			if(!$this->isLoggedIn()){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Are there more than one ID's?
			if(is_array($userIDs)){
				if(!$this->isAdmin()){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				foreach($userIDs as $v){
					$user[] = $this->info($v);
				}
			}elseif(isset($userIDs) && is_numeric($userIDs)){
				// Do you have permission?
				if(isset($password) && $userIDs == $loggedinUser['id']){
					self::$ips->converge->converge_load_member(self::$ips->member['email']);
					if(!self::$ips->converge->member['converge_id']){
						$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('loginWrongPass'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
					// Check password...
					if(self::$ips->converge->converge_authenticate_member(md5(self::$ips->parse_clean_value($password))) != true ){
						$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('loginWrongPass'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
				}elseif(!$this->isAdmin()){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				$user[] = $this->info($userIDs);
			}
			// Let's finish the job.
			foreach($user as $v){
				// Insert: ADMIN LOGS
				if($this->isAdmin()){
					$adminLogs = array('id' => self::$ips->DB->get_insert_id(),
						'act' =>		'mem',
						'code' =>		'member_delete',
						'member_id' =>	$loggedinUser['id'],
						'ctime' =>		time(),
						'note' =>		'Deleted Member(s) ('.$v['id'].')',
						'ip_address' =>	$loggedinUser['ip_address']
					);
					self::$ips->DB->force_data_type = self::$ips->DB->no_escape_fields = array(
						'id' =>			false,
						'act'	=>		false,
						'code' =>		false,
						'member_id' =>	false,
						'ctime' =>		false,
						'note' =>		false,
						'ip_address' =>	false
					);
					self::$ips->DB->do_insert('admin_logs', $adminLogs);
				}
				// Delete All Member Info
				self::$ips->DB->query('DELETE FROM ibf_contacts WHERE member_id="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_dnames_change WHERE dname_member_id="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_members WHERE id="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_members_converge WHERE converge_id="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_member_extra WHERE id="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_message_topics WHERE mt_owner_id="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_pfields_content WHERE member_id="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_profile_comments WHERE comment_for_member_id="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_profile_friends WHERE friends_member_id ="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_profile_portal WHERE pp_member_id="'.$v['id'].'" LIMIT 1');
				self::$ips->DB->query('DELETE FROM ibf_warn_logs WHERE wlog_mid="'.$v['id'].'" LIMIT 1');
			}
			return true;
		}
		/**
		 * @desc			Gets the Member Name associated with a Member ID.
		 * @param	mixed	$userIDs Member Ids. If you pass an array with IDs, the function also returns an array with each ID beeing the key and the member name as its value. If a member ID could not be found, the value will be set to false.
		 * @return	mixed	Single member name, assoc. array with name/id pairs, or false if the ID(s) could not be found
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->id2name(55);
		 * $ipbwi->member->id2name(array(55,22,77));
		 * </code>
		 * @since			2.0
		 */
		public function id2name($userIDs){
			if(is_array($userIDs)){
				foreach($userIDs as $i => $j){
					if($row = $this->info($j)){
						$names[$i] = $row['name'];
					}else{
						$names[$i] = false;
					}
				}
				return $names;
			}else{
				if($row = $this->info($userIDs)){
					return $row['name'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Gets the Member ID associated with a Display Name.
		 * @param	mixed	$names Member Names. If you pass an array with names, the function also returns an array with each name beeing the key and the ID as its value. If a member name could not be found, the value will be set to false.
		 * @return	mixed	Single Member ID, assoc. array with id/name pairs, or false if the name(s) could not be found
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->displayname2id('displayname');
		 * $ipbwi->member->displayname2id(array('displayname2','displayname2','displayname3'));
		 * </code>
		 * @since			2.0
		 */
		public function displayname2id($names){
			if(is_array($names)){
				foreach($names as $i => $j){
					self::$ips->DB->query('SELECT id FROM ibf_members WHERE LOWER(members_display_name)="'.$this->ipbwi->makeSafe(strtolower(trim($j))).'"');
					if($row = self::$ips->DB->fetch_row()){
						$ids[$i] = $row['id'];
					}else{
						$ids[$i] = false;
					}
				}
				return $ids;
			}else{
				self::$ips->DB->query('SELECT id FROM ibf_members WHERE LOWER(members_display_name)="'.$this->ipbwi->makeSafe(strtolower(trim($names))).'"');
				if($row = self::$ips->DB->fetch_row()){
					return $row['id'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Gets the Member Display Name associated with a Member ID.
		 * @param	mixed	$userIDs Member IDs. If you pass an array with IDs, the function also returns an array with each ID beeing the key and the member name as its value. If a member ID could not be found, the value will be set to false.
		 * @return	mixed	Single member name, assoc. array with name/id pairs, or false if the ID(s) could not be found
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->id2displayname(55);
		 * $ipbwi->member->id2displayname(55,77,99));
		 * </code>
		 * @since			2.0
		 */
		public function id2displayname($userIDs){
			if(is_array($userIDs)){
				foreach($userIDs as $i => $j){
					if($row = $this->info($j)){
						if(isset($row['members_display_name'])){
							$names[$i] = $row['members_display_name'];
						}else{
							$names[$i] = $row['name'];
						}
					}else{
						$names[$i] = false;
					}
				}
				return $names;
			}else{
				if($row = $this->info($userIDs)){
					if(isset($row['members_display_name'])){
						return $row['members_display_name'];
					}elseif(isset($row['name'])){
						return $row['name'];
					}else{
						return false;
					}
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Gets the Member ID associated with a Member Email.
		 * @param	mixed	$emails Member Emails. If you pass an array with emails, the function also returns an array with each email beeing the key and the ID as its value. If a member email could not be found, the value will be set to false.
		 * @return	mixed	Single Member ID, assoc. array with id/email pairs, or false if the email(s) could not be found
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->email2id('email');
		 * $ipbwi->member->email2id(array('email1','email2','email3'));
		 * </code>
		 * @since			2.0
		 */
		public function email2id($emails){
			if(is_array($emails)){
				foreach($emails as $i => $j){
					self::$ips->DB->query('SELECT id FROM ibf_members WHERE LOWER(email)="'.strtolower($j).'"');
					if($row = self::$ips->DB->fetch_row()){
						$ids[$i] = $row['id'];
					}else{
						$ids[$i] = false;
					}
				}
				return $ids;
			}else{
				self::$ips->DB->query('SELECT id FROM ibf_members WHERE LOWER(email)="'.strtolower($emails).'"');
				if($row = self::$ips->DB->fetch_row()){
					return $row['id'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Grab a list of custom profile fields, and their properties.
		 * @return	array	custom profile fields and properties, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->listCustomFields();
		 * </code>
		 * @since			2.0
		 */
		public function listCustomFields(){
			// Check for cache...
			if($cache = $this->ipbwi->cache->get('listCustomFields', 1)){
				return $cache;
			}else{
				self::$ips->DB->query('SELECT * FROM ibf_pfields_data ORDER BY pf_id');
				if(self::$ips->DB->get_num_rows()){
					while($info = self::$ips->DB->fetch_row()){
						$fields['field_'.$info['pf_id']] = $info;
					}
					$this->ipbwi->cache->save('listCustomFields', 1, $fields);
					return $fields;
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Update properties of a member's record.
		 * @param	array	$update Associative array with fieldnames and values to update
		 * The following fields can be used in the $update array:
		 * + members_display_name
		 * + avatar_location
		 * + avatar_type
		 * + avatar_size
		 * + aim_name
		 * + icq_number
		 * + location
		 * + signature
		 * + website
		 * + yahoo
		 * + interests
		 * + msnname
		 * + integ_msg
		 * + title
		 * + allow_admin_mails
		 * + hide_email
		 * + email_pm
		 * + skin
		 * + language
		 * + view_sigs
		 * + view_img
		 * + view_avs
		 * + view_pop
		 * + bday_day
		 * + bday_month
		 * + bday_year
		 * + dst_in_use
		 * + email
		 * + pp_member_id
		 * + pp_profile_update
		 * + pp_bio_content
		 * + pp_last_visitors
		 * + pp_comment_count
		 * + pp_rating_hits
		 * + pp_rating_value
		 * + pp_rating_real
		 * + pp_friend_count
		 * + pp_main_photo
		 * + pp_main_width
		 * + pp_main_height
		 * + pp_thumb_photo
		 * + pp_thumb_width
		 * + pp_thumb_height
		 * + pp_gender
		 * + pp_setting_notify_comments
		 * + pp_setting_notify_friend
		 * + pp_setting_moderate_comments
		 * + pp_setting_moderate_friends
		 * + pp_setting_count_friends
		 * + pp_setting_count_comments
		 * + pp_setting_count_visitors
		 * + pp_profile_views
		 * @param	int		$userID The Member ID to update
		 * @param	int		$bypassPerms Default: false=use board permissions to allow update, true=bypass permissions
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Jan Ecker <info@Jan-Ecker.eu>
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->updateMember(array('website' => 'http://ipbwi.com', 'title' => 'mytitle'));
		 * $ipbwi->member->updateMember(array('website' => 'http://ipbwi.com'), 55, true);
		 * </code>
		 * @since			2.0
		 */
		public function updateMember($update = array(), $userID = false, $bypassPerms = false){
			// Do we have a member to update or not?
			if(!$userID){
				$userID = self::$ips->member['id'];
			}
			$userID = intval($userID);
			// Check we are logged in and can update profiles
			$info = $this->info($userID);
			if((!$this->isLoggedin() OR !$info['g_edit_profile']) AND !$bypassPerms){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(isset($update['members_display_name'])){
				$update['members_l_display_name'] = $update['members_display_name'];
			}
			if(isset($update['icq_number']) && strlen($update['icq_number']) > 9){
				$update['icq_number'] = false;
			}
			// Array of allowed array keys in $update we can update
			$meAllowed				= array('avatar_location', 'avatar_type', 'avatar_size', 'aim_name', 'icq_number', 'location', 'signature', 'website', 'yahoo', 'interests', 'msnname', 'integ_msg');
			$allowed				= array('members_display_name','members_l_display_name','title','allow_admin_mails','hide_email', 'email_pm', 'skin','language','view_sigs', 'view_img', 'view_avs', 'view_pop', 'bday_day', 'bday_month', 'bday_year', 'dst_in_use','email');
			$ppAllowed				= array('pp_member_id', 'pp_profile_update', 'pp_bio_content', 'pp_last_visitors', 'pp_comment_count', 'pp_rating_hits', 'pp_rating_value', 'pp_rating_real', 'pp_friend_count', 'pp_main_photo', 'pp_main_width', 'pp_main_height', 'pp_thumb_photo', 'pp_thumb_width', 'pp_thumb_height', 'pp_gender', 'pp_setting_notify_comments', 'pp_setting_notify_friend', 'pp_setting_moderate_comments', 'pp_setting_moderate_friends', 'pp_setting_count_friends', 'pp_setting_count_comments', 'pp_setting_count_visitors', 'pp_profile_views');
			// Init
			$ppSQLupdate			= false;
			$ppsqlInsert['fields']	= false;
			$ppsqlInsert['values']	= false;
			$sql					= false;
			$meSQL					= false;
			$ppSQL					= false;
			// If we have something to update
			if(count($update) > 0){
				foreach($update as $i => $j){
					if(in_array($i, $allowed)){
						// We can do this!!!!
						$update[$i] = $this->ipbwi->makeSafe($j);
						if($sql){
							$sql .= ','.$i.'="'.$update[$i].'"';
						}else{
							$sql .= $i.'="'.$update[$i].'"';
						}
					}
					if(in_array($i, $meAllowed)){
						// We can do this!!!!
						$meUpdate[$i] = $this->ipbwi->makeSafe($j);
						if($meSQL){
							$meSQL .= ','.$i.'="'.$meUpdate[$i].'"';
						}else{
							$meSQL .= $i .'="'.$meUpdate[$i].'"';
						}
					}
					if(in_array($i, $ppAllowed)){
						// We can do this!!!!
						$ppUpdate[$i] = $this->ipbwi->makeSafe($j);
						if(isset($ppSQLupdate) && $ppSQLupdate != ''){
							$ppSQLupdate .= ','.$i.'="'.$ppUpdate[$i].'"';
						}else{
							$ppSQLupdate = $i.'="'.$ppUpdate[$i].'"';
						}
						$ppsqlInsert['fields'] .= ','.$i;
						$ppsqlInsert['values'] .= ',"'.$ppUpdate[$i].'"';
					}
				}
				// Check we have something to do again
				if($sql || $meSQL || $ppSQL){
					// Update in Database
					if($sql){
						self::$ips->DB->query('UPDATE ibf_members SET '.$sql.' WHERE id="'.$info['id'].'"');
						if(isset($update['email'])){
							self::$ips->DB->query('UPDATE ibf_members_converge SET converge_email="'.$update['email'].'" WHERE converge_id="'.$info['id'].'"');
						}
					}
					if($meSQL){
						self::$ips->DB->query('UPDATE ibf_member_extra SET '.$meSQL.' WHERE id="'.$info['id'].'"');
					}
					if($ppsqlInsert && $ppSQLupdate){
						self::$ips->DB->query('INSERT INTO ibf_profile_portal (pp_member_id'.$ppsqlInsert['fields'].') VALUES("'.$info['id'].'"'.$ppsqlInsert['values'].') ON DUPLICATE KEY UPDATE '.$ppSQLupdate);
					}
					// Update in get_advinfo() cache.
					if(isset($update)) $info = array_merge($info, $update);
					if(isset($meUpdate)) $info = array_merge($info, $meUpdate);
					if(isset($ppUpdate)) $info = array_merge($info, $ppUpdate);
					$this->ipbwi->cache->save('memberInfo', $info['id'], $info);
					return true;
				}
			}
			return false;
		}
		/**
		 * @desc			Changes a user's password.
		 * @param	string	$newPass The new Member's password
		 * @param	string	$userID The Member's ID. If not set, the currently logged in member will be updated.
		 * @param	string	$currentPass Current password check for more security
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Jan Ecker <info@Jan-Ecker.eu>
		 * @author			Pita <peter@randomnity.com>
		 * @author			Saint <saint@saintdevelopment.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->updatePassword('new password');
		 * $ipbwi->member->updatePassword('new password',55,'old password');
		 * </code>
		 * @since			2.0
		 */
		public function updatePassword($newPass, $userID = false, $currentPass = false){
			$new_md5pass = md5(self::$ips->parse_clean_value($newPass));
			// Do we have a member to update or not?
			if($userID){
				$userID = intval($userID);
	 		}else{
				$userID = self::$ips->member['id'];
			}
			// Check we are logged in
			$info = $this->info($userID);
			if(!$this->isLoggedIn() && empty($userID)){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('noPerms'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(empty($newPass) OR strlen($newPass) < 3 OR strlen($newPass) > 32){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accPass'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			// Check current password...
			if($currentPass != false){
				self::$ips->converge->converge_load_member($info['email']);
				if(!self::$ips->converge->member['converge_id']){
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accPass'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				if(self::$ips->converge->converge_authenticate_member(md5(self::$ips->parse_clean_value($currentPass))) != true)
				{
					$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('accPass'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}
			$memkey	= self::$ips->converge->generate_auto_log_in_key();
			$salt	= self::$ips->converge->generate_password_salt(5);
			while(!(strpos($salt, "'") === false) || !(strpos($salt, '"') === false)){
				$salt = self::$ips->converge->generate_password_salt(5);
			}
			$passhash = self::$ips->converge->generate_compiled_passhash($salt, $new_md5pass);
			self::$ips->DB->query('UPDATE ibf_members_converge SET converge_pass_hash="'.$passhash.'",converge_pass_salt="'.$salt.'" WHERE converge_id="'.$userID.'"');
			self::$ips->DB->query('UPDATE ibf_members SET member_login_key="'.$memkey.'" WHERE id="'.$userID.'"');
			return true;
		}
		/**
		 * @desc			Updates the value of a custom profile field.
		 * @param	int		$ID Custom Profile field's ID
		 * @param	string	$newValue New Value for the field
		 * @param	bool	$bypassPerms Default: false=use board permissions to allow update, true=bypass permissions
		 * @param	bool	$memberID Member ID where the custom profile field should be updated. If no ID is delivered, the currently logged in user will be updated.
		 * @return	bool	true on success, otherwise false
		 * @author			Jan Ecker <info@Jan-Ecker.eu>
		 * @author			Matthias Reuter (make it possible to update other member custom pfields) <public@pc-intern.com> http://pc-intern.com | http://straightvisions.com
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->updateCustomField(2,'new value);
		 * $ipbwi->member->updateCustomField(1,'new value,true,55);
		 * </code>
		 * @since			2.0
		 */
		public function updateCustomField($ID, $newValue, $bypassPerms = false, $memberID = false){
			if(empty($memberID)){
				$memberID = self::$ips->member['id'];
			}
			$fieldinfo = $this->listCustomFields($memberID);
			if($info = $fieldinfo['field_' . $ID]){
				if($info['pf_member_edit'] OR $bypassPerms){
					if($info['pf_type'] == 'drop'){
						$allowed = array();
						$i = explode ('|', $info['pf_content']);
						foreach($i as $j){
							$k = explode ('=', $j);
							$allowed[] = $k['0'];
						}
						if(!in_array($newValue, $allowed)){
							$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('cfInvalidValue'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
					}
					if($info['pf_not_null'] AND !$newValue){
						$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('cfMustFillIn'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
					self::$ips->DB->query('UPDATE ibf_pfields_content SET field_'.$ID.'="'.$newValue.'" WHERE member_id="'.$memberID.'"');
					return true;
				}else{
					$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('cfCantEdit'), $ID),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',sprintf($this->ipbwi->getLibLang('cfNotExist'), $ID),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Update the current member's signature
		 * @param	string	$newSig New signature text. HTML allowed as per board settings.
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Jan Ecker <info@Jan-Ecker.eu>
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->updateSig('[b]my sig[/b]');
		 * </code>
		 * @since			2.0
		 */
		public function updateSig($newSig){
			if(!$this->isLoggedIn()){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(strlen($newSig) > self::$ips->vars['max_sig_length']){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('sigTooLong'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(self::$ips->vars['sig_allow_ibc']){
				self::$ips->parser->parse_html		= self::$ips->vars['sig_allow_html'];
				self::$ips->parser->parse_bbcode	= self::$ips->vars['sig_allow_ibc'];
				self::$ips->parser->strip_quotes	= 1;
				self::$ips->parser->parse_nl2br		= 1;
				$newSig = self::$ips->parser->pre_db_parse(stripslashes($newSig));
				$newSig = self::$ips->parser->pre_display_parse($newSig);
			}
			self::$ips->DB->query('UPDATE ibf_member_extra SET signature="'.$this->ipbwi->makeSafe($newSig).'" WHERE id="'.self::$ips->member['id'].'"');
			return true;
		}
		/**
		 * @desc			Update current member's avatar.
		 * @param	string	Name of the input upload field which contains avatar file
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->updateAvatar(); // use standard upload field name ('avatar_new')
		 * $ipbwi->member->updateAvatar('input_field_name'); // set upload field name
		 * $ipbwi->member->updateAvatar(false,true); // delete the avatar
		 * </code>
		 * @since			2.01
		 */
		public function updateAvatar($fieldName='avatar_new',$deleteAvatar=false){
			if(!$this->isLoggedIn() && $this->ipbwi->getBoardVar('avatars_on') != 1){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$member = $this->info();
			// Remove Photo
			if($deleteAvatar != false){
				$location = explode(':',$member['avatar_location']);
				if($this->ipbwi->member->updateMember(array('avatar_type' => '', 'avatar_location' => '', 'avatar_size' => ''))){
					if($location[0] == 'upload'){
						unlink($this->ipbwi->getBoardVar('upload_dir').$location[1]);
					}
					$this->ipbwi->addSystemMessage('Success', $this->ipbwi->getLibLang('avatarSuccess'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return true;
				}else{
					$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('avatarError'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}elseif(isset($_FILES[$fieldName]['size']) && $_FILES[$fieldName]['size'] > 0 && ($_FILES[$fieldName]['size'] <= ($this->ipbwi->getBoardVar('avup_size_max')*1024)) && $deleteAvatar == false){
				/*
				 * @todo implement check of [avatar_ext] => gif,jpg,jpeg,png
				 */
				$file_ext = strtolower(substr($_FILES[$fieldName]['name'],strrpos($_FILES[$fieldName]['name'],'.'))); // exclude file extension of the name
				$avatarname = 'av-'.$member['id'].$file_ext; // define avatarname
				$target_location = $this->ipbwi->getBoardVar('upload_dir').$avatarname; // define target url
				list($width, $height, $type, $attr) = getimagesize($_FILES[$fieldName]['tmp_name']); // get avatar proberties
				$avatar_dims = explode('x',$this->ipbwi->getBoardVar('avatar_dims'));
				if($width <= $avatar_dims[0] && $height <= $avatar_dims[1]){
					if(move_uploaded_file($_FILES[$fieldName]['tmp_name'],$target_location)){ // move uploaded avatar to target
						$avatar_img_size = $width.'x'.$height; // merge avatarsize to IPB compatible format
						if($this->ipbwi->member->updateMember(array('avatar_type' => 'upload', 'avatar_location' => 'upload:'.$avatarname, 'avatar_size' => $avatar_img_size))){
							$this->ipbwi->addSystemMessage('Success', $this->ipbwi->getLibLang('avatarSuccess'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return true;
						}else{
							$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('avatarError'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
					}else{
						$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('avatarError'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
				}else{
					$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('avatarError'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Update current member's photograph.
		 * @return	bool	true on success, otherwise false
		 * @author			Jan Ecker <info@Jan-Ecker.eu>
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->updatePhoto(); // use standard upload field name ('upload_photo')
		 * $ipbwi->member->updatePhoto('photo_new'); // set upload field name
		 * $ipbwi->member->updatePhoto(false,true); // delete the photo
		 * </code>
		 * @since			2.0
		 */
		public function updatePhoto($fieldName=false,$deletePhoto=false){
			if(!$this->isLoggedIn()){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}

			// Remove Photo
			if(isset($_POST['delete_photo']) || $deletePhoto != false){
				if(isset($_POST['delete_photo'])){
					$_POST['delete_photo'] = 1;
				}elseif(isset($deletePhoto)){
					$_POST['delete_photo'] = 1;
				}else{
					$_POST['delete_photo'] = 0;
				}
				$deleted = self::$ips->usercp->lib_upload_photo();
				if($deleted['status'] == 'deleted'){
					return true;
				}else{
					return false;
				}
			}elseif(isset($_POST['upload_photo']) || isset($fieldName)){
				$_POST['delete_photo'] = 0;
				// check first for POST data
				if(isset($fieldName) && isset($_FILES[$fieldName])){
					$_FILES['upload_photo'] = $_FILES[$fieldName];
				}elseif(empty($_FILES['upload_photo'])){
					return false;
				}
				// Get system vars
				$info = $this->info();
				$max = explode(':', $info['g_photo_max_vars']);
				// check if file is empty
				if(isset($_FILES['upload_photo']['size']) && $_FILES['upload_photo']['size'] > 0){
					// check if file is too big
					if($_FILES['upload_photo']['size'] < $max['0']*1024){
						// check if file has right extension
						$ext = strtolower(substr($_FILES['upload_photo']['name'],strrpos($_FILES['upload_photo']['name'],'.')));
						$allowed_ext = explode(',',self::$ips->vars['photo_ext']);
						if(in_array(str_replace('.','',$ext),$allowed_ext)){
							$photo = self::$ips->usercp->lib_upload_photo();
							if($photo && self::$ips->DB->query('INSERT INTO ibf_profile_portal (pp_member_id,pp_main_photo,pp_main_width,pp_main_height,pp_thumb_photo,pp_thumb_width,pp_thumb_height) VALUES ("'.self::$ips->member['id'].'","'.$photo['final_location'].'","'.$photo['final_width'].'","'.$photo['final_height'].'","'.$photo['t_final_location'].'","'.$photo['t_final_width'].'","'.$photo['t_final_height'].'") ON DUPLICATE KEY UPDATE pp_main_photo="'.$photo['final_location'].'", pp_main_width="'.$photo['final_width'].'", pp_main_height="'.$photo['final_height'].'", pp_thumb_photo="'.$photo['t_final_location'].'", pp_thumb_width="'.$photo['t_final_width'].'", pp_thumb_height="'.$photo['t_final_height'].'"')){
							//if($photo && self::$ips->DB->query('UPDATE ibf_profile_portal SET pp_main_photo="'.$photo['final_location'].'", pp_main_width="'.$photo['final_width'].'", pp_main_height="'.$photo['final_height'].'", pp_thumb_photo="'.$photo['t_final_location'].'", pp_thumb_width="'.$photo['t_final_width'].'", pp_thumb_height="'.$photo['t_final_height'].'" WHERE pp_member_id="'.self::$ips->member['id'].'"')){
								return true;
							}else{
								$this->ipbwi->addSystemMessage('Error','Upload failed: Database Update failed.','Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
								return false;
							}
						}else{
							$this->ipbwi->addSystemMessage('Error','Upload failed: File-Extension is not allowed. Use one of the following: '.self::$ips->vars['photo_ext'],'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
							return false;
						}
					}else{
						$this->ipbwi->addSystemMessage('Error','Upload failed: File is too big. '.round($_FILES['upload_photo']['size']/1024,2).' KB uploaded and '.$max['0'].' KB allowed.','Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
						return false;
					}
				}else{
					//$this->ipbwi->addSystemMessage('Error','Upload failed: File has size of 0 Bytes','Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
			}
		}
		/**
		 * @desc			Gets the value of a custom profile field for a given member. If $userID is ommitted, the last known member id is used.
		 * @param	int		$fieldID Field ID (number) to retrieve.
		 * @param	int		$userID Member ID to read the custom profile field from.
		 * @return	string	Value of memberid's custom profile field field-id
		 * @author			Matthias Reuter
		 * @author			Jan Ecker <info@Jan-Ecker.eu>
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->customFieldValue(3,55);
		 * </code>
		 * @since			2.0
		 */
		public function customFieldValue($fieldID, $userID = false){
			$info = $this->info($userID);
			if(isset($info['field_' . $fieldID]) && $info['field_' . $fieldID]){
				self::$ips->DB->query('SELECT pf_content, pf_type FROM ibf_pfields_data WHERE pf_id="'.intval($fieldID).'"');
				if(self::$ips->DB->get_num_rows()){
					$field_info = self::$ips->DB->fetch_row();
					if($field_info['pf_type'] == 'drop')
					{
						$field = explode('|',$field_info['pf_content']);
						$element = array();
						foreach($field as $item){
							$temp = explode('=',$item);
							$temp = array($temp[0] => $temp[1]);
							$element = array_merge($element,$temp);
						}
						return $element[$info['field_'.$fieldID]];
					}
					return $info['field_'.$fieldID];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Get member's sig in BBCode
		 * @param	int		$userID Member ID to read the signature from.
		 * @return	string	Member Code in BBCode.
		 * @author			Matthias Reuter
		 * @author			Jan Ecker <info@Jan-Ecker.eu>
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->rawSig(55);
		 * </code>
		 * @since			2.0
		 */
		public function rawSig($userID = false){
			if(!$userID){
				$userID = self::$ips->member['id'];
			}
			if($info = $this->info($userID)){
				self::$ips->parser->parse_nl2br			= 1;
				self::$ips->parser->parse_smilies		= 0;
				self::$ips->parser->parsing_signature	= 1;
				self::$ips->parser->parse_html			= self::$ips->vars['sig_allow_html'];
				self::$ips->parser->parse_bbcode		= self::$ips->vars['sig_allow_ibc'];
				return self::$ips->parser->pre_edit_parse($info['signature']);
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns the number of new posts of the currently logged in member since its last visit.
		 * @return	int		Number of posts since last visit
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			CTiga <crouchintiga@comcast.net>
		 * @sample
		 * <code>
		 * $ipbwi->member->numNewPosts();
		 * </code>
		 * @since			2.0
		 */
		public function numNewPosts(){
			if(!$this->isLoggedIn()){
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('membersOnly'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			self::$ips->DB->query('SELECT COUNT(pid) AS new FROM ibf_posts WHERE post_date > "'.self::$ips->member['last_visit'].'"');
			if($posts = self::$ips->DB->fetch_row()){
				return $posts['new'];
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns the amount of pips a member has.
		 * @param	int		$ID Member's ID
		 * @return	int		Member Pips Count
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->pips(55);
		 * </code>
		 * @since			2.0
		 */
		public function pips($ID = false){
			if(empty($ID)){
				$ID = self::$ips->member['id'];
			}
			if($info = $this->info($ID)){
				// Grab Pips
				self::$ips->DB->query('SELECT * FROM ibf_titles ORDER BY pips ASC');
				$pips = '0';
				// Loop through pip numbers checking which is good
				while($row = self::$ips->DB->fetch_row()){
					if(isset($info['posts']) && $row['posts'] <= $info['posts']){
						$pips = $row['pips'];
					}
				}
				return $pips;
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('badMemID'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Returns a member's icon in HTML
		 * @param	int		$ID Member's ID
		 * @return	string	HTML for member's icon
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->icon(55);
		 * </code>
		 * @since			2.0
		 */
		public function icon($userID = false){
			if(!$userID){
				$userID = self::$ips->member['id'];
			}
			if($info = $this->info($userID)){
				$skinInfo = $this->ipbwi->skin->info($this->ipbwi->skin->id());
				if($info['g_icon']){
					// Use Group Icon
					if(substr($info['g_icon'],0,7) == 'http://')
					{
					$info['g_icon'] = '<img src="' . $info['g_icon'] . '" alt="'.$this->ipbwi->getLibLang('groupIcon').'" />';
					$skinInfo = $this->ipbwi->skin->info($this->ipbwi->skin->id());
					$skinInfo['set_image_dir'] = $skinInfo['set_image_dir'] ? $skinInfo['set_image_dir'] : '1';
					$info['g_icon'] = str_replace("<#IMG_DIR#>",$skinInfo['set_image_dir'],$info['g_icon']);
					return $info['g_icon'];
					}
					else
					{
					$skinInfo['set_image_dir'] = $skinInfo['set_image_dir'] ? $skinInfo['set_image_dir'] : '1';
					$url = '<img src="'.$this->ipbwi->getBoardVar('url').$info['g_icon'].'" alt="'.$this->lang['sdk_groupIcon'].'" />';
					$url = str_replace('<#IMG_DIR#>',$skinInfo['set_image_dir'],$url);
					return $url;
					}
				}else{
					// Use Pips
					$pips = $this->pips($userID);
					$pipsc = '';
					while($pips > 0){
						$skinInfo['set_image_dir'] = $skinInfo['set_image_dir'] ? $skinInfo['set_image_dir'] : '1';
						$pipsc .= '<img src="'.$this->ipbwi->getBoardVar('url').'style_images/'.$skinInfo['set_image_dir'].'/pip.gif" alt="*" />';
						$pips = $pips - '1';
					}
					return $pipsc;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('badMemID'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Login a user.
		 * @param	string	$userName Member's Username
		 * @param	string	$password Member's Password
		 * @param	integer	$cookie Default: true=Use cookie to save login session, false=no cookies
		 * @param	integer	$anon Default: false=Keep user anonymous on forums, true=keep anon.
		 * @param	integer	$sticky Default: true='Remember Me' cookie, false=auto log off when session expires
		 * @return	bool	true on success, otherwise false
		 * @author			Jan Ecker <info@Jan-Ecker.eu>
		 * @author			Matthias Reuter
		 * @author			DigitalisAkujin
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @author			CTiga <crouchintiga@comcast.net>
		 * @sample
		 * <code>
		 * $ipbwi->member->login(55,'password');
		 * $ipbwi->member->login(55,'password',true,false,true);
		 * </code>
		 * <b>Important</b><br>
		 * Cookie Settings of your Board<br>
		 * These Settings should be choosed to make a login on your website possible:<br>
		 * Cookie Domain: .your-domain.com<br>
		 * Cookie Name Prefix: {blank}<br>
		 * Cookie Path: {blank}<br>
		 * If you want to get the login work on subdomains, you have to turn off "Create a stronghold auto-log in cookie" in your Cookie-Settings of your Board.<br>
		 * This function sends http-headers, so you have to call it before any output is sent to the browser.
		 * @since			2.0
		 */
		public function login($userName, $password, $cookie = true, $anon = false, $sticky = true){
			$userName = self::$ips->txt_stripslashes($userName);
			$userName = preg_replace('/&#([0-9]+);/', '-', $userName);
			$userName = $this->ipbwi->makeSafe($userName);
			$sticky = $sticky ? 1 : 0;
			if(!$userName OR !$password){
				$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('loginNoFields'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			if(strlen($userName) > 32 OR strlen($password) > 32){
				$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('loginLength'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
			$userName = strtolower(str_replace('|', '|', $userName));
			// NAME LOG IN
			self::$ips->DB->cache_add_query('login_getmember', array('username' => $userName));
			self::$ips->DB->cache_exec_query();
			if(self::$ips->member = self::$ips->DB->fetch_row()){
				// Got a username?
				if(!self::$ips->member['id']){
					$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('loginMemberID'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				self::$ips->converge->converge_load_member(self::$ips->member['email']);
				if(!self::$ips->converge->member['converge_id']){
					$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('loginWrongPass'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// Check password...
				if(self::$ips->converge->converge_authenticate_member(md5(self::$ips->parse_clean_value($password))) != true ){
					$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('loginWrongPass'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
					return false;
				}
				// Still here... Means its Okely Doke
				if(isset($_COOKIE['session_id'])){
					$sid = $_COOKIE['session_id'];
				}else{
					$sid = md5(uniqid(microtime()));
				}
				if($cookie){
					if(ipbwi_COOKIE_DOMAIN != ''){
						self::$ips->vars['cookie_domain'] = ipbwi_COOKIE_DOMAIN;
					}
					$expire_date = time()+604800;
					self::$ips->my_setcookie('member_id', self::$ips->member['id'], $sticky, 5);
					self::$ips->my_setcookie('pass_hash', self::$ips->member['member_login_key'], $sticky, 5);
					self::$ips->my_setcookie('session_id', $sid, $sticky, 5);
					// Set 'Cookie Expire' - one week the cookie will be saved.
				}else{
					self::$ips->my_setcookie('session_id', $sid, -1);
					// Set 'Cookie Expire' - this cookie will be saved temporaly and deleted after browser exit.
					$expire_date = time();
				}
				// Create Session
				$browser = substr($_SERVER['HTTP_USER_AGENT'], 0, 64);
				$ip = substr($_SERVER['REMOTE_ADDR'], 0, 16);
				$dbString = self::$ips->DB->compile_db_insert_string(
					array(
						'id' => $sid,
						'member_name' => self::$ips->member['name'],
						'member_id' => self::$ips->member['id'],
						'running_time' => time(),
						'member_group' => self::$ips->member['mgroup'],
						'ip_address' => $ip,
						'browser' => $browser,
						'login_type' => $anon ? '1' : '0'
					)
				);
				self::$ips->DB->query('DELETE FROM ibf_sessions WHERE id = "'.$sid.'"');
				self::$ips->DB->query('INSERT INTO ibf_sessions ('.$dbString['FIELD_NAMES'].') VALUES ('.$dbString['FIELD_VALUES'].')');
				// Set 'Privacy Status'
				self::$ips->DB->query('UPDATE ibf_members SET login_anonymous="'.intval($anon).'&1",member_login_key_expire="'.$expire_date.'" WHERE id="'.self::$ips->member['id'].'"');
				self::$ips->loggedin	= true;
				$this->loggedIn			= true;
				self::$ips->member		= $this->info(self::$ips->member['id']);

				// Update Session
				self::updateSession();

				return self::$ips->member;
			}else{
				$this->ipbwi->addSystemMessage('Error', $this->ipbwi->getLibLang('loginNoMember'), 'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			Logout a user.
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Jan Ecker <info@jan-ecker.eu>
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->logout();
		 * </code>
		 * @since			2.0
		 */
		public function logout(){
			if(ipbwi_COOKIE_DOMAIN != ''){
				self::$ips->vars['cookie_domain'] = ipbwi_COOKIE_DOMAIN;
			}
			global $HTTP_COOKIE_VARS;
			// Are we even logged in?
			if(!$this->isLoggedIn()){
				return true;
			}
			// Update the DB
			self::$ips->DB->query('DELETE FROM ibf_sessions WHERE member_id = "'.self::$ips->member['id'].'"');
			@list($privacy, $loggedin) = explode('&', self::$ips->member['login_anonymous']);
			self::$ips->DB->query('UPDATE ibf_members SET login_anonymous = "'.$privacy.'&0", last_visit = "'.time().'", last_activity = "'.time().'" WHERE id = "'.self::$ips->member['id'].'"');
			// Set some cookies
			self::$ips->my_setcookie('member_id', '0');
			self::$ips->my_setcookie('pass_hash', '0');
			self::$ips->my_setcookie('anonlogin', '-1');
			if(is_array($HTTP_COOKIE_VARS)){
	 			foreach($HTTP_COOKIE_VARS as $cookie => $value){
	 				if(preg_match('/^('.self::$ips->vars['cookie_id'].'ibforum.*$)/i', $cookie, $match)){
	 					self::$ips->my_setcookie(str_replace(self::$ips->vars['cookie_id'], '', $match[0]) , '-', -1 );
	 				}
	 			}
	 		}
	 		$this->loggedIn = false;
			return true;
		}
		/**
		 * @desc			Lists the board's members.
		 * @param	array	$options Overwrites default behaviour of SQL query.
		 * The following options can be used to overwrite the default query results.
		 * <br>'order' default: 'asc'
		 * <br>'start' default: '0' start with first record
		 * <br>'limit' default: '30' no. of members per page
		 * <br>'orderby' default: 'name' other keys see below
		 * <br>'group' default: '*' all groups. You can specifiy a number or list of numbers
		 *
		 * Sort keys: any field from ibf_members or ibf_groups.
		 * To avoid trouble ordering by a field 'xxx', use <b>m.XXX</b> or <b>g.XXX</b> as
		 * the full qualified fieldname, not just 'xxx'.
		 * @return	array	Members
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->getList();
		 * $ipbwi->member->getList(array('order' => 'asc', 'start' => '0', 'limit' => '30', 'orderby' => 'name', 'group' => '*'));
		 * </code>
		 * @since			2.0
		 */
		public function getList($options = array('order' => 'asc', 'start' => '0', 'limit' => '30', 'orderby' => 'name', 'group' => '*')) {
			// Ordering
			$orders = array('id', 'name', 'posts', 'joined');
			if(!in_array($options['orderby'], $orders)){
				$options['orderby'] = 'name';
			}
			// Order By
			$options['order'] = ($options['order'] == 'desc') ? 'DESC' : 'ASC';
			// Start and Limit
			$filter = 'LIMIT '.intval($options['start']).','.intval($options['limit']);
			// Grouping
			$where = '';
			if(is_array($options['group']) AND $options['group'] != '*'){
				foreach($options['group'] as $i){
					$i = (int)$i;
					if($i > 0){
						if($where){
							$where .= 'OR mgroup="'.$i.'" ';
						}else{
							$where .= 'mgroup="'.$i.'" ';
						}
					}
				}
			}
			if($where){
				$where = 'WHERE m.id != "0" AND ('.$where.')';
			}else{
				$where = 'WHERE m.id != "0"';
			}
			self::$ips->DB->query('SELECT m.*, g.*, cf.* FROM ibf_members m LEFT JOIN ibf_groups g ON (m.mgroup=g.g_id) LEFT JOIN ibf_pfields_content cf ON (cf.member_id=m.id) '.$where.' ORDER BY '.$options['orderby'].' '.$options['order'].' '.$filter);
			$return = array();
			while($row = self::$ips->DB->fetch_row()){
				$return[$row['id']] = $row;
			}
			return $return;
		}
		/**
		 * @desc			Get an array of online members.
		 * @param	bool	$detailed if true, function returns multi-dimensional array containing the result of get_advinfo() for each member. Default false - simple list.
		 * @param	bool	$formatted if true, function will return an html list (string) of display names, each linked to each member's personal profile. Default false - returns array.
		 * @param	bool	$show_anon if true, function will ignore logged-in member's anonymity choice. Default false - normal board action.
		 * @param	string	$order_by choose what to order the results by - choose from 'member_name', 'member_id', 'running_time', 'location'. Default "running_time".
		 * @param	string	$order choose what order to order the results in. Options are ascending; 'ASC', or descending; 'DESC'. Default 'DESC'.
		 * @param	string	$separator - if $formatted set to true, this string will go between each linked display name. Default ', '.
		 * @return	array	online member list
		 * @author			Matthias Reuter
		 * @author			Jan Ecker <info@jan-ecker.eu>
		 * @author			Andrew Beveridge <andrewthecoder@googlemail.com>
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->member->listOnlineMembers();
		 * $ipbwi->member->listOnlineMembers(true,true,true,'member_name','ASC',' - ');
		 * </code>
		 * @since			2.0
		 */
		public function listOnlineMembers($detailed = false, $formatted = false, $show_anon = false, $order_by = 'running_time', $order = 'DESC', $separator = ', '){
			// Grab the cut-off length in minutes from the board settings
			$cutoff = self::$ips->vars['au_cutoff'] ? self::$ips->vars['au_cutoff'] : '15';
			// Create a timestamp for the current time, and subtract the cut-off length to get a timestamp in the past
			$timecutoff = time()-($cutoff * 60);
			if($formatted){
				// the $formatted param is true, so let's return an HTML list of display name links, separated by $separator
				// if this function has already been run and has saved a cache, return the cached value from database for speed
				if($online = $this->ipbwi->cache->get('listOnlineMembers', 'formatted') && isset($online) && is_array($online) && count($online) > 0){
					// For each key in the $online array we just read from the database, set the value to the html formatted display name link
					foreach($online as $key => $value){
						// Grab advanced info for the member so we have the display name, prefix and suffix
						$member = $this->info($value);
						// Create the html-formatted string
						$link = '<a href="'.$this->ipbwi->getBoardVar('url').'index.php?showuser='.$value.'">'.$member['prefix'].$member['members_display_name'].$member['suffix'].'</a>';
						$online[$key] = $link;
					}
					// Now we have an array full of html links... But that isn't very helpful to a PHP newbie. Lets just return an html string. Implode the array with $separator
					$online = implode($separator,$online);
					return $online;
				}
				// if we are happy to ignore logged-in members' requests to be anonymous, we need a slightly different database query.
				if($show_anon){
					self::$ips->DB->query('SELECT member_id FROM ibf_sessions s WHERE s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
				}else{
					// ok so this is the normal database query which should return the member IDs of all logged-in members. It does not return guests as they have no member ID :)
					self::$ips->DB->query('SELECT member_id FROM ibf_sessions s WHERE s.login_type != "1" AND s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
				}
				// For each result from the MySQL query, add the member's ID to the $options array with the key and value both equal to the member's ID
				while($row = self::$ips->DB->fetch_row()){
					$ID = $row['member_id'];
					$online[$ID] = $ID;
				}
				// We didn't do all that just to have to do it again next time. Cache the result to the database for speed next time.
				$this->ipbwi->cache->save('listOnlineMembers', 'formatted', $online);
				// For each key in the $online array we just cached to the database, set the value to the html formatted display name link
				if(isset($online) && is_array($online) && count($online) > 0){
					foreach($online as $key => $value){
						// Grab advanced info for the member so we have the display name, prefix and suffix
						$member = $this->info($value);
						// Create the html-formatted string
						$link = '<a href="'.$this->ipbwi->getBoardVar('url').'index.php?showuser='.$value.'">'.$member['prefix'].$member['members_display_name'].$member['suffix'].'</a>';
						$online[$key] = $link;
					}
					// Now we have an array full of html links... But that isn't very helpful to a PHP newbie. Lets just return an html string. Implode the array with $separator
					$online = implode($separator,$online);
					// Finally, return the array
					return $online;
				} else{
					return false;
				}
			}
			// if the $detailed param is true, return extra info :)
			if($detailed){
				// if this function has already been run and has saved a cache, return the cached value from database for speed
				if($online = $this->ipbwi->cache->get('listOnlineMembers', 'nodetail') && isset($online) && is_array($online) && count($online) > 0){
					// For each key in the $online array we just read from the database, set the value to the result of get_advinfo(value)
					foreach($online as $key => $value){
						$online[$key] = $this->info($value);
					}
					// Return the array which now has extra info :)
					return $online;
				}
				// if we are happy to ignore logged-in members' requests to be anonymous, we need a slightly different database query.
				if($show_anon){
					self::$ips->DB->query('SELECT member_id FROM ibf_sessions s WHERE s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
				}else{
					// ok so this is the normal database query which should return the member IDs of all logged-in members. It does not return guests as they have no member ID :)
					self::$ips->DB->query('SELECT member_id FROM ibf_sessions s WHERE s.login_type != "1" AND s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
				}
				// For each result from the MySQL query, add the member's ID to the $options array with the key and value both equal to the member's ID
				while($row = self::$ips->DB->fetch_row()){
					$ID = $row['member_id'];
					$online[$ID] = $ID;
				}
				// We didn't do all that just to have to do it again next time. Cache the result to the database for speed next time.
				$this->ipbwi->cache->save('listOnlineMembers', 'nodetail', $online);
				// For each key in the $online array we just cached to the database, set the value to the result of get_advinfo(value)
				if(isset($online) && is_array($online) && count($online) > 0){
					foreach($online as $key => $value){
						$online[$key] = $this->info($value);
					}
					// Finally, return the array
					return $online;
				}else{
					 return false;
				}
			}
			// neither $detailed or $formatted are true, so return a simple list
			// if this function has already been run and has saved a cache, return the cached value from database for speed
			if($online = $this->ipbwi->cache->get('listOnlineMembers', 'simple')){
				return $online;
			}
			// if we are happy to ignore logged-in members' requests to be anonymous, we need a slightly different database query.
			if($show_anon){
				self::$ips->DB->query('SELECT member_id FROM ibf_sessions s WHERE s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
			}else{
				// ok so this is the normal database query which should return the member IDs of all logged-in members. It does not return guests as they have no member ID :)
				self::$ips->DB->query('SELECT member_id FROM ibf_sessions s WHERE s.login_type != "1" AND s.member_id != "0" AND s.running_time > "'.$timecutoff.'" ORDER BY '.$order_by.' '.$order);
			}
			// For each result from the MySQL query, add the member's ID to the $options array with the key and value both equal to the member's ID
			while($row = self::$ips->DB->fetch_row()){
				$ID = $row['member_id'];
				$online[$ID] = $ID;
			}
			// We didn't do all that just to have to do it again next time. Cache the result to the database for speed next time.
			$this->ipbwi->cache->save('listOnlineMembers', 'simple', $online);
			// Finally, return the array
			return $online;
		}
		/**
		 * @desc			Get an array of random members.
		 * @param	int		$limit How many Member should be returned? default: 5
		 * @param	string	$where For advanced requests: SQL-Statement to filter the output
		 * @return	array	Random Members
		 * @author			Jan Ecker <info@jan-ecker.eu>
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->listRandomMembers();
		 * $ipbwi->member->listRandomMembers(5,'posts!=0 AND me.avatar_location != "" AND me.avatar_location != "noavatar"');
		 * </code>
		 * @since			2.0
		 */
		public function listRandomMembers($limit = 5,$where = false){
			if($where){
				$where = 'WHERE '.$where;
			}
			self::$ips->DB->query('SELECT * FROM ibf_members m LEFT JOIN ibf_member_extra me ON (me.id=m.id)'.$where.' ORDER BY RAND() LIMIT '.intval($limit));
			$random = array();
			while($row = self::$ips->DB->fetch_row()){
				$random[$row['id']] = $row;
			}
			return $random;
		}
		/**
		 * @desc			Removes a friend
		 * @param	int		$userID Member ID to be deleted
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->removeFriend(55);
		 * </code>
		 * @since			2.0
		 */
		public function removeFriend($userID){
			if($this->isLoggedIn()){
				self::$ips->DB->query('DELETE FROM ibf_profile_friends WHERE friends_friend_id="'.intval($userID).'" AND friends_member_id="'.self::$ips->member['id'].'"');
				if(self::$ips->DB->get_affected_rows()){
					// recache
					self::$ips->pack_and_update_member_cache(self::$ips->member['id'], array('friends' => $this->friendsList()));
					self::$ips->pack_and_update_member_cache(intval($userID), array('friends' => $this->friendsList(false,$userID)));
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Adds a friend
		 * @param	int		$userID Member ID to be added
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->addFriend(55);
		 * </code>
		 * @since			2.0
		 */
		public function addFriend($userID){
			if($this->isLoggedIn()){
				// Check user exists
				if(!$userID OR !$this->info(intval($userID))){
					return false;
				}
				// o_O. Firstly check if there is already an entry.
				self::$ips->DB->query('SELECT * FROM ibf_profile_friends WHERE friends_friend_id="'.intval($userID).'"AND friends_member_id="'.self::$ips->member['id'].'"');
				if($row = self::$ips->DB->fetch_row()){
					return true;
				}else{
					// We can just add an entry because theres nothing there.
					$friend = $this->info($userID);
					// support for moderate_friends-field have to be added (including sending confirmation message)
					//if($friend['pp_setting_moderate_friends']) $friends_approved = 0; else $friends_approved = 1;
					$friends_approved = 1;
					if(self::$ips->DB->query('INSERT INTO ibf_profile_friends VALUES ("", "'.self::$ips->member['id'].'","'.intval($userID).'","'.$friends_approved.'", "'.time().'")')){
						// recache
						self::$ips->pack_and_update_member_cache(self::$ips->member['id'], array('friends' => $this->friendsList()));
						self::$ips->pack_and_update_member_cache(intval($userID), array('friends' => $this->friendsList(false,$userID)));
					}
					return true;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns information on the current user's contacts.
		 * @param	bool	$userID Member-ID to get friends of this member. If not set, friends of currently logged in member will be listed.
		 * @param	bool	$details Detailed Member Information, default: false
		 * @param	bool	$unapproved List unapproved friends, default: false
		 * @return	array	Friends Informations
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->member->friendsList();
		 * $ipbwi->member->friendsList(55,true,true);
		 * </code>
		 * @since			2.0
		 */
		public function friendsList($userID = false,$details = false,$unapproved = false){
			// check for memberid
			if(is_string($userID)) {
				$member = intval($userID);
			}elseif($this->isLoggedIn()){
				$member = self::$ips->member['id'];
			}else{
				return false;
			}
			// check if unapproved
			if(empty($unapproved)){
				$approved = ' AND friends_approved="1"';
			}
			self::$ips->DB->query('SELECT * FROM ibf_profile_friends WHERE friends_member_id="'.$member.'"'.$approved);
			$friends = array();
			while($row = self::$ips->DB->fetch_row()){
				$friends[$row['friends_id']] = $row;
			}
			// check for details
			if($details === true){
				foreach($friends as $friend){
					$friends[$friend['friends_id']]['details'] = $this->info($friend['friends_friend_id']);
				}
			}
			return $friends;
		}
	}
?>