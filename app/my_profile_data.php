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
	if (!ereg("^[_a-zA-Z0-9-](.{0,1}[_a-zA-Z0-9-])*@([a-zA-Z0-9-]{2,}.){0,}[a-zA-Z0-9-]{3,}(.[a-zA-Z]{2,4}){1,2}$", $_POST['email']))
	{
		$MSG_ERROR = $_TEXT['ERROR_EMAIL'];
	}
	else if (file_exists('data/user/'.$_SESSION['Benutzername'].'.usr.ini'))
	{
		$udat = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');
		$udat['name'] = format_string($_POST['name']);
		$udat['birthday_day'] = format_string($_POST['birthday_day']);
		$udat['birthday_month'] = format_string($_POST['birthday_month']);
		$udat['birthday_year'] = format_string($_POST['birthday_year']);
		$udat['sex'] = format_string($_POST['sex']);
		$udat['zip'] = format_string($_POST['zip']);
		$udat['location'] = format_string($_POST['location']);
		$udat['country'] = format_string($_POST['country']);
		if (($udat['email'] <> $_POST['email']) && ($udat['email_old'] == '')) $udat['email_old'] = format_string($udat['email']);
		$udat['email'] = format_string($_POST['email']);
		$udat['show_email'] = $_POST['show_email']=='on';
		$udat['homepage'] = format_string($_POST['homepage']);
		$udat['icq'] = format_string($_POST['icq']);
		$udat['skype'] = format_string($_POST['skype']);
		$udat['msn'] = format_string($_POST['msn']);
		$udat['aim'] = format_string($_POST['aim']);
		$udat['yahoo'] = format_string($_POST['yahoo']);
		$udat['text'] = format_text($_POST['text']);
		PluginHook('my_profile_data-save');
		IniSave('data/user/'.$_SESSION['Benutzername'].'.usr.ini',$udat);
		CreateBirthdayList();
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
$_SUBNAV[] = array($_TEXT['PROFILE_DATA'], url('my_profile_data.php'));

require 'include/page_top.php';
require 'include/my_profile_top.php';

$udat = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');

echo '
	<table class="main">
	<tr><td class="oben">'.$_TEXT['PROFILE_DATA'].'</td></tr>
	<tr><td class="w">
		<form action="'.url('my_profile_data.php?action=change').'" method="post">
		<table style="width:100%;">
			<tr>
				<td colspan="2" class="g"><b>'.$_TEXT['PROFILE_DATA_PERSONAL'].'</b></td>
			</tr>
			<tr>
				<td style="width:20%;text-align:right;">'.$_TEXT['LOGIN_USERNAME'].':</td>
				<td style="width:80%;"><b>'.$_SESSION['Benutzername'].'</b></td>
			</tr>
			<tr>
				<td style="text-align:right;">'.$_TEXT['PROFILE_DATA_NAME'].':</td>
				<td><input type="text" value="'.format_input($udat['name']).'" name="name" style="width:300px;" size="30" maxlength="100" /></td>
			</tr>
			<tr>
				<td style="text-align:right;">'.$_TEXT['PROFILE_DATA_BIRTHDAY'].':</td>
				<td>
					<select name="birthday_day" id="birthday_day">
						<option value="">---</option>
';
						for ($i = 1; $i <= 31; $i++)
						{
							echo '<option value="'.$i.'" '.($udat['birthday_day']==$i?'selected="selected"':'').'>'.$i.'.</option>';
						}
echo '
					</select>
					<select name="birthday_month" id="birthday_month">
						<option value="">---</option>
';
						$months = explode(',', $_TEXT['MONTHS']);
						for ($i = 1; $i <= count($months); $i++)
						{
							echo '<option value="'.$i.'" '.($udat['birthday_month']==$i?'selected="selected"':'').'>'.$months[$i-1].'</option>';
						}
echo '
					</select>
					<select name="birthday_year" id="birthday_year">
						<option value="">---</option>
';
						for ($i = date('Y'); $i >= 1900; $i--)
						{
							echo '<option value="'.$i.'" '.($udat['birthday_year']==$i?'selected="selected"':'').'>'.$i.'</option>';
						}
echo '
					</select>
				</td>
			</tr>
			<tr>
				<td style="text-align:right;">'.$_TEXT['PROFILE_DATA_SEX'].':</td>
				<td><select name="sex" id="sex">
					<option value="">'.$_TEXT['NOT_SPECIFIED'].'</option>
					<option value="1" '.($udat['sex']=='1'?'selected="selected"':'').'>'.$_TEXT['PROFILE_DATA_SEX_MALE'].'</option>
					<option value="2" '.($udat['sex']=='2'?'selected="selected"':'').'>'.$_TEXT['PROFILE_DATA_SEX_FEMALE'].'</option>
				</select></td>
			</tr>
			<tr>
				<td style="text-align:right;vertical-align_top;">'.$_TEXT['PROFILE_DATA_ZIP'].', '.$_TEXT['PROFILE_DATA_LOCATION'].':</td>
				<td>
					<input type="text" value="'.format_input($udat['zip']).'" name="zip" style="width:50px;" size="10" maxlength="10" /> <input type="text" value="'.format_input($udat['location']).'" name="location" style="width:200px;" size="30" maxlength="100" />
				</td>
			</tr>
			<tr>
				<td style="text-align:right;vertical-align_top;">'.$_TEXT['PROFILE_DATA_COUNTRY'].':</td>
				<td><input type="text" value="'.format_input($udat['country']).'" name="country" style="width:300px;" size="30" maxlength="100" /></td>
			</tr>
			<tr>
				<td colspan="2" class="g"><b>'.$_TEXT['PROFILE_DATA_CONTACT'].'</b></td>
			</tr>
			<tr>
				<td style="text-align:right;">'.$_TEXT['EMAIL'].':</td>
				<td><input type="text" value="'.format_input($udat['email']).'" name="email" style="width:300px;" size="30" maxlength="100" /></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input type="checkbox" '.($udat['show_email']?'checked="checked" ':'').' name="show_email" id="show_email" /> <label for="show_email">'.$_TEXT['PROFILE_SETTINGS_SHOW_EMAIL'].'</label></td>
			</tr>

			<tr>
				<td style="text-align:right;">'.$_TEXT['HOMEPAGE'].':</td>
				<td><input type="text" value="'.format_input($udat['homepage']).'" name="homepage" style="width:300px;" size="30" maxlength="100" /></td>
			</tr>
			<tr>
				<td style="text-align:right;">'.$_TEXT['ICQ'].':</td>
				<td><input type="text" value="'.format_input($udat['icq']).'" name="icq" style="width:100px;" size="30" maxlength="50" /></td>
			</tr>
			<tr>
				<td style="text-align:right;">'.$_TEXT['MSN'].':</td>
				<td><input type="text" value="'.format_input($udat['msn']).'" name="msn" style="width:150px;" size="30" maxlength="50" /></td>
			</tr>
			<tr>
				<td style="text-align:right;">'.$_TEXT['SKYPE'].':</td>
				<td><input type="text" value="'.format_input($udat['skype']).'" name="skype" style="width:150px;" size="30" maxlength="50" /></td>
			</tr>
			<tr>
				<td style="text-align:right;">'.$_TEXT['YAHOO'].':</td>
				<td><input type="text" value="'.format_input($udat['yahoo']).'" name="yahoo" style="width:150px;" size="30" maxlength="50" /></td>
			</tr>
			<tr>
				<td style="text-align:right;">'.$_TEXT['AIM'].':</td>
				<td><input type="text" value="'.format_input($udat['aim']).'" name="aim" style="width:150px;" size="30" maxlength="50" /></td>
			</tr>
			<tr>
				<td colspan="2" class="g"><b>'.$_TEXT['PROFILE_DATA_TEXT'].'</b></td>
			</tr>
			<tr>
				<td colspan="2"><textarea name="text" id="text" style="width:98%;height:100px;" cols="30" rows="5">'.undo_ubb($udat['text']).'</textarea></td>
			</tr>
';
PluginHook('my_profile_data-settings');
echo '
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