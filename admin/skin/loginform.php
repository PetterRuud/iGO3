<?php check_login();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xml:lang="en" lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset={$zone->ips->vars['gb_char_set']}" /> 
<title><?=$site_name;?> - Site ACP</title>
<link rel="shortcut icon" href="favicon.ico" />
<style type='text/css' media="all">
@import url( "<?php echo $acp->skin_acp_url;?>/acp_css.css" );
</style>
</head>
<body>
	<div id='ipdwrapper'><!-- WRAPPER -->


<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<div align='center'>
<div style='width:500px'>
<div class='outerdiv' id='global-outerdiv'><!-- OUTERDIV -->
<table cellpadding='0' cellspacing='8' width='100%' id='tablewrap'>
<tr>
 <td id='rightblock'>
 <div>
 <form action="?action=login" method="post">
  <table width='100%' cellpadding='0' cellspacing='0' border='0'>
  <tr>
   <td width='200' class='tablerow1' valign='top' style='border:0px;width:200px'>
   <div style='text-align:center;padding-top:20px'>
   	<img src='<?=$acp->skin_acp_url;?>/images/acp-login-lock.gif' alt='Portal' border='0' />
   </div>
   <br />
   <div class='desctext' style='font-size:10px'>
   <div align='center'><strong>Welcome to <?=$site_name;?> </strong></div>
   <br />
  	<div style='font-size:9px;color:gray'>&copy; <?=$site_name;?>.</div>
   </div>
   </td>
   <td width='300' style='width:300px' valign='top'>
	 <table width='100%' cellpadding='5' cellspacing='0' border='0'>
	 <tr>
	  <td colspan='2' align='center'>
		 <br /><img src='acp-logo.png' alt='Portal' border='0' />
		 <div style='font-weight:bold;color:red'>No admin sessions found</div>
	  </td>
	 </tr>
	 <tr>

		<td align='right'><strong>User Name</strong></td>

	  <td><input style='border:1px solid #AAA' type='text' size='20' name='username' id='namefield' value='<?=$member['members_display_name'];?>' /></td>
	 </tr>
	 <tr>
	  <td align='right'><strong>Password</strong></td>
	  <td><input style='border:1px solid #AAA' type='password' size='20' name='password' value='' /></td>
	 </tr>
	 <tr>
	  <td colspan='2' align='center'><input type='submit' style='border:1px solid #AAA' value='Log In' /></td>
	 </tr>
	 <tr>
	  <td colspan='2'><br />
		  
	  </td>
	 </tr>
	</table>
   </td>
  </tr>
  </table>
 </form>
 
 </div>
 </td>
</tr>
</table>
</div><!-- / OUTERDIV -->

</div>
</div>

	<br />
	</div><!-- / WRAPPER -->
	<script type="text/javascript">
	function clickclear(thisfield, defaulttext) {
		if (thisfield.value == defaulttext) {
			thisfield.value = "";
		}
	}
	function clickrecall(thisfield, defaulttext) {
		if (thisfield.value == "") {
			thisfield.value = defaulttext;
		}
	}
</script>
	</body>
	</html>