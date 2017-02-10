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

$messageids = $_POST['messageids'];
$action = ($_POST['actionbox_top']<>''?$_POST['actionbox_top']:$_POST['actionbox_bottom']);


if (!defined('CONFIG_PM_ITEMS_PER_PAGE')) define('CONFIG_PM_ITEMS_PER_PAGE', 20);

AuthUser();
$folders = array('inbox', 'outbox', 'send');
list($folder, $p1, $p2, $p3, $p4) = explode('/', $_SERVER['QUERY_STRING']);
if (!in_array($folder, $folders)) $folder = $folders[0];

$MESSAGE = '';
if (($folder=='send') && IsUser($p1) && ($_POST['send'] == 'ok'))
{
	if ((strlen($_POST['text']) < 10) OR (strlen($_POST['text']) > (CONFIG_POST_MAXCHAR+100)))
	{
		$MESSAGE = '<div class="error" style="margin-top:0px;">'.MultiReplace($_TEXT['ERROR_TEXT_LENGTH'], CONFIG_POST_MAXCHAR).'</div>';
	}
	else
	{
		$ini = array();
		$ini['datetime'] = time();
		$ini['from'] = $_SESSION['Benutzername'];
		$ini['to'] = $p1;
		$ini['subject'] = format_string($_POST['subject']);
		$ini['text'] = format_text($_POST['text']);
		$ini['read'] = false;
		IniSave('data/pm_'.$ini['to'].'_inbox_'.$ini['datetime'].'.ini', $ini);
		$ini['read'] = true;
		IniSave('data/pm_'.$ini['from'].'_outbox_'.$ini['datetime'].'.ini', $ini);
		$MESSAGE = '<div class="confirm" style="margin-top:0px;">'.MultiReplace($_TEXT['PM_SEND_OK'], $p1).'</div>';
		$folder = 'inbox';
		$replace = array();
		$replace['FROM'] = $ini['from'];
		$replace['SUBJECT'] = $ini['subject'];
		$replace['URL'] = url('pm.php?inbox', '', '', '', false, true);
		$udata = LoadUserIni($ini['to']);
		if ($udata['settings_pm_notification']) SendEmail(array($ini['to']), $_TEXT['EMAIL_NEW_PM_SUBJECT'], $_TEXT['EMAIL_NEW_PM'], $replace);
	}
}
if (($folder == 'inbox') OR ($folder == 'outbox'))
{
	if (file_exists('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$p1.'.ini'))
	{
		$ini = IniLoad('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$p1.'.ini');
		if ($p2 == 'delete_ok')
		{
			unlink('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$p1.'.ini');
		}
		else if (!$ini['read'])
		{
			$ini['read'] = true;
			IniSave('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$p1.'.ini', $ini);
		}
	}
	if (($action == 'markasunread') OR ($action == 'markasread'))
	{
		if (count($messageids)>0) foreach ($messageids as $messageid)
		{
			if (file_exists('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$messageid.'.ini'))
			{
				$ini = IniLoad('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$messageid.'.ini');
				$ini['read'] = ($action == 'markasread');
				IniSave('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$messageid.'.ini', $ini);
			}
		}
	}
	if (($p3 == 'delete_all') && ($p4<>''))
	{
		foreach (explode(',', $p4) as $messageid)
		{
			@unlink('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$messageid.'.ini');
		}
	}
}

$_SUBNAV[] = array($_TEXT['PM'], url('pm.php'));
switch ($folder)
{
	case 'inbox': $_SUBNAV[] = array($_TEXT['PM_INBOX'], url('pm.php?inbox')); break;
	case 'outbox': $_SUBNAV[] = array($_TEXT['PM_OUTBOX'], url('pm.php?oubox')); break;
	case 'send': $_SUBNAV[] = array($_TEXT['PM_SEND'], url('pm.php?send')); break;
}

require_once 'include/page_top.php';

$pm_inbox_count = 0;
foreach(GetFileList('data/pm_'.$_SESSION['Benutzername'].'_inbox_*.ini') as $file)
{
	$ini = IniLoad(DIR.$file);
	if (!$ini['read']) $pm_inbox_count++;
}
$pm_outbox_count = 0;
foreach(GetFileList('data/pm_'.$_SESSION['Benutzername'].'_outbox_*.ini') as $file)
{
	$ini = IniLoad(DIR.$file);
	if (!$ini['read']) $pm_outbox_count++;
}


echo'
	<div class="content">
	<table style="width:100%;table-layout:fixed;">
	<tr><td style="width:25%;padding:0px;padding-right:20px;vertical-align:top;">
		
		<ul class="subnav">
			<li '.($folder=='inbox'?'class="current"':'').'><a href="'.url('pm.php?inbox').'">'.($pm_inbox_count>0?'<b>':'').$_TEXT['PM_INBOX'].($pm_inbox_count>0?'</b> ('.$pm_inbox_count.')':'').'</a></li>
			<li '.($folder=='outbox'?'class="current"':'').'><a href="'.url('pm.php?outbox').'">'.($pm_outbox_count>0?'<b>':'').$_TEXT['PM_OUTBOX'].($pm_outbox_count>0?'</b> ('.$pm_outbox_count.')':'').'</a></li>
			<li '.($folder=='send'?'class="current"':'').'><a href="'.url('pm.php?send').'">'.$_TEXT['PM_SEND'].'</a></li>
		</ul>
	</td><td style="width:75%;padding:0px;vertical-align:top;">
		'.$MESSAGE.'
';
if (($folder == 'inbox') OR ($folder == 'outbox'))
{
	if (file_exists('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$p1.'.ini'))
	{
		$ini = IniLoad('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_'.$p1.'.ini');
		if ($p2 == 'delete')
		{
			echo '<div class="notice" style="margin-top:0px;">'.MultiReplace($_TEXT['PM_DELETE_CONFIRM'], $ini['subject']).'<p class="buttons"><a href="'.url('pm.php?'.$folder.'/'.$p1.'/delete_ok').'">'.$_TEXT['CONFIRM_YES'].'</a> <a href="'.url('pm.php?'.$folder.'/'.$p1).'">'.$_TEXT['CONFIRM_NO'].'</a></p></div>';
		}
		echo '
			<table class="main" style="width:100%;">
			<tr><td class="oben">'.$_TEXT['PM_MESSAGE'].'</td></tr>
			<tr><td class="g" style="padding:1px;">
				<table style="width:100%;">
				<tr>
					<td style="width:10%;">'.$_TEXT['PM_FROM'].':</td>
					<td style="width:25%;">'.user($ini['from']).'</td>
					<td style="width:10%;">'.$_TEXT['PM_TO'].':</td>
					<td style="width:25%;">'.user($ini['to']).'</td>
					<td style="width:30%; text-align:right;">'.ftime($ini['datetime']).'</td>
				</tr>
				<tr>
					<td style="width:10%;">'.$_TEXT['PM_SUBJECT'].':</td>
					<td style="width:90%;" colspan="4"><b>'.($ini['subject']<>''?$ini['subject']:$_TEXT['PM_NO_SUBJECT']).'</b></td>
				</tr>
				</table>
			</td></tr>
			<tr><td class="w" style="padding:0px;">
				<table style="width:100%;table-layout:fixed;overflow:hidden;"><tr><td>
				'.format_post($ini['text']).'
				<ul class="post_buttons">
					'.($folder=='inbox'?'<li><a href="'.url('pm.php?send/'.$p1).'"><img src="styles/'.STYLE.'/images/btn_answer.png"> <span class="text">'.$_TEXT['PM_REPLY'].'</span></a></li>':'').'
					<li><a href="'.url('pm.php?'.$folder.'/'.$p1.'/delete').'"><img src="styles/'.STYLE.'/images/btn_delete.png"> <span class="text">'.$_TEXT['PM_DELETE'].'</span></a></li>
				</ul>
				</td></tr></table>
			</td></tr>
			</table>
		';
	}
	else
	{
		if (($action == 'delete') && (count($messageids)>0))
		{
			echo '<div class="notice" style="margin-top:0px;">'.MultiReplace($_TEXT['PM_DELETE_ALL_CONFIRM']).'<p class="buttons"><a href="'.url('pm.php?'.$folder.'/'.$p1.'/'.$p2.'/delete_all/'.implode(',', $messageids)).'">'.$_TEXT['CONFIRM_YES'].'</a> <a href="'.url('pm.php?'.$folder.'/'.$p1.'/'.$p2).'">'.$_TEXT['CONFIRM_NO'].'</a></p></div>';
		}

		$sort = $p1;
		if (!in_array($sort, array('from', 'to', 'subject', 'datetime'))) $sort = 'datetime';
		$sort_direction = $p2;
		if (!in_array($sort_direction, array('asc', 'desc'))) $sort_direction = (in_array($sort, array('datetime'))?'desc':'asc');

		$filelist = GetFileList('data/pm_'.$_SESSION['Benutzername'].'_'.$folder.'_*.ini');

		$page = $p2;
		$pages = ceil(count($filelist)/CONFIG_PM_ITEMS_PER_PAGE);
		if ($page>$pages) $page=$pages;
		if ($page<1) $page=1;
		$von = ($page-1)*CONFIG_PM_ITEMS_PER_PAGE;
		$bis = ($page*CONFIG_PM_ITEMS_PER_PAGE)-1;
		if ($bis>=count($filelist)) $bis=count($filelist)-1;

		echo '
			<script type="text/javascript">
			$(document).ready(function()
			{
				$("#check_all_top").click(function()				
				{
					var checked_status = this.checked;
					$("input[@name=messageids][type=\'checkbox\']").attr(\'checked\', checked_status);
					$("#check_all_bottom").attr(\'checked\', checked_status);
				});					
				$("#check_all_bottom").click(function()				
				{
					var checked_status = this.checked;
					$("input[@name=messageids][type=\'checkbox\']").attr(\'checked\', checked_status);
					$("#check_all_top").attr(\'checked\', checked_status);
				});
			});
			
			</script>
			<form action="'.url('pm.php?'.$folder.'/'.$p1.'/'.$p2).'" method="post">
			<table class="main">
			<tr>
				<td class="oben" style="width:5%;">&nbsp;</td>
				<td class="oben" style="width:20%;vertical-align:top;"><a href="'.url('pm.php?'.$folder.'/'.($folder=='inbox'?'from':'to').($sort==($folder=='inbox'?'from':'to')?($sort_direction=='asc'?'/desc':''):'')).'">'.$_TEXT[($folder=='inbox'?'PM_FROM':'PM_TO')].'</a>'.($sort==($folder=='inbox'?'from':'to')?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
				<td class="oben" style="width:50%;vertical-align:top;"><a href="'.url('pm.php?'.$folder.'/subject'.($sort=='subject'?($sort_direction=='asc'?'/desc':''):'')).'">'.$_TEXT['PM_SUBJECT'].'</a>'.($sort=='subject'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
				<td class="oben" style="width:25%;vertical-align:top;"><a href="'.url('pm.php?'.$folder.'/datetime'.($sort=='datetime'?($sort_direction=='desc'?'/asc':''):'')).'">'.$_TEXT['PM_DATE'].'</a>'.($sort=='datetime'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
			</tr>
			<tr>
				<td class="tb" style="width:5%;"><input type="checkbox" id="check_all_top"></td>
				<td class="tb" colspan="2">
					<select name="actionbox_top" id="actionbox_top" onchange="this.form.submit();">
						<option value="">'.$_TEXT['PM_ACTIONBOX'].'</option>
						<option value="markasread">'.$_TEXT['PM_MARKASREAD'].'</option>
						<option value="markasunread">'.$_TEXT['PM_MARKASUNREAD'].'</option>
						<option value="delete">'.$_TEXT['PM_DELETE'].'</option>
					</select>
				</td>
				<td class="tb" style="text-align:right;">
		';
		if (count($filelist) > 0) 
		{
			foreach(getPageArray($pages, $page) as $i)
			{
				if ($i=='..')
				{
					echo '&nbsp;..';
				}
				else if ($i==$page) 
				{
					echo '&nbsp;<span class="link_current">'.$i.'</span>';
				}
				else 
				{
					echo '&nbsp;<a href="'.url('pm.php?'.$folder.'/'.$p1.'/'.$i).'" class="link">'.$i.'</a>';
				}
			}
		}
		echo '
			</tr>
		';
		if (count($filelist) > 0)
		{
			$inilist = array();
			foreach($filelist as $item)
			{
				$ini = IniLoad(DIR.$item);
				$ini['text'] = '';
				$inilist[] = $ini;
			}
			function sort_list($a, $b) 
			{
				GLOBAL $sort, $sort_direction;
			    	if (strtolower($a[$sort]) == strtolower($b[$sort])) return 0;
			    	$result = (strtolower($a[$sort]) > strtolower($b[$sort])) ? 1 : -1;
				if ($sort_direction == 'desc') $result = 0 - $result;
				return $result;
			}
			usort($inilist, 'sort_list');

			$class = 'g';
			for($i = $von; $i <= $bis; $i++)
			{
				$ini = $inilist[$i];
				echo '
					<tr>
						<td class="'.$class.'" style="width:10px;vertical-align:middle;padding-top:0px;padding-bottom:0px;"><input type="checkbox" name="messageids[]" value="'.$ini['datetime'].'" '.(is_array($messageids)&&in_array($ini['datetime'], $messageids)?'checked="checked"':'').'></td>
						<td class="'.$class.'" style="vertical-align:top;">'.user($ini[($folder=='inbox'?'from':'to')]).'</td>
						<td class="'.$class.'" style="vertical-align:top;"><a href="'.url('pm.php?'.$folder.'/'.$ini['datetime']).'">'.(!$ini['read']?'<b>':'').''.($ini['subject']<>''?$ini['subject']:$_TEXT['PM_NO_SUBJECT']).''.(!$ini['read']?'</b>':'').'</a></td>
						<td class="'.$class.'" style="vertical-align:top;"><a href="'.url('pm.php?'.$folder.'/'.$ini['datetime']).'">'.(!$ini['read']?'<b>':'').''.ftime($ini['datetime']).''.(!$ini['read']?'</b>':'').'</a></td>
					</tr>
				';
				$class = ($class=='g'?'w':'g');
			}
		}
		else
		{
			echo '<tr><td class="g" colspan="4">'.$_TEXT['PM_NO_MESSAGES'].'</td></tr>';
		}
		echo '
			<tr>
				<td class="tb" style="width:10px;"><input type="checkbox" id="check_all_bottom"></td>
				<td class="tb" colspan="3">
					<select name="actionbox_bottom" id="actionbox_bottom" onchange="this.form.submit();">
						<option value="">'.$_TEXT['PM_ACTIONBOX'].'</option>
						<option value="markasread">'.$_TEXT['PM_MARKASREAD'].'</option>
						<option value="markasunread">'.$_TEXT['PM_MARKASUNREAD'].'</option>
						<option value="delete">'.$_TEXT['PM_DELETE'].'</option>
					</select>
				</td>
			</tr>
			</table>
			</form>
		';
	}
}
if ($folder == 'send')
{
	if (($p1<>'') && file_exists('data/pm_'.$_SESSION['Benutzername'].'_inbox_'.$p1.'.ini'))
	{
		$ini = IniLoad('data/pm_'.$_SESSION['Benutzername'].'_inbox_'.$p1.'.ini');
		$p1 = $ini['from'];
		$_POST['subject'] = 'Re: '.$ini['subject'];
		$_POST['text'] = '[quote]'.html_entity_decode(undo_ubb($ini['text'])).'[/quote]'."\n";
	}

	$list = array();
	if ($_POST['to'] <> '')
	{
		$filelist = GetFileList('data/user/*.usr.ini');
		foreach($filelist as $file)
		{
			$temp = str_replace('data/user/', '', $file);
			$temp = str_replace('.usr.ini', '', $temp);
			if (InStr(strtolower($_POST['to']), strtolower($temp))) $list[] = $temp;

		}
		if (count($list) == 1)
		{
			$p1 = $list[0];
		}
	}
	echo '
		<form action="'.url('pm.php?send/'.$p1).'" method="post">
		<table class="main">
		<tr><td class="oben">'.$_TEXT['PM_SEND'].'</td></tr>
		<tr><td class="g">
			<table style="width:100%;table-layout:fixed;">
			<tr><td style="width:20%;">'.$_TEXT['PM_TO'].':</td><td style="width:80%;">
	';
	if (!IsUser($p1))
	{
		echo '<input type="text" name="to" id="to" value="'.format_input($_POST['to']).'"> <input type="submit" value="'.$_TEXT['PM_TO_SEARCH'].'"></td></tr>';
		if (count($list) > 0)
		{
			echo '<tr><td class="w">&nbsp;</td><td class="w">';
			natcasesort($list);
			foreach($list as $key => $value)
			{
				$list[$key] = '<a href="'.url('pm.php?send/'.$value).'">'.$value.'</a>';
			}
			echo implode(', ', $list);
			echo '</td></tr>';
		}		
	}
	else
	{
		echo '
			<input type="hidden" name="send" value="ok">
			'.user($p1).'</td></tr>
			<tr><td style="width:20%;">'.$_TEXT['PM_SUBJECT'].':</td><td style="width:80%;"><input type="text" name="subject" id="subject" value="'.format_input($_POST['subject']).'" style="width:100%;"></td></tr>
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
			<tr><td colspan="2">
					<textarea id="text" name="text" class="textarea" style="width:100%;" cols="100" rows="5" onKeyUp="showChars()" maxlength="'.CONFIG_POST_MAXCHAR.'">'.htmlentities($_POST['text']).'</textarea><br /><span name="char_viewer" id="char_viewer">'.CONFIG_POST_MAXCHAR.'</span> '.$_TEXT['TEXT_CHARS'].'
			</td></tr>
			<tr>
				<td colspan="2" style="text-align:center"><input type="submit" name="submit" value="'.$_TEXT['PM_CONFIRM'].'"></td>
			</tr>
			<script type="text/javascript">
			<!--
				var max_chars = '.CONFIG_POST_MAXCHAR.';
				$(document).ready(function()	
				{
					$(\'#text\').markItUp(mySettings); 
					$(\'#text\').width($(\'.markItUpContainer\').innerWidth() - 24);
					$(\'#emoticons a\').click(function() 
					{
					        emoticon = $(this).attr("title");
					        $.markItUp( { replaceWith:emoticon } );
					});
					showChars();
				});
			-->
			</script>
		';
	}
	echo '
			</table>
		</td></tr>
		</table>
		</form>
	';
}


echo '
	</td></tr>
	</table>
	</div>
';

require 'include/page_bottom.php';	
?>