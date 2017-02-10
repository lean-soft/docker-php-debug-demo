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

if (!defined('CONFIG_BOARD_ITEMS_PER_PAGE')) define('CONFIG_BOARD_ITEMS_PER_PAGE', 20);

if (!((file_exists('data/'.$_GET['board'].'/')) && (is_numeric($_GET['board']))))
{
	$MSG_ERROR = $_TEXT['ERROR_BOARD'];
	require 'include/page_top.php';
	require 'include/page_bottom.php';
	Exit;
}

auth('auth_read');

$_BOARD = IniLoad('data/'.$_GET['board'].'/board.ini');

$_SUBNAV = array_merge($_SUBNAV, GetBoardParents($_GET['board']));
$_SUBNAV[] = array($_BOARD['title'], url('board.php', $_GET['board']), 'boardS.png');
$_SUBNAV_BOTTOM = $_BOARD['description'];

require_once 'include/page_top.php';

if (file_exists('data/'.$_GET['board'].'/infotext.txt'))
{
	echo '<div class="info">'.format_text(file_get_contents('data/'.$_GET['board'].'/infotext.txt'), false).'</div>';
} 

$show = false;
$string = '<div class="content"><table class="main"><tr><td class="oben" colspan="3">'.$_TEXT['BOARDS'].'</td></tr>';
$class = 'g';
foreach(Group2Array($_BOARDS['b'.$_GET['board'].'_children']) as $item)
{
	if (substr($item, 0, 1) == 'b')
	{
		$board = substr($item, 1, 100);
		if (is_numeric($board))
			if (auth('auth_show', false, $board))
			{
				$string .= PreviewBoard($board, $class, $user_in_board[$board], $guests_in_board[$board]);
				$class = ($class == 'g'?'w':'g');
				$show = true;
			}
	}
}
$string .= '</table></div>';
if ($show) echo $string;

$OPTIONS = '
	<div class="dropdown">
		<ul>
			<li><a href="'.url('post.php?do=newthread&board='.$_GET['board']).'"><img src="styles/'.STYLE.'/images/btn_answer.png" border="0"> <span class="text">'.$_TEXT['NEW_TOPIC'].'</span></a></li>
';
			if (IsUser())
			{
				if (IsInGroup($_BOARD['notification'], $_SESSION['Benutzername']))
				{
					$OPTIONS .= '<li><a href="'.url('do.php?board='.$_GET['board'].'&do=notification_board_off').'"><img src="./styles/'.STYLE.'/images/btn_notification_on.png" border="0"> <span class="text">'.$_TEXT['NOTIFICATION_OFF'].'</span></a></li>';
				}	
				else
				{
					$OPTIONS .= '<li><a href="'.url('do.php?board='.$_GET['board'].'&do=notification_board_on').'"><img src="./styles/'.STYLE.'/images/btn_notification_off.png" border="0"> <span class="text">'.$_TEXT['NOTIFICATION_ON'].'</span></a></li>';
				}
			}
$OPTIONS .= '
				</ul>
			</div>
';

$inis = LoadFileList('./data/'.$_GET['board'].'/', '.txt.ini');
$items = array();

$_SESSION['new_posts_board_'.$_GET['board']] = 0;

foreach ($inis as $ini)
{
	$item = IniLoad('./data/'.$_GET['board'].'/'.$ini);
	$item['id'] = str_replace('.txt.ini', '', $ini);
	$item['new'] = (($item['lastpost_date'] > $_SESSION['new_posts_date']) && ($_SESSION['new_posts_date'] <> '') && (!IsInGroup($_SESSION['new_posts_seen'], $_GET['board'].'_'.$item['id'])));
	if ($item['new']) $_SESSION['new_posts_board_'.$_GET['board']]++;
	array_push($items, $item);
}

if ($_SESSION['new_posts_board_'.$_GET['board']] == 0) 
{
	AddToGroup($_SESSION['new_posts_boards_seen'], $_GET['board']);
}
else
{
	DeleteFromGroup($_SESSION['new_posts_boards_seen'], $_GET['board']);
}

$sort_array = explode('.', $_GET['sort']<>''?$_GET['sort']:($_POST['sort']<>''?$_POST['sort']:''));
$sort = $sort_array[0];
if (!in_array($sort, array('date', 'title', 'rating'))) $sort = 'date';
$sort_direction = $sort_array[1];
if (!in_array($sort_direction, array('asc', 'desc'))) $sort_direction = ((($sort=='date')OR($sort=='rating'))?'desc':'asc');

function sort_list($a, $b) 
{
	GLOBAL $sort, $sort_direction;
	if ($a['pin'] && !$b['pin']) return -1;
	if (!$a['pin'] && $b['pin']) return 1;
	if ($sort == 'rating')
	{
		$a1 = ($a['rating_count']>0?($a['rating_points']/$a['rating_count']):0);
		$b1 = ($b['rating_count']>0?($b['rating_points']/$b['rating_count']):0);
	}
	else
	{
	 	$a1 = strtolower(html_entity_decode($a[$sort]));
		$b1 = strtolower(html_entity_decode($b[$sort]));
	}
   	if ($a1 == $b1)
	{
		$result = ($a['lastpost_date'] > $b['lastpost_date']) ? 1 : -1;
	}
    	else $result = ($a1 > $b1) ? 1 : -1;
	if ($sort_direction == 'desc') $result = 0 - $result;
	return $result;
}
usort($items, 'sort_list');

if (($_GET['page'] == '') && ($_GET['board'] == $_SESSION['last_board'])) $_GET['page'] = $_SESSION['last_board_page'];
$pages = ceil(count($items)/CONFIG_BOARD_ITEMS_PER_PAGE);
if ($_GET['page']>$pages) $_GET['page']=$pages;
if ($_GET['page']<1) $_GET['page']=1;
$_SESSION['last_board_page'] = $_GET['page'];
$_SESSION['last_board'] = $_GET['board'];
$von = ($_GET['page']-1)*CONFIG_BOARD_ITEMS_PER_PAGE;
$bis = ($_GET['page']*CONFIG_BOARD_ITEMS_PER_PAGE)-1;
if ($bis>=count($items)) $bis=count($items)-1;

if ((count($items) > 0) OR ($_BOARD['auth_topic'] <> '') OR auth('auth_topic', false))
{

echo '
	<div id="content">
	<table class="main">
	<tr>
		<td class="oben" colspan="3"><a href="'.url('board.php', $_GET['board'], 'title'.($sort=='title'?($sort_direction=='asc'?'.desc':''):'')).'">'.$_TEXT['TITLE'].'</a>'.($sort=='title'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
		'.($_BOARD['auth_rating']!=''?'<td class="oben"><a href="'.url('board.php', $_GET['board'], 'rating'.($sort=='rating'?($sort_direction=='asc'?'':'.asc'):'')).'">'.$_TEXT['RATING'].'</a>'.($sort=='rating'?'&nbsp;<img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>':'').'
		<td class="oben"><a href="'.url('board.php', $_GET['board'], 'date'.($sort=='date'?($sort_direction=='asc'?'':'.asc'):'')).'">'.$_TEXT['LAST_POST'].'</a>'.($sort=='date'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
	</tr>
	<tr>
		<td class="tb" colspan="'.($_BOARD['auth_rating']!=''?'4':'3').'" style="padding:0px;">'.$OPTIONS.'</td>
		<td class="tb" style="text-align:right;padding-top:0px;padding-bottom:0px;vertical-align:middle;">
';
if (count($items) > 0) show_pages_($pages, $_GET['page'], 'board.php', $_GET['board'], $_GET['sort']);
echo '
		</td>
	</tr>
';

$color='g';

for ($i=$von; $i<=$bis; $i++)
{
	$item = $items[$i];
	echo '
		<tr>
			<td class="'.$color.'" style="width:5%; text-align:center;"><a href="'.url('thread.php',$_GET['board'], $item['id']).'"><img src="styles/'.STYLE.'/images/';
				if ($item['new'])
					echo 'thema_new.png';
				else if (($item['tag'] <> '') AND (file_exists('styles/'.STYLE.'/images/thema_tag.png')))
					echo 'thema_tag.png';
				else if ($item['lock'])
					echo 'thema_lock.png';
				else if ($item['pin']) 
					echo 'thema_pin.png';
				elseif ($item['answers']>9) 
					echo 'thema_popular.png';
				else
					echo 'thema.png';
				echo '" border="0" alt="'.$item['title'].'"></a>
			</td>
			<td class="'.$color.'" style="width:auto;">'.($item['tag'] <> ''?'<font class="tag">[ '.$item['tag'].' ]</font> ':'').($item['pin']?'<b>':'').'<a href="'.url('thread.php',$_GET['board'], $item['id']).'">'.$item['title'].'</a>'.($item['pin']?'</b>':'').'
	';
				if ($item['answers']>9)
				{
					echo ' <nobr>';
					show_pages_(ceil((($item['answers']+1)/10)), 0,'thread.php', $_GET['board'], $item['id']);
					echo '</nobr>';
				}
	echo '
				<p class="sub" style="margin-top:8px;">'.$_TEXT['BY'].' '.user($item['author']).' | '.$_TEXT['ANSWERES'].': '.fnum($item['answers']).' | '.$_TEXT['VIEWS'].': '.fnum($item['views']).'
	';
				if (($user_in_thema[$item['id']][0] != '') || ($guests_in_thema[$item['id']] > 0))
				{	
					echo '<br /><img src="styles/'.STYLE.'/images/usersS.png" alt="" /> <b>'.$_TEXT['STAT_NOW_ONLINE'].':</b> ';

					foreach($user_in_thema[$item['id']] as $user) 
					{
						if ($user != $user_in_thema[$item['id']][0]) echo ', '; 
						echo user($user);
					}

					if ($guests_in_thema[$item['id']] > 0)

					{

						if ($user != $user_in_thema[$item['id']][0]) {echo ' '.$_TEXT['STAT_AND'].' ';}
						if ($guests_in_thema[$item['id']] == 1)
						{
							echo $guests_in_thema[$item['id']].' '.$_TEXT['STAT_GUEST'];
						}
						else
						{
							echo $guests_in_thema[$item['id']].' '.$_TEXT['STAT_GUESTS'];
						}
					}
				}
	echo '
			</p></td>
			<td class="'.$color.'" style="text-align:right;">
	';
				if ($item['attachment']) echo '<img src="styles/'.STYLE.'/images/downloadS.png" border="0" alt="'.$_TEXT['UPLOAD_ATT'].'" />&nbsp;';
				if ($item['poll']) echo '<img src="styles/'.STYLE.'/images/pollS.png" border="0" alt="'.$_TEXT['POLL'].'" />&nbsp;';
				if (($_SESSION['Benutzername'] <> '') && (IsInGroup($item['notification'], $_SESSION['Benutzername'])))
					echo '<img src="styles/'.STYLE.'/images/mailS.png" border="0" alt="'.$_TEXT['EMAIL_NOTIFICATION'].'" />&nbsp;';
	echo '
			</td>
	';
	if ($_BOARD['auth_rating'] != '')
	{
		$rating_width = '0px';
		$rating_text = '';
		if ($item['rating_count'] > 0)
		{
			$avarage = $item['rating_points'] / $item['rating_count'];
			$rating_width = number_format(14*$avarage, 2).'px';
			$rating_text = MultiReplace($_TEXT['RATING_TEXT'], $item['rating_count'], number_format($avarage, 2, '.', ','));		
		}
		echo '
			<td class="'.$color.'" style="width:70px;" title="'.$rating_text.'">
				<ul class="rating-view">
					<li class="current-rating-view" style="width:'.$rating_width.'" title="'.$rating_text.'">&nbsp;</li>
				</ul>
			</td>
		';
	}
	echo '
			<td class="'.$color.'" style="width:35%;"><a href="'.url('thread.php', $_GET['board'], $item['id'], $item['answers']).'" title="'.$item['lastpost_title'].'"><img src="styles/'.STYLE.'/images/gotoS.png" border="0" alt="'.$_TEXT['LAST_POST'].'"></a> <a href="'.url('thread.php', $_GET['board'], $item['id'], $item['answers']).'" title="'.$item['lastpost_title'].'">'.($item['lastpost_title']<>''?$item['lastpost_title']:'...').'</a><p class="sub">'.ftime($item['lastpost_date']).' '.$_TEXT['BY'].' '.user($item['lastpost_from']).'</p></td>
		</tr>
	';
	$color = ($color=='g'?'w':'g');
	if ($i == $von)
	{
		if (file_exists('data/advertising_boardafter1.txt'))
		{
			echo '
				<tr><td class="'.$color.'" colspan="'.($_BOARD['auth_rating']!=''?'5':'4').'">
					'.file_get_contents('data/advertising_boardafter1.txt').'
				</td></tr>
			';
			$color = ($color=='w'?'g':'w');
		}
	}
	if ((($von-$i+4) % 5 == 0) && ($i <> $bis))
	{
		if (file_exists('data/advertising_boardevery5.txt'))
		{
			echo '
				<tr><td class="'.$color.'" colspan="'.($_BOARD['auth_rating']!=''?'5':'4').'">
					'.file_get_contents('data/advertising_boardevery5.txt').'
				</td></tr>
			';
			$color = ($color=='w'?'g':'w');
		}
	}

}

if (count($items) > 0)
{
	echo '
	  <tr>
		<td class="tb" colspan="'.($_BOARD['auth_rating']!=''?'4':'3').'" style="padding:0px;">'.$OPTIONS.'</td>
		<td class="tb" style="text-align:right;padding-top:0px;padding-bottom:0px;vertical-align:middle;">
	';

	show_pages_($pages, $_GET['page'], 'board.php', $_GET['board'], $_GET['sort']);
	
	echo '
			</td>
	  </tr>
	';
}
echo '
	</table>
	</div>
';
}
require './include/page_bottom.php';
?>