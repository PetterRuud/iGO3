<?php
	/**
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @version			$LastChangedDate: 2008-10-31 23:53:28 +0000 (Fr, 31 Okt 2008) $
	 * @package			skin
	 * @copyright		2007-2009 IPBWI development team
	 * @link			http://ipbwi.com/examples/skin.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */
	class ipbwi_skin extends ipbwi {
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
		 * @desc			Returns the Skin ID of the skin used by a member.
		 * @param	int		$memberID Member ID. If Member ID is ommitted, the current User will be used.
		 * @return	int		Skin ID or false on failure
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->skin->id($memberID);
		 * </code>
		 * @since			2.0
		 */
		public function id($memberID = false){
			$info = $this->ipbwi->member->info($memberID);
			if(isset($info['skin'])){
				return $info['skin'];
			}else{
				self::$ips->DB->query('SELECT set_skin_set_id FROM ibf_skin_sets WHERE set_default="1"');
				if($row = self::$ips->DB->fetch_row()){
					return $row['set_skin_set_id'];
				}else{
					return false;
				}
			}
		}
		/**
		 * @desc			Gets information on a skin.
		 * @param	int		$skinID ID of the Skin
		 * @return	array	Information on Skin or false on failure
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Ripper
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->skin->info($skinID);
		 * </code>
		 * @since			2.0
		 */
		public function info($skinID){
			// Adapted from the original function submitted by ripper
			if($skinID >= 0){ // If they've specified a skin
				self::$ips->DB->query('SELECT * FROM ibf_skin_sets WHERE set_skin_set_id="'.$skinID.'"');
				if($row = self::$ips->DB->fetch_row()){
					return $row;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		/**
		 * @desc			Pulls and displays CSS from forums depending on user's skin.
		 * @param	bool	$return Whether to return CSS instead of sending it to the browser. Default: false
		 * @param	bool	$addTag Whether to wrap the CSS with the html style-tag. Default: true
		 * @return	string	The Style sheet if $return was set, else true
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Scyth <scyth@wewub.com>
		 * @sample
		 * <code>
		 * $ipbwi->skin->css();
		 * $ipbwi->skin->css(true,false);
		 * </code>
		 * @since			2.0
		 */
		public function css($return = false, $addTag = true){
			$skin = $this->info($this->id()); // there we have the $skin var now..
			$getcss = $skin['set_skin_set_id']; // heh css id please
			self::$ips->DB->query('SELECT set_skin_set_id FROM ibf_skin_sets WHERE set_default = 1');
			$default = self::$ips->DB->fetch_row();
			if($getcss == '' && isset($default['set_skin_set_id'])){ // what if its 0 (guest etc)
				$getcss = $default['set_skin_set_id']; // make it the default skin - change 13 to match
			}elseif($getcss == ''){
				return false;
			}
			// now we have the table.. but now what?
			// apparently, we have to grab the cached css, as the normal field tends to disappear
			self::$ips->DB->query('SELECT set_cache_css FROM ibf_skin_sets WHERE set_skin_set_id = '.$getcss);
			$css = self::$ips->DB->fetch_row();
			$css = $css['set_cache_css'];
			// convert <#IMG_DIR#>
			$css = str_replace('<#IMG_DIR#>', $skin['set_image_dir'], $css);
			// convert to lead to forums
			$css = str_replace('style_images', $this->ipbwi->getBoardVar('url').'style_images', $css);
			// and here are the awesome style tags (used for later)
			// with an ID to use client side scripting
			if($addTag){
				$style = '<style type="text/css" id="css_'.$getcss.'">'.$css.'</style>';
			}else{
				$style = $css;
			}
			if($return){
				return $style;
			}else{
				echo $style;
				return true;
			}
		}
		/**
		 * @desc			Grabs the IDs of all the avaliable skins.
		 * @return	array	Skin IDs
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->skin->getList();
		 * </code>
		 * @since			2.0
		 */
		public function getList(){
			// Grab all skins which aren't hidden
			self::$ips->DB->query('SELECT set_skin_set_id FROM ibf_skin_sets WHERE set_hidden="0"');
			$skins = array();
			while($row = self::$ips->DB->fetch_row()){
				$skins[] = $row['set_skin_set_id'];
			}
			return $skins;
		}
		/**
		 * @desc			Changes the current user's skin.
		 * @param	int		$skinID Skin ID
		 * @param	int		$memberID Member ID. If Member ID is ommitted, the current User will be used.
		 * @return	bool	true on success, otherwise false
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->skin->set();
		 * </code>
		 * @since			2.0
		 */
		public function set($skinID,$memberID=false){
			// Check it exists
			if($this->info($skinID)){
				// Grab current member id unless specified
				if(empty($memberID)){
					$memberID = self::$ips->member['id'];
				}
				if($this->ipbwi->member->updateMember(array('skin' => $skinID), $memberID)){
					return true;
				}else{
					return false;
				}
			}else{
				$this->ipbwi->addSystemMessage('Error',$this->ipbwi->getLibLang('skinNotExist'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
	}
?>