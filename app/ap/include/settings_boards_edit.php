<?PHP
$_BOARD = IniLoad('../data/'.$_GET['id'].'/board.ini');
$_BOARDS = IniLoad('../data/boards.ini');

if ($_GET['id'] == 'new')
{
	$new_id = 0;
	while(($new_id == 0) OR (IsInGroup($_BOARDS['order'], 'b'.$new_id))) $new_id++;
	if (!(is_dir('../data/'.$new_id))) 
	{
		if (ini_get("safe_mode") != "1")
		{
			mkdir('../data/'.$new_id, 0777);
			chmod('../data/'.$new_id, 0777);
		}
		if ((!is_writeable('../data/'.$new_id.'/')) OR (!(is_dir('../data/'.$new_id))))
		{
			echo '<p><img src="images/ap_no.gif" /> '.str_replace('%BOARD%', $new_id, $_TEXT['AP_BOARDS_CHMOD']).'</p>';
			Exit;
		}
	}
}

if (is_numeric($_GET['id']) && ($_GET['id'] <> ''))
{
	if (!(is_dir('../data/'.$_GET['id']))) 
	{
		if (ini_get("safe_mode") != "1")
		{
			mkdir('../data/'.$_GET['id'], 0777);
			chmod('../data/'.$_GET['id'], 0777);
		}
		if ((!is_writeable('../data/'.$_GET['id'].'/')) OR (!(is_dir('../data/'.$_GET['id']))))
		{
			echo '<p><img src="images/ap_no.gif" /> '.str_replace('%BOARD%', $_GET['id'], $_TEXT['AP_BOARDS_CHMOD']).'</p>';
			Exit;
		}
	}
}


if ($_GET['action'] == 'save')
{
	if ($_GET['id'] == 'new')
	{
		// Defaultwerte setzen
		$_BOARD['topics'] = 0;
		$_BOARD['answeres'] = 0;
		$_BOARD['auth_show'] = '*0'.$TRENNZEICHEN.'*1';
		$_BOARD['auth_read'] = '*0'.$TRENNZEICHEN.'*1';
		$_BOARD['auth_answere'] = '*1';
		$_BOARD['auth_topic'] = '*1';
		$_BOARD['auth_upload'] = '*1';
		$_BOARD['auth_download'] = '*1';
		$_BOARD['auth_poll'] = '*1';
		$_BOARD['auth_vote'] = '*1';
		$_BOARDS = IniLoad('../data/boards.ini');
		$_GET['id'] = $new_id;
		AddToGroup($_BOARDS['order'], 'b'.$new_id, false);
		$_BOARDS['b'.$new_id.'_layer'] = 1;
		IniSave('../data/boards.ini', $_BOARDS);
		RepairBoardsIni();
	}

	$_BOARD['title'] = format_string($_POST['title']);
	$_BOARD['description'] = format_string($_POST['description']);
	$_BOARD['hide'] = ($_POST['hide'] == 'on');
	IniSave('../data/'.$_GET['id'].'/board.ini', $_BOARD);

	if ($_POST['text'] == '')
	{
		@unlink('../data/'.$_GET['id'].'/infotext.txt');
	}
	else
	{
		$fp = fopen('../data/'.$_GET['id'].'/infotext.txt', "w");
		fputs($fp, stripslashes($_POST['text']));
		fclose($fp);
	}
}

echo '<h1>'.$_TEXT['AP_BOARDS'].'</h1>';

if (is_numeric($_GET['id']) && ($_GET['id'] <> ''))
{
	$infotext_filename = '../data/'.$_GET['id'].'/infotext.txt';

	if ($_GET['action'] == 'recalculate')
	{
		RepairBoardIni($_GET['id']);
	}

	if ($_GET['action'] == 'group_delete')
	{
			DeleteFromGroup($_BOARD[$_GET['group']], $_POST['user_delete']);
			if ($_POST['user_delete'] == '*1') DeleteFromGroup($_BOARD[$_GET['group']], '*0');
			if ($_GET['group'] == 'auth_show')
			{
				DeleteFromGroup($_BOARD['auth_topic'], $_POST['user_delete']);
				if ($_POST['user_delete'] == '*1') DeleteFromGroup($_BOARD['auth_topic'], '*0');
				DeleteFromGroup($_BOARD['auth_answere'], $_POST['user_delete']);
				if ($_POST['user_delete'] == '*1') DeleteFromGroup($_BOARD['auth_answere'], '*0');
				DeleteFromGroup($_BOARD['auth_read'], $_POST['user_delete']);
				if ($_POST['user_delete'] == '*1') DeleteFromGroup($_BOARD['auth_read'], '*0');
			}
			if ($_GET['group'] == 'auth_read')
			{
				DeleteFromGroup($_BOARD['auth_topic'], $_POST['user_delete']);
				if ($_POST['user_delete'] == '*1') DeleteFromGroup($_BOARD['auth_topic'], '*0');
				DeleteFromGroup($_BOARD['auth_answere'], $_POST['user_delete']);
				if ($_POST['user_delete'] == '*1') DeleteFromGroup($_BOARD['auth_answere'], '*0');
			}
			IniSave('../data/'.$_GET['id'].'/board.ini', $_BOARD);
	}
	if (($_GET['action'] == 'group_add') && ($_POST['user_add'] <> '----------------'))
	{
			AddToGroup($_BOARD[$_GET['group']], $_POST['user_add']);
			if ($_POST['user_add'] == '*0') AddToGroup($_BOARD[$_GET['group']], '*1');
			if ($_GET['group'] == 'auth_topic')
			{
				AddToGroup($_BOARD['auth_read'], $_POST['user_add']);
				if ($_POST['user_add'] == '*0') AddToGroup($_BOARD['auth_read'], '*1');
				AddToGroup($_BOARD['auth_show'], $_POST['user_add']);
				if ($_POST['user_add'] == '*0') AddToGroup($_BOARD['auth_show'], '*1');
			}
			if ($_GET['group'] == 'auth_answere')
			{
				AddToGroup($_BOARD['auth_read'], $_POST['user_add']);
				if ($_POST['user_add'] == '*0') AddToGroup($_BOARD['auth_read'], '*1');
				AddToGroup($_BOARD['auth_show'], $_POST['user_add']);
				if ($_POST['user_add'] == '*0') AddToGroup($_BOARD['auth_show'], '*1');
			}
			if ($_GET['group'] == 'auth_read')
			{
				AddToGroup($_BOARD['auth_show'], $_POST['user_add']);
				if ($_POST['user_add'] == '*0') AddToGroup($_BOARD['auth_show'], '*1');
			}
			IniSave('../data/'.$_GET['id'].'/board.ini', $_BOARD);
	}
}

function echoAuth($val, $text)
{
	GLOBAL $_BOARD, $_TEXT, $_GET;
	$list = str_replace('*0', $_TEXT['AP_GUESTS'], $_BOARD[$val]);
	$list = str_replace('*1', $_TEXT['AP_USERS'], $list);
		echo '
			<tr><td style="vertical-align:top;">'.$text.':</td><td>'.implode(', ', Group2Array($list)).'</td></tr>
			<tr><td class="border-bottom">&nbsp;</td><td class="border-bottom">
			<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.$_GET['id'].'&action=group_delete&group='.$val.'" method="post">
		';
			$groups = LoadFileList('../data/user/', '.grp.ini');
			$groups = array_diff($groups, array('Admins.grp.ini'));
			if (count($groups) > 0)
			{
				array_unshift($groups, '*0', '*1', '----------------');
			}
			else
			{
				$groups = array('*0', '*1');
			}

			echo '<select name="user_delete" style="width:100px;" class="txt">';
			foreach (Group2Array($_BOARD[$val]) as $item)
			{
				$caption = str_replace('*0', $_TEXT['AP_GUESTS'], $item);
				$caption = str_replace('*1', $_TEXT['AP_USERS'], $caption);
				echo '<option value="'.$item.'">'.$caption.'</option>';
			}
			echo '</select> <input type="submit" name="submit" value="'.$_TEXT['AP_DELETE'].'" '.($list == ''?'disabled="disabled"':'').' /> 
			</form>
			<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.$_GET['id'].'&action=group_add&group='.$val.'" method="post">
			<select name="user_add" style="width:100px;">';
			$has_item = false;
			foreach ($groups as $item)
			{
				$item = str_replace('.grp.ini', '', $item);
				if (!IsInGroup($_BOARD[$val], $item))
				{
					$caption = str_replace('*0', $_TEXT['AP_GUESTS'], $item);
					$caption = str_replace('*1', $_TEXT['AP_USERS'], $caption);
					echo '<option value="'.$item.'">'.$caption.'</option>';
					if ($caption <> '----------------') $has_item = true;
				}
			}
		echo '
			</select> <input type="submit" name="submit" value="'.$_TEXT['AP_ADD'].'" '.(!$has_item?'disabled="disabled" ':'').' /></nobr>
			</form>
			</td></tr>
		';
}

echo '
	<fieldset>
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.$_GET['id'].'&action=save" method="post">
		<table class="auto">
	 		<tr>
				<td style="width:20%;">'.$_TEXT['TITLE'].'</td>
				<td style="width:80%;"><input type="text" name="title" maxlength="50" value="'.format_input($_BOARD['title']).'" style="width:100%;"></td>
			</tr>
			<tr>
				<td>'.$_TEXT['TEXT'].'</td>
				<td><input type="text" name="description" maxlength="150" value="'.format_input($_BOARD['description']).'" style="width:100%;"></td>
			</tr>
			<tr>
				<td style="vertical-align:top;">'.$_TEXT['AP_INFOTEXT'].'</td>
				<td><textarea name="text" style="width:100%;" rows="10">'; if (file_exists($infotext_filename)) readfile($infotext_filename); echo '</textarea></td>
			</tr>
			<tr>
				<td colspan="2"><input type="checkbox" name="hide" id="hide" '.($_BOARD['hide']?'checked="checked"':'').' /> <label for="hide">'.$_TEXT['AP_BOARDS_HIDE'].'</td>
			</tr>
			<tr>
				<td style="text-align:center;" colspan="2"><INPUT TYPE="SUBMIT" name="submit" VALUE="'.$_TEXT['AP_SAVE'].'"></td>
			</tr>
		</table>
		</form>
	</fieldset>
';

if (is_numeric($_GET['id']) && ($_GET['id'] <> ''))
{
echo '
	<fieldset>
		<legend>'.$_TEXT['TOPICS'].' & '.$_TEXT['ANSWERES'].'</legend>
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.$_GET['id'].'&action=recalculate" method="post">
		<table class="auto">
		<tr>
			<td>'.$_TEXT['TOPICS'].': '.fnum($_BOARD['topics']).' | '.$_TEXT['ANSWERES'].': '.fnum($_BOARD['answeres']).'</td>
			<td style="text-align:center;"><input type=submit value="'.$_TEXT['AP_BOARDS_RECALCULATE'].'"></td>
		</tr>
		</table>
		</form>
	</fieldset>

	<fieldset>
		<legend>'.$_TEXT['AP_BOARDS_AUTH'].'</legend>
		<table class="auto">
';
		echoAuth('auth_show',  $_TEXT['AP_BOARDS_AUTH_SHOW']);
		echoAuth('auth_read',  $_TEXT['AP_BOARDS_AUTH_READ']);
		echoAuth('auth_download', $_TEXT['AP_BOARDS_AUTH_DOWNLOAD']);
		echoAuth('auth_answere',  $_TEXT['AP_BOARDS_AUTH_ANSWERE']);
		echoAuth('auth_topic', $_TEXT['AP_BOARDS_AUTH_TOPIC']);
		echoAuth('auth_upload', $_TEXT['AP_BOARDS_AUTH_UPLOAD']);
		echoAuth('auth_poll', $_TEXT['AP_BOARDS_AUTH_POLL']);
		echoAuth('auth_vote', $_TEXT['AP_BOARDS_AUTH_VOTE']);
		echoAuth('auth_rating', $_TEXT['AP_BOARDS_AUTH_RATING']);
		PluginHook('ap-boards_edit-echoauth');
echo '
		</table>
	</fieldset>	


	<fieldset>
		<legend>'.$_TEXT['MODERATOR'].'</legend>
		<table class="auto">
';
if ($_BOARD['mods'] != '')
{
    echo '
		<tr><td>'.implode(', ', Group2Array($_BOARD['mods'])).'</td></tr>
		<tr><td>
			<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.$_GET['id'].'&action=group_delete&group=mods" method="post">
			<select name="user_delete" style="width:150px;">
    ';
			foreach (Group2Array($_BOARD['mods']) as $item)	echo '<option value="'.$item.'">'.$item.'</option>';
    echo '
			</select> <input type="submit" name="submit" value="'.$_TEXT['AP_DELETE'].'"> 
			</form>
    ';
}
else
{
    echo '
		<tr><td>
    ';
}
echo '
			<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.$_GET['id'].'&action=group_add&group=mods" method="post">
			<select name="user_add" style="width:150px;">
';
			foreach (LoadFileList('../data/user/', '.usr.ini') as $item)
			{
				$item = str_replace('.usr.ini', '', $item);
				if (!IsInGroup($_BOARD['mods'], $item))	echo '<option value="'.$item.'">'.$item.'</option>';
			}
echo '
			</select> <input type="submit" name="submit" value="'.$_TEXT['AP_ADD'].'">
			</form>
		</td></tr>
		</table>
	</fieldset>
  ';
}
?>