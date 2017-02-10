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

function create_headline($label)
{
	return '</table></fieldset><fieldset><legend>'.$label.'</legend><table class="auto">';
}

function create_input($name, $label, $label2 = '', $size = '250px', $maxsize = '', $behind = '')
{
	GLOBAL $_FORUM;
	return '
		<tr>
			<td style="width:50%;"><label for="'.$name.'">'.$label.($label2<>''?'<br /><small>'.$label2.'</small>':'').'</label></td>
			<td style="width:50%;"><input type="text" name="'.$name.'" id="'.$name.'" '.($size<>''?'style="width:'.$size.';" ':'').($maxsize<>''?'maxlength="'.$maxsize.'"':'').' value="'.format_input($_FORUM[$name]).'" /> '.$behind.'</td>
		</tr>
	';
}

function create_inputs($name1, $label1, $width1 = '250px', $maxsize1 = '', $name2, $label2, $width2 = '250px', $maxsize2 = '')
{
	GLOBAL $_FORUM;
	return '
		<tr>
			<td style="width:50%;"><label for="'.$name1.'">'.$label1.'</label></td>
			<td style="width:50%;">
				'.($name1<>''?'<input type="text" name="'.$name1.'" id="'.$name1.'" '.($width1<>''?'style="width:'.$width1.';" ':'').($maxsize1<>''?'maxlength="'.$maxsize1.'"':'').' value="'.format_input($_FORUM[$name1]).'" /> ':'').'
				<label for="'.$name2.'">'.$label2.'</label>
				<input type="text" name="'.$name2.'" id="'.$name2.'" '.($width2<>''?'style="width:'.$width2.';" ':'').($maxsize2<>''?'maxlength="'.$maxsize2.'"':'').' value="'.format_input($_FORUM[$name2]).'" /> 
			</td>
		</tr>
	';
}

function create_box($name, $label, $label_kl = '', $name2 = '', $label2 = '', $disabled = false)
{
	GLOBAL $_FORUM, $_TEXT;
	return '
		<tr>
			<td style="width:50%;">'.$label.($label_kl<>''?'<br /><small>'.$label_kl.'</small>':'').'</td>
			<td style="width:50%;">
				<input type="checkbox" '.($disabled?'disabled="disabled" ':'').'name="'.$name.'" id="'.$name.'"'.(($_FORUM[$name] AND (!($disabled)))?' checked="checked"':'').' /> <label for="'.$name.'">'.$_TEXT['AP_ACTIVATE'].'</label>
				'.($name2<>''?'<br /><label for="'.$name2.'">'.$label2.'</label> <input type="text" name="'.$name2.'" id="'.$name2.'" style="width:30px;" maxlength="2" value="'.format_input($_FORUM[$name2]).'" />':'').'
			</td>
		</tr>
	';
}

function create_guestuser($name, $label, $label_kl = '', $disabled = false)
{
	GLOBAL $_FORUM, $_TEXT;
	return '
		<tr>
			<td style="width:50%;">'.$label.($label_kl<>''?'<br /><small>'.$label_kl.'</small>':'').'</td>
			<td style="width:50%;">
				<input type="checkbox" '.($disabled?'disabled="disabled" ':'').'name="'.$name.'_guest" id="'.$name.'_guest"'.(($_FORUM[$name.'_guest'] AND (!($disabled)))?' checked="checked"':'').' /> <label for="'.$name.'_guest">'.$_TEXT['AP_GUESTS'].'</label>
				<input type="checkbox" '.($disabled?'disabled="disabled" ':'').'name="'.$name.'_user" id="'.$name.'_user"'.(($_FORUM[$name.'_user'] AND (!($disabled)))?' checked="checked"':'').' /> <label for="'.$name.'_user">'.$_TEXT['AP_USERS'].'</label>
			</td>
		</tr>
	';
}


if (($_GET['action'] == 'change') && ($_POST['settings_forum_language'] <> ''))
{
	if (trim($_POST['avatar_type_delete']) <> '---')
	{
		DeleteFromGroup($_FORUM['settings_system_upload_avatar_formats'], trim($_POST['avatar_type_delete']));
	}
	if (trim($_POST['avatar_type_add']) <> '')
	{
		AddToGroup($_FORUM['settings_system_upload_avatar_formats'], trim(strtolower($_POST['avatar_type_add'])));
	}

	if (trim($_POST['file_type_delete']) <> '---')
	{
		DeleteFromGroup($_FORUM['settings_system_upload_file_formats'], trim($_POST['file_type_delete']));
	}
	if (trim($_POST['file_type_add']) <> '')
	{
		AddToGroup($_FORUM['settings_system_upload_file_formats'], trim(strtolower($_POST['file_type_add'])));
	}

	if (!is_numeric($_POST['settings_user_ranking2'])) $_POST['settings_user_ranking2'] = 50;
	if (!is_numeric($_POST['settings_user_ranking3'])) $_POST['settings_user_ranking3'] = 100;
	if (!is_numeric($_POST['settings_user_ranking4'])) $_POST['settings_user_ranking4'] = 250;
	if (!is_numeric($_POST['settings_user_ranking5'])) $_POST['settings_user_ranking5'] = 500;
	if ($_POST['settings_timeformat'] == '') $_POST['settings_timeformat'] = 'd.m.y H:i';
	if (!is_numeric($_POST['settings_timedif'])) $_POST['settings_timedif'] = '0';

	$index = 'settings_design_lastposts_count';
	if ((!is_numeric($_POST[$index])) OR ($_POST[$index] < 1) OR ($_POST[$index]>50)) $_POST[$index] = '10';
	$index = 'settings_design_rss_count';
	if ((!is_numeric($_POST[$index])) OR ($_POST[$index] < 1) OR ($_POST[$index]>50)) $_POST[$index] = '10';
	$index = 'settings_design_javascript_count';
	if ((!is_numeric($_POST[$index])) OR ($_POST[$index] < 1) OR ($_POST[$index]>50)) $_POST[$index] = '10';
	$index = 'settings_system_upload_file_size';
	if ((!is_numeric($_POST[$index])) OR ($_POST[$index] < 10) OR ($_POST[$index]>99999)) $_POST[$index] = '1000';
	$index = 'settings_system_upload_avatar_size';
	if ((!is_numeric($_POST[$index])) OR ($_POST[$index] < 10) OR ($_POST[$index]>99999)) $_POST[$index] = '500';
	$index = 'settings_system_upload_avatar_pixel';
	if ((!is_numeric($_POST[$index])) OR ($_POST[$index] < 10) OR ($_POST[$index]>99999)) $_POST[$index] = '100';

	$_FORUM['settings_forum_name'] = format_string($_POST['settings_forum_name']);
	$_FORUM['settings_forum_header'] = format_string($_POST['settings_forum_header'], false);
	$_FORUM['settings_forum_logo'] = format_string($_POST['settings_forum_logo']);
	$_FORUM['settings_forum_url'] = format_string(trim($_POST['settings_forum_url'], '/\\'));
	$_FORUM['settings_forum_description'] = format_string($_POST['settings_forum_description']);
	$_FORUM['settings_forum_keywords'] = format_string($_POST['settings_forum_keywords']);
	$_FORUM['settings_forum_email'] = format_string($_POST['settings_forum_email']);
	$_FORUM['settings_forum_language'] = $_POST['settings_forum_language'];
	$_FORUM['settings_design_style'] = $_POST['settings_design_style'];
	$_FORUM['settings_design_showstat_guest'] = (($_POST['settings_design_showstat_guest'] == 'on'));
	$_FORUM['settings_design_showstat_user'] = (($_POST['settings_design_showstat_guest'] == 'on') OR ($_POST['settings_design_showstat_user'] == 'on'));
	$_FORUM['settings_design_showwhois_guest'] = (($_POST['settings_design_showwhois_guest'] == 'on'));
	$_FORUM['settings_design_showwhois_user'] = (($_POST['settings_design_showwhois_guest'] == 'on') OR ($_POST['settings_design_showwhois_user'] == 'on'));
	$_FORUM['settings_design_showlast_guest'] = (($_POST['settings_design_showlast_guest'] == 'on'));
	$_FORUM['settings_design_showlast_user'] = (($_POST['settings_design_showlast_guest'] == 'on') OR ($_POST['settings_design_showlast_user'] == 'on'));
	$_FORUM['settings_design_ranking_guest'] = (($_POST['settings_design_ranking_guest'] == 'on'));
	$_FORUM['settings_design_ranking_user'] = (($_POST['settings_design_ranking_guest'] == 'on') OR ($_POST['settings_design_ranking_user'] == 'on'));
	$_FORUM['settings_design_lastposts_count'] = $_POST['settings_design_lastposts_count'];
	$_FORUM['settings_design_rss'] = $_POST['settings_design_rss'] == 'on';
	$_FORUM['settings_design_rss_count'] = $_POST['settings_design_rss_count'];
	$_FORUM['settings_design_javascript'] = $_POST['settings_design_javascript'] == 'on';
	$_FORUM['settings_design_javascript_count'] = $_POST['settings_design_javascript_count'];
	$_FORUM['settings_admin_edit'] = $_POST['settings_admin_edit'] == 'on';
	$_FORUM['settings_admin_notification'] = $_POST['settings_admin_notification'] == 'on';
	$_FORUM['settings_register_nonewuser'] = $_POST['settings_register_nonewuser'] == 'on';
	$_FORUM['settings_register_rules'] = $_POST['settings_register_rules'] == 'on';
	$_FORUM['settings_register_code'] = $_POST['settings_register_code'] == 'on';
	$_FORUM['settings_user_ranking2'] = $_POST['settings_user_ranking2'];
	$_FORUM['settings_user_ranking3'] = $_POST['settings_user_ranking3'];
	$_FORUM['settings_user_ranking4'] = $_POST['settings_user_ranking4'];
	$_FORUM['settings_user_ranking5'] = $_POST['settings_user_ranking5'];
	$_FORUM['settings_user_ranking1_text'] = $_POST['settings_user_ranking1_text'];
	$_FORUM['settings_user_ranking2_text'] = $_POST['settings_user_ranking2_text'];
	$_FORUM['settings_user_ranking3_text'] = $_POST['settings_user_ranking3_text'];
	$_FORUM['settings_user_ranking4_text'] = $_POST['settings_user_ranking4_text'];
	$_FORUM['settings_user_ranking5_text'] = $_POST['settings_user_ranking5_text'];
	$_FORUM['settings_user_guestbook'] = $_POST['settings_user_guestbook'] == 'on';
	$_FORUM['settings_user_hidestatus'] = $_POST['settings_user_hidestatus'] == 'on';
	$_FORUM['settings_directanswer'] = $_POST['settings_directanswer'] == 'on';
	$_FORUM['settings_post_code_guest'] = (($_POST['settings_post_code_guest'] == 'on') OR ($_POST['settings_post_code_user'] == 'on'));
	$_FORUM['settings_post_code_user'] = (($_POST['settings_post_code_user'] == 'on'));
	$_FORUM['settings_loading'] = $_POST['settings_loading'];
	$_FORUM['settings_system_shorturls'] = $_POST['settings_system_shorturls'] == 'on';
	$_FORUM['settings_system_upload_file'] = $_POST['settings_system_upload_file'] == 'on';
	$_FORUM['settings_system_upload_file_size'] = $_POST['settings_system_upload_file_size'];
	$_FORUM['settings_system_upload_avatar'] = $_POST['settings_system_upload_avatar'] == 'on';
	$_FORUM['settings_system_upload_avatar_size'] = $_POST['settings_system_upload_avatar_size'];
	$_FORUM['settings_system_upload_avatar_pixel'] = $_POST['settings_system_upload_avatar_pixel'];
	$_FORUM['settings_timeformat'] = $_POST['settings_timeformat'];
	$_FORUM['settings_timedif'] = $_POST['settings_timedif'];
	$_FORUM['settings_iplock'] = $_POST['settings_iplock'] == 'on';
	PluginHook('ap-settings-save');
	IniSave('../data/forum.ini', $_FORUM);

}

echo '
	<h1>'.$_TEXT['AP_SETTINGS'].'</h1>

	<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=change" method="post">
	<fieldset><legend>'.$_TEXT['AP_SETTINGS'].'</legend><table class="auto">
		'.create_input('settings_forum_name', $_TEXT['AP_SETTINGS_FORUM_NAME'], $_TEXT['AP_SETTINGS_FORUM_NAME2']).'
		'.create_input('settings_forum_header', $_TEXT['AP_SETTINGS_FORUM_HEADER'], $_TEXT['AP_SETTINGS_FORUM_HEADER2']).'
		'.create_input('settings_forum_logo', $_TEXT['AP_SETTINGS_FORUM_LOGO'], $_TEXT['AP_SETTINGS_FORUM_LOGO2']).'
		'.create_input('settings_forum_url', $_TEXT['AP_SETTINGS_FORUM_URL'], $_TEXT['AP_SETTINGS_FORUM_URL2']).'
		'.create_input('settings_forum_description', $_TEXT['AP_SETTINGS_FORUM_DESCRIPTION']).'
		'.create_input('settings_forum_keywords', $_TEXT['AP_SETTINGS_FORUM_KEYWORDS']).'
		'.create_input('settings_forum_email', $_TEXT['AP_SETTINGS_EMAIL'], $_TEXT['AP_SETTINGS_EMAIL2']).'
		<tr>
			<td><label for="settings_forum_language">'.$_TEXT['AP_LANGUAGE'].'</label></td>
			<td><select name="settings_forum_language" id="settings_forum_language">
';
			foreach ($LANGUAGES as $item)
			{
				echo '<option value="'.$item['file'].'"'.($item['file'] == $_FORUM['settings_forum_language']?' selected="selected"':'').'>'.$item['name'].'</option>';
			}
echo '
			</select></td>
		</tr>
';
PluginHook('ap-settings-settings');
echo '
		'.create_headline($_TEXT['AP_SETTINGS_DESIGN']).'
		<tr>
			<td><label for="settings_design_style">'.$_TEXT['AP_SETTINGS_DESIGN_STYLE'].'</label></td>
			<td><select name="settings_design_style" id="settings_design_style">
';
			$list = LoadFileList('../styles/');
			foreach ($list as $item)
			{
				echo '<option value="'.$item.'"'.($item == $_FORUM['settings_design_style']?' selected="selected"':'').'>'.$item.'</option>';
			}
echo '
			</select></td>
		</tr>
		'.create_guestuser('settings_design_showstat', $_TEXT['AP_SETTINGS_DESIGN_STAT']).'
		'.create_guestuser('settings_design_showwhois', $_TEXT['AP_SETTINGS_DESIGN_WHOISONLINE']).'
		<tr>
			<td>'.$_TEXT['AP_SETTINGS_DESIGN_LASTPOSTS'].'</td>
			<td>
				<input type="checkbox"  NAME="settings_design_showlast_guest" id="settings_design_showlast_guest" '.($_FORUM['settings_design_showlast_guest']?'checked="checked" ':'').'/> <label for="settings_design_showlast_guest">'.$_TEXT['AP_GUESTS'].'</label>
				<input type="checkbox"  NAME="settings_design_showlast_user" id="settings_design_showlast_user" '.($_FORUM['settings_design_showlast_user']?'checked="checked" ':'').'/> <label for="settings_design_showlast_user">'.$_TEXT['AP_USERS'].'</label>
				<br /><label for="settings_design_lastposts_count">'.$_TEXT['AP_SETTINGS_DESIGN_LASTPOSTS_COUNT'].'</label> <input type="text" name="settings_design_lastposts_count" id="settings_design_lastposts_count" style="width:30px;" maxlength="2" value="'.format_input($_FORUM['settings_design_lastposts_count']).'" />
			</td>
		</tr>
		'.create_guestuser('settings_design_ranking', $_TEXT['AP_SETTINGS_DESIGN_RANKING']).'
		'.create_box('settings_design_rss', $_TEXT['AP_SETTINGS_DESIGN_RSS'], '', 'settings_design_rss_count', $_TEXT['AP_SETTINGS_DESIGN_LASTPOSTS_COUNT']).'
		'.create_box('settings_design_javascript', $_TEXT['AP_SETTINGS_DESIGN_JAVASCRIPT'], '', 'settings_design_javascript_count', $_TEXT['AP_SETTINGS_DESIGN_LASTPOSTS_COUNT']).'
		<tr>
			<td><label for="settings_loading">'.$_TEXT['AP_SETTINGS_LOADING'].'</label></td>
			<td>
				<select name="settings_loading" id="settings_loading">
';
$items = array($_TEXT['AP_SETTINGS_LOADING_OFF'], $_TEXT['AP_SETTINGS_LOADING_ON'], $_TEXT['AP_SETTINGS_LOADING_PREMIUM']);
$values = array('off', 'on', 'premium');
for ($i = 0; $i < count($items); $i++) echo '<option value="'.$values[$i].'"'.($values[$i]==$_FORUM['settings_loading']?' selected="selected"':'').'>'.$items[$i].'</option>';
echo '
				</select>
			</td>
		</tr>
';
PluginHook('ap-settings-design');
echo '
		'.create_headline($_TEXT['AP_SETTINGS_ADMIN']).'
		<tr>
			<td>'.$_TEXT['AP_SETTINGS_ADMIN_GROUP'].'</td>
			<td><a href="?nav=user&page=groups_edit&group=Admins">'.$_TEXT['AP_SETTINGS_ADMIN_GROUP_EDIT'].'</a></td>
		</tr>
		'.create_box('settings_admin_edit', $_TEXT['AP_SETTINGS_ADMIN_EDIT']).'
		'.create_box('settings_admin_notification', $_TEXT['AP_SETTINGS_ADMIN_NOTIFICATION']).'

		'.create_headline($_TEXT['AP_SETTINGS_USER']).'
		'.create_box('settings_register_nonewuser', $_TEXT['AP_SETTINGS_USER_NO_NEW']).'
		'.create_box('settings_register_rules', $_TEXT['AP_SETTINGS_USER_RULES']).'
		'.create_box('settings_register_code', $_TEXT['AP_SETTINGS_USER_CODE'], null, null, null, !extension_loaded('gd')).'
		'.create_inputs('', MultiReplace($_TEXT['AP_SETTINGS_USER_RANKING'], 1), '50px', 4, 'settings_user_ranking1_text', '0 | '.$_TEXT['AP_SETTINGS_USER_RANKING_STATUS'].':', '150px', 50).'
		'.create_inputs('settings_user_ranking2', MultiReplace($_TEXT['AP_SETTINGS_USER_RANKING'], 2), '50px', 4, 'settings_user_ranking2_text', '| '.$_TEXT['AP_SETTINGS_USER_RANKING_STATUS'].':', '150px', 50).'
		'.create_inputs('settings_user_ranking3', MultiReplace($_TEXT['AP_SETTINGS_USER_RANKING'], 3), '50px', 4, 'settings_user_ranking3_text', '| '.$_TEXT['AP_SETTINGS_USER_RANKING_STATUS'].':', '150px', 50).'
		'.create_inputs('settings_user_ranking4', MultiReplace($_TEXT['AP_SETTINGS_USER_RANKING'], 4), '50px', 4, 'settings_user_ranking4_text', '| '.$_TEXT['AP_SETTINGS_USER_RANKING_STATUS'].':', '150px', 50).'
		'.create_inputs('settings_user_ranking5', MultiReplace($_TEXT['AP_SETTINGS_USER_RANKING'], 5), '50px', 4, 'settings_user_ranking5_text', '| '.$_TEXT['AP_SETTINGS_USER_RANKING_STATUS'].':', '150px', 50).'
		'.create_box('settings_user_guestbook', $_TEXT['AP_SETTINGS_USER_GUESTBOOK']).'
		'.create_box('settings_user_hidestatus', $_TEXT['AP_SETTINGS_USER_HIDESTATUS']).'
		'.create_box('settings_directanswer', $_TEXT['AP_SETTINGS_DIRECTANSWER']).'
		'.create_guestuser('settings_post_code', $_TEXT['AP_SETTINGS_POST_CODE'], !extension_loaded('gd')).'
		'.create_headline($_TEXT['AP_SETTINGS_SYSTEM']).'
		'.create_box('settings_system_shorturls', $_TEXT['AP_SETTINGS_SYSTEM_SHORTURLS'], $_TEXT['AP_SETTINGS_SYSTEM_SHORTURLS2']).'

		'.create_box('settings_system_upload_file', '<b>'.$_TEXT['AP_SETTINGS_SYSTEM_UPLOAD_FILE'].'</b><br /><small>'.$_TEXT['AP_SETTINGS_SYSTEM_UPLOAD_FILE2'].'</small>').'
		<tr><td>'.$_TEXT['AP_UPLOAD_TYPE'].'</td><td>
			<select name="file_type_delete"><option>---</option>
';
			foreach (Group2Array($_FORUM['settings_system_upload_file_formats']) as $item) echo '<option>'.$item.'</option>';
echo '
			</select> <input type="submit" value="'.$_TEXT['AP_DELETE'].'" /> <input type="text" name="file_type_add" style="width:40px;" /> <input type="submit" value="'.$_TEXT['AP_ADD'].'" />
		</td></tr>
		'.create_input('settings_system_upload_file_size', $_TEXT['AP_UPLOAD_SIZE'], '', '50px', '5', 'kB').'

		'.create_box('settings_system_upload_avatar', '<b>'.$_TEXT['AP_SETTINGS_SYSTEM_UPLOAD_AVATAR'].'</b>').'
		<tr><td>'.$_TEXT['AP_UPLOAD_TYPE'].'</td><td>
			<select name="avatar_type_delete"><option>---</option>
';
			foreach (Group2Array($_FORUM['settings_system_upload_avatar_formats']) as $item) echo '<option>'.$item.'</option>';
echo '
			</select> <input type="submit" value="'.$_TEXT['AP_DELETE'].'" /> <input type="text" name="avatar_type_add" style="width:40px;" /> <input type="submit" value="'.$_TEXT['AP_ADD'].'" />
		</td></tr>
		'.create_input('settings_system_upload_avatar_size', $_TEXT['AP_UPLOAD_SIZE'], '', '50px', '5', 'kB').'
		'.create_input('settings_system_upload_avatar_pixel', $_TEXT['AP_UPLOAD_PIXEL'], '', '50px', '5', 'px').'
';
PluginHook('ap-settings-admin');
echo '
		'.create_headline($_TEXT['AP_SETTINGS_OTHER']).'
		'.create_input('settings_timeformat', $_TEXT['AP_SETTINGS_OTHER_TIMEFORMAT'], $_TEXT['AP_SETTINGS_OTHER_TIMEFORMAT2']).'
		'.create_input('settings_timedif', $_TEXT['AP_SETTINGS_OTHER_TIMEDIF'], $_TEXT['AP_SETTINGS_OTHER_TIMEDIF2'].': '.date('d.m.Y H:i', time())).'
		'.create_box('settings_iplock', $_TEXT['AP_SETTINGS_OTHER_IPLOCK'], $_TEXT['AP_SETTINGS_OTHER_IPLOCK2']).'
';
PluginHook('ap-settings-other');
echo '
	</table></fieldset>
	<p style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['AP_SAVE'].'"> <input type="reset" name="reset" value="'.$_TEXT['AP_CANCEL'].'"></p>
	</form>
';
?>