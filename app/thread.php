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

require_once 'include/init.php';

if (!((file_exists('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')) && (is_numeric($_GET['board'])) && (is_numeric($_GET['thema']))))
{
	$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
	require 'index.php';
	Exit;
}

auth('auth_read');

$_BOARD = IniLoad('data/'.$_GET['board'].'/board.ini');
$_THEMA = IniLoad('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
$data = FileLoad('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt');
$gesamt = count($data);

// Selfrepare
if (($_THEMA['title'] == '') OR ($_THEMA['answers'] <> ($gesamt-1)))
{
	RepairThreadIni($_GET['board'], $_GET['thema']);
}

// Views zählen
$_THEMA['views']++;
if ($_THEMA['title'] <> '') IniSave('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $_THEMA);

// Last & Next Thread
$PREV['thema'] = '';
$PREV['title'] = '';
$PREV['date'] = 0;
$NEXT['thema'] = '';
$NEXT['title'] = '';
$NEXT['date'] = 100000000000000;

if ($_THEMA['pin']) $_THEMA['lastpost_date'] += 100000000000;

$list = LoadFileList('./data/'.$_GET['board'].'/', '.txt.ini');
foreach ($list as $item)
{
	$ini = IniLoad('./data/'.$_GET['board'].'/'.$item);
	if ($ini['pin']) $ini['lastpost_date'] += 100000000000;
	if ($ini['lastpost_date'] < $_THEMA['lastpost_date'])
	{
		if ($ini['lastpost_date'] > $PREV['date'])
		{
			$PREV['thema'] = str_replace('.txt.ini', '', $item);
			$PREV['title'] = $ini['title'];
			$PREV['date'] = $ini['lastpost_date'];
		}
	}
	else if ($ini['lastpost_date'] > $_THEMA['lastpost_date'])
	{
		if ($ini['lastpost_date'] < $NEXT['date'])
		{
			$NEXT['thema'] = str_replace('.txt.ini', '', $item);
			$NEXT['title'] = $ini['title'];
			$NEXT['date'] = $ini['lastpost_date'];
		}
	}
}




$RATING = '';
if ($_BOARD['auth_rating'] != '')
{
	$rating_width = '0px';
	$rating_text = '';
	if ($_THEMA['rating_count'] > 0)
	{
		$avarage = $_THEMA['rating_points'] / $_THEMA['rating_count'];
		$rating_width = number_format(20*$avarage, 2).'px';
		$rating_text = MultiReplace($_TEXT['RATING_TEXT'], $_THEMA['rating_count'], number_format($avarage, 2, '.', ','));		
	}

	if (
		((IsInGroup($_THEMA['rating_ips'], $_SESSION['IP'])) 
	   OR
		((IsInGroup($_THEMA['rating_user'], $_SESSION['Benutzername']) && IsUser())))
	   OR
		(!auth('auth_rating', false))
	   )
	{
		$RATING .=  '
			<div style="padding:0;margin:0;" align="right">
			 <ul class="rating" title="'.$rating_text.'">
			  <li class="current-rating" style="width: '.$rating_width.'" title="'.$rating_text.'">&nbsp;</li>
			 </ul>
			</div>
		';
	}
	else
	{
		$RATING .=  '
			<div style="position:relative; padding:0;margin:0;" align="right">
			 <table style="width:100px;"><tr><td style="width:auto;text-align:right;padding:0px;"><ul class="rating" title="'.$rating_text.'">
			  <li class="current-rating" style="width: '.$rating_width.'" title="'.$rating_text.'">&nbsp;</li>
			 </ul></td><td style="width:10px;"><a href="javascript:showhideObj(\'rating\');"><img src="images/arrow_down.png" border="0" alt="'.$_TEXT['RATING_VOTE'].'"></a></td></tr></table>
			 <div style="position:absolute;right:0px;top:25px;display:none;width:150px;" id="rating">
			  <table class="main">
				<tr>
					<td class="g" style="text-align:center;"><center>
						'.$_TEXT['RATING_VOTE'].':
						<ul class="rating">
							<li><a class="r1-voted" title="'.$_TEXT['RATING_VOTE'].': 1/5" href="do.php?do=rating&amp;board='.$_GET['board'].'&amp;thema='.$_GET['thema'].'&amp;vote=1"></a></li>
							<li><a class="r2-voted" title="'.$_TEXT['RATING_VOTE'].': 2/5" href="do.php?do=rating&amp;board='.$_GET['board'].'&amp;thema='.$_GET['thema'].'&amp;vote=2"></a></li>
							<li><a class="r3-voted" title="'.$_TEXT['RATING_VOTE'].': 3/5" href="do.php?do=rating&amp;board='.$_GET['board'].'&amp;thema='.$_GET['thema'].'&amp;vote=3"></a></li>
							<li><a class="r4-voted" title="'.$_TEXT['RATING_VOTE'].': 4/5" href="do.php?do=rating&amp;board='.$_GET['board'].'&amp;thema='.$_GET['thema'].'&amp;vote=4"></a></li>
							<li><a class="r5-voted" title="'.$_TEXT['RATING_VOTE'].': 5/5" href="do.php?do=rating&amp;board='.$_GET['board'].'&amp;thema='.$_GET['thema'].'&amp;vote=5"></a></li>
						</ul>
					</center></td>
				</tr>
			  </table>
			 </div>
			</div>

		';
	}
}

$_SUBNAV = array_merge($_SUBNAV, GetBoardParents($_GET['board']));
$_SUBNAV[] = array($_BOARD['title'], url('board.php', $_GET['board']), 'boardS.png');
$_SUBNAV[] = array($_THEMA['title'], url('thread.php', $_GET['board'], $_GET['thema']), 'threadS.png', $_THEMA['tag']);
if ($NEXT['thema'].$PREV['thema'] <> '')
{
	$_SUBNAV_RIGHT = ($NEXT['thema']<>''?'<a href="'.url('thread.php', $_GET['board'], $NEXT['thema']).'" title="'.$NEXT['title'].'"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/back.png" /></a>':'<img src="./styles/'.$_FORUM['settings_design_style'].'/images/back2.png" />').' '.($PREV['thema']<>''?'<a href="'.url('thread.php', $_GET['board'], $PREV['thema']).'" title="'.$PREV['title'].'"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/forward.png" /></a>':'<img src="./styles/'.$_FORUM['settings_design_style'].'/images/forward2.png" />');
}

require_once 'include/page_top.php';

if (($_SESSION['new_posts_date'] <> '') && ($_SESSION['new_posts_date'] < $_THEMA['lastpost_date']))
{
	if (!IsInGroup($_SESSION['new_posts_seen'], $_GET['board'].'_'.$_GET['thema']))
	{
		$_SESSION['new_posts_board_'.$_GET['board']]--;
		AddToGroup($_SESSION['new_posts_seen'], $_GET['board'].'_'.$_GET['thema']);
		if ($_SESSION['new_posts_board_'.$_GET['board']] == 0) AddToGroup($_SESSION['new_posts_boards_seen'], $_GET['board']);
	}
}

$items = 10;
$pages = ceil(($gesamt)/$items);
if ($_GET['page']=="last") $_GET['page']=$pages;
if ($_GET['page']>$pages) $_GET['page']=$pages;
if ($_GET['page']<1) $_GET['page']=1;

echo '
	<div id="content">
	<table class="main">
	<tr>
		<td '.($RATING<>''?'':'colspan="2"').' class="oben" style="width:'.($RATING<>''?'70':'100').'%;">'.($_THEMA['tag']<>''?'['.$_THEMA['tag'].'] ':'').$_THEMA['title'].'</td>
		'.($RATING<>''?'<td class="oben" style="padding-top:0px;padding-bottom:0px;vertical-align:middle;text-align:right;width:30%;">'.$RATING.'</td>':'').'
	</tr>
';

ob_start();
echo '
	<tr>
		<td class="tb" style="padding:0px;width:70%;">
			<div class="dropdown"><ul>
';
echo ($_THEMA['lock']?'<li><span class="item"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_locked.png" border="0" alt="'.$_TEXT['LOCKED'].'"> <span class="text">'.$_TEXT['LOCKED'].'</span></span><ul><li><span class="item">'.ftime($_THEMA['lock_time']).'<br><nobr>'.$_TEXT['BY'].' '.user($_THEMA['lock_user'], false).'</nobr></span></li></ul></li>':'<li><a href='.url('post.php?do=reply&board='.$_GET['board'].'&thema='.$_GET['thema']).'><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_answer.png" border="0"> <span class="text">'.$_TEXT['ANSWER'].'</span></a></li>');
if (IsUser() && !$_THEMA['lock'])
{
	if (IsInGroup($_THEMA['notification'], $_SESSION['Benutzername']))
	{
		echo '<li><a href="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&do=notificationoff').'"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/btn_notification_on.png" border="0"> <span class="text">'.$_TEXT['NOTIFICATION_OFF'].'</span></a></li>';
	}
	else
	{
		echo '<li><a href="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&do=notification').'"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/btn_notification_off.png" border="0"> <span class="text">'.$_TEXT['NOTIFICATION_ON'].'</span></a></li>';
	}
}

if (IsMod($_GET['board']))
{
	echo '
			<li><span class="item"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_moderation.png" border="0"> <span class="text">'.$_TEXT['MODERATION_MENU'].'</span></span>
			  <ul>
				<li><a href="'.url('do.php?do=del&board='.$_GET['board'].'&thema='.$_GET['thema'].'&beitrag=0').'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_delete.png" border="0" alt="'.$_TEXT['DELETE_THREAD'].'"> <span class="text">'.$_TEXT['DELETE_THREAD'].'</span></a></li>
	';
	if ($_THEMA['lock'])
		{echo '<li><a href="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&do=unlock').'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_unlocked.png" border="0" alt="'.$_TEXT['UNLOCK'].'"> <span class="text">'.$_TEXT['UNLOCK'].'</span></a></li>';}
	else
		{echo '<li><a href="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&do=lock').'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_locked.png" border="0" alt="'.$_TEXT['LOCK'].'"> <span class="text">'.$_TEXT['LOCK'].'</span></a></li>';}
	if ($_THEMA['pin'])
		{echo '<li><a href="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&do=unpin').'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_pin_on.png" border="0" alt="'.$_TEXT['UNPIN'].'"> <span class="text">'.$_TEXT['UNPIN'].'</span></a></li>';}
	else
		{echo '<li><a href="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&do=pin').'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_pin_off.png" border="0" alt="'.$_TEXT['PIN'].'"> <span class="text">'.$_TEXT['PIN'].'</span></a></li>';}
	echo '
				<li><a href="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&do=edit_tag').'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_tag.png" border="0" alt="'.$_TEXT['EDIT_TAG'].'"> <span class="text">'.$_TEXT['EDIT_TAG'].'</span></a></li>
				<li><a href="'.url('do_move.php?board='.$_GET['board'].'&thema='.$_GET['thema']).'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_move.png" border="0" alt="'.$_TEXT['MOVE'].'"> <span class="text">'.$_TEXT['MOVE'].'</span></a></li>
			  </ul>
			</li>
	';
}


echo '
			</ul></div>
		</td>
		<td class="tb" style="text-align:right;padding-top:0px;padding-bottom:0px;vertical-align:middle;width:30%;">
';
			show_pages_($pages, $_GET['page'], 'thread.php', $_GET['board'], $_GET['thema']);
echo '
		</td>

	</tr>
';
$OPTIONS = ob_get_contents();
ob_end_clean();

echo $OPTIONS;

$von = ($_GET['page']-1)*$items;
$bis = $_GET['page']*$items;

if ($von<0) $von=0;
if ($bis>$gesamt) $bis=$gesamt;
$color="g";
for ($i=$von; $i<$bis; $i++)
{
	$user = $data[$i][1];
	$POLL = false;
	$POLL_STRING = '';
	if (file_exists('data/poll_'.$data[$i][6].'.ini'))
	{
		$ini = IniLoad('data/poll_'.$data[$i][6].'.ini');
		if (($_POST['voteid'] == $data[$i][6]) && auth('auth_vote', false))
		{
			if (
				(is_numeric($_POST['vote']))
			   AND
				($_POST['vote'] > 0)
			   AND
				($_POST['vote'] <= 10)
			   )
			{
				if (!(
					((IsInGroup($ini['ips'], $_SESSION['IP'])) 
				   OR
					((IsInGroup($ini['user'], $_SESSION['Benutzername']) && IsUser())))
				   OR
					($ini['c'])
				   OR
					(!auth('auth_vote', false))
				   ))
				{
					$ini['v'.$_POST['vote']]++;
					if (IsUser()) 
					{
						AddToGroup($ini['user'],$_SESSION['Benutzername']); 
					}
					else
					{
						AddToGroup($ini['ips'],$_SESSION['IP']); 
					}
					IniSave('data/poll_'.$data[$i][6].'.ini', $ini);
				}
			}
		}
		$POLL_STRING .= '
				<fieldset class="poll">
					<table>
					<tr><td colspan="2" style="padding:3px;"><b>'.$ini['q'].'</b></td></tr>

		';
	
		if (
			((IsInGroup($ini['ips'], $_SESSION['IP'])) 
		   OR
			((IsInGroup($ini['user'], $_SESSION['Benutzername']) && IsUser())))
		   OR
			($_GET['show_vote'] == $data[$i][6])
		   OR
			($ini['c'])
		   OR
			(!auth('auth_vote', false))
		   )
		{
			$vote_sum = 0;
			for($j = 1; $j <= 10; $j++)
			{
				if ($ini['a'.$j] != '')
				{
					$vote_sum += $ini['v'.$j];
				}
			}
			for($j = 1; $j <= 10; $j++)
			{
				if ($ini['a'.$j] != '')
				{
					$temp = ($vote_sum>0?$ini['v'.$j]/$vote_sum:0);
					$POLL_STRING .= '
						<tr>
							<td style="padding:3px;">
								'.$ini['a'.$j].' <small>('.$ini['v'.$j].'/'.$vote_sum.')</small>
								<br /><img src="images/poll'.$j.'a.png" /><img src="images/poll'.$j.'b.png" style="width:'.(80*$temp).'%;height:10px;"/><img src="images/poll'.$j.'c.png" /> '.number_format(100*$temp, 2, ',', '.').' %
							</td>
						</tr>
					';
				}
			}
	
			if (($_GET['show_vote'] == $data[$i][6]) AND (!$ini['c']) AND (auth('auth_vote', false)))
			{
				$POLL_STRING .= '
					<tr>
						<td colspan="2"><a href="'.url('thread.php', $_GET['board'], $_GET['thema'], $i).'">'.$_TEXT['POLL_SUBMIT'].'</a></td>
					</tr>
				';
			}
		}
		else
		{
			$POLL_STRING .= '
					<form action="'.url('thread.php', $_GET['board'], $_GET['thema'], $i).'" method="post">
					<input type="hidden" name="voteid" value="'.$data[$i][6].'" />
			';
			for($j = 1; $j <= 10; $j++)
			{
				if ($ini['a'.$j] != '')
				{
					$POLL_STRING .= '
						<tr>
							<td style="padding:3px;"><input type="radio" name="vote" id="vote'.$j.'" value="'.$j.'" /> <label for="vote'.$j.'">'.$ini['a'.$j].'</label></td>
						</td>
					';
				}
			}
			$POLL_STRING .= '
						<tr>
							<td>
								<input type="submit" name="submit" value="'.$_TEXT['POLL_SUBMIT'].'" />
								<a href="'.url('thread.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&page='.$_GET['page'].'&show_vote='.$data[$i][6].'#'.$i).'">'.$_TEXT['POLL_RESULT'].'</a>
							</td>
						</tr>
					</form>
			';
		}
		$POLL_STRING .= '
					</table>
				</fieldset>
		';
		if (InStr('[poll]', $data[$i][3])) 
		{
			$data[$i][3] = str_replace('[poll]', $POLL_STRING, $data[$i][3]);
		}
		else
		{
			$POLL = true;
		}
	}
	$replace_array = array();
	$replace_array['ID'] = $i;
	$replace_array['DATETIME'] = ftime($data[$i][0]);

	$suser = user($user, true, true, true, true);
	$avatar = '';
	if (file_exists('data/user/'.$user.'.usr.ini'))
	{
		$udat = IniLoad('data/user/'.$user.'.usr.ini');
		if ($udat['avatar'] <> '') $avatar = '<p><img src="'.$udat['avatar'].'" border="0" alt=""></p>';
	}
	else
	{
		$suser .= '<p class="sub">'.$_TEXT['NOT_REGISTERED'].'</p>';
	}
	$replace_array['USER'] = $suser;
	$replace_array['AVATAR'] = $avatar;

	$ip = '';
	if (IsAdmin() && ($data[$i][4]<>'') && (strlen($data[$i][4])>4))
	{
		$temp = implode('', PluginHook('thread-ip', $data[$i][4]));
		if ($temp <> '')
		{
			$ip .= '<p class="sub" style="margin-top:20px;">'.$temp.'</p>';
		}
		else 
			$ip .= '<p class="sub" style="margin-top:20px;"><img src="styles/'.STYLE.'/images/btn_ip.png" border="0" alt="IP: '.$data[$i][4].'" title="IP: '.$data[$i][4].'" /> '.$data[$i][4].'</p>';
	}
	$replace_array['IP'] = $ip;
	$replace_array['TAG'] = ((($_THEMA['tag'] <> '') AND ($i == 0))?'<font class="tag">[ '.$_THEMA['tag'].' ]</font> ':'');
	$replace_array['HEADLINE'] = $data[$i][2];
	$replace_array['TEXT'] = format_post($data[$i][3]);

	ob_start();
 	if (defined('CONFIG_PROFILE_SIGNATURE_AUTOAPPEND') && (CONFIG_PROFILE_SIGNATURE_AUTOAPPEND == 1) && (IsUser($user)))
	{
		if ($udat['signature'] <> '') echo format_post($udat['signature']);
	}
	if (strlen($data[$i][5]) > 8)
	{
		
		echo '
				<fieldset>
					<legend>'.$_TEXT['UPLOAD_ATT'].'</legend>
					<table>
		';
		foreach (Group2Array(SubGroup2Group($data[$i][5])) as $att)
		{
			show_att($att, true);
		}
		echo '
					</table>
				</fieldset>
		';
	}
	if ($POLL) echo $POLL_STRING;
	PluginHook('thread-post_end');
	$replace_array['ADD_TEXT'] = ob_get_contents();
	ob_end_clean();
	
	$post_buttons = '';
	if (!$_THEMA['lock']) 
	{
		$post_buttons .= '<li><a href="'.url('post.php?do=reply&board='.$_GET['board'].'&thema='.$_GET['thema'].'&beitrag='.$i).'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_answer.png" border="0" alt="'.$_TEXT['QUOTE'].'"> <span class="text">'.$_TEXT['QUOTE'].'</span></a></li>';
	}
	if (
		(($_SESSION['Benutzername'] == $data[$i][1]) AND (!$_THEMA['lock']))
	      OR
		(IsMod($_GET['board']) && $_FORUM['settings_admin_edit'])
	   ) 
	{
		$post_buttons .= '<li><a href="'.url('post.php?do=edit&board='.$_GET['board'].'&thema='.$_GET['thema'].'&beitrag='.$i).'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_edit.png" border="0" alt="'.$_TEXT['EDIT'].'"> <span class="text">'.$_TEXT['EDIT'].'</span></a></li>';
	}
	if (IsMod($_GET['board']))
	{
		$post_buttons .= '<li><a href="'.url('do.php?do=del&board='.$_GET['board'].'&thema='.$_GET['thema'].'&beitrag='.$i).'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_delete.png" border="0" alt="'.($i==0?$_TEXT['DELETE_THREAD']:$_TEXT['DELETE']).'"> <span class="text">'.($i==0?$_TEXT['DELETE_THREAD']:$_TEXT['DELETE']).'</span></a></li>';
	}
	else if (($_SESSION['Benutzername'] == $data[$i][1]) AND (($i > 0) OR ($gesamt == 1)))
	{
		$post_buttons .= '<li><a href="'.url('do.php?do=del&board='.$_GET['board'].'&thema='.$_GET['thema'].'&beitrag='.$i).'"><img src="styles/'.$_FORUM['settings_design_style'].'/images/btn_delete.png" border="0" alt="'.$_TEXT['DELETE'].'"> <span class="text">'.$_TEXT['DELETE'].'</span></a></li>';
	}
	$replace_array['POST_BUTTONS'] = $post_buttons;

	echo ArrayReplace(GetTemplate('thread_item_'.($color=='g'?'1':'2')), $replace_array);
	
	$color = ($color=='w'?'g':'w');
	if ($i == $von)
	{
		if (file_exists('data/advertising_threadafter1.txt'))
		{
			echo '
				<tr><td class="'.$color.'" colspan="2">
					'.file_get_contents('data/advertising_threadafter1.txt').'
				</td></tr>
			';
			$color = ($color=='w'?'g':'w');
		}
	}
	if ((($von-$i+2) % 3 == 0) && ($i <> $bis-1))
	{
		if (file_exists('data/advertising_threadevery3.txt'))
		{
			echo '
				<tr><td class="'.$color.'" colspan="2">
					'.file_get_contents('data/advertising_threadevery3.txt').'
				</td></tr>
			';
			$color = ($color=='w'?'g':'w');
		}
	}
}

echo '
		'.$OPTIONS.'
	</table>
	</div>
';

if (($_FORUM['settings_directanswer']) && (!$_THEMA['lock']) && auth('auth_answere', false))
{
	$_GET['do'] = 'reply';
	$_GET['beitrag'] = '';
	require 'post.php';
}
require_once 'include/page_bottom.php';
?>