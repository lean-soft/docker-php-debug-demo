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

$_BOARD = IniLoad('data/'.$_GET['board'].'/board.ini');

if ($_GET['do'] == "newthread")
{
	auth('auth_topic');
}
else
{
	if (!((file_exists('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')) && (is_numeric($_GET['board'])) && (is_numeric($_GET['thema']))))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require 'index.php';
		Exit;
	}

	$_THEMA = IniLoad('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');

	if ($_GET['do'] == "reply")
	{
		auth('auth_answere');
	}

	$data = FileLoad("data/".$_GET['board']."/".$_GET['thema'].".txt");

	if ($_GET['do'] == "edit")
	{
		if (
			($_SESSION['Benutzername'] != $data[$_GET['beitrag']][1])
		    AND
			(!($_FORUM['settings_admin_edit']  AND IsMod($_GET['board'], $_SESSION['Benutzername'])))
		   )
		{
			$MSG_ERROR = $_TEXT['ERROR_WRONG_LOGIN'];
			require 'thread.php';
			Exit;
		}
	}
}

$signatur = '';
if (IsUser())
{
	$udat = IniLoad('./data/user/'.$_SESSION['Benutzername'].'.usr.ini');
	if (defined('CONFIG_PROFILE_SIGNATURE_AUTOAPPEND') && (CONFIG_PROFILE_SIGNATURE_AUTOAPPEND == 1))
	{
		$signatur = '';
	}
	else
	{
		$signatur = ($udat['signature']<>''?"\n".undo_ubb($udat['signature']):'');
	}
	$post_no_autolink = ($udat['settings_post_no_autolink']?'checked="checked"':'');
	$emailben = ($udat['settings_post_notification']?'checked="checked"':'');
}

$_SUBNAV = array_merge($_SUBNAV, GetBoardParents($_GET['board']));
$_SUBNAV[] = array($_BOARD['title'], url('board.php', $_GET['board']), 'boardS.png');
if ($_GET['do'] =="newthread") 
{
	$_SUBNAV_BOTTOM = $_BOARD['description'];
}
else if ($_GET['do'] =="reply") 
{
	$_SUBNAV[] = array($_THEMA['title'], url('thread.php', $_GET['board'], $_GET['thema']), 'threadS.png');
} 
else if ($_GET['do'] =="edit") 
{
	$_SUBNAV[] = array($_THEMA['title'], url('thread.php', $_GET['board'], $_GET['thema']), 'threadS.png');
}
require_once 'include/page_top.php';


if ($_GET['do'] =="newthread") 
{
	$htext = $_TEXT['NEW_TOPIC'];
	$name = $_SESSION['Benutzername'];
	$title = "";
	$text = $signatur;
	$url = 'do.php?do='.$_GET['do'].'&board='.$_GET['board']; 
	$attachment = '';
	$poll = '';
	PluginHook('post-newthread');
} 
else if ($_GET['do'] =="reply") 
{
	$htext = $_TEXT['ANSWER'];
	$name = $_SESSION['Benutzername'];
	$title = 'Re: '.$data[0][2];
	if ($_GET['beitrag']!="") 
	{
		$data = file("./data/".$_GET['board']."/".$_GET['thema'].".txt");
		$data = explode($TRENNZEICHEN, $data[$_GET['beitrag']]);
		$text = "[quote=".$data[1]."]\n".undo_ubb($data[3])."\n[/quote]\n".$signatur;
		if (strlen($text) > CONFIG_POST_MAXCHAR)
		{
			$text = "[quote=".$data[1]."]\n".substr(undo_ubb($data[3]), 0, CONFIG_POST_MAXCHAR-strlen($text))."\n[/quote]\n".$signatur;
		}
	}
	else 
	{
		$text = $signatur;
	}
	if (IsInGroup($_THEMA['notification'], $name)) $emailben = 'checked="checked"';
	$url = 'do.php?do='.$_GET['do'].'&board='.$_GET['board'].'&thema='.$_GET['thema']; 
	$attachment = '';
	$poll = '';
	PluginHook('post-reply');
} 
else if ($_GET['do'] =="edit") 
{
	$htext = $_TEXT['EDIT'];
	$name = $data[$_GET['beitrag']][1];
	$title = $data[$_GET['beitrag']][2];
	$text = undo_ubb($data[$_GET['beitrag']][3]);
	if (IsInGroup($_THEMA['notification'], $name)) $emailben = 'checked="checked"';
	$url = 'do.php?do='.$_GET['do'].'&board='.$_GET['board'].'&thema='.$_GET['thema'].'&beitrag='.$_GET['beitrag']; 
	$attachment = SubGroup2Group($data[$_GET['beitrag']][5]);
	$poll = trim($data[$_GET['beitrag']][6]);
	PluginHook('post-edit');
} 
else
{
	require_once 'thread.php';
	Exit;
}



if ($preview)
{
 	echo '
		<div id="content">
		<table class="main">
		<tr><td class="oben">'.$_TEXT['PREVIEW'].'</td></tr>
		<tr><td class="w" style="width:100%;">
			<div style="width:auto;overflow:hidden;">
			<b>'.format_string($_POST['titel']).'</b><p>'.format_post(format_text($_POST['text'])).'</p>
			</div>
		</td></tr>
		</table>
		</div>
	';
}

if ($loadfrompost)
{
	$name = $_POST['name'];
	$title = format_input(format_string($_POST['titel']));
	$text = htmlentities($_POST['text']);
	$emailben = ($_POST['emailben']?'checked="checked"':'');
	$attachment = $_POST['attachment'];
	$poll = $_POST['poll'];
	$post_no_autolink = ($_POST['post_no_autolink'] == 'on'?'checked="checked"':'');
	PluginHook('post-loadfrompost');
}

echo '
	<div id="content">
	<table class="main">
	<tr>
		<td class="oben">'.$htext.'</td>
	</tr>
	<tr>
		<td class=g>
			<form action="'.url($url).'" method="post" name="post" enctype="multipart/form-data" onSubmit="showLoading();">
			<table style="width:100%;table-layout:fixed;">
			<tr>
				<td style="width:20%;"><label for="name">'.$_TEXT['LOGIN_USERNAME'].':</label></td>
				<td style="width:80%;">
';
				if (($_SESSION['Benutzername'] != '') && (!(($_GET['do'] == "edit") && ($name != $_SESSION['Benutzername']))))
				{
					echo user($name).' [<a href="'.url('index.php?action=logout').'">'.$_TEXT['LOGIN_LOGOUT'].'</a>]<INPUT TYPE="hidden" value="'.$_SESSION['Benutzername'].'" name="name">';
				}
				else
				{
					if ($_GET['do'] == 'edit')
					{
						echo user($name).' <input type="hidden" value="'.$name.'" name="name" id="name">';
					}
					else
					{
						echo '<input type="text" value="'.$name.'" name="name" id="name" size="20" MAXLENGTH="30" tabindex="1"> [<a href="'.url('login.php').'">'.$_TEXT['LOGIN_REQUIRED'].'</a>]';
					}
				}
echo '
				</td>
			</tr>
			<tr>
				<td><label for="titel">'.$_TEXT['TITLE'].':</label></td>
				<td><input type="text" value="'.format_input($title).'" name="titel" id="titel" style="width:100%" maxlength="100" tabindex="3"></td>
			</tr>
';
$smilies = IniLoad('styles/'.STYLE.'/smilies/smilies.ini');		
if (count($smilies) > 0) 
{
	echo '
			<tr>
				<td colspan="2" class="w"><div id="emoticons">
	';
	foreach (array_keys($smilies) as $item)
	{
		echo '<a title="'.$item.'" style="cursor:hand;cursor:pointer;"><img src="styles/'.STYLE.'/smilies/'.$smilies[$item].'" border="0" alt="'.$item.'"></a> ';
	}
	echo '
				</div></td>
			</tr>
	';
}
echo '
			<tr>
				<td colspan="2">
					<textarea id="text" name="text" class="textarea" style="width:100%;" cols="100" rows="15" onKeyUp="showChars()" maxlength="'.CONFIG_POST_MAXCHAR.'" tabindex="4">'.$text.'</textarea><br /><span name="char_viewer" id="char_viewer">'.CONFIG_POST_MAXCHAR.'</span> '.$_TEXT['TEXT_CHARS'].'
				</td>
			</tr>
';

$show_attachment = (($_FORUM['settings_system_upload_file']) && (auth('auth_upload', false)));
$show_poll = auth('auth_poll', false);

echo '
			<tr><td colspan="2" class="w">
				<div id="options" style="display:none;">
					<a href="javascript:showOption(\'settings\');">'.$_TEXT['SETTINGS'].'</a>
';
					if ($show_attachment) echo ' | <a href="javascript:showOption(\'attachment\');">'.$_TEXT['UPLOAD_ATT'].'</a>';
					if ($show_poll) echo ' | <a href="javascript:showOption(\'poll\');">'.$_TEXT['POLL'].'</a>';
					PluginHook('post-option_select');
echo '
				</div>
			</td></tr>
			<tr><td colspan="2">
';
if ($show_attachment)
{
	echo '
				<fieldset id="attachment">
				<input type="hidden" name="attachment" value="'.$attachment.'" />
				<legend>'.$_TEXT['UPLOAD_ATT'].'</legend>
	';
	if (strlen($attachment) > 8)
	{
		echo ' 
				<table>
		';
		foreach (Group2Array($attachment) as $att)
		{
			show_att($att, false, true);
		}
		echo '
				</table>
		';
	}
	echo ' 
				'.$_TEXT['UPLOAD_NEW'].': <input name="upload" type="file"> <input type="submit" name="submit" value="'.$_TEXT['UPLOAD'].'">
				<br /><small>'.$_TEXT['UPLOAD_TYPE'].': '.implode(', ', Group2Array($_FORUM['settings_system_upload_file_formats'])).' | '.$_TEXT['UPLOAD_SIZE'].': '.$_FORUM['settings_system_upload_file_size'].' kB</small>
				</fieldset>
	';
}
if ($show_poll)
{
	echo '
				<fieldset id="poll">
				<input type="hidden" name="poll" value="'.$poll.'" />
				<legend>'.$_TEXT['POLL'].'</legend>
	';
	if ($poll <> '')
	{
		$poll_ini = IniLoad('data/poll_'.$poll.'.ini');
		echo '
				<table>
				<tr>
					<td style="width:20%; text-align:right;"><label for="poll_q">'.$_TEXT['POLL_QUESTION'].':</label></td>
					<td style="width:80%;"><input type="text" value="'.format_input($poll_ini['q']).'" name="poll_q" id="poll_q" style="width:90%;" /></td>
				</tr>
		';
		for ($i = 1; $i <= 10; $i++)
		{
			echo '
				<tr>
					<td style="text-align:right;"><label for="poll_a'.$i.'">'.$_TEXT['POLL_ANSWER'].' '.$i.':</label></td>
					<td><input type="text" value="'.format_input($poll_ini['a'.$i]).'" name="poll_a'.$i.'" id="poll_a'.$i.'" style="width:50%;" /> ('.fnum($poll_ini['v'.$i]).'x)</td>
				</tr>	
			';
		}
		echo '
				<tr>
					<td>&nbsp;</td>
					<td>
						<input type="checkbox" id="poll_c" name="poll_c" '.($poll_ini['c']?'checked="checked"':'').'> <label for="poll_c">'.$_TEXT['POLL_CLOSE'].'</label>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<input type="checkbox" id="poll_delete" name="poll_delete"> <label for="poll_delete">'.$_TEXT['POLL_DELETE'].'</label>
					</td>
				</tr>
				</table>
		';

	}
	else
	{
		echo '
				<input type="submit" name="submit" value="'.$_TEXT['POLL_CREATE'].'">
		';
	}
	echo ' 
				</fieldset>
	';
}
echo '
				<fieldset id="settings">
				<legend>'.$_TEXT['SETTINGS'].'</legend>
					'.(IsUSer()?'<input type="checkbox" id="emailben" name="emailben" '.$emailben.'> <label for="emailben">'.$_TEXT['EMAIL_NOTIFICATION'].'</label><br />':'').'
					<input type="checkbox" id="post_no_autolink" name="post_no_autolink" '.$post_no_autolink.'> <label for="post_no_autolink">'.$_TEXT['POST_NO_AUTOLINK'].'</label>
				</fieldset>
';
PluginHook('post-option_fieldset');
echo '
			</td></tr>
';


if (extension_loaded('gd') && auth_guestuser('settings_post_code'))
{
	echo ' 
			<tr><td colspan="2">
				<fieldset>
				<legend>'.$_TEXT['REG_CODE'].'</legend>
				<img src="include/code.php?'.session_name().'='.session_id().'" style="margin:15px;" id="img_code" /> <img src="images/reload.png" onClick=\'var today = new Date();$("#img_code").attr("src", "include/code.php?'.session_name().'='.session_id().'&time="+today.getTime());\' style="cursor:pointer;cursor:hand;"/>
				<br /><label for="new_code">'.$_TEXT['REG_CODE_TEXT'].':</label> <input type="text" id="new_code" name="new_code" size="20" maxlength="6" />
				</fieldset>
			</td></tr>
	';
} 
echo '
			<tr>
				<td colspan="2" style="text-align:center"><input type="submit" name="submit" value="'.$_TEXT['CONFIRM'].'" tabindex="5"> <input type="submit" name="submit" value="'.$_TEXT['PREVIEW'].'"></td>
			</tr>
			</table>
			</form>
		</td></tr>
	</table>
	</div>
	<script type="text/javascript">
	<!--
		var max_chars = '.CONFIG_POST_MAXCHAR.';
		function showOption(name)
		{	
			hideObj(\'settings\');
';
	if ($show_attachment) echo 'hideObj(\'attachment\');';
	if ($show_poll) echo 'hideObj(\'poll\');';
	PluginHook('post-option_javascript');
echo '
			showObj(name);
		}
		$(document).ready(function()	
		{
			$(\'#text\').markItUp(mySettings); $(\'#text\').width($(\'.markItUpContainer\').innerWidth() - 24);
			$(\'#emoticons a\').click(function() 
			{
			        emoticon = $(this).attr("title");
			        $.markItUp( { replaceWith:emoticon } );
			});
			showObj(\'options\');
			showOption(\''.($java_showoption!=''?$java_showoption:'settings').'\');
			showChars();
		});
	-->
	</script>
';

require './include/page_bottom.php'; 
?>