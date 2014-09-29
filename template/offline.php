<?php include("template/header.php"); ?>
<div>&nbsp;</div>
<form action="?action=login" method="post">
	<div class="maintitle">&nbsp;Site Offline</div>
	<div style="background: url(<?=$ipbwi->getBoardVar('url');?>style_images/iGo3/boardbg.gif);">
		<div style="padding: 5px 15px 5px 15px;"><?=$ipbwi->site_offline_msg();?></div>
		<div>
			<div style="padding: 5px 15px 5px 15px;"><b>Username</b></div>
			<div style="padding: 5px 15px 5px 15px;"><input class="username swap" type="text" size="20" name="username" value="Username" /></div>
			<div style="padding: 5px 15px 5px 15px;"><b>Password</b></div>
			<div style="padding: 5px 15px 5px 15px;"><input class="pw swap" type="password" size="20" name="password" value="password" /></div>
		</div>
		<div class="formbuttonrow" align="center"><input class="button" type="submit" name="login" value="Log in" /></div>
		<!-- <input type="hidden" name="action" value="login" />-->
			<input type="hidden" name="setcookie" value="1" />
	</div>
</form>

<!-- Start The gfooter -->
<table cellspacing="0">
	<tr> 
		<td class="catend" width="918px"></td>
	</tr>
</table>
<!-- End the gfooter -->
<?php include(ROOT."template/footer.php"); ?>