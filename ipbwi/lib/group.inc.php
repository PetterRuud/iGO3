<?php
	/**
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @version			$LastChangedDate: 2009-08-26 19:19:41 +0200 (Mi, 26 Aug 2009) $
	 * @package			group
	 * @copyright		2007-2009 IPBWI development team
	 * @link			http://ipbwi.com/examples/group.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */
	class ipbwi_group extends ipbwi {
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
		 * @desc			Returns information on a group.
		 * @param	int		$group Group ID. If $group is ommited, the last known group (of the last member) is used.
		 * @return	array	Group Information
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			CTiga <crouchintiga@comcast.net>
		 * @sample
		 * <code>
		 * $ipbwi->group->info(5);
		 * </code>
		 * @since			2.0
		 */
		public function info($group=false){
			if(!$group){
				// No Group? Return current group info
				$group = self::$ips->member['mgroup'];
			}
			// Check for cache - if exists don't bother getting it again
			if($cache = $this->ipbwi->cache->get('groupInfo', $group)){
				return $cache;
			}else{
				// Return group info if group given
				self::$ips->DB->query('SELECT g.* FROM ibf_groups g WHERE g_id="'.intval($group).'"');
				if(self::$ips->DB->get_num_rows()){
					$info = self::$ips->DB->fetch_row();
					$this->ipbwi->cache->save('groupInfo', $group, $info);
					return $info;
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Changes Member group to delivered group-id.
		 * @param	int		$group Group ID
		 * @param	int		$member Member ID. If no Member-ID is delivered, the currently logged in member will moved.
		 * @param	array	$extra secondary Group-IDs
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @sample
		 * <code>
		 * $ipbwi->group->change(5);
		 * $ipbwi->group->change(7,12,array(1,2,3,4));
		 * </code>
		 * @since			2.0
		 */
		public function change($group,$member=false,$extra=false){
			if(!$member){
				$member = self::$ips->member['id'];
			}
			if($extra){
				$sql_extra = ', SET mgroup_others="'.implode(',',$extra).'"';
			}
			if(self::$ips->DB->query('UPDATE ibf_members SET mgroup="'.$group.'"'.$sql_extra.' WHERE id="'.intval($member).'"')){
				return true;
			}else{
				return false;
			}
		}
		/**
		 * @desc			Returns whether a member is in the specified group(s).
		 * @param	int		$group Group ID or array of groups-ids separated with comma: 2,5,7
		 * @param	int		$member Member ID to find
		 * @param	bool	$extra Include secondary groups to test against?
		 * @return	mixed	Whether member is in group(s)
		 * @author			Matthias Reuter
		 * @author			Pita
		 * @author			Cow
		 * @author			DigitalisAkujin
		 * @sample
		 * <code>
		 * $ipbwi->group->isInGroup(5);
		 * $ipbwi->group->isInGroup(7,12,true);
		 * </code>
		 * @since			2.0
		 */
		function isInGroup($group, $member = false, $extra = true) {
			if (!is_array($group)) $group = explode(',', $group);
			settype($group, 'array');
			if ($member) {
				self::$ips->DB->query('SELECT `mgroup`,`mgroup_others` FROM ibf_members WHERE id="'.$member.'"');
				if($row = self::$ips->DB->fetch_row()){
					if(in_array($row['mgroup'], $group)){
						return true;
					}
					if($extra){
						$others = explode(',',$row['mgroup_others']);
						foreach($others as $other){
							if(in_array($other,$group)){
								return true;
							}
						}
					}
				}
				return false;
			}else{
				if(in_array(self::$ips->member['mgroup'], $group)){
					return true;
				}else{
					// START CHANGE
					$other = explode(',',self::$ips->member['mgroup_others']);
					if(is_array($other)) {
						foreach($other as $v) {
							if(in_array($v, $group)) {
								return true;
							}
						}
					}
					// END CHANGE
					return false;
				}
			}
		}
	}
?>