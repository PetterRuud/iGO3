<?php check_login(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?=$site_name;?></title>
	<link rel="shortcut icon" href="/images/favicon.ico" />
	<!-- css -->
	<link rel="stylesheet" href="<?=$ipbwi->getBoardVar('url');?>style_images/css_5.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="/style/site.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="/style/jquery.fancybox.css" type="text/css" media="screen" />
	<!-- js -->
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="/jscript/jquery.validate.pack.js"></script>
	<script type="text/javascript" src="/jscript/jquery.anythingslider.js"></script>
	<script type="text/javascript" src="/jscript/jquery.fancybox.pack.js"></script>
	<script type="text/javascript" src="/jscript/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="/jscript/igo_go.js"></script>
</head>
<body>
<a name="top"></a>
<div class="menubar"><!-- start menubar -->
	<ul id="memberbar"><!-- start memberbar -->
	<?php if($ipbwi->member->isLoggedIn()){ ?>
	<!-- if logged in -->
		<li class="menubutton"><a href="#">
			<img src="images/name.gif" alt=""/>Logged in as <strong><?=$member['members_display_name'];?></strong></a>
			<ul class="menu_down">
			<?php if($ipbwi->member->isAdmin()){?>
				<li><a href="/admin/" target="_blank"><strong>Site ACP</strong></a></li>
				<li><a href="<?=$ipbwi->getBoardVar('url');?>/admin/" target="_blank"><strong>Board ACP</strong></a></li>
				<li><a href="<?=$ipbwi->getBoardVar('url');?>" title="#"><strong>My Controls</strong></a></li>
			<?php } ?>
				<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=Search&amp;CODE=getnew">New Posts</a></li>
			</ul>
		</li>
		<li class="menubutton"><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=Msg&amp;CODE=01&amp;VID=in"><img src="images/pmbox.gif" alt=""/><strong><?=$ipbwi->pm->numNewPMs();?></strong> New message</a></li>
		<li class="menubutton"><a href="#"><img src="images/users.png" alt="user"/><strong><?=$online;?></strong> 	users online</a>
			<ul class="menu_down long">
				<li><?=$onlinelist;?></li>
			</ul>
		</li>
		<li class="menubutton"><a href="?action=logout"><img src="images/logout.gif" alt="Log out"/>Log out</a></li>

		<?php }else{ ?>

		<!-- if not logged in -->
			<li class="menubutton2">
			<form action="?action=login" method="post">
				<input class="username swap" type="text" size="20" name="username" value="Username" />
				<input class="pw swap" type="password" size="20" name="password" value="password"/>
				<input class="go" type="submit" name="login" value="" />
				<!-- <input type="hidden" name="action" value="login" />-->
				<input type="hidden" name="setcookie" value="1" />
			</form>
		</li>
		<li class="menubutton2"><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=Reg&CODE=00"><img src="images/bt_reg.png" alt="register" /></a></li>
		<li class="menubutton2"><a href="#help"><img src="images/bt_help.png" alt="help" /></a></li>
	<?php } ?>

	</ul><!-- end memberbar -->
	<!-- start topmenu -->
	<ul id="topmenu">
		<li class="menubutton"><a href="#"><img src="images/bt_tools.png" alt="" /></a>
			<ul class="menu_down">
				<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=members">Members</a></li>
				<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=help">Help</a></li>
				<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=calendar">Calender</a></li>
				<li><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=search">Search</a></li>
			</ul>
		</li>
		<li class="menubutton"><a href="<?=$ipbwi->getBoardVar('url');?>index.php?act=boardrules"><img src="images/bt_rules.png" alt="" /></a></li>
			<li class="menubutton">
				<form action="<?=$ipbwi->getBoardVar('url');?>index.php?act=Search&amp;CODE=01" method="post">
				<input type='hidden' name='forums' id="earch-forums-top" value='all' /> 
				<input type="text" size="20" name="keywords" id="search-box-top" class="search swap" />
				<input class="go" type="submit" value="" />
				</form>
			</li>
	</ul><!-- end topmenu -->
</div><!-- end menubar -->

<!-- start header -->
<a href="/index.php"><span class="logo"><?=$site_name;?></span></a><!-- end logo -->

<!-- start submenu -->
<div class="subbar">
	<table id="submenu" cellspacing="0" cellpadding="0">
		<tr>
		<td width="66px"></td>
		<td nowrap="nowrap" width="120px">
			<a href="<?=$ipbwi->getBoardVar('url');?>">
				<img src='images/submenu_forum.gif' border='0' alt='forum' />
			</a>
		</td>
		<td nowrap="nowrap" width="98px">
			<a href="<?=$ipbwi->getBoardVar('url');?>index.php?autocom=downloads">
				<img src='images/submenu_downloads.gif' border='0' alt='downloads' />
			</a>
		</td>
		<td nowrap="nowrap" width="98px">
			<a href="<?=$site_url;?>/index.php?act=cheats">
				<img src='images/submenu_cheats.gif' border='0' alt='cheats' />
			</a>
		</td>
		<td nowrap="nowrap" width="183px">
			<a href="<?=$site_url;?>">
				<img src='images/submenu-home.gif' border='0' alt='Home' />
			</a>
		</td>
		<td nowrap="nowrap" width="98px">
			<a href="#downloads">
				<img src='images/submenu-clans.gif' border='0' alt='clans' />
			</a>
		</td>
		<td nowrap="nowrap" width="98px">
			<a href="#servers">
				<img src='images/submenu-servers.gif' border='0' alt='servers' />
			</a>
		</td>
		<td nowrap="nowrap" width="120px">
			<a href="<?=$site_url;?>/index.php?act=contact">
				<img src='images/submenu-contact.gif' border='0' alt='contact' />
			</a>
		</td>
	</tr>
	</table>
</div><!-- end submenu -->

<!-- start undermenu  -->
<div id="undermenu">
<!-- 
	<table align="center" width="532px" style="padding: 4px 0px 0px 35px;margin:0 auto;">
	<tr>
		<td class="nowrap" width="47px">
			<a href="#news">
				<img src='images/undermenu_news.gif' border='0' alt='news' />
			</a>
		</td>
		<td class="nopad" width="93px">
			<a href="#org">
				<img src='images/undermenu_organisation.gif' border='0' alt='organisation' />
			</a>
		</td>
		<td class="nopad" width="65px">
			<a href="#articles">
				<img src='images/undermenu_articles.gif' border='0' alt='articles' />
			</a>
		</td>
		<td class="nopad" width="68px">
			<a href="#journals">
				<img src='images/undermenu_journals.gif' border='0' alt='journals' />
			</a>
		</td>
		<td class="nopad" width="49px">
			<a href="#blogs">
				<img src='images/undermenu_blogs.gif' border='0' alt='blogs' />
			</a>
		</td>
		<td class="nopad" width="115px">
			<a href="#soa">
				<img src='images/undermenu_soa.gif' border='0' alt='send own article' />
			</a>
		</td>
		<td class="nopad" width="69px">
			<a href="#subscribe">
				<img src='images/undermenu_subscribe.gif' border='0' alt='subscribe' />
			</a>
		</td>
	</tr>
	</table>
	-->
</div><!-- end undermenu -->

<div id="wrapper2">
	<div id="wrapper3">