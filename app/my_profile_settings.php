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
	if (file_exists('data/user/'.$_SESSION['Benutzername'].'.usr.ini'))
	{
		$udat = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');
		$udat['newsletter'] = $_POST['newsletter']=='on';
		$udat['settings_hidestatus'] = (($_FORUM['settings_user_hidestatus'] OR IsAdmin()) && $_POST['hidestatus']=='on');
		$udat['settings_post_notification'] = $_POST['post_notification']=='on';
		$udat['settings_post_no_autolink'] = $_POST['post_no_autolink']=='on';
		$udat['settings_pm_notification'] = $_POST['pm_notification']=='on';
		PluginHook('my_profile_settings-save');
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
$_SUBNAV[] = array($_TEXT['PROFILE_SETTINGS'], url('my_profile_settings.php'));

require 'include/page_top.php';
require 'include/my_profile_top.php';

$udat = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');

echo '
	<table class="main">
	<tr><td class="oben">'.$_TEXT['PROFILE_SETTINGS'].'</td></tr>
	<tr><td class="w">
		<form action="'.url('my_profile_settings.php?action=change').'" method="post">
		<table style="width:100%;">
			<tr>
				<td><input type="checkbox" '.($udat['settings_post_notification']?'checked="checked" ':'').' name="post_notification" id="post_notification" /> <label for="post_notification">'.$_TEXT['PROFILE_SETTINGS_POST_NOTIFICATION'].'</label></td>
			</tr>
			<tr>
				<td><input type="checkbox" '.($udat['settings_post_no_autolink']?'checked="checked" ':'').' name="post_no_autolink" id="post_no_autolink" /> <label for="post_no_autolink">'.$_TEXT['PROFILE_SETTINGS_POST_NO_AUTOLINK'].'</label></td>
			</tr>
			<tr>
				<td><input type="checkbox" '.($udat['settings_pm_notification']?'checked="checked" ':'').' name="pm_notification" id="pm_notification" /> <label for="pm_notification">'.$_TEXT['PROFILE_SETTINGS_PM_NOTIFICATION'].'</label></td>
			</tr>
			<tr>
				<td><input type="checkbox" '.($udat['newsletter']?'checked="checked" ':'').' name="newsletter" id="newsletter" /> <label for="newsletter">'.$_TEXT['PROFILE_SETTINGS_NEWSLETTER'].'</label></td>
			</tr>
			'.(($_FORUM['settings_user_hidestatus'] OR IsAdmin())?'<tr>
				<td><input type="checkbox" '.($udat['settings_hidestatus']?'checked="checked" ':'').' name="hidestatus" id="hidestatus" /> <label for="hidestatus">'.$_TEXT['PROFILE_SETTINGS_HIDESTATUS'].'</label></td>
			</tr>':'').'
';
PluginHook('my_profile_settings-settings');
echo '
			<tr>
				<td style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></td>
			</tr>
		</table>
    	</td></tr>
	</table>
';
require 'include/my_profile_bottom.php';
require 'include/page_bottom.php';
?>