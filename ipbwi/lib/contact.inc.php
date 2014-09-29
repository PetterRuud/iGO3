<?php
class class_contact {
	
function auto_load() {
	global $ipbwi;
	switch ($_GET['code'])
	{		
		case "show":	
		$this->show();					
		break;
	  	case "do_send": 	
		$this->do_send();		
		break;

		default:
		$this->show();
		break;
	}
}
//-----------------------------------------------//
//				SHOW CONTACT FORM
//-----------------------------------------------//
function show() {
	global $ipbwi;
	
	$member = $ipbwi->member->info();
	$username = $member['members_display_name'];
	$useremail = $member['email'];
	$send_to = "webmaster@itsgameover.com";
	
	// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
// Additional headers
$headers .= 'From: itsGAMEOVER <itsgameover@itsgameover.com>' . "\r\n";
	
	if(isset($_GET['renewImage']) && $_GET['renewImage'] == true){
        die($ipbwi->antispam->renewGdImage());
    } 

	$html .= <<<EOF
	
	
	<div class="add_cheat">
	<h2>Contact</h2>
EOF;
	if(isset($_POST['submit'])){
       	if($ipbwi->antispam->validate()){
			$username = $_POST['username'];
			$ip = $_SERVER["REMOTE_ADDR"];
			$email = $_POST['email'];
			$useremail = $_POST['useremail'];
			$name = $_POST['name'];
			$subject = $_POST['subject'];
			$message = $_POST['message'];
	
$fullSubject = <<<EOF
itsGAMEOVER - {$subject}
EOF;
$fullMessage = <<<EOF
<html>
<head>
  <title>Email from itsgameover</title>
</head>
<body>
  <p>This e-mail was sent using the contactform at http://itsgameover.com/act=contact</p>
  <table>
    <tr>
      <th>From: {$name} ( {$username} )</th>
      <th>E-mail: {$email} ( {$useremail}</th>
      <th>IP: {$ip}</th>
    </tr>
    <tr>
      <td>{$message}</td>
    </tr>
  </table>
</body>
</html>
EOF;

		mail($send_to,$fullSubject,$fullMessage,$headers);
	$html .= <<<EOF
	<div style="margin: 3px 15px;" class="successwrap">
		<h4>-Email sent</h4>
		<p>Thanks for yummy email...</p>
	</div>
EOF;
        }else{
$html .= <<<EOF
        <div style="margin: 3px 15px;" class="errorwrap">
			<h4>The error returned was:</h4>
			<p>One or all of fields was blank, please take the time to fill them out.</p>
		</div>
EOF;
        }
    } 
$html .= <<<EOF
        <form id="form" action="index.php?act=contact" method="post">

		<label for="name">Name</label>
		<input class="required" type="text" id="name" name="name" value="Name" onclick="clickclear(this, 'Name')" onblur="clickrecall(this,'Name')"/>
		<br /><span class="smalltext">Username used on forum:  <em>{$username}</em></span>
		
		<label for="email">E-mail</label>
		<input class="required" type="text" id="email" name="email" value="E-mail" onclick="clickclear(this, 'E-mail')" onblur="clickrecall(this,'E-mail')"/>
		<br /><span class="smalltext">E-mail used on forum:  <em>{$useremail}</em></span>
		
		<label for="subject">Subject</label>
		<input class="required" size="60" type="text" id="subject" name="subject" value="Subject" onclick="clickclear(this, 'Subject')" onblur="clickrecall(this,'Subject')"/>
		
		<label for="message">Message</label>
		<textarea class="required" id="message" name="message" style="width: 94%; height: 120px;"></textarea>
		
		<label>Spam Protection</label>
        
		<input type="hidden" value="{$username}" name="username" />
		<input type="hidden" value="{$useremail}" name="useremail" />
EOF;
            $html .= $ipbwi->antispam->getHTML('?act=contact&amp;renewImage=true');
$html .= <<<EOF
		<br />
		<input type="submit" id="submit" name="submit" value="Send" />
		<input class="reset" type="reset" value="reset" />
	</form>
	</div>
EOF;
echo $html;

}


}// eoc
?>