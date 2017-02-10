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
	
$file = '';

if (file_exists('../data/user/'.$_GET['user'].'.usr.ini')) $file = '../data/user/'.$_GET['user'].'.usr.ini';
else if (file_exists('../data/user/'.$_GET['user'].'.usr.tmp')) $file = '../data/user/'.$_GET['user'].'.usr.tmp';
else if (file_exists('../data/user/'.$_GET['user'].'.usr.del')) $file = '../data/user/'.$_GET['user'].'.usr.del';

if ($file == '')
{
	require 'include/user_admin.php';
}
else 
{
	$tabs = array();
	$tabs[] = '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab=">'.$_TEXT['PROFILE_DATA'].'</a>';
	$tabs[] = '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab=password">'.$_TEXT['PROFILE_PASSWORD'].'</a>';
	$tabs[] = '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab=settings">'.$_TEXT['PROFILE_SETTINGS'].'</a>';
	$tabs[] = '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab=signature">'.$_TEXT['PROFILE_SIGNATURE'].'</a>';
	$tabs[] = '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab=avatar">'.$_TEXT['PROFILE_AVATAR'].'</a>';
	$tabs[] = '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab=points">'.$_TEXT['PROFILE_POINTS'].'</a>';
	$tabs[] = '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab=add">'.$_TEXT['AP_USER_ADMIN_ADDTEXT'].'</a>';
	PluginHook('ap-user_edit-tabs');
	echo '
		<h1>'.$_TEXT['AP_USER_ADMIN'].'</h1>
		<p>'.$_TEXT['LOGIN_USERNAME'].': <b>'.substr(str_replace('../data/user/', '', $file), 0, -8).'</b></p>
		<p>'.implode(' | ', $tabs).'</p>
	';
	$udat = IniLoad($file);

// load tabs ...
if ($_GET['tab'] == 'password')
{
	if (($_POST['action'] == 'change') && ($_POST['passwd'] == $_POST['passwd2']) && ($_POST['passwd'] <> ''))
	{
		$udat = IniLoad($file);
		$udat['password'] = md5($_POST['passwd']);
		IniSave($file,$udat);
		echo '<div class="confirm">'.$_TEXT['AP_SAVED'].'</div>';
	}
	echo '
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab='.$_GET['tab'].'" method="post">
		<input type="hidden" name="action" value="change">
		<fieldset>
		<legend>'.$_TEXT['PROFILE_PASSWORD'].'</legend>
		<table style="width:100%;">
			<tr>
				<td style="width:30%;text-align:right;">'.$_TEXT['PROFILE_PASSWORD_NEW'].':</td>
				<td style="width:70%;"><input type="password" name="passwd" style="width:250px;" SIZE="20" MAXLENGTH="20"></td>
			</tr>
			<tr>
				<td style="width:30%;text-align:right;">'.$_TEXT['PROFILE_PASSWORD_REPEAT'].':</td>
				<td><input type="password" name="passwd2" style="width:250px;" SIZE="20" MAXLENGTH="20"></td>
			</tr>
		</table>
    		</fieldset>
		<center><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></center>
		</form>
	';
}
else if ($_GET['tab'] == 'settings')
{
	if ($_POST['action'] == 'change')
	{
		$udat = IniLoad($file);
		$udat['newsletter'] = $_POST['newsletter']=='on';
		$udat['settings_hidestatus'] = $_POST['hidestatus']=='on';
		$udat['settings_post_notification'] = $_POST['post_notification']=='on';
		$udat['settings_post_no_autolink'] = $_POST['post_no_autolink']=='on';
		$udat['settings_pm_notification'] = $_POST['pm_notification']=='on';
		PluginHook('ap-user_edit-settings_save');
		IniSave($file,$udat);
		echo '<div class="confirm">'.$_TEXT['AP_SAVED'].'</div>';
	}
	echo '
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab='.$_GET['tab'].'" method="post">
		<input type="hidden" name="action" value="change">
		<fieldset>
		<legend>'.$_TEXT['PROFILE_SETTINGS'].'</legend>
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
			<tr>
				<td><input type="checkbox" '.($udat['settings_hidestatus']?'checked="checked" ':'').' name="hidestatus" id="hidestatus" /> <label for="hidestatus">'.$_TEXT['PROFILE_SETTINGS_HIDESTATUS'].'</label></td>
			</tr>
	';
	PluginHook('ap-user_edit-settings');
	echo '
		</table>
    		</fieldset>
		<center><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></center>
		</form>
	';
}
else if ($_GET['tab'] == 'signature')
{
	if ($_POST['action'] == 'change')
	{
		$udat = IniLoad($file);
		$udat['signature'] = format_text($_POST['signature']);
		IniSave($file,$udat);
		echo '<div class="confirm">'.$_TEXT['AP_SAVED'].'</div>';
	}
	echo '
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab='.$_GET['tab'].'" method="post">
		<input type="hidden" name="action" value="change">
		<fieldset>
		<legend>'.$_TEXT['PROFILE_SIGNATURE'].'</legend>
		<table style="width:100%;">
			<tr>
				<td colspan="2"><textarea name="signature" id="signature" style="width:98%;height:100px;" cols="30" rows="5">'.undo_ubb($udat['signature']).'</textarea></td>
			</tr>
		</table>
    		</fieldset>
		<center><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></center>
		</form>
	';
}
else if ($_GET['tab'] == 'avatar')
{
	if ($_POST['action'] == 'change')
	{
		if ($_FILES['f1']['size'] > 0)
		{
			$MSG_ERROR = '';
			$info = pathinfo($_FILES['f1']['name']);
			if (!in_array(strtolower($info['extension']), Group2Array($_FORUM['settings_system_upload_avatar_formats'])))
			{
				$MSG_ERROR = MultiReplace($_TEXT['ERROR_UPLOAD_TYPE'], '<b>'.$info['extension'].'</b>');
			} 
			else if (!ImageResize($_FILES['f1']['tmp_name'], $_FILES['f1']['tmp_name'], $_FORUM['settings_system_upload_avatar_pixel'], $_FORUM['settings_system_upload_avatar_pixel']))
			{
				if ($_FILES['f1']['size'] > ($_FORUM['settings_system_upload_avatar_size']*1024)) 
				{
					$MSG_ERROR = MultiReplace($_TEXT['ERROR_UPLOAD_SIZE'], '<b>'.round($_FILES['f1']['size']/1024).' kB</b>');
				}
		   		$size=getimagesize($_FILES['f1']['tmp_name']);
				if (($size[0]>$_FORUM['settings_system_upload_avatar_pixel']) OR ($size[1]>$_FORUM['settings_system_upload_avatar_pixel']))
		   		{
					$MSG_ERROR = MultiReplace($_TEXT['ERROR_UPLOAD_PIXEL'], '<b>'.$size[0].' x '.$size[1].'</b>');
				}
			}
			if ($MSG_ERROR == '')
			{
				$path = str_replace('/ap/?', '/data/upload/', strtr(getcwd(), "\\", "/").'/?');
				$filename = 'av_'.$_GET['user'].'.jpg';
				if(!copy ($_FILES['f1']['tmp_name'],$path.$filename))
				{
					$MSG_ERROR = $_TEXT['ERROR_UPLOAD'];
				} 
				else 
				{
					$ini = array();
					$ini['filename'] = $_FILES['f1']['name'];
					$ini['url'] = $filename;
					$ini['type'] = $_FILES['f1']['type'];
					IniSave(DIR.'data/upload/av_'.$_GET['user'].'.ini', $ini);
					$_POST['rb_avatar'] = 'upload';
				}
			}
		}
		$udat = IniLoad($file);
		if ($_POST['rb_avatar'] == 'upload') 
			$udat['avatar'] = 'download.php?type=avatar&id='.$_GET['user'];
		else if ($_POST['rb_avatar'] == 'no') 
			$udat['avatar'] = '';
		else
			$udat['avatar'] = 'avatar/'.$_POST['rb_avatar'];
		IniSave($file,$udat);
		echo '<div class="confirm">'.$_TEXT['AP_SAVED'].'</div>';
	}
	echo '
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab='.$_GET['tab'].'" method="post" enctype="multipart/form-data">
		<input type="hidden" name="action" value="change">
		<fieldset>
		<legend>'.$_TEXT['PROFILE_AVATAR'].'</legend>
		<table style="width:100%;">
			<tr>
				<td style="text-align:center;width:33%;"><input type="radio" name="rb_avatar" '.($udat['avatar']==''?'checked="checked" ':'').' value="no"> '.$_TEXT['PROFILE_AVATAR_NO'].'</td>
';
$checked = false;
$avatars = LoadFileList('../avatar/', '.');
$counter = 1;
foreach ($avatars as $avatar)
{
	echo '<td style="text-align:center;width:33%;"><img src="../avatar/'.$avatar.'"><br /><input type="radio" name="rb_avatar" ';
	if ("avatar/".$avatar == $udat['avatar'])
	{
		echo 'checked="checked" ';
		$checked = true;
	}
	echo 'value="'.$avatar.'"> '.$avatar.'</td>';
	$counter++;
	if ($counter == 3)
	{
		$counter = 0;
		echo '
			</tr>
			<tr>
		';
	}
}
echo '
			</tr>
';
if ($_FORUM['settings_system_upload_avatar'])
{
	$checked_upload = ($udat['avatar'] == 'download.php?type=avatar&id='.$_GET['user']);
	echo '
			<tr>
				<td colspan="3"><hr></td>
			</tr>
			<tr>
				
				'.(file_exists(DIR.'data/upload/av_'.$_GET['user'].'.ini')?'<td class="g"><input type=radio name="rb_avatar" '.($checked_upload?'checked="checked" ':'').'value="upload"> <img src="'.DIR.'download.php?type=avatar&id='.$_GET['user'].'" style="vertical-align:middle;" /></td><td class="g" colspan="2">':'<td colspan="3" class="g">').'
						<b>'.$_TEXT['UPLOAD_NEW'].':</b>
						<br /><input name="f1" type="file"> <input type="submit" value="'.$_TEXT['UPLOAD'].'">
						<br /><small>'.$_TEXT['UPLOAD_TYPE'].': '.implode(', ', Group2Array($_FORUM['settings_system_upload_avatar_formats'])).(!extension_loaded('gd')?' | '.$_TEXT['UPLOAD_SIZE'].': '.$_FORUM['settings_system_upload_avatar_size'].' kB | '.$_TEXT['UPLOAD_PIXEL'].': '.$_FORUM['settings_system_upload_avatar_pixel'].' x '.$_FORUM['settings_system_upload_avatar_pixel']:'').' | '.$_TEXT['UPLOAD_NEW2'].'</small>
				</td>
			</tr>
	';
}
echo '

		</table>
    		</fieldset>
		<center><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></center>
		</form>
	';
}
else if ($_GET['tab'] == 'points')
{
	if ($_POST['action'] == 'change')
	{
		$udat = IniLoad($file);
		$udat['count_topics'] = format_string($_POST['count_topics']);
		$udat['count_answeres'] = format_string($_POST['count_answeres']);
		$udat['count_answeres2'] = format_string($_POST['count_answeres2']);
		$udat['count_locked'] = format_string($_POST['count_locked']);
		IniSave($file,$udat);
		echo '<div class="confirm">'.$_TEXT['AP_SAVED'].'</div>';
	}
	echo '
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab='.$_GET['tab'].'" method="post">
		<input type="hidden" name="action" value="change">
		<fieldset>
		<legend>'.$_TEXT['PROFILE_POINTS'].'</legend>
		<table style="width:100%;">
			<tr><td style="vertical-align:top;"><b>&raquo; '.$_TEXT['PROFILE_POINTS_TOPICS'].'</b><br>'.$_TEXT['PROFILE_POINTS_TOPICS_DESCR'].'</td><td style="text-align:right;"><input type="text" name="count_topics" style="width:50px;" value="'.format_input($udat['count_topics']).'"></td><td><nobr>x 5 '.$_TEXT['POINTS'].'</td><td>=</td><td style="text-align:right;"><nobr>'.fnum($udat['count_topics']*5).' '.$_TEXT['POINTS'].'</td></tr>
			<tr><td style="vertical-align:top;"><b>&raquo; '.$_TEXT['PROFILE_POINTS_ANSWERES'].'</b><br>'.$_TEXT['PROFILE_POINTS_ANSWERES_DESCR'].'</td><td style="text-align:right;"><input type="text" name="count_answeres" style="width:50px;" value="'.format_input($udat['count_answeres']).'"></td><td><nobr>x 2 '.$_TEXT['POINTS'].'</td><td>=</td><td style="text-align:right;"><nobr>'.fnum($udat['count_answeres']*2).' '.$_TEXT['POINTS'].'</td></tr>
			<tr><td style="vertical-align:top;"><b>&raquo; '.$_TEXT['PROFILE_POINTS_ANSWERES2'].'</b><br>'.$_TEXT['PROFILE_POINTS_ANSWERES2_DESCR'].'</td><td style="text-align:right;"><input type="text" name="count_answeres2" style="width:50px;" value="'.format_input($udat['count_answeres2']).'"></td><td><nobr>x 1 '.$_TEXT['POINTS'].'</td><td>=</td><td style="text-align:right;"><nobr>'.fnum($udat['count_answeres2']).' '.$_TEXT['POINTS'].'</td></tr>
			<tr><td style="vertical-align:top;"><b>&raquo; '.$_TEXT['PROFILE_POINTS_LOCKED'].'</b><br>'.$_TEXT['PROFILE_POINTS_LOCKED_DESCR'].'</td><td style="text-align:right;"><input type="text" name="count_locked" style="width:50px;" value="'.format_input($udat['count_locked']).'"></td><td><nobr>x -2 '.$_TEXT['POINTS'].'</td><td>=</td><td style="text-align:right;"><nobr>-'.fnum($udat['count_locked']*2).' '.$_TEXT['POINTS'].'</td></tr>
			<tr><td class="g" colspan="4"><b>&raquo; '.$_TEXT['PROFILE_POINTS_SUM'].'</b></td><td class="g" style="text-align:right;"><nobr><b>'.fnum(user_points($_GET['user'])).' '.$_TEXT['POINTS'].'</b></td></tr>
		</table>
    		</fieldset>
		<center><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></center>
		</form>
	';
}
else if ($_GET['tab'] == 'add')
{
	if ($_POST['action'] == 'change')
	{
		$udat = IniLoad($file);
		$udat['addtext'] = format_text($_POST['addtext']);
		IniSave($file,$udat);
		echo '<div class="confirm">'.$_TEXT['AP_SAVED'].'</div>';
	}
	echo '
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab='.$_GET['tab'].'" method="post">
		<input type="hidden" name="action" value="change">
		<fieldset>
		<legend>'.$_TEXT['AP_USER_ADMIN_ADDTEXT'].' ('.$_TEXT['AP_USER_ADMIN_ADDTEXT2'].')</legend>
		<table style="width:100%;">
			<tr>
				<td style="width:30%;text-align:right;">'.$_TEXT['TIME_OF_REGISTRATION'].':</td>
				<td>'.ftime($udat['register_date']).($udat['register_ip']<>''?' (IP: '.$udat['register_ip'].')':'').'</td>
			</tr>
			<tr>
				<td style="width:30%;text-align:right;">'.$_TEXT['TIME_OF_LAST_VISIT'].':</td>
				<td>'.ftime($udat['lastonline_date']).'</td>
			</tr>

			<tr>
				<td colspan="2"><textarea name="addtext" id="addtext" style="width:98%;height:200px;" cols="30" rows="5">'.undo_ubb($udat['addtext']).'</textarea></td>
			</tr>
		</table>
    		</fieldset>
		<center><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></center>
		</form>
	';
}
else 
{
   $results = PluginHook('ap-user_edit-gettab', $_GET['tab']);
   $found = false;
   if (count($results)>0) foreach($results as $result) if ($result == true) $found = true;
   if (!$found)
   {
	if ($_POST['action'] == 'change')
	{
		$udat = IniLoad($file);
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
		IniSave($file,$udat);
		echo '<div class="confirm">'.$_TEXT['AP_SAVED'].'</div>';
	}

	echo '
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&user='.$_GET['user'].'&tab='.$_GET['tab'].'" method="post">
		<input type="hidden" name="action" value="change">
		<fieldset>
		<legend>'.$_TEXT['PROFILE_DATA'].'</legend>
		<table style="width:100%;">
			<tr>
				<td colspan="2" class="g"><b>'.$_TEXT['PROFILE_DATA_PERSONAL'].'</b></td>
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
				<td><input type="text" value="'.format_input($udat['email']).'" name="email" style="width:300px;" size="30" maxlength="100" /> '.($udat['email_old']<>''?'<br />('.$udat['email_old'].')':'').'</td>
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
		</table>
    		</fieldset>
		<center><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></center>
		</form>
	';
   }
}
}
?>