<?php
//******************************* Lizenzbestimmungen *******************************//
//                                                                                  //
//  Der Quellcode von diesen Forum ist urheberrechtlich geschützt.                     //
//  Bitte beachten Sie die AGB auf www.frank-karau.de/agb.php                       //
//                                                                                  //
//  Dieser Lizenzhinweis darf nicht entfernt werden.                                //
//                                                                                  //
//  (C) phpFK - Forum ohne MySQL - www.frank-karau.de - support@frank-karau.de      //
//                                                                                  //
//**********************************************************************************//

require_once 'include/init.php';

if ($_POST['action'] == 'lostpasswd')
{

	if (file_exists('./data/user/'.$_POST['name'].'.usr.ini'))
	{
		$udat = IniLoad('./data/user/'.$_POST['name'].'.usr.ini');
		if ($udat['email'] == $_POST['email'])
		{		
			$message = $_TEXT['EMAIL_ANREDE']." ".$_POST['name'].",\n\n".$_TEXT['EMAIL_LOST_PASSWORD_CONFIRM']."\n\n".$_FORUM['settings_forum_url']."/login.php?action=snp&name=".$_POST['name']."&id=".md5($udat['password'])." \n\n".$_TEXT['EMAIL_FOOTER'];
			SendMessage(array($_POST['name']), $_TEXT['LOGIN_LOST_PASSWORD'], $message);
			$MSG_CONFIRM = $_TEXT['LOGIN_LOST_PASSWORD_CONFIRM_OK'];
			include('./index.php');
			Exit;
		}
	}
	$MSG_ERROR = $_TEXT['LOGIN_LOST_PASSWORD_ERROR'];
}

if ($_GET['action'] == 'snp')
{

	if (file_exists('./data/user/'.$_GET['name'].'.usr.ini'))
	{
		$udat = IniLoad('./data/user/'.$_GET['name'].'.usr.ini');
		if (md5($udat['password']) == $_GET['id'])
		{		
			$new_passwd = "";
			$allchars = "qwertzuiopasdfghjklyxcvbnmQWERTZUIOPASDFGHJKLYXCVBNM1234567890_-.,:";	
			mt_srand((double)microtime()*1000000);
			for ($i=1; $i<7; $i++)
		 	{
				$new_passwd = $new_passwd.$allchars[mt_rand (0,strlen($allchars) -5)];
			}
	
			$udat['password'] = md5($new_passwd);
			IniSave('./data/user/'.$_GET['name'].'.usr.ini', $udat);
			$message = $_TEXT['EMAIL_ANREDE']." ".$_GET['name'].",\n\n".$_TEXT['EMAIL_LOST_PASSWORD']."\n\n".$_TEXT['LOGIN_USERNAME'].": ".$_GET['name']."\n".$_TEXT['LOGIN_PASSWORD'].": ".$new_passwd."\n\n".$_TEXT['EMAIL_FOOTER'];
			SendMessage(array($_GET['name']), $_TEXT['LOGIN_LOST_PASSWORD'], $message);
			$MSG_CONFIRM = $_TEXT['LOGIN_LOST_PASSWORD_OK'];
			include('./index.php');
			Exit;
		}
	}
}

if (($_POST['action'] == 'login') && (IsUser()) && ($_SESSION['Benutzername'] == $_POST['name']))
{
	require 'index.php';
	Exit;
}

$_SUBNAV[] = array($_TEXT['NAV_LOGIN'], url('login.php'));

require_once 'include/page_top.php';

echo '
	<div class="content">
		<form action="'.url('index.php').'" method="post" onSubmit="showLoading();">
		<input type="hidden" name="action" value="login" />
		<table class="main">
		<tr><td class="oben">'.$_TEXT['NAV_LOGIN'].'</td></tr>
		<tr><td class="g">
			<center><table style="width:60%;">
			<tr>
				<td style="text-align:right;"><label for="name">'.$_TEXT['LOGIN_USERNAME'].':</label></td>
				<td><input type="text" id="name" name="name" value="'.$_COOKIE['loginname'].'" style="width:150px;" /></td>
			</tr>
			<tr>
				<td style="text-align:right;"><label for="passwd">'.$_TEXT['LOGIN_PASSWORD'].':</label></td>
				<td><input type="password" id="passwd" name="passwd" style="width:150px;" /></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;"><input type="checkbox" name="cookie" id="cookie"> <label for="cookie">'.$_TEXT['LOGIN_COOKIE'].'</label></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['LOGIN_LOGIN'].'"></td>
			</tr>
			</table>
		</td></tr>
		</table>
		</form>
	</div>
	<div class="content">
		<form action="'.url('login.php').'" method="post" onSubmit="showLoading();">
		<input type="hidden" name="action" value="lostpasswd" />
		<table class="main">
		<tr><td class="oben">'.$_TEXT['LOGIN_LOST_PASSWORD'].'</td></tr>
		<tr><td class="g">
			<center><table style="width:60%;">
			<tr>
				<td style="text-align:right;"><label for="name">'.$_TEXT['LOGIN_USERNAME'].':</label></td>
				<td><input type="text" id="name" name="name" value="'.$_COOKIE['loginname'].'" style="width:150px;" /></td>
			</tr>
			<tr>
				<td style="text-align:right;"><label for="email">'.$_TEXT['EMAIL'].':</label></td>
				<td><input type="text" id="email" name="email" value="" style="width:150px;" /></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['LOGIN_LOST_PASSWORD_SEND'].'"></td>
			</tr>
			</table>
		</td></tr>
		</table>
		</form>
	</div>
';

require './include/page_bottom.php';
?>