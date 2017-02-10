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

$search_boards = $_POST['search_boards'];

require 'include/init.php';

$_SUBNAV[] = array($_TEXT['NAV_SEARCH'], url('search.php'));

$show_info = false;
$show_form = true;
$show_results = false;

if (!defined('CONFIG_SEARCH_ITEMS_PER_PAGE')) define('CONFIG_SEARCH_ITEMS_PER_PAGE', 20);

if ($_GET['action'] == 'new')
{
	@unlink('data/cache_search_'.session_id().'.ini');
	@unlink('data/cache_search_'.session_id().'.db');
}

if ($_POST['search'] == 'new')
{
	$search = CreateSearchArray($_POST['search_text'], 3);
	$ini = array();
	$ini['search_text'] = CreateSearchText($search);
	$ini['search_user'] = $_POST['search_user'];
	$ini['search_boards'] = '';
	if ((count($search_boards)>0) && (!in_array('all', $search_boards))) foreach ($search_boards as $item)
	{
		$item = trim($item);
		if (is_numeric($item)) AddToGroup($ini['search_boards'], $item);
	}
	if (implode('', $ini) <> '')
	{
		IniSave('data/cache_search_'.session_id().'.ini', $ini);
		CreateResultFile($search);
	}
	else
	{
		@unlink('data/cache_search_'.session_id().'.ini');
		@unlink('data/cache_search_'.session_id().'.db');
	}
	Header('Location: '.url('search.php', '', '', '', true, true));
	Exit;
}

function CreateResultFile($search)
{
	$ini = IniLoad('data/cache_search_'.session_id().'.ini');
	$db = array();
	$boards = Group2Array($ini['search_boards']);
	if (count($boards) == 0) $boards = GetBoardsArray();
	foreach ($boards as $board)
	{
		if (auth('auth_read', false, $board))
		{
			foreach(LoadFileList('data/'.$board.'/', '.txt.ini') as $thread)
			{
				$thread = str_replace('.txt.ini', '', $thread);
				$data = FileLoad('data/'.$board.'/'.$thread.'.txt');
				$lines = count($data);
				for ($i = 0; $i < $lines; $i++)
				{
					$line = $data[$i];
					$insert = true;
					if (($ini['search_user']<>'') && !InStr(strtolower($ini['search_user']), strtolower($line[1]))) $insert = false;
					if ($insert)
					{
						$temp = strtolower($line[2].'        '.$line[3]);
						foreach ($search as $item)
						{
							if (!InStr(strtolower(format_string($item, true)), $temp)) $insert = false;
						}
					}
					if ($insert)
					{
						$array = array();
						$array['date'] = $line[0];				
						$array['user'] = $line[1];				
						$array['title'] = $line[2];				
						$array['board'] = $board;				
						$array['thread'] = $thread;				
						$array['post'] = $i;
						$db[] = $array;				
					}
				}
			}
		}
	}
	file_put_contents('data/cache_search_'.session_id().'.db', serialize($db));
	return true;
}

require 'include/page_top.php';
$db = array();
$ini = array();
if (file_exists('data/cache_search_'.session_id().'.db'))
{
	$ini = IniLoad('data/cache_search_'.session_id().'.ini');
	$db = unserialize(file_get_contents('data/cache_search_'.session_id().'.db'));
	$show_info = true;
	$show_form = (count($db) == 0);
	$show_results = !$show_form;
}

if ($_GET['action'] == 'change')
{
	$show_info = false;
	$show_form = true;
	$show_results = false;
}

if ($show_info)
{
	echo '
		<div class="info">
			'.$_TEXT['SEARCH_FOR'].' <b>'.format_string($ini['search_text'], true).'</b>
			<br /><b>'.MultiReplace($_TEXT['SEARCH_RESULT'], count($db)).'</b> | <a href="'.url('search.php?action=new').'">'.$_TEXT['SEARCH_NEW'].'</a>
			
		</div>
	';
}

if ($show_form)
{
	echo '
		<div id="content">
			<table class="main">
			<tr><td class="oben">'.$_TEXT['NAV_SEARCH'].'</td></tr>
			<tr><td class="w">
				<form action="'.url('search.php').'" method="post" onSubmit="showLoading();">
				<input type="hidden" name="search" value="new">
				<table style="width:100%;">
				<tr>
					<td class="g" style="width:25%;text-align:right;"><b>'.$_TEXT['SEARCH_KEY'].':</b></td>
					<td class="g" style="width:75%;"><input type="text" name="search_text" value="'.format_input($ini['search_text']).'" style="width:98%;"><br /><small>'.$_TEXT['SEARCH_KEY_TEXT'].'</td>
				</tr>
				<tr>
					<td style="text-align:right;">'.$_TEXT['SEARCH_USER'].':</td>
					<td ><input type="text" name="search_user" value="'.format_input($ini['search_user']).'" style="width:250px;"></td>
				</tr>
				<tr>
					<td style="text-align:right;vertical-align:top;">'.$_TEXT['SEARCH_BOARDS'].':</td>
					<td ><select name="search_boards[]" style="height:200px;width:250px;" multiple="true"><option value="all" '.($ini['search_boards']==''?'selected="selected"':'').'>'.$_TEXT['SEARCH_BOARDS_ALL'].'</option><option value="">--------------------------</option>'.GetBoardsOptions($ini['search_boards']).'</select><br /><small>'.$_TEXT['SEARCH_BOARDS_TEXT'].'</td>
				</tr>
				<tr><td style="text-align:center;" colspan="2" class="g"><input type="submit" value="'.$_TEXT['SEARCH_SUBMIT'].'">
				</table>
			</td></tr>
			</table>
		</div>
	';
}

if ($show_results)
{
	$sort_array = explode('.', $_GET['sort']<>''?$_GET['sort']:($_POST['sort']<>''?$_POST['sort']:''));
	$sort = $sort_array[0];
	if (!in_array($sort, array('date', 'user', 'title'))) $sort = 'date';
	$sort_direction = $sort_array[1];
	if (!in_array($sort_direction, array('asc', 'desc'))) $sort_direction = ($sort=='date'?'desc':'asc');

	function sort_list($a, $b) 
	{
		GLOBAL $sort, $sort_direction;
		$a1 = strtolower(html_entity_decode($a[$sort]));
		$b1 = strtolower(html_entity_decode($b[$sort]));
	    	if ($a1 == $b1)
		{
			$result = ($a['date'] > $b['date']) ? 1 : -1;
		}
	    	else $result = ($a1 > $b1) ? 1 : -1;
		if ($sort_direction == 'desc') $result = 0 - $result;
		return $result;
	}
	usort($db, 'sort_list');
	
	$OPTIONS = '<div class="dropdown"><ul><li><a href="'.url('search.php?action=change').'"><img src="styles/'.STYLE.'/images/btn_search.png"> <span class="text">'.$_TEXT['SEARCH_CHANGE'].'</span></a></li></ul></div>';

	$pages = ceil(count($db)/CONFIG_SEARCH_ITEMS_PER_PAGE);
	if ($_GET['page']>$pages) $_GET['page']=$pages;
	if ($_GET['page']<1) $_GET['page']=1;
	$von = ($_GET['page']-1)*CONFIG_SEARCH_ITEMS_PER_PAGE;
	$bis = ($_GET['page']*CONFIG_SEARCH_ITEMS_PER_PAGE)-1;
	if ($bis>=count($db)) $bis=count($db)-1;
	
	echo '
		<div id="content">
			<table class="main">
			<tr>
				<td class="oben" colspan="2" style="width:60%;"><a href="search.php?sort=title'.($sort=='title'?($sort_direction=='asc'?'.desc':''):'').'">'.$_TEXT['TITLE'].'</a>'.($sort=='title'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
				<td class="oben" style="width:20%;"><a href="search.php?sort=user'.($sort=='user'?($sort_direction=='asc'?'.desc':''):'').'">'.$_TEXT['AUTHOR'].'</a>'.($sort=='user'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
				<td class="oben" style="width:20%;"><a href="search.php?sort=date'.($sort=='date'?($sort_direction=='asc'?'.desc':'.asc'):'').'">'.$_TEXT['DATETIME'].'</a>'.($sort=='date'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
			</tr>
			<tr>
				<td class="tb" colspan="2" style="padding:0px;">'.$OPTIONS.'</td>
				<td class="tb" colspan="2" style="text-align:right;">
	';
					show_pages($pages, $_GET['page'], 'search.php?sort='.$_GET['sort']);
	echo '
				</td>
			</tr>
	';
	$color='g';
	for ($i=$von; $i<=$bis; $i++)
	{
		$line = $db[$i];
		$board_ini = IniLoad('data/'.$line['board'].'/board.ini');
		$thread_ini = IniLoad('data/'.$line['board'].'/'.$line['thread'].'.txt.ini');
		echo '
			<tr>
				<td class="'.$color.'" style="width:5%;"><a href="'.url('thread.php', $line['board'], $line['thread'], $line['post']).'"><img src="styles/'.STYLE.'/images/';
				if (($line['date'] > $_SESSION['new_posts_date']) && ($_SESSION['new_posts_date'] <> '') && (!IsInGroup($_SESSION['new_posts_seen'], $line['board'].'_'.$line['post'])))
					echo 'thema_new.png';
				else if (($thread_ini['tag']<>'') && ($line['post']==0) && file_exists('styles/'.STYLE.'/images/thema_tag.png'))
					echo 'thema_tag.png';
				else if ($thread_ini['lock'])
					echo 'thema_lock.png';
				else if ($thread_ini['pin']) 
					echo 'thema_pin.png';
				elseif ($thread_ini['answers']>=10) 
					echo 'thema_popular.png';
				else
					echo 'thema.png';
				echo '" border="0" alt="'.$item['title'].'"></a></td>
				<td class="'.$color.'" style="width:55%;">
					'.(($thread_ini['tag']<>'')&&($line['post']==0)?'<font class="tag">[ '.$thread_ini['tag'].' ]</font> ':'').'<a href="'.url('thread.php', $line['board'], $line['thread'], $line['post']).'">'.($thread_ini['pin']?'<b>':'').$line['title'].($thread_ini['pin']?'</b>':'').'</a>
					<p class="sub">';
					$parents = GetBoardParents($line['board']);
					if (count($parents) > 0) foreach ($parents as $parent)
					{
						echo '<a href="'.$parent[1].'"><img src="styles/'.STYLE.'/images/boardS.png"></a>&nbsp;<a href="'.$parent[1].'">'.$parent[0].'</a> &raquo; ';
					}
					echo '<a href="'.url('board.php', $line['board']).'"><img src="styles/'.STYLE.'/images/boardS.png"></a>&nbsp;<a href="'.url('board.php', $line['board']).'">'.$board_ini['title'].'</a></p>
				</td>
				<td class="'.$color.'">'.user($line['user']).'</td>
				<td class="'.$color.'">'.ftime($line['date']).'</td>
			</tr>		
		';
		$color = ($color=='w'?'g':'w');
	}
	echo '
			<tr>
				<td class="tb" colspan="2" style="padding:0px;">'.$OPTIONS.'</td>
				<td class="tb" colspan="2" style="text-align:right;">
	';
					show_pages($pages, $_GET['page'], 'search.php?sort='.$_GET['sort']);
	echo '
				</td>
			</tr>
			</table>
		</div>
	';

}

require 'include/page_bottom.php';
?>