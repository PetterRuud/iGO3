<?php
	/**
	 * @desc			This file loads all IPBWI functions. Include this file to your
	 * 					php-scripts and load the ipbwi-class to use the functions.
	 * @author			Matthias Reuter ($LastChangedBy: matthias $)
	 * @version			$LastChangedDate: 2009-08-26 19:49:26 +0200 (Mi, 26 Aug 2009) $
	 * @package			IPBWI
	 * @copyright		2007-2009 IPBWI development team
	 * @link			http://ipbwi.com
	 * @since			2.0
	 * @license			http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
	 */
	// load config file
	require_once('config.inc.php');
	// check if PHP version is 5 or higher
	if(version_compare(PHP_VERSION,'5.0.0','<')){
		die('<p>ERROR: You need PHP 5 or higher to use IPBWI. Your current version is '.PHP_VERSION.'</p>');
	}
	// check if board path is set
	if(!defined('ipbwi_BOARD_PATH') || ipbwi_BOARD_PATH == ''){
		die('<p>ERROR: You have to define a board\'s path in your IPBWI config file.</p>');
	}
	// check if ipbwi path is set
	if(!defined('ipbwi_ROOT_PATH') || ipbwi_ROOT_PATH == ''){
		die('<p>ERROR: You have to define the root path of your IPBWI installation in your IPBWI config file.</p>');
	}
	class ipbwi {
		const 				VERSION			= '2.07';
		const 				TITLE			= 'IPBWI';
		const 				PROJECT_LEADER	= 'Matthias Reuter';
		const 				DEV_TEAM		= 'Matthias Reuter, Jan Ecker';
		const 				WEBSITE			= 'http://ipbwi.com/';
		const 				DOCS			= 'http://docs.ipbwi.com/';
		private static		$lang			= ipbwi_LANG;
		private static		$libLang		= array();
		protected static	$ips			= null;
		private static 		$systemMessage	= array();
		private 			$board			= array();
		public				$DB				= null;
		/**
		 * @desc			Load's requested libraries dynamicly
		 * @param	string	$name library-name
		 * @return			class object of the requested library
		 * @author			Matthias Reuter
		 * @since			2.0
		 * @ignore
		 */
		public function __get($name){
			if(file_exists(ipbwi_ROOT_PATH.'lib/'.$name.'.inc.php')){
				require_once(ipbwi_ROOT_PATH.'lib/'.$name.'.inc.php');
				$classname = 'ipbwi_'.$name;
				$this->$name = new $classname($this);
				return $this->$name;
			}else{
				die('Class '.$name.' could not be loaded (tried to load class-file '.ipbwi_ROOT_PATH.'lib/'.$name.'.inc.php'.')');
			}
		}
		/**
		 * @desc			Loads and checks different vars when class is initiating
		 * @author			Matthias Reuter
		 * @since			2.0
		 * @ignore
		 */
		public function __construct(){
			// check for DB prefix
			if(ipbwi_DB_prefix == ''){
				define('ipbwi_DB_prefix','ipbwi_');
			}
			// for compatibility with IP.board
			$INFO = '';
			define('IPB_THIS_SCRIPT', 'public');
			if(ipbwi_IN_IPB === true){
				global $ipsclass;
				self::$ips						= $ipsclass;
			}else{
				// loads all required libraries from IP.board
				require_once ipbwi_BOARD_PATH.'init.php';
				require_once ipbwi_BOARD_PATH.'sources/action_public/xmlout.php';
				require_once ipbwi_BOARD_PATH.'sources/ipsclass.php';
				require_once ipbwi_BOARD_PATH.'ips_kernel/class_converge.php';
				require_once ipbwi_BOARD_PATH.'conf_global.php';
				require_once ipbwi_BOARD_PATH.'sources/classes/class_display.php';
				require_once ipbwi_BOARD_PATH.'sources/classes/class_session.php';
				require_once ipbwi_BOARD_PATH.'sources/classes/class_forums.php';
				require_once ipbwi_BOARD_PATH.'sources/handlers/han_parse_bbcode.php';
				require_once ipbwi_BOARD_PATH.'sources/lib/func_usercp.php';

				// instanciating all required objects from IP.board
				self::$ips						= new ipsclass();
				self::$ips->vars				= &$INFO;
				self::$ips->print				= new display();
				self::$ips->print->ipsclass		= &self::$ips;
				self::$ips->sess				= new session();
				self::$ips->sess->ipsclass		= &self::$ips;
				self::$ips->forums				= new forum_functions();
				self::$ips->forums->ipsclass	= &self::$ips;
				self::$ips->usercp				= new func_usercp();
				self::$ips->usercp->ipsclass	= &self::$ips;
				self::$ips->usercp->upload_path	= &self::$ips->vars['upload_path']; // bug in IP.Board which forgot to set this var in it's class
				self::$ips->init_db_connection();
				self::$ips->converge			= new class_converge(&self::$ips->DB);
				self::$ips->parse_incoming();
				self::$ips->cache_array			= array('rss_calendar', 'rss_export','components','banfilters', 'settings', 'group_cache', 'systemvars', 'skin_id_cache', 'forum_cache', 'moderators', 'stats', 'languages');
				self::$ips->init_load_cache(self::$ips->cache_array);
				self::$ips->initiate_ipsclass();
				self::$ips->member				= &self::$ips->sess->authorise();
				self::$ips->lastclick			= &self::$ips->sess->last_click;
				self::$ips->location			= &self::$ips->sess->location;
				self::$ips->session_id			= &self::$ips->sess->session_id;
				self::$ips->my_session			= &self::$ips->sess->session_id;
				self::$ips->no_print_header		= false;
				self::$ips->parser 				= new parse_bbcode;
				self::$ips->parser->ipsclass	= &self::$ips;
				self::$ips->parser->check_caches();
				self::$ips->load_language('lang_global');
				self::$ips->load_language('lang_register');
				self::$ips->load_template('skin_global', $this->skin->id());
				settype(self::$ips->input['act'], 'string');
				self::$ips->load_skin();

				// Update Session
				self::updateSession();
			}
			// define emoticon url for internal board functions
			self::$ips->vars['EMOTICONS_URL']	= self::$ips->vars['board_url'].'/style_emoticons/'.self::$ips->skin['_emodir'].'/';
			self::$ips->vars['AVATARS_URL']		= self::$ips->vars['board_url'].'/style_avatars';

			// retrieve common vars
			$this->board					= &self::$ips->vars;
			$this->board['version']			= &self::$ips->version;
			$this->board['version_long']	= &self::$ips->vn_full;
			$this->board['url']				= self::$ips->vars['board_url'].'/';
			$this->board['name']			= &self::$ips->vars['board_name'];
			$this->board['desc']			= &self::$ips->vars['board_desc'];
			$this->board['basedir']			= &self::$ips->vars['base_dir'];
			$this->board['upload_dir']		= self::$ips->vars['upload_dir'].'/';
			$this->board['upload_url']		= self::$ips->vars['upload_url'].'/';
			$this->board['home_name']		= &self::$ips->vars['home_name'];
			$this->board['home_url']		= self::$ips->vars['home_url'].'/';
			$this->board['emo_url']			= &self::$ips->vars['EMOTICONS_URL'];
			$this->DB						= &self::$ips->DB;

			if(defined('ipbwi_LANG')){
				self::setLang(ipbwi_LANG);
			}else{
				self::setLang('en');
			}
		}
		public function __destruct() {
			// Finally, register shutdown function...
			self::$ips->update_forum_cache();
			if(USE_SHUTDOWN){
				register_shutdown_function(array(&self::$ips, 'my_deconstructor'));
			}
		}
		/**
		 * @desc			Set's current SDK language
		 * @param	string	$lang language-name
		 * @return			true if language was loaded, otherwise false
		 * @author			Matthias Reuter
		 * @since			2.0
		 */
		public static function setLang($lang){
			$libLang = array();
			if(file_exists(ipbwi_ROOT_PATH.'lib/lang/'.$lang.'.inc.php')){
				if(include(ipbwi_ROOT_PATH.'lib/lang/'.$lang.'.inc.php')){
//					ipbwi_OVERWRITE_LOCAL
//					ipbwi_OVERWRITE_CHARSET
					if(ipbwi_UTF8){
						$encoding = 'UTF-8';
					}
					if(defined('ipbwi_OVERWRITE_LOCAL') && ipbwi_OVERWRITE_LOCAL !== false){
						$local = ipbwi_OVERWRITE_LOCAL;
					}
					if(defined('ipbwi_OVERWRITE_ENCODING') && ipbwi_OVERWRITE_ENCODING !== false){
						$encoding = ipbwi_OVERWRITE_ENCODING;
					}
					setlocale(LC_ALL, "$local.$encoding");

					// Change $this->lang,
					self::$lang		= $lang;
					self::$libLang	= $libLang;
					return true;
				}else{
					// Can't include it. Return false.
					self::addSystemMessage('Error','Language-File <strong>'.$lang.'</strong> exists, but can\'t be loaded');
					return false;
				}
			}else{
				// Doesn't exist. Invalid Language.
				self::addSystemMessage('Error','Language-File <strong>'.$lang.'</strong> does not exist.');
				return false;
			}
		}
		/**
		 * @desc			gets the language-var from actual requested native language-bit
		 * @param	string	$var language-var-name
		 * @return			native language bit or error msg
		 * @author			Matthias Reuter
		 * @since			2.0
		 */
		public function getLibLang($var=false){
			if(isset($var)){
				if(isset(self::$libLang[$var])){
					return self::$libLang[$var];
				}else{
					return 'The requested libLang <strong>'.$var.'</strong> is not defined.';
				}
			}else{
				return self::libLang;
			}
		}
		/**
		 * @desc			gets the requested board-var
		 * @param	string	$var board-var-name
		 * @return			board-var-value, returns false if not exists
		 * @author			Matthias Reuter
		 * @since			2.0
		 */
		public function getBoardVar($var){
			if(isset($this->board[$var])){
				return $this->board[$var];
			}else{
				return false;
			}
		}
		/**
		 * @desc			informations about the current IPBWI and PHP installation
		 * @return	string	HTML-code including the informations
		 * @author			Matthias Reuter
		 * @since			2.0
		 */
		public function info(){
			if($this->member->isAdmin()){
				ob_start();
				phpinfo();
				$phpinfo = ob_get_clean();
				return '
				<div class="center">
					<table border="0" cellpadding="3" width="600">
						<tr class="h"><td><h1 class="p">'.self::TITLE.'</h1></td></tr>
					</table><br />
					<table border="0" cellpadding="3" width="600">
						<tr><td class="e">Default Language:</td><td class="v">'.ipbwi_LANG.'</td></tr>
						<tr><td class="e">Current Language:</td><td class="v">'.self::$lang.'</td></tr>
						<tr><td class="e">IPBWI Version:</td><td class="v">'.self::VERSION.'</td></tr>
						<tr><td class="e">IPBWI Website:</td><td class="v">'.self::WEBSITE.'</td></tr>
						<tr><td class="e">Project Leader:</td><td class="v">'.self::PROJECT_LEADER.'</td></tr>
						<tr><td class="e">Development Team:</td><td class="v">'.self::DEV_TEAM.'</td></tr>
					</table><br />
				</div>
				<div class="center">
					<table border="0" cellpadding="3" width="600">
						<tr class="h"><td><h1 class="p">Invision Power Board</h1></td></tr>
					</table><br />
					<table border="0" cellpadding="3" width="600">
						<tr><td class="e">Version</td><td class="v">'.$this->getBoardVar('version').'</td></tr>
						<tr><td class="e">Detailed Version</td><td class="v">'.$this->getBoardVar('version_long').'</td></tr>
						<tr><td class="e">URL</td><td class="v">'.$this->getBoardVar('url').'</td></tr>
						<tr><td class="e">Name</td><td class="v">'.$this->getBoardVar('name').'</td></tr>
						<tr><td class="e">Description</td><td class="v">'.$this->getBoardVar('desc').'</td></tr>
						<tr><td class="e">Base Directory</td><td class="v">'.$this->getBoardVar('basedir').'</td></tr>
						<tr><td class="e">Upload Directory</td><td class="v">'.$this->getBoardVar('upload_dir').'</td></tr>
						<tr><td class="e">Upload URL</td><td class="v">'.$this->getBoardVar('upload_url').'</td></tr>
						<tr><td class="e">Home Name</td><td class="v">'.$this->getBoardVar('home_name').'</td></tr>
						<tr><td class="e">Home URL</td><td class="v">'.$this->getBoardVar('home_url').'</td></tr>
					</table><br />
				</div>
				'.$phpinfo;
			}else{
				$this->addSystemMessage('Error',$this->getLibLang('noAdmin'),'Located in file <strong>'.__FILE__.'</strong> at class <strong>'.__CLASS__.'</strong> in function <strong>'.__FUNCTION__.'</strong> on line #<strong>'.__LINE__.'</strong>');
				return false;
			}
		}
		/**
		 * @desc			adds a system message
		 * @param	string	$level Message-Level, common levels: Notice, Error, Hidden
		 * @param	string	$message Message-Content
		 * @return	bool	this function always returns true
		 * @author			Matthias Reuter
		 * @since			2.0
		 */
		public static function addSystemMessage($level,$message,$location=false){
			self::$systemMessage[$level][] = array(
				'message' => $message,
				'location' => $location
			);
			return true;
		}
		/**
		 * @desc			prints system messages
		 * @param	string	$level Message-Level, common levels: Notice, Error, Hidden
		 * @return	true	HTML-Code containing requested messages
		 * @author			Matthias Reuter
		 * @since			2.0
		 */
		public function printSystemMessages($level=false,$simple=false){
			if($simple === true){
				$output = '';
				foreach(self::$systemMessage as $k => $levels){
					if(($k == 'Hidden' && $this->member->isAdmin()) || $k != 'Hidden'){
						$i = 1;
						foreach($levels as $m){
							$output .= '<strong>'.$this->getLibLang('sysMsg_'.$k).'</strong> '.$m['message'].(($i < count($levels) ? '<br />' : ''));
							$i++;
						}
					}
				}
			}else{
				$output = '<div class="center"><table border="0" cellpadding="3">';
				// specific message-level requested, print only this
				if(isset($level) && is_string($level) && is_array(self::$systemMessage) && count(self::$systemMessage[$level]) > 0){
					if($level == 'Hidden' && !$this->member->isAdmin()){
						return false;
					}elseif($this->member->isAdmin()){
						$hLocation = '<th>Location</th>';
					}else{
						$hLocation = false;
					}
					$output .= '<tr class="h"><th colspan="2"> '.$level.'s</th>'.$hLocation.'</tr>';
					$i = 1;
					foreach(self::$systemMessage[$level] as $m){
						if($this->member->isAdmin()){
							$location = '<td class="v">'.$m['location'].'</td>';
						}else{
							$location = false;
						}
						$output .= '<tr><td class="e">'.$this->getLibLang('sysMsg_'.$level).' #'.$i.':</td><td class="v">'.$m['message'].'</td>'.$location.'</tr>';
						$i++;
					}
				}elseif(is_array(self::$systemMessage) && count(self::$systemMessage) > 0){
					// print all messages
					$i = 1;
					foreach(self::$systemMessage as $k => $levels){
						if(($k == 'Hidden' && $this->member->isAdmin()) || $k != 'Hidden'){
							if($this->member->isAdmin()){
								$hLocation = '<th>Location</th>';
							}else{
								$hLocation = '';
							}
							$output .= '<tr class="h"><th colspan="2">'.$k.'s</th>'.$hLocation.'</tr>';
							$location	= '';
							foreach($levels as $m){
								if($this->member->isAdmin()){
									$location = '<td class="v">'.$m['location'].'</td>';
								}
								$output .= '<tr><td class="e">'.$this->getLibLang('sysMsg_'.$k).' #'.$i.':</td><td class="v">'.$m['message'].'</td>'.$location.'</tr>';
								$i++;
							}
							$i = 1;
						}
					}
				}else{
					return false;
				}
				$output .= '</table><br /></div>';
			}
			return $output;
		}
		/**
		 * @desc			filtering html-strings, e.g. for db-queries
		 * @param	string	$var html-string
		 * @return			proper and safe string
		 * @author			Matthias Reuter
		 * @since			2.0
		 */
		public function makeSafe($var){
			$var = stripslashes($var);
			$var = trim($var);
			$search = array(
				'!',
				' & ',
				'\n',
				'&#032;',
			);
			$replace = array(
				'&#33;',
				' &amp; ',
				'<br />',
				' ',
			);
			$var = str_replace($search,$replace,$var);
			$search = array(
				'/\\\$/', // replace $-var
			);
			$replace = array(
				'&#036;',
			);
			$var = preg_replace($search,$replace,$var);
			if(ipbwi_UTF8){
				$var = iconv('UTF-8','ISO-8859-1',$var);
			}
			$var = addslashes($var);

			return $var;
		}
		/**
		 * @desc			filtering HTML strings
		 * @param	string	$var html-string
		 * @return			proper XHTML string
		 * @author			Matthias Reuter
		 * @since			2.0
		 */
		public function properXHTML($var){
			$search = array(
				'style_emoticons/<#EMO_DIR#>',
				' border="0"',
				' target="_blank"',
				' & '
			);
			$replace = array(
				$this->getBoardVar('emo_url'),
				'',
				'',
				' &amp; '
			);
			$var = str_replace($search,$replace,$var);

			$search = array(
				//'/ emoid=\"(.*)\"/U',
			);
			$replace = array(
				'',
			);
			$var = preg_replace($search,$replace,$var);
			if(ipbwi_UTF8){
				$var = iconv('ISO-8859-1','UTF-8',$var);
			}
			return $var;
		}
		/**
		 * @desc			Returns textual/timestamp offsetted date by board
		 * 					and by member offset and DST setting.
		 * @param	int		$timeStamp Numeric representation of the time beeing formatted
		 * @param	string	$dateFormat strftime() compliant format (see PHP manual)
		 * @param	int		$noBoard true = Offset with Board Time firstly, default = false
		 * @param	int		$noMember true = Bypass member's time offset and DST, default = false
		 * @return	string	formatted, localized date
		 * @author			Matthias Reuter
		 * @author			Cow <khlo@global-centre.com>
		 * @since			2.0
		 */
		public function date($timeStamp = false, $dateFormat = '%A, %d. %B %Y @ %T', $noBoard = false, $noMember = false){
			$info = $this->member->info();
			// If theres no timestamp make it current time using time()
			if(!$timeStamp){
				$timeStamp = time();
			}
			// Offset with Board Time firstly, if enabled
			// Also Check no member offset
			if(!$noBoard){
				if(!$noMember && empty($info['time_offset'])){
					$timeStamp = $timeStamp + (self::$ips->vars['time_offset'] * 60);
				}
			}
			// Board Time Adjust
			if(self::$ips->vars['time_adjust']){
				$timeStamp = $timeStamp + (self::$ips->vars['time_adjust'] * 60);
			}
			// If member has set an indiviual offset in the User CP
			// because they may be in a totally different country
			// using DST or whatever we can make those times affect it
			// across the whole website as well :D
			if($this->member->isLoggedIn() && !$noMember){
				if($info['time_offset']){
					$timeStamp = $timeStamp + ($info['time_offset'] * 3600);
				}
				if($info['dst_in_use']){
					$timeStamp = $timeStamp - 3600;
				}
			}
			if($dateFormat){
				$timeStamp = strftime($dateFormat, $timeStamp);
			}
			return $timeStamp;
		}
		/**
		 * @desc			Update Member Session Informations
		 * @return	void
		 * @author			Matthias Reuter
		 * @since			2.01
		 */
		protected static function updateSession(){
			if(isset($_COOKIE['session_id'])){
				$sid = $_COOKIE['session_id'];
			}else{
				$sid = md5(uniqid(microtime()));
			}
			$sessUpdate['id'] = $sid;
			$sessUpdate['member_name'] = self::$ips->member['members_display_name'];
			$sessUpdate['member_id'] = self::$ips->member['id'];
			$sessUpdate['ip_address'] = $_SERVER['REMOTE_ADDR'];
			$sessUpdate['browser'] = $_SERVER['HTTP_USER_AGENT'];
			$sessUpdate['running_time'] = time();
			$sessUpdate['login_type'] = intval(substr(self::$ips->member['login_anonymous'],0, 1));
			$sessUpdate['location'] = '';
			$sessUpdate['member_group'] = self::$ips->member['mgroup'];
			$sessUpdate['in_error'] = 0;
			$sessUpdate['location_1_type'] = '';
			$sessUpdate['location_1_id'] = '';
			$sessUpdate['location_2_type'] = '';
			$sessUpdate['location_2_id'] = '';
			$sessUpdate['location_3_type'] = '';
			$sessUpdate['location_3_id'] = '';
			$dbString = self::$ips->DB->compile_db_insert_string($sessUpdate);
			self::$ips->DB->query('DELETE FROM ibf_sessions WHERE id = "'.$sid.'"');
			self::$ips->DB->query('INSERT INTO ibf_sessions ('.$dbString['FIELD_NAMES'].') VALUES ('.$dbString['FIELD_VALUES'].')');
/*
			self::$ips->DB->query('UPDATE ibf_sessions SET
			member_name="'.$sessUpdate['member_name'].'",
			member_id="'.$sessUpdate['member_id'].'",
			ip_address="'.$sessUpdate['ip_address'].'",
			browser="'.$sessUpdate['browser'].'",
			running_time="'.$sessUpdate['running_time'].'",
			login_type="'.$sessUpdate['login_type'].'",
			location="'.$sessUpdate['location'].'",
			member_group="'.$sessUpdate['member_group'].'",
			in_error="'.$sessUpdate['in_error'].'" WHERE id="'.$_COOKIE['session_id'].'"');
			die($_COOKIE['session_id']);*/
		}
		
		// shorten 
function shorten($text,$length) {
	$text = strip_tags($text);
	$replacer = "...";
  	if(strlen($text) > $length) {
  		$text = preg_match('/^(.*)\W.*$/', substr($text, 0, $length+1), $matches) ? $matches[1] : substr($text, 0, $length) . $replacer;
 	}
	//$text = preg_replace("/\<img.+?src=\"(.+?)\".+?\/>/","",$text);
	//$text = preg_replace("/--/","-->",$text);
  	return $text;
}

//boink
function boink_it($url="",$msg="")
	{
			echo <<<EOF
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
			<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml"> 
				<head>
				    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" /> 
					<title>Redirecting...</title>
					<meta http-equiv="refresh" content="2; url=$url" />
					<link href="/css/skin.css" type="text/css" rel="stylesheet" />
							<script type='text/javascript'>
							//<![CDATA[
							// Fix Mozilla bug: 209020
							if ( navigator.product == 'Gecko' )
							{
								navstring = navigator.userAgent.toLowerCase();
								geckonum  = navstring.replace( /.*gecko\/(\d+)/, "$1" );

								setTimeout("moz_redirect()",1500);
							}

							function moz_redirect()
							{
								var url_bit     = "{$url}";
								window.location = url_bit.replace( new RegExp( "&amp;", "g" ) , '&' );
							}
							//>
							</script>
							</head>
							<body>
								<div class="black">
								<div id="redirectwrap">
									<h4>Redirecting...</h4>
									<div class="center"><img src="/images/loading.gif" alt="loading" /></div>
									<p>{$msg}<br /><br />Wait while you are being redirected</p>
									<p class="redirectfoot">(<a href="$url">Click here if you dont want to wait</a>)</p>
								</div>
								</div>
							</body>
						</html>
				
EOF;
}

    /**
	* Show board offline message
	*/
    function site_offline(){
    	//-----------------------------------------
    	// Get offline status
    	//-----------------------------------------	
		$this->DB->query("SELECT site_offline FROM ipbwi_site_settings");
		$row = $this->DB->fetch_row($query);
		return $row['site_offline'];
	}
	function site_offline_msg(){
    	//-----------------------------------------
    	// Get offline message (not cached)
    	//-----------------------------------------	
		$this->DB->query("SELECT offline_msg FROM ipbwi_site_settings");
		$row = $this->DB->fetch_row($query);
		return $row['offline_msg'];
	}
	
	function comments_offline(){
		$this->DB->query("SELECT * FROM ipbwi_site_settings");
		$row = $this->DB->fetch_row($query);
		return $row['comments_offline'];
	}
	function cheats_display(){
		$this->DB->query("SELECT * FROM ipbwi_site_settings");
		$row = $this->DB->fetch_row($query);
		return $row['cheats_display'];
	}

	
	} //eof

	// start class
	if(empty($ipbwi)){
		$ipbwi = new ipbwi();
	}else{
		die('<p>Error: You have to include and load IPBWI once only.</p>');
	}
?>