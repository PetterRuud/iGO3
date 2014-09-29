<?php
	/**
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @version			$LastChangedDate: 2009-08-26 19:19:41 +0200 (Mi, 26 Aug 2009) $
	 * @package			bbcode
	 * @copyright		2007-2009 IPBWI development team
	 * @link			http://ipbwi.com/examples/bbcode.php
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */
	class ipbwi_bbcode extends ipbwi {
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
		 * @desc			converts BBCode to HTML using IPB's native parser.
		 * @param	string	$input bbcode-formatted string
		 * @param	bool	$smilies set to true to parse smilies, otherwise false
		 * @return	string	HTML version of input
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->bbcode->bbcode2html('[b]test[/b]',true);
		 * </code>
		 * @since			2.0
		 */
		public function bbcode2html($input, $smilies = true){
			self::$ips->parser->parse_smilies = $smilies;
			self::$ips->parser->parse_html = 0;
			self::$ips->parser->parse_bbcode = 1;
			self::$ips->parser->strip_quotes = 1;
			self::$ips->parser->parse_nl2br = 1;
			$input = @self::$ips->parser->pre_db_parse($input);
			// Leave this here in case things go pear-shaped...
			$input = self::$ips->parser->pre_display_parse($input);
			if($smilies){
				$input	= $this->ipbwi->properXHTML($input);
			}
			return $input;
		}
		/**
		 * @desc			converts HTML to BBCode using IPB's native parser.
		 * @param	string	$input html-formatted string
		 * @return	string	BBCode version of input
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @sample
		 * <code>
		 * $ipbwi->bbcode->html2bbcode('<b>test</b>');
		 * </code>
		 * @since			2.0
		 */
		public function html2bbcode($input){
			self::$ips->parser->parse_smilies	= 1;
			self::$ips->parser->parse_html		= 0;
			self::$ips->parser->parse_bbcode	= 1;
			self::$ips->parser->strip_quotes	= 1;
			self::$ips->parser->parse_nl2br		= 1;
			$input = self::$ips->parser->pre_edit_parse($input);
			return $input;
		}
		/**
		 * @desc			List emoticons, optional limit the result to clickable emoticons only.
		 * @param	bool	$clickable set to true to list clickable emoticons only, otherwise set to false
		 * @return	array	Assoc array with Emoticons, keys 'typed', 'image'
		 * @author			Matthias Reuter
		 * @author			Pita <peter@randomnity.com>
		 * @author			Cow <khlo@global-centre.com>
		 * @author			Foxrer
		 * @sample
		 * <code>
		 * $ipbwi->bbcode->listEmoticons(true);
		 * </code>
		 * @since			2.0
		 */
		public function listEmoticons($clickable = false){
			if($clickable){
				self::$ips->DB->query('SELECT typed, image FROM ibf_emoticons WHERE clickable="1"');
			}else{
				self::$ips->DB->query('SELECT typed, image FROM ibf_emoticons');
			}
			$emos = array();
			while($row = self::$ips->DB->fetch_row()){
				$emos[$row['typed']] = $row['image'];
			}
			return $emos;
		}
	}
?>