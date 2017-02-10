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
 
$robots_index = true; 
require 'include/page_top.php';

if (IsAdmin() && ($_FORUM['versioncheck']))
{
	if ($_GET['versioncheck'] == 'hide')
	{
		$_FORUM['versioncheck'] = false;
		IniSave('data/forum.ini', $_FORUM);
	}
	else if ($string = @file_get_contents('http://www.frank-karau.de/version.txt'))
	{
		if (version_compare($_FORUM['version'], $string, '<'))
			echo '<div id="notice"><b>Hinweis:</b> Ihr Forum ist nicht mehr aktuell. Die neue Version '.$string.' steht auf <a href="http://www.frank-karau.de" target="_blank">www.frank-karau.de</a> zum Download bereit. [<a href="'.url('index.php?versioncheck=hide').'">'.$_TEXT['HIDE'].'</a>]</div>';
	}
}

PluginHook('forum-start');

$replace_array = array();

$content = '';
if (file_exists('data/infotext.txt')) $content = '<div class="info">'.do_ubb(nl2br(file_get_contents('data/infotext.txt'))).'</div>';
$replace_array['INFOTEXT'] = $content;

$content = '';
$arr = array();
foreach(Group2Array($_BOARDS['index_children']) as $caption)
{
	$show = false;
	$string = '<table class="main">';
	if ($_BOARDS[$caption] <> '')
	{
		$string .= '<tr><td class="oben" colspan="3"><a name="'.$caption.'"></a><b>'.$_BOARDS[$caption].'</b></td></tr>';
	}
	$class = 'g';
	foreach(Group2Array($_BOARDS[$caption.'_children']) as $item)
	{
		if (substr($item, 0, 1) == 'b')
		{
			$board = substr($item, 1, 100);
			if (is_numeric($board) && auth('auth_show', false, $board))
			{
				$string .= PreviewBoard($board, $class, $user_in_board[$board], $guests_in_board[$board]);
				$class = ($class == 'g'?'w':'g');
				$show = true;
			}
		}
	}
	$string .= '</table>';
	if ($show) $arr[] = $string;
}
$content .= '<div class="content">'.implode('<img src="images/blank.gif" style="height:5px;border:0px">', $arr).'</div>';
$replace_array['BOARDS'] = $content;

$content = '';
if (auth_guestuser('settings_design_showstat')) 
{
	$threads_count = 0;
	$answeres_count = 0;
	foreach(Group2Array($_BOARDS['order']) as $item)
	{
		if (substr($item, 0, 1) == 'b')
		{
			$ini = IniLoad('data/'.substr($item, 1, 100).'/board.ini');
			$threads_count += $ini['topics'];
			$answeres_count += $ini['answeres'];
		}
	}
 	$content .= '
	<div class="content">
		<table class="main"> 
			<tr><td class="oben" colspan="2"><b><a name="stat"></a>'.$_TEXT['STAT'].'</b></td></tr>
			<tr>
				<td class="g" style="text-align:center;width:5%;"><img src="styles/'.STYLE.'/images/usersM.png" alt=""></td>
				<td class="g" style="width:95%;">
				'.(auth_guestuser('settings_design_showwhois', true)?'<a href="'.url('whoisonline.php').'">':'').'<b>'.$_COUNT['online'].' '.($_COUNT['online']==1?$_TEXT['STAT_X_ONLINE']:$_TEXT['STAT_XX_ONLINE']).'</b>'.(auth_guestuser('settings_design_showwhois', true)?'</a>':'').' 
					('.count($count_online_user).' '.(count($count_online_user) == 1?$_TEXT['STAT_USER']:$_TEXT['STAT_USERS']).' 
					'.$_TEXT['STAT_AND'].' 
					'.($_COUNT['online']-count($count_online_user)).' '.(($_COUNT['online']-count($count_online_user)) == 1?$_TEXT['STAT_GUEST']:$_TEXT['STAT_GUESTS']).')
					<p class="sub">
	';
 				foreach($count_online_user as $user) {if ($user != $count_online_user[0]) {$content .= ', ';} $content .= user($user);}
 	$content .= '
					</p>
					<p class="sub" style="margin-top:6px;">
	';
	if (defined('CONFIG_FORUM_STATISTIC_EXTENDED'))
	{
		$content .= '
						'.$_TEXT['STAT_TODAY'].': '.fnum($_COUNT['today']).' 
		';
		$users = Group2Array($_COUNT['today_user']);
		if (count($users) > 0)
		{
			foreach ($users as $key=>$value) $users[$key] = user($value);
			$content .= ' ('.implode(', ', $users).')';
		}
		$content .= '
						<br />'.$_TEXT['STAT_TOTAL'].': '.fnum($_COUNT['entire']).'
		';
	}
	else
	{
		$content .= '
						'.$_TEXT['STAT_TODAY'].': '.fnum($_COUNT['today']).' | '.$_TEXT['STAT_TOTAL'].': '.fnum($_COUNT['entire']).'
		';
	}

	$content .= '
					</p>
				</td>
			</tr>
			<tr>
				<td class="w" style="text-align:center;"><img src="styles/'.STYLE.'/images/statM.png" alt=""></td>
				<td class="w">'.$_TEXT['TOPICS'].': '.fnum($threads_count).' | '.$_TEXT['ANSWERES'].': '.fnum($answeres_count).' | '.$_TEXT['STAT_USERS'].': '.fnum(count(LoadFileList('./data/user/', '.usr.ini'))).'
	';
 			if (IsUser($_FORUM['newest_user'])) $content .= ' | '.$_TEXT['STAT_NEWEST_USER'].': '.user($_FORUM['newest_user']);
 	$content .= '
				</td>
			</tr>
			<tr>
				<td class="g" style="text-align:center;"><img src="styles/'.STYLE.'/images/birthdayM.png" alt=""></td>
				<td class="g">
					<b>'.$_TEXT['STAT_BIRTHDAY'].'</b> 
					<br />
	';
	if (!file_exists('data/birthday.ini')) CreateBirthdayList();
	$ini = IniLoad('data/birthday.ini');
	$lists = array();
	for ($i = 0; $i <= 21; $i++)
	{
		$timestamp = time() + ($i*3600*24);
		$list = array();
		foreach(Group2Array($ini[date('j', $timestamp).'.'.date('n', $timestamp).'.']) as $user)
		{
			$data = IniLoad('data/user/'.$user.'.usr.ini');
			$age = 0;
			if ($data['birthday_year'] != '')
			{	
				$age = date('Y') - $data['birthday_year'];
				if (mktime(0, 0, 0, $data['birthday_month'], $data['birthday_day']+1, date('Y')) < time())
				{
					$age++;
				}
			}
			$list[] = '<nobr>'.user($user).($age>0?' ('.$age.')':'').'</nobr>';
		}
		if (count($list) > 0)
		{
			$lists[] = '<nobr>'.($i==0?$_TEXT['TODAY']:($i==1?$_TEXT['TOMORROW']:MultiReplace($_TEXT['IN_DAYS'], $i))).':</nobr> '.implode(', ', $list);
		}
	}
	if (count($lists) == 0) 
	{
		$content .= $_TEXT['STAT_BIRTHDAY_NO_RESULTS'];
	}
	else
	{
		$content .= implode(' | ', $lists);
	}
	$content .= '
				</td>
			</tr>
		</table>
	</div>
	';
}
$replace_array['STATISTICS'] = $content;

$content = '';
if (auth_guestuser('settings_design_showlast')) 
{
 	$content .= '
		<div class="content">
			<table class="main">
 				<tr><td class="oben" colspan="4">
	';
 				if ($_FORUM['settings_design_rss'])
 				{
 					$content .= ' <a href="'.url('rss.php').'" target="_blank"><img src="images/rss.gif" alt="XML | RSS 2.0 Feed" title="XML | RSS 2.0 Feed" align="right"></a>';
 				}
 	$content .= '
				'.MultiReplace($_TEXT['STAT_LAST_POSTS'], $_FORUM['settings_design_lastposts_count']).'</td></tr>
	';
	$hist = FileLoad('data/history.txt');
	$i_begin = count($hist)-$_FORUM['settings_design_lastposts_count'];
	$i_end = count($hist)-1;
	if ($i_begin < 0) $i_begin = 0;
	if ($i_end < 0) $i_end = 0;
	$class = 'g';
	for ($i=$i_end; $i >= $i_begin; $i--)
	{
		if (auth('auth_show', false, $hist[$i][0]))
		{ 
			$post_filename = 'data/'.$hist[$i][0].'/'.$hist[$i][1].'.txt';
			if (file_exists($post_filename))
			{ 
				$post = file($post_filename);
 				if (count($post) > $hist[$i][2])
 				{ 
					$post_link = url('thread.php',$hist[$i][0],$hist[$i][1],$hist[$i][2]);
    					$post_array = explode($TRENNZEICHEN, $post[$hist[$i][2]]);
 					$post_text = history_text($post_array[2], $post_array[3], 60);
 					$post_date = $post_array[0];
 					$post_from = $post_array[1];
					$post_att = (strlen($post_array[5])>8);
					$post_poll = (file_exists('data/poll_'.$post_array[6].'.ini'));
 					$content .= '
						<tr>
							<td class="'.$class.'" style="padding:3px;width:1%;padding-right:0px;"><a href="'.$post_link.'"><img src="styles/'.STYLE.'/images/threadS.png" alt="" style="vertical-align:middle;"></a></td>
							<td class="'.$class.'" style="padding:3px;width:64%;"><a href="'.$post_link.'">'.$post_text.'</a></td>
							<td class="'.$class.'" style="padding:3px;width:5%;text-align:right;">
								'.($post_att?'<img src="styles/'.STYLE.'/images/downloadS.png" border="0" alt="'.$_TEXT['UPLOAD_ATT'].'" />&nbsp;':'').'
								'.($post_poll?'<img src="styles/'.STYLE.'/images/pollS.png" border="0" alt="'.$_TEXT['POLL'].'" />&nbsp;':'').'
							</td>
							<td class="'.$class.'" style="padding:3px;width:30%;"><p class="sub">'.ftime($post_date).' '.$_TEXT['BY'].' '.user($post_from).'</p></td>
						</tr>
					';
					$class = ($class=='g'?'w':'g');
 				}
			} 
		} 
		else 
		{
			if ($i_begin > 0) $i_begin--;
		}
	}
 	$content .= '
				</td></tr>
			</table>
		</div>
	'; 
}  
$replace_array['LASTPOSTS'] = $content;

echo ArrayReplace(GetTemplate('forum'), $replace_array);

PluginHook('forum-end');

require './include/page_bottom.php';  
?>