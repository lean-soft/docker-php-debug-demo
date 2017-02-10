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

if ($_FORUM['settings_register_nonewuser'])
{
	$MSG_ERROR = $_TEXT['ERROR_MAXUSER'];
	require 'index.php';
	Exit;
}

$ERROR = array();

if ($_POST['action'] == 'new_user')
{
	@include("option_domaincheck.php");
 	if (($OPTIONS_CHECKDOMAIN_ENABLED) && (!is_integer(strpos($_POST['new_email'],"@".$OPTIONS_CHECKDOMAIN_DOMAINNAME))))
 	{
 		$ERROR['email'] = $OPTIONS_CHECKDOMAIN_ERROR;
 	}
 	else
	{
		foreach(Group2Array($_FORUM['settings_blacklist']) as $item)
		{
			if (strpos(strtolower($_POST['new_email']), strtolower($item)) !== false)
			{
				$ERROR['email'] = $_TEXT['ERROR_EMAIL'];
			}
		}
		if ($ERROR['email'] == '')
			if (!checkEmail($_POST['new_email'])) $ERROR['email'] = $_TEXT['ERROR_EMAIL'];
	}
	if (((strtolower($_POST['new_code']) != strtolower($_SESSION['new_code2'])) OR ($_POST['new_code'] == "")) && (extension_loaded('gd')) && $_FORUM['settings_register_code'])
	{ 
		$ERROR['code'] = $_TEXT['ERROR_REG_CODE'];
	}
	if (!checkUsername($_POST['new_name'])) $ERROR['name'] = $_TEXT['ERROR_USERNAME_INVALID'];
	if (count($ERROR) == 0)
	{ 
	 	if (user_register($_POST['new_name'], $_POST['new_email']) == '')
		{
			$MSG_CONFIRM = $_TEXT['REG_CONFIRMATION'];
			require 'index.php';
			Exit;
 		}
	}
}

$_SUBNAV[] = array($_TEXT['NAV_REGISTER'], url('reg.php'));

require_once 'include/page_top.php';

echo '
	<div id="content">
		<form action="'.url('reg.php?'.session_name().'='.session_id()).'" method="post">
		<table class="main">
		<tr><td class="oben">'.$_TEXT['NAV_REGISTER'].'</td></tr>
 		<tr><td class="g" style="text-align:center;">
';
if ($_FORUM['settings_register_rules'] && ($_POST['rules'] <> 'accept'))
{
	echo '
			<input type="hidden" name="rules" value="accept" />
 			<center><table style="width:100%;">
				<tr><td>'.$_TEXT['REG_RULES'].'</td></tr>
				<tr><td><iframe style="width:100%; height:200px;" src="'.url('rules_save.php').'"></iframe><br /><a href="'.url('rules_save.php').'" target="_blank">'.$_TEXT['REG_RULES_PRINTSAVE'].'</a></td></tr>
				<tr><td style="text-align:center;"><input type="SUBMIT" name="submit" value="'.$_TEXT['REG_RULES_ACCEPT'].'" /></td></tr>
 			</table>
	';
}
else
{
	echo '
			<input type="hidden" name="action" value="new_user" />
			<input type="hidden" name="rules" value="accept" />
 			<center><table style="width:100%;table-layout:fixed;">
				<colgroup>
					<col width="20%">
					<col width="30%">
					<col width="50%">
				</colgroup>
				<tr>
					<td style="width:20%;"><label for="new_name">'.$_TEXT['LOGIN_USERNAME'].':</label></td>
	 				<td style="width:30%;"><input type="text" id="new_name" name="new_name" value="'.format_input($_POST['new_name']).'" size="30" maxlength="20" /></td>
					<td style="width:50%;">'.($ERROR['name']!=''?'<div id="error" style="margin:0px;">'.$ERROR['name'].'</div>':'').'</td>
				</tr>
	 			<tr>
					<td><label for="new_email">'.$_TEXT['EMAIL'].':</label></td>
	 				<td><input type="text" id="new_email" name="new_email" value="'.format_input($_POST['new_email']).'" size="30" maxlength="200" /></td>
					<td>'.($ERROR['email']!=''?'<div id="error" style="margin:0px;">'.$ERROR['email'].'</div>':'<div class="info" style="margin:0px;">'.$_TEXT['REG_INFORMATION'].'</div>').'</td>
				</tr>
		';
 	if (extension_loaded('gd') && $_FORUM['settings_register_code'])
 	{
 		echo '
	 			<tr>
					<td><label for="new_code">'.$_TEXT['REG_CODE'].':</label></td>
	 				<td><img src="include/code.php?'.session_name().'='.session_id().'" id="img_code" /> <img src="images/reload.png" onClick=\'var today = new Date();$("#img_code").attr("src", "include/code.php?'.session_name().'='.session_id().'&time="+today.getTime());\' style="cursor:pointer;cursor:hand;"/></td>
					<td rowspan="2">'.($ERROR['code']!=''?'<div id="error" style="margin:0px;">'.$ERROR['code'].'</div>':'').'</td>
				</tr>
	 			<tr>
					<td><small>'.$_TEXT['REG_CODE_TEXT'].'</small></td>
	 				<td><input type="text" id="new_code" name="new_code" size="6" maxlength="6" /></td>
				</tr>
		';
 	} 
	echo '
 				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" name="submit" value="'.$_TEXT['REG_CONFIRM'].'" /></td>
					<td>&nbsp;</td>
				</tr>
			</table>
	';
}
echo '




		</td></tr></table>
		</form>
 	</div>
';

require './include/page_bottom.php';
?>