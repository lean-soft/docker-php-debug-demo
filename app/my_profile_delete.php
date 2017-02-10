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

if ($_GET['action'] == 'delete')
{
	if (!(val_user($_SESSION['Benutzername'], $_POST['passwd'])))
	{
		$MSG_ERROR = $_TEXT['ERROR_WRONG_PASSWORD'];
	}
	else
	{
		DeleteUser($_SESSION['Benutzername']);
		$MSG_CONFIRM = $_TEXT['PROFILE_DELETE_OK'];
		$_SESSION['Benutzername'] = '';
		unset($_SESSION['Benutzername']);
		require 'index.php';
		Exit;
	}

}

$_SUBNAV[] = array($_TEXT['LOGIN_PROFILE'], url('my_profile.php'));
$_SUBNAV[] = array($_TEXT['PROFILE_DELETE'], url('my_profile_delete.php'));

require 'include/page_top.php';
require 'include/my_profile_top.php';

echo '
	<table class="main">
	<tr><td class="oben">'.$_TEXT['PROFILE_DELETE'].'</td></tr>
	<tr><td class="w">
		<form action="'.url('my_profile_delete.php?action=delete').'" method="post">
		<table style="width:100%;">
		<tr><td>
			'.$_TEXT['PROFILE_DELETE_TEXT'].'
			<p>'.$_TEXT['PROFILE_DELETE_PASSWORD'].' <INPUT TYPE="password" NAME="passwd" SIZE="10" MAXLENGTH="100">
			<p><center><INPUT TYPE="SUBMIT" name="submit" VALUE="'.$_TEXT['PROFILE_DELETE'].'">
    		</td></tr>
		</table>
		</form>
	</td></tr>
	</table>
';

require 'include/my_profile_bottom.php';
require 'include/page_bottom.php';
?>