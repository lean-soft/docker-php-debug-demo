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

require './include/init.php';

AuthUser();

if ($_GET['action'] == 'change')
{
	if (file_exists('data/user/'.$_SESSION['Benutzername'].'.usr.ini'))
	{
		$udat = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');
		$udat['signature'] = format_text($_POST['text']);
		IniSave('data/user/'.$_SESSION['Benutzername'].'.usr.ini',$udat);
		include 'my_profile.php';
		Exit;
	}
	else
	{
		$MSG_ERROR = $_TEXT['ERROR_SAVE_DATA'];
		include 'my_profile.php';
		Exit;
	}	
}

$_SUBNAV[] = array($_TEXT['LOGIN_PROFILE'], url('my_profile.php'));
$_SUBNAV[] = array($_TEXT['PROFILE_SIGNATURE'], url('my_profile_signature.php'));

require 'include/page_top.php';
require 'include/my_profile_top.php';

$udat = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');

echo '
	<table class="main">
	<tr><td class="oben">'.$_TEXT['PROFILE_SIGNATURE'].'</td></tr>
	<tr><td class="w">
		<form action="'.url('my_profile_signature.php?action=change').'" method="post">
		<table style="width:100%;">
			<tr><td>'.$_TEXT['PROFILE_SIGNATURE_TEXT'].'</td></tr>
			<tr><td><textarea name="text" id="text" style="width:100%;height:100px;" cols="50" rows="10">'.undo_ubb($udat['signature']).'</textarea></td></tr>
			<tr>
				<td colspan="2" style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></td>
			</tr>
		</table>
    	</td></tr>
	</table>
	<script type="text/javascript">
	<!--
		$(document).ready(function()	
		{
			$(\'#text\').markItUp(mySettings); $(\'#text\').width($(\'.markItUpContainer\').innerWidth() - 24);
		});
	-->
	</script>
';
require 'include/my_profile_bottom.php';
require 'include/page_bottom.php';
?>