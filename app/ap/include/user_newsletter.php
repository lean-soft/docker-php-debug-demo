<?PHP

echo '
	<h1>'.$_TEXT['AP_USER_EMAIL'].'</h1>
	<table class="main">
';
if ($_GET['action'] == 'sendmail')
{
	$_POST['text'] = stripslashes($_POST['text']);

	if ($_POST['empfaenger'] == 'one')
	{
		$udat = IniLoad('../data/user/'.$_POST['to'].'.usr.ini');
		echo "<tr><td class=g>".$_POST['to']." (".$udat['email'].") ";
		if ($_FORUM['config_mail_addparameter'] <> '')
		{
			if (mail($udat['email'], $_POST['title'], str_replace("%NAME%", $_POST['to'], $_POST['text']), "From: ".$_POST['from'], $_FORUM['config_mail_addparameter']))
				{ echo '<img src="./images/ap_yes.gif" border="0">';}
			else
				{ echo '<img src="./images/ap_no.gif" border="0">';}
		}
		else
		{
			if (mail($udat['email'], $_POST['title'], str_replace("%NAME%", $_POST['to'], $_POST['text']), "From: ".$_POST['from']))
				{ echo '<img src="./images/ap_yes.gif" border="0">';}
			else
				{ echo '<img src="./images/ap_no.gif" border="0">';}
		}
		echo "</td></tr></table>";
	}
	else if ($_POST['empfaenger'] == 'group')
	{
		echo "<tr><td class=g>";
		if (file_exists('../data/user/'.$_POST['to_group'].'.grp.ini'))
		{
			$group = IniLoad('../data/user/'.$_POST['to_group'].'.grp.ini');
			foreach(Group2Array($group['members']) as $user)
			{
				$udat = IniLoad("../data/user/".$user.".usr.ini");
				echo "$user (".$udat['email'].") ";
				if ($_FORUM['config_mail_addparameter'] <> '')
				{
					if (mail($udat['email'], $_POST['title'], str_replace("%NAME%", $user, $_POST['text']), "From: ".$_POST['from'], $_FORUM['config_mail_addparameter']))
						{ echo '<img src="./images/ap_yes.gif" border="0"><br>';}
					else
						{ echo '<img src="./images/ap_no.gif" border="0"><br>';}
				}
				else
				{
					if (mail($udat['email'], $_POST['title'], str_replace("%NAME%", $user, $_POST['text']), "From: ".$_POST['from']))
						{ echo '<img src="./images/ap_yes.gif" border="0"><br>';}
					else
						{ echo '<img src="./images/ap_no.gif" border="0"><br>';}
				}
			}
		}
		echo "</td></tr>";

	}
	else
	{
		echo "<tr><td class=g>";
		$users = LoadFileList('../data/user/', '.usr.ini');
		foreach($users as $user)
		{
			$user = str_replace(".usr.ini","",$user);
			$udat = IniLoad("../data/user/".$user.".usr.ini");
			if ($udat['newsletter'])
			{
				echo "$user (".$udat['email'].") ";
				if ($_FORUM['config_mail_addparameter'] <> '')
				{
					if (mail($udat['email'], $_POST['title'], str_replace("%NAME%", $user, $_POST['text']), "From: ".$_POST['from'], $_FORUM['config_mail_addparameter']))
						{ echo '<img src="./images/ap_yes.gif" border="0"><br>';}
					else
						{ echo '<img src="./images/ap_no.gif" border="0"><br>';}
				}
				else
				{
					if (mail($udat['email'], $_POST['title'], str_replace("%NAME%", $user, $_POST['text']), "From: ".$_POST['from']))
						{ echo '<img src="./images/ap_yes.gif" border="0"><br>';}
					else
						{ echo '<img src="./images/ap_no.gif" border="0"><br>';}
				}
			}		
		}
		echo "</td></tr>";
	}
Exit;
}

echo '
	<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=sendmail" method="post">
	<tr>
		<td class="w" style="width:30%;vertical-align:top;">'.$_TEXT['AP_USER_EMAIL_TO'].'</td>
		<td class="w" style="width:70%;">
			<input type="radio" name="empfaenger" value="one" checked="checked"> <select name="to">
';
				$users = LoadFileList('../data/user/', '.usr.ini');
				foreach($users as $user)
				{
					echo '<option>'.str_replace('.usr.ini','',$user).'</option>';
				}
echo '
			</select>
			<br /><input type="radio" name="empfaenger" value="group"> <select name="to_group">
';
				$groups = LoadFileList('../data/user/', '.grp.ini');
				foreach($groups as $group)
				{
					echo '<option>'.str_replace('.grp.ini','',$group).'</option>';
				}

echo '
			</select>
			<br /><input type="radio" name="empfaenger" value="all"> '.$_TEXT['AP_USER_EMAIL_ALL'].'
		</td>
	</tr>
	<tr>
		<td class="w">'.$_TEXT['AP_USER_EMAIL_FROM'].'<br /><small>'.$_TEXT['AP_USER_EMAIL_FROM2'].'</td>
		<td class="w"><input type="text" name="from" style="width:100%;" size="30" maxlength="50" value="'.$_FORUM['settings_forum_email'].'"></td>
	</tr>
	<tr>
		<td class="w">'.$_TEXT['AP_USER_EMAIL_SUBJECT'].'</td>
		<td class="w"><input type="text" name="title" style="width:100%;" size="30" maxlength="50" value="'.$_FORUM['settings_forum_name'].'"></td>
	</tr>
	<tr>
		<td class="w" style="vertical-align:top;">'.$_TEXT['AP_USER_EMAIL_TEXT'].'<br /><small>'.$_TEXT['AP_USER_EMAIL_TEXT2'].'</td>
		<td class="w"><textarea name="text" style="width:100%;" cols="50" rows="20">Hallo %NAME%,</textarea></td>
	</tr>
	<tr>
		<td class="g" colspan="2" style="text-align:center;"><input type="submit" value="'.$_TEXT['AP_SEND'].'"></td>
	</tr>
	</table>
	</form>
';
?>