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

require 'include/init.php';

AuthUser();

if ($_GET['action'] == 'change')
{
	if (($_POST['passwd'] == $_POST['passwd2']) && (val_user($_SESSION['Benutzername'],$_POST['passwdalt'])) && (strlen($_POST['passwd'])>3))
	{
		$udat = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');
		$udat['password'] = md5($_POST['passwd']);
		IniSave('data/user/'.$_SESSION['Benutzername'].'.usr.ini',$udat);

		@setcookie("loginpasswd", md5($_POST['passwd']), time()+99999999999999, "/");
		include 'my_profile.php';
		Exit;
	}
	else
	{
		$MSG_ERROR = $_TEXT['ERROR_SAVE_DATA'];
	}	
}

$_SUBNAV[] = array($_TEXT['LOGIN_PROFILE'], url('my_profile.php'));
$_SUBNAV[] = array($_TEXT['PROFILE_PASSWORD'], url('my_profile_password.php'));

require 'include/page_top.php';
require 'include/my_profile_top.php';

echo '
	<table class="main">
	<tr><td class="oben">'.$_TEXT['PROFILE_PASSWORD'].'</td></tr>
	<tr><td class="w">
		<form action="'.url('my_profile_password.php?action=change').'" method="post">
		<table style="width:100%;">
			<tr>
				<td style="width:30%;text-align:right;">'.$_TEXT['PROFILE_PASSWORD_OLD'].':</td>
				<td style="width:70%;"><input type="password" name="passwdalt" style="width:250px;" SIZE="20" MAXLENGTH="20"></td>
			</tr>
			<tr>
				<td style="width:30%;text-align:right;">'.$_TEXT['PROFILE_PASSWORD_NEW'].':</td>
				<td><input type="password" name="passwd" style="width:250px;" SIZE="20" MAXLENGTH="20"></td>
			</tr>
			<tr>
				<td style="width:30%;text-align:right;">'.$_TEXT['PROFILE_PASSWORD_REPEAT'].':</td>
				<td><input type="password" name="passwd2" style="width:250px;" SIZE="20" MAXLENGTH="20"></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></td>
			</tr>
		</table>
    	</td></tr>
	</table>
';
require 'include/my_profile_bottom.php';
require 'include/page_bottom.php';
?>