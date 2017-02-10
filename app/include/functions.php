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

IF (!defined('SICHERHEIT_FORUM')) die('Access denied.');

include_once('php7patch.php');

$TRENNZEICHEN = "¿";
$TRENNZEICHEN2 = ",";

//**********************************************************************************//
//   $_POST, $_GET, $_COOKIE
//**********************************************************************************//

if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()==1)
{
	$mqs = strtolower(ini_get('magic_quotes_sybase'));
	if (TRUE == empty($mqs) || 'off' == $mqs)
	{
		$_POST = array_map('stripslashes', $_POST);
		$_GET = array_map('stripslashes', $_GET);
		$_COOKIE = array_map('stripslashes', $_COOKIE);
	}
	else
	{
		foreach($_POST as $key => $value)
		{
			$_POST[$key] = str_replace("''", "'", $value);
		}
		foreach($_GET as $key => $value)
		{
			$_GET[$key] = str_replace("''", "'", $value);
		}
		foreach($_COOKIE as $key => $value)
		{
			$_COOKIE[$key] = str_replace("''", "'", $value);
		}
        }
}
$_POST = array_map('trim', $_POST);
$_GET = array_map('trim', $_GET);
$_COOKIE = array_map('trim', $_COOKIE);

if (function_exists('set_magic_quotes_runtime') && (@get_magic_quotes_runtime())) @set_magic_quotes_runtime(false);


//**********************************************************************************//
//   Plugins 
//**********************************************************************************//

class Plugin 
{
	var $name = 'Plugin';
	var $description = '';
	var $author = '';
	var $version = '1.0';
	var $hooks = array();
	var $priority = 50;



	function Activate()
	{
		return true;
	}

	function Deactivate()
	{
		return true;
	}

	function GetInfo()
	{
		$info = array();
		$info['name'] = $this->name;
		$info['description'] = $this->description;
		$info['author'] = $this->author;
		$info['version'] = $this->version;
		return $info;
	}

	function GetHooks()
	{
		return $this->hooks;
	}

	function GetPriority($hook_name)
	{
		if (is_array($this->priority))
		{
			return $this->priority[$hook_name];
		}
		return $this->priority;
	}

	function GetVersion()
	{
		return $this->version;
	}

	function IsIndexPage()
	{
		return false;
	}

	function IsSinglePage()
	{
		return false;
	}

	function PageName()
	{
		return $this->name;
	}

	function PageInit()
	{
		return true;
	}

	function PageContent()
	{
		return true;
	}

	function ApSettings()
	{
		return true;
	}
}

$PLUGINS = array();
$PLUGIN_HOOKS = array();
$PLUGIN_INDEXPAGE = '';

	if (is_dir(DIR.'plugins/'))
	{
		$ini = IniLoad(DIR.'data/forum.ini');
		foreach (Group2Array($ini['plugins']) as $item)
		{
			if (file_exists(DIR.'plugins/'.$item.'/class.'.$item.'.php'))
			{
				require_once DIR.'plugins/'.$item.'/class.'.$item.'.php';
				eval('$PLUGINS[$item] = new '.$item.';');
				foreach($PLUGINS[$item]->GetHooks() as $hook_name => $function_name)
				{
					$PLUGIN_HOOKS[$hook_name][] = array(&$PLUGINS[$item], $function_name);
				}
				if ($PLUGINS[$item]->IsIndexPage())
				{
					$PLUGIN_INDEXPAGE = &$PLUGINS[$item];
				}
			}
		}
	}


function PluginHook($hook_name, $param1 = '¿', $param2 = '¿', $param3 = '¿', $param4 = '¿', $param5 = '¿')
{
   GLOBAL $PLUGINS, $PLUGIN_HOOKS;
   $result = array();
   if (count($PLUGIN_HOOKS[$hook_name]) > 0)
   {
	$param_array = array();
	if ($param1 == '¿') $params = 0;
	else if ($param2 == '¿') $params = 1;
	else if ($param3 == '¿') $params = 2;
	else if ($param4 == '¿') $params = 3;
	else if ($param5 == '¿') $params = 4;
	else $params = 5;

	for ($i = 1; $i <= $params; $i++)
	{
		$param_array[] = '$param'.$i;
	}
	if (count($PLUGIN_HOOKS[$hook_name]) > 1)
	{
		foreach ($PLUGIN_HOOKS[$hook_name] as $key => $item) 
		{
			$sort[$key] = $item[0]->GetPriority($hook_name);
		}
		array_multisort($sort, SORT_ASC, $PLUGIN_HOOKS[$hook_name]);		

	}
	foreach ($PLUGIN_HOOKS[$hook_name] as $item)
	{
		eval('$result[] = $item[0]->'.$item[1].'('.implode(', ', $param_array).');');
	}
   }
   return $result;
}

function PluginAddToNavigation($pluginname, $caption = 'Plugin', $visible = '')
{
	$ini = IniLoad(DIR.'data/navigation.ini');
	$i = 1;
	while($ini['l'.$i.'_link'] != '') $i++;
	AddToGroup($ini['order'], 'l'.$i, false);
	$ini['l'.$i.'_link'] = $caption;
	$ini['l'.$i.'_url'] = url('index.php', $pluginname);
	$ini['l'.$i.'_target'] = '';
	$ini['l'.$i.'_visible'] = $visible;
	return IniSave(DIR.'data/navigation.ini', $ini);
}

function PluginDeleteFromNavigation($pluginname)
{
	$found = false;
	$url = url('index.php', $pluginname);
	$ini = IniLoad(DIR.'data/navigation.ini');
	foreach(Group2Array($ini['order']) as $item)
	{
		if ($ini[$item.'_url'] == $url)
		{
			$found = true;
			DeleteFromGroup($ini['order'], $item);
			$ini[$item.'_link'] = '';
			$ini[$item.'_url'] = '';
			$ini[$item.'_target'] = '';
			$ini[$item.'_visible'] = '';
		}
	}
	IniSave(DIR.'data/navigation.ini', $ini);
	return $found;
}

PluginHook('functions-start');

//**********************************************************************************//
//   Templates 
//**********************************************************************************//

function GetTemplate($filename)
{
	if (file_exists(DIR.'styles/'.STYLE.'/templates/'.$filename.'.html'))
	{
		return @file_get_contents(DIR.'styles/'.STYLE.'/templates/'.$filename.'.html');
	}
	else
	{
		return @file_get_contents(DIR.'templates/'.$filename.'.html');
	}
}

function ArrayReplace($string, $array)
{
	foreach ($array as $key=>$value)
	{
		$string = str_replace('{_'.$key.'_}', $value, $string);
	}
	return $string;
}

function MultiReplace($string, $var1 = '', $var2 = '', $var3 = '', $var4 = '', $var5 = '')
{
	$string = str_replace('%1', $var1, $string);
	$string = str_replace('%2', $var2, $string);
	$string = str_replace('%3', $var3, $string);
	$string = str_replace('%4', $var4, $string);
	$string = str_replace('%5', $var5, $string);
	return $string;
} 

//**********************************************************************************//
//   Log-Datei 
//**********************************************************************************//

function LogAppend($string)
{
	$lines = @file(DIR.'data/admin.log');
	if (!is_array($lines)) $lines = array();
	$source = fopen(DIR.'data/admin.log', "w");
	fwrite($source, ftime(time(), false).": ".$string."\n");
	$bis = count($lines);
	if ($bis>99) $bis = 99;
	for($i=0;$i<$bis;$i++)
	{
		fwrite($source, trim($lines[$i])."\n");
	}
	fclose($source);
	return true;
}

function LogDelete()
{
	return @unlink(DIR.'data/admin.log');
}

//**********************************************************************************//
//   Design 
//**********************************************************************************//

function url($file, $p1 = '', $p2 = '', $p3 = '', $showsession = true, $showbase = false, $showpage = false)
{
	GLOBAL $_FORUM, $PLUGIN_INDEXPAGE;
	$url = '';
	if (in_array($file, array('board.php', 'thread.php')))
	{
		$board = $p1;
		if ($file == 'board.php')
		{
			$sort = $p2;
		}
		else
		{
			$thema = $p2;
		}
		if (!is_numeric($p3))
		{
			$beitrag = '0';
		}
		else
		{
			$beitrag = $p3;
		}
		$page = ceil(($beitrag+1)/10);
	}
	if (!is_object($PLUGIN_INDEXPAGE))
	{
		if ($file == 'forum.php') $file = 'index.php';
	}
	if (($file == 'index.php') && (!file_exists('index.html')) && $_FORUM['settings_system_shorturls'] && ($p1 == ''))
		$file = '';
	if (($_FORUM['settings_forum_url'] != '') && $showbase) 
		$url = $_FORUM['settings_forum_url'].'/';
	$no_cookie = (($_COOKIE[session_name()]=='') AND ($_COOKIE['loginname']==''));
	if (($_FORUM['settings_system_shorturls']) && (in_array($file, array('board.php', 'thread.php', 'user.php', 'index.php'))) && ($p1 != ''))
	{ 
		$url .= 'board/';
		if (($showsession) AND ($no_cookie) AND IsUser()) 
		{
			$url .= 'sess_'.session_id().'/';
		}
		if (in_array($file, array('board.php', 'thread.php')))
		{
			if ($board <> '')
			{
				foreach(GetBoardParents($board) as $item)
				{
					$text .= $item[0].'/';
				}
				$array = IniLoad('data/'.$board.'/board.ini');
				$text .= $array['title'].'/';
			}
			if ($thema <> '')
			{
				$array = IniLoad('data/'.$board.'/'.$thema.'.txt.ini');
				$text .= $array['title'].'-';
			}
			else
			{
				$text .= 'index-';
			}
			$text = strtolower($text);
			$text = html_entity_decode($text);
			$text = str_replace("ä","ae",$text);
			$text = str_replace("ö","oe",$text);
			$text = str_replace("ü","ue",$text);
			$text = str_replace("ß","ss",$text);
			for ($i = 0; $i < strlen($text); $i++)
			{
				if (is_numeric(strpos('qwertzuiopasdfghjklyxcvbnm1234567890-/', substr($text, $i, 1))))
				{
					$url .= substr($text, $i, 1);
				}
				else
				{
					$url .= '-';
				}
			}
			$url = preg_replace('/-{2,}/','-',$url);
			$url = str_replace('-/','/',$url);
			if ($file == "board.php")
			{
				if (($page > 1) OR ($showpage))
					$url .= $board.'__'.$page.'.html';
				else
					$url .= $board.'.html';
				if ($sort <> '') $url .= '/'.$sort;
			}
			if ($file == 'thread.php')
			{
				if (($page > 1) OR ($showpage))
					$url .= $board.'_'.$thema.'_'.$page.'.html';
				else
					$url .= $board.'_'.$thema.'.html';
				if ($beitrag > 0)
					$url .= '#'.$beitrag;
			}
		}
		if ($file == 'user.php')
		{
			$url .= 'user/'.$p1.'.html';
		}
		if ($file == 'index.php')
		{
			if ($p2 == '')
			{
				$url .= 'plugin/'.$p1.'.html';
			}
			else
				$url .= 'plugin/'.$p1.'/'.$p2.'.html';
		}
	} 
	else
	{
		$url .= $file;
		if (($showsession) AND ($no_cookie) AND IsUser()) 
			$url .= ((is_integer(strpos($url,'?')))?'&':'?').session_name().'='.session_id();

		if (in_array($file, array('board.php', 'thread.php')))
		{
			if ($board != '')
				$url .= ((is_integer(strpos($url,'?')))?'&':'?').'board='.$board;
			if ($thema != '')
				$url .= '&thema='.$thema;
			if (($page > 1) OR ($showpage))
				$url .= '&page='.$page;
			if ($sort <> '')
				$url .= '&sort='.$sort;
			if (($beitrag > 0) AND ($thema <> ''))
				$url .= '#'.$beitrag;
		}
		else if ($file == "user.php")
		{
			if ($p1 != '')
				$url .= ((is_integer(strpos($url,'?')))?'&':'?').'user='.$p1;
		}
		else if ($file == "index.php")
		{
			if ($p1 != '')
				$url .= ((is_integer(strpos($url,'?')))?'&':'?').'plugin='.$p1;
			if ($p2 != '')
				$url .= ((is_integer(strpos($url,'?')))?'&':'?').'page='.$p2;
		}
	}
	return $url;
}

function style_convert($line)
{
	GLOBAL $_FORUM, $_TEXT, $_COUNT, $_SESSION, $LOADING_TIME, $PLUGIN_INDEXPAGE;
	if (strpos($line, '{_') !== false)
	{
	  	$line = str_replace('{_USER_ONLINE_}',($_FORUM['settings_design_showstat']?'<a href="'.url('forum.php#stat').'"><img src="styles/'.STYLE.'/images/statS.png" alt="" style="vertical-align:bottom;" /></a> <a href="'.url('forum.php#stat').'">'.$_COUNT['online'].' '.($_COUNT['online']==1?$_TEXT['STAT_X_ONLINE']:$_TEXT['STAT_XX_ONLINE']).'</a>':'<img src="styles/'.STYLE.'/images/statS.png" alt="" style="vertical-align:bottom;" /> '.$_COUNT['online'].' '.($_COUNT['online']==1?$_TEXT['STAT_X_ONLINE']:$_TEXT['STAT_XX_ONLINE'])),$line);
	 	$line = str_replace('{_HEADLINE_}',$_FORUM['settings_forum_header'],$line);
	 	$line = str_replace('{_LOGO_}','<img src="'.($_FORUM['settings_forum_logo']<>''?$_FORUM['settings_forum_logo']:'styles/'.STYLE.'/logo.png').'" border="0" alt="">',$line);
		if (strpos($line, '{_NAVIGATION_') !== false)
		{
			$nav = array();
			if (is_object($PLUGIN_INDEXPAGE))
			{
				$nav[] = '<a href="'.url('index.php').'">'.$PLUGIN_INDEXPAGE->PageName().'</a>';
			}
			$ini = IniLoad(DIR.'data/navigation.ini');
			foreach(Group2Array($ini['order']) as $item)
			{
				if ($item == '*0') $nav[] = '<a href="'.url('forum.php').'">'.$_TEXT['NAV_FORUM'].'</a>';
				else if ($item == '*1') $nav[] = '<a href="'.url('reg.php').'">'.$_TEXT['NAV_REGISTER'].'</a>';
				else if ($item == '*2') 
				{
					if (($_FORUM['settings_design_ranking_guest']) OR (($_FORUM['settings_design_ranking_user'] && ($_SESSION['Benutzername'] <> ''))))
					{
						$nav[] = '<a href="'.url('members.php').'">'.$_TEXT['NAV_MEMBERS'].'</a>';
					}
				}
				else if ($item == '*3') $nav[] = '<a href="'.url('search.php').'">'.$_TEXT['NAV_SEARCH'].'</a>';
				else if ($item == '*4')
				{
					if (file_exists('data/rules.txt'))
					{
						$nav[] = '<a href="'.url('rules.php').'">'.$_TEXT['NAV_RULES'].'</a>';
					}
				}
				else if ($item == '*5') $nav[] = '<a href="'.url('faq.php').'">'.$_TEXT['NAV_FAQ'].'</a>';
				else if ($item == '*6') 
				{
					if (file_exists('data/impressum.txt'))
					{
						$nav[] = '<a href="'.url('imprint.php').'">'.$_TEXT['NAV_IMPRESSUM'].'</a>';
					}
				}
				else
				{
					if (($ini[$item.'_visible'] == '') OR IsUser())
						$nav[] = '<a href="'.$ini[$item.'_url'].'" '.($ini[$item.'_target']!=''?'target="'.$ini[$item.'_target'].'"':'').'>'.$ini[$item.'_link'].'</a>';
				}
			}
		
		 	$line = str_replace('{_NAVIGATION_}', implode($nav,' | '),$line);
		 	$line = str_replace('{_NAVIGATION_LIST_}', '<ul><li>'.implode($nav,'</li><li>').'</li></ul>',$line);
		}	
		$line = str_replace('{_STYLE_}',STYLE,$line);
	 	$line = str_replace('{_FORUM_VERSION_}',$_FORUM['version'],$line);
		if (IsUser())
		{
			$pm_count = 0;
			foreach(GetFileList('data/pm_'.$_SESSION['Benutzername'].'_*.ini') as $file)
			{
				$ini = IniLoad(DIR.$file);
				if (!$ini['read']) $pm_count++;
			}
			$replace_with = ''.user($_SESSION['Benutzername']).' | <a href="'.url('my_profile.php').'">'.$_TEXT['LOGIN_PROFILE'].'</a> | <a href="'.url('pm.php').'">'.($pm_count > 0?'<b>':'').$_TEXT['PM'].($pm_count > 0?'</b>':'').'</a>'.($pm_count > 0?' ('.$pm_count.')':'').' | '.(IsAdmin()?'<a href="'.url('ap/').'" target="_blank">'.$_TEXT['NAV_ADMINISTRATION'].'</a> | ':'').'<a href="'.url('index.php?action=logout').'">'.$_TEXT['LOGIN_LOGOUT'].'</a>';
		}		
		else
			$replace_with = '<a href="'.url('login.php').'" onclick="return showPanel(event);"><img src="styles/'.STYLE.'/images/loginS.png" alt="" style="vertical-align:bottom;" /></a>&nbsp;<a href="'.url('login.php').'" onclick="return showPanel(event);">'.$_TEXT['LOGIN_LOGIN'].'</a>';
		$line = str_replace('{_USER_LOGIN_}',$replace_with,$line);
		if (!IsUser())
			$replace_with = '<a href="'.url('login.php').'"><img src="styles/'.STYLE.'/images/loginS.png" alt="" style="vertical-align:bottom;" /></a>&nbsp;<a href="'.url('login.php').'">'.$_TEXT['LOGIN_LOGIN'].'</a>';
		$line = str_replace('{_USER_LOGIN_SIMPLE_}',$replace_with,$line);
		$line = str_replace('{_LOADING_TIME_}',number_format($LOADING_TIME,2,',','.'),$line);
	}
	return $line;
}

function getPageArray($pages, $page)
{
	$arr = array();
	$start = $page-2;
	if ($start < 1) $start = 1;
	$end = ($start+4);
	if ($end > $pages)
	{
		$end = $pages;
		$start = $end-4;
		if ($start < 1) $start = 1;
	}
	if ($start > 1)
	{
		$arr[] = 1;
	}
	if ($start > 2)
	{
		$arr[] = '..';
	}
	if ($start > 10)
	{
		// Seitensprünge
		$step = round(($start) / 2);
		$pos = 1;
		while ($pos+$step < $start-3)
		{
			$pos = $pos + $step;
			$arr[] = $pos;
			$arr[] = '..';
		}
	}
	for($i=$start; $i<=$end; $i++)
	{
		if (($i>0) && ($i<=$pages)) $arr[] = $i;
	}
	if (($end+2) <= $pages)
	{
		$arr[] = '..';
	}
	if ($pages-$end > 10)
	{
		// Seitensprünge
		$step = round(($pages-$end) / 2);
		$pos = $end;
		while ($pos+$step < $pages-3)
		{
			$pos = $pos + $step;
			$arr[] = $pos;
			$arr[] = '..';
		}
	}
	if ($end < $pages) $arr[] = $pages;
	return $arr;
}

function show_pages($pages, $page, $url)
{
	GLOBAL $_FORUM;
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
			echo '&nbsp;<a href="'.url($url.'&page='.$i).'" class="link">'.$i.'</a>';
		}
	}
}

function show_pages_($pages, $page, $file, $board = '', $thema = '')
{
	GLOBAL $_FORUM;
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
			echo '&nbsp;<a href="'.url($file, $board, $thema, ($i-1)*10, true, false, true).'" class="link">'.$i.'</a>';
		}
	}
}

function show_att($id, $show_delete = false, $show_insertlink = false)
{
	GLOBAL $_FORUM, $_SESSION, $_TEXT;
	if (file_exists('./data/upload/'.$id.'.ini'))
	{
		$ini = IniLoad('./data/upload/'.$id.'.ini');
		if (($ini['board'] <> '') AND (auth('auth_read', false, $ini['board'])))
		{
			echo '
				<tr>
					<td style="width:50px; text-align:center;"><a href="'.url('download.php?type=file&id='.$id).'"><img src="'.($ini['thumbnail']<>''?url('download.php?type=thumbnail&id='.$id):'styles/'.STYLE.'/images/downloadM.png').'" alt="'.$ini['filename'].'" /></a></td>
					<td style="width:100%;">
						<a href="'.url('download.php?type=file&id='.$id).'">'.$ini['filename'].'</a>
			';
			if ($ini['count'] > 0) echo ' ('.$ini['count'].'x)';
			if ($show_delete) if (IsMod($ini['board'], $_SESSION['Benutzername']) OR ($_SESSION['Benutzername'] == $ini['user'])) 
				echo ' [ <a href="'.url('do.php?do=del_file&id='.$id.'&board='.$_GET['board'].'&thema='.$_GET['thema'].'&page='.$_GET['page']).'">'.$_TEXT['DELETE'].'</a> ]';
			if (($show_insertlink) && ($ini['image']<>''))
				echo ' <a href="javascript:$.markItUp( { replaceWith:\'[img]'.url('download.php?type=image&id='.$id).'[/img]\'});">['.$_TEXT['INSERT_IMG'].']</a>';
			echo '
						<p class="sub">Mime-Type: '.$ini['type'].', '.fnum(ceil($ini['size']/1024)).' kB</p>
					</td>
				</tr>
			';
		}
	}
}

function fhtml($string)
{
 	$string = htmlentities($string);
	return $string;
}

function fnum($number)
{
	if (is_numeric($number)) $number = number_format($number,0,',','.');
	return $number;
}

function ftime($timestamp, $replace=true, $show_online = false)
{
	GLOBAL $_FORUM, $_TEXT;
	$timeformat = $_FORUM['settings_timeformat'];
	if (is_numeric($_FORUM['settings_timedif']))
		$timestamp = $timestamp + ($_FORUM['settings_timedif'] * 3600);
	if (($timestamp >= (time() - 300)) && $show_online) return $_TEXT['DATETIME_ONLINE'];
	if ($replace)
	{
		$diff = round((time()-$timestamp)/60);
		if ($diff <= 1) return MultiReplace($_TEXT['DATETIME_MINUTE'], 1);
		if ($diff <= 59) return MultiReplace($_TEXT['DATETIME_MINUTES'], $diff);
		$diff = round($diff/60);
		if ($diff <= 1) return MultiReplace($_TEXT['DATETIME_HOUR'], 1);
		if ($diff <= 12) return MultiReplace($_TEXT['DATETIME_HOURS'], $diff);
	}
	if ((version_compare(phpversion(), "5.0.0") <> -1) && ($replace))
	{
		if (date('d.m.y', time()) == date('d.m.y', $timestamp))
		{
			$timeformat = str_ireplace('d.m.y', '{1}', $timeformat);
			$timeformat = str_ireplace('d.n.y', '{1}', $timeformat);
			$timeformat = str_ireplace('d.f.y', '{1}', $timeformat);
		}
		else if (date('d.m.y', time()-86400) == date('d.m.y', $timestamp))
		{
			$timeformat = str_ireplace('d.m.y', '{2}', $timeformat);
			$timeformat = str_ireplace('d.n.y', '{2}', $timeformat);
			$timeformat = str_ireplace('d.f.y', '{2}', $timeformat);
		}
	}
	$string = date($timeformat, $timestamp);
	$string = str_replace('{1}', $_TEXT['TODAY'], $string);
	$string = str_replace('{2}', $_TEXT['YESTERDAY'], $string);
	return $string;
}

// PreviewBoard
	function CountAddChildren($id, &$ini)
	{
		GLOBAL $_BOARDS;
		if (count($_BOARDS) == 0) $_BOARDS = IniLoad('data/boards.ini');
		if (auth('auth_show', false, $id))
		{
			$temp_ini = IniLoad('data/'.$id.'/board.ini');
			$ini['count_threads'] += $temp_ini['topics'];
			$ini['count_answeres'] += $temp_ini['answeres'];
			if ($ini['lastpost_date'] < $temp_ini['lastpost_date'])
			{
				$ini['lastpost_date'] = $temp_ini['lastpost_date'];
				$ini['lastpost_board'] = $id;
				$ini['lastpost_thread'] = $temp_ini['lastpost'];
				$ini['lastpost_post'] = $temp_ini['lastpost_beitrag'];
				$ini['lastpost_from'] = $temp_ini['lastpost_from'];
				$ini['lastpost_title'] = $temp_ini['lastpost_title'];
	
			}
			if (($_SESSION['new_posts_date'] <> '') && ($_SESSION['new_posts_date'] < $temp_ini['lastpost_date']) && !$ini['new_posts'])
			{
				$ini['new_posts'] = !IsInGroup($_SESSION['new_posts_boards_seen'], $id);
			}
	
			if ($_BOARDS['b'.$id.'_children'] <> '') foreach(Group2Array($_BOARDS['b'.$id.'_children']) as $item)
				CountAddChildren(substr($item, 1, 10), $ini);
		}
		return true;
	}

function PreviewBoard($board, $class = 'w', $user_in_board = array(), $guests_in_board = '')
{
 	GLOBAL $_FORUM, $_TEXT, $_SESSION, $_BOARDS;
	$string = '';
 	$ini = IniLoad('data/'.$board.'/board.ini');
	if (count($_BOARDS) == 0) $_BOARDS = IniLoad('data/boards.ini');

	$board_ini = array();
	$board_ini['count_threads'] = 0;
	$board_ini['count_answeres'] = 0;
	$board_ini['lastpost_date'] = 0;
	$board_ini['new_posts'] = false;
	CountAddChildren($board, $board_ini);

	$sub_lines = array();

	if ($_BOARDS['b'.$board.'_children'] <> '')
	{
		$arr = array();
		foreach(Group2Array($_BOARDS['b'.$board.'_children']) as $item)
		{
			$item = substr($item, 1, 10);
			if (auth('auth_show', false, $item))
			{
				$temp_ini = IniLoad('data/'.$item.'/board.ini');
				$arr[] = '<a href="'.url('board.php', $item).'"><img src="styles/'.STYLE.'/images/boardS.png" alt="" style="vertical-align:middle;" /></a> <a href="'.url('board.php', $item).'">'.$temp_ini['title'].'</a>';
			}
		}
		if (count($arr) > 0) $sub_lines[] = implode(', ', $arr);
	}
	
	$temp = $_TEXT['TOPICS'].': '.fnum($board_ini['count_threads']).' | '.$_TEXT['ANSWERES'].': '.fnum($board_ini['count_answeres']);
	if ($ini['mods'] != '')
	{
		$arr = array();
		foreach(Group2Array($ini['mods']) as $user) $arr[] = user($user); 
		$temp .= ' | '.$_TEXT['MODERATOR'].': '.implode(', ', $arr);
	}
	$sub_lines[] = $temp;

	if ((count($user_in_board) > 0) || ($guests_in_board > 0))
	{	 				
		$temp = '<img src="styles/'.STYLE.'/images/usersS.png" alt="" /> <b>'.$_TEXT['STAT_NOW_ONLINE'].':</b> ';
		$arr = array();
		foreach($user_in_board as $user) $arr[] = user($user); 
		$temp .= implode(', ', $arr);
		if ($guests_in_board > 0)
		{
			if (count($user_in_board)>0) {$temp .= ' '.$_TEXT['STAT_AND'].' ';}
 			if ($guests_in_board == 1)
 			{
 				$temp .= $guests_in_board.' '.$_TEXT['STAT_GUEST'];
 			}
 			else
 			{
 				$temp .= $guests_in_board.' '.$_TEXT['STAT_GUESTS'];
 			}
 		}
		$sub_lines[] = $temp;
 	}

	$string .= '
 		<tr>
			<td class="'.$class.'" style="text-align:center; width:5%;"><a href="'.url('board.php',$board).'"><img src="styles/'.STYLE.'/images/'.($board_ini['new_posts']?'board_new.png':'board.png').'" alt="'.$ini['title'].'" /></a></td>
			<td class="'.$class.'" style="width:55%;"><a href="'.url('board.php',$board).'"><b>'.$ini['title'].'</b></a>
				<br />'.$ini['description'].'
				<p class="sub" style="margin-top:8px;">'.implode('<br />', $sub_lines).'</p>
			</td>
			<td class="'.$class.'" style="width:40%;">
				'.($board_ini['lastpost_date']>0?'
					<a href="'.url('thread.php', $board_ini['lastpost_board'], $board_ini['lastpost_thread'], $board_ini['lastpost_post']).'"><img src="styles/'.STYLE.'/images/gotoS.png" alt="'.$_TEXT['LAST_POST'].'" /></a>
					<a href="'.url('thread.php', $board_ini['lastpost_board'], $board_ini['lastpost_thread'], $board_ini['lastpost_post']).'"><b>'.($board_ini['lastpost_title']<>''?$board_ini['lastpost_title']:'...').'</b></a>
					<p class="sub">'.ftime($board_ini['lastpost_date']).' '.$_TEXT['BY'].' '.user($board_ini['lastpost_from']).'</p>
				':'&nbsp;').'
			</td>
		</tr>
	';
	return $string;
}

//**********************************************************************************//
//   2. Users
//**********************************************************************************//

function user($user, $showlink = true, $showsymbol = true, $showstatus = false, $bold = false)
{
	PluginHook('functions-user');
	GLOBAL $_FORUM, $_TEXT;
	$symbol = '';
	$status = '';

	if (!IsUser($user))
	{
		if ($user[0] == '@')
		{
			$user = substr($user, 1, strlen($user));
		}
		return $user;
	}
	else
	{
		if (IsAdmin($user)) 
		{
			$symbol = '<img src="styles/'.STYLE.'/images/admin.gif" border="0" alt="">';
			$status = $_TEXT['ADMINISTRATOR'];
		}
		else
		{
			GLOBAL $ALL_MODS;
			if ($ALL_MODS == '')
			{
				foreach(GetBoardsArray() as $item)
				{
					$ini = IniLoad('./data/'.$item.'/board.ini');
					$ALL_MODS .= '¿'.$ini['mods'];
				}
			}
			if (IsInGroup($ALL_MODS, $user)) 
			{
				$symbol = '<img src="styles/'.STYLE.'/images/mod.gif" border="0" alt="">';
				$status = $_TEXT['MODERATOR'];
			}
		}
		if ($symbol == '')
		{
			$points = user_points($user);
			if ($points >= $_FORUM['settings_user_ranking5'])
			{
				$symbol = '<img src="styles/'.STYLE.'/images/5.png" border="0" alt="">';
				$status = $_FORUM['settings_user_ranking5_text'];
			}
			else if ($points >= $_FORUM['settings_user_ranking4']) 
			{
				$symbol = '<img src="styles/'.STYLE.'/images/4.png" border="0" alt="">';
				$status = $_FORUM['settings_user_ranking4_text'];
			}
			else if ($points >= $_FORUM['settings_user_ranking3']) 
			{
				$symbol = '<img src="styles/'.STYLE.'/images/3.png" border="0" alt="">';
				$status = $_FORUM['settings_user_ranking3_text'];
			}
			else if ($points >= $_FORUM['settings_user_ranking2']) 
			{
				$symbol = '<img src="styles/'.STYLE.'/images/2.png" border="0" alt="">';
				$status = $_FORUM['settings_user_ranking2_text'];
			}
			else 
			{
				$symbol = '<img src="styles/'.STYLE.'/images/1.png" border="0" alt="">';
				$status = $_FORUM['settings_user_ranking1_text'];
			}
		}
		return ($showlink?'<a href="'.url('user.php', $user).'">':'').($bold?'<b>':'').$user.($bold?'</b>':'').($showlink?'</a>':'').($showsymbol?'&nbsp;'.$symbol:'').($showstatus&&($status<>'')?'<p class="sub">'.$status.'</p>':'');
	}
}

function user_points($user)
{
	$udat = IniLoad(DIR.'data/user/'.$user.'.usr.ini');
	return($udat['count_topics']*5 + $udat['count_answeres']*2 + $udat['count_answeres2'] - $udat['count_locked']*2);
}

function LoadUserIni($name)
{
	return IniLoad(DIR.'data/user/'.$name.'.usr.ini');
}

function SaveUserIni($name, $ini)
{
	return IniSave(DIR.'data/user/'.$name.'.usr.ini', $ini);
}

function IsUser($name = '%')
{
	if ($name == '%') $name = $_SESSION['Benutzername'];
	if (empty($name))
	{
		return false;
	}
	else
	{
		return file_exists(DIR.'data/user/'.$name.'.usr.ini');
	}
}

function IsMod($board, $name = '%')
{
	if ($name == '%') $name = $_SESSION['Benutzername'];
	$ausgabe = false;
	$_ADMINS = IniLoad(DIR.'data/user/Admins.grp.ini');
	$ausgabe = IsInGroup($_ADMINS['members'], $name);
	if (!$ausgabe)
	{
		$_BOARD = IniLoad(DIR.'data/'.$board.'/board.ini');
		$ausgabe = IsInGroup($_BOARD['mods'], $name);
	}
	return($ausgabe);
}

function IsAdmin($name = '%')
{
	if ($name == '%') $name = $_SESSION['Benutzername'];
	$_ADMINS = IniLoad(DIR.'data/user/Admins.grp.ini');
	return (IsInGroup($_ADMINS['members'], $name) && ($name != ''));
}

function val_user($user, $passwd)
{
	$udat = IniLoad(DIR."data/user/".$user.".usr.ini");
	return(($udat['password'] == md5($passwd)) && ($passwd!=""));
}

function val_user_md5($user, $passwd)
{
	$udat = IniLoad(DIR."data/user/".$user.".usr.ini");
	return(($udat['password'] == $passwd) && ($passwd!=""));
}

function normaliseUsername($name)
{
	$return = '';
	$allchars = "qwertzuiopasdfghjklyxcvbnmäöü1234567890";	
	$name = strtolower($name);
	for ($i = 0; $i < strlen($name); $i++)
	{
		if (is_numeric(strpos($allchars, substr($name, $i, 1))))
		{
			$return .= substr($name, $i, 1);
		}
	}
	return $return;
}

function checkUsername($name)
{
	$allchars = "qwertzuiopasdfghjklyxcvbnmQWERTZUIOPASDFGHJKLYXCVBNM1234567890_-.,:";	
	for ($i = 0; $i < strlen($name); $i++)
	{
		if (!is_numeric(strpos($allchars, substr($name, $i, 1))))
		{
			return false;
		}
	}	
	if ((strlen($name)<3) OR (strlen($name)>20))
	{
		return false;
	}
	if (file_exists(DIR."data/user/".$name.".usr.ini"))
	{
		return false;
	}
	if (file_exists(DIR."data/user/".$name.".usr.tmp"))
	{
		return false;
	}
	if (file_exists(DIR."data/user/".$name.".usr.del"))
	{
		return false;
	}
	if (is_integer(strpos($name,"admin")))
	{
		return false;
	}

	$list = LoadFileList(DIR.'data/user/', '.usr.');
	foreach ($list as $file)
	{
		$file = substr($file, 0, strpos($file, '.usr.'));
		if (normaliseUsername($file) == normaliseUsername($name)) return false;
	}

	return true;
}

function checkEmail($email)
{
	if (!ereg("^[_a-zA-Z0-9-](.{0,1}[_a-zA-Z0-9-])*@([a-zA-Z0-9-]{2,}.){0,}[a-zA-Z0-9-]{3,}(.[a-zA-Z]{2,4}){1,2}$", $email))
	{
		return false;
	}

	$list = LoadFileList(DIR.'data/user/', '.usr.');
	foreach ($list as $file)
	{
		$ini = IniLoad(DIR.'data/user/'.$file);
		if ($ini['email'] == $email) return false;
	}

	return true;
}

function user_register($new_name, $new_email)
{	
	GLOBAL $_TEXT, $_FORUM, $_SESSION;

	$error = "";
	$password_chars = "qwertzuiopasdfghjklyxcvbnmQWERTZUIOPASDFGHJKLYXCVBNM1234567890";	
	if (!checkUsername($new_name))
	{
		$error = "ERROR_USERNAME_INVALID";
	}
	else
	{
		if (!checkEmail($new_email))
			$error = "ERROR_EMAIL";
	}
	if ($error == "")
	{
		$new_passwd = "";
		mt_srand((double)microtime()*1000000);
		for ($i=1; $i<7; $i++)
			$new_passwd = $new_passwd.$password_chars[mt_rand (0,strlen($password_chars))];
		$message = $_TEXT['EMAIL_ANREDE']." ".$new_name.",\n\n".$_TEXT['EMAIL_NEW_ACCOUNT']."\n\n".$_TEXT['LOGIN_USERNAME'].": ".$new_name."\n".$_TEXT['LOGIN_PASSWORD'].": ".$new_passwd."\n\n".$_TEXT['EMAIL_FOOTER']."\n------------------------------\n".$_FORUM['settings_forum_name']." - ".$_FORUM['settings_forum_url'];
		if ($_FORUM['config_mail_addparameter'] <> '')
		{
			mail($new_email, $_FORUM['settings_forum_name'], $message, 'From: '.$_FORUM['settings_forum_name'].' <'.$_FORUM['settings_forum_email'].'>', $_FORUM['config_mail_addparameter']);
		}
		else
		{
			mail($new_email, $_FORUM['settings_forum_name'], $message, 'From: '.$_FORUM['settings_forum_name'].' <'.$_FORUM['settings_forum_email'].'>');
		}
		$udat = array();
		$udat['password'] = md5($new_passwd);
		$udat['email'] = $new_email;
		$udat['register_date'] = time();
		$udat['register_ip'] = $_SESSION['IP'];
		$udat['newsletter'] = true;
		$udat['count_topics'] = 0;
		$udat['count_answeres'] = 0;
		$udat['count_answeres2'] = 0;
		$udat['count_locked'] = 0;
		IniSave(DIR."data/user/".$new_name.".usr.tmp", $udat);
	}
	return $error;
}

function SendMessage($users, $title, $text, $from = '', $add_default_subject = true)
{
	GLOBAL $_FORUM;
	$users = array_unique($users);

	$text = str_replace("[b]","",$text);
	$text = str_replace("[/b]","",$text);
	$text = str_replace("[i]","",$text);
	$text = str_replace("[/i]","",$text);
	$text = str_replace("[u]","",$text);
	$text = str_replace("[/u]","",$text);
	$text = str_replace("[list]","",$text);
	$text = str_replace("[/list]","",$text);
	$text = str_replace("[*]","-",$text);
	$text = str_replace("[left]","",$text);
	$text = str_replace("[/left]","",$text);
	$text = str_replace("[center]","",$text);
	$text = str_replace("[/center]","",$text);
	$text = str_replace("[right]","",$text);
	$text = str_replace("[/right]","",$text);
	$text = str_replace("[justify]","",$text);
	$text = str_replace("[/justify]","",$text);
	$text = eregi_replace("\\[colour=([^\\[]*)\\]([^[]*)\\[/colour\\]","\\2",$text);
	$text = eregi_replace("\\[color=([^\\[]*)\\]([^[]*)\\[/color\\]","\\2",$text);
	$text = eregi_replace("\\[email=([^\\[]*)\\]([^[]*)\\[/email\\]","\\1",$text);
	$text = eregi_replace("\\[email\\]([^\\[]*)\\[/email\\]","\\1",$text);
	$text = eregi_replace("\\[img\\]([^[]*)\\[/img\\]","[ \\1 ]",$text);
	$text = eregi_replace("\\[url=([^\\[]*)\\]([^[]*)\\[/url\\]","\\1",$text);
	$text = eregi_replace("\\[url\\]([^\\[]*)\\[/url\\]","\\1",$text);

	$text = html_entity_decode($text);
	$title = html_entity_decode($title);


	if ($from == '') $from = $_FORUM['settings_forum_name'].' <'.$_FORUM['settings_forum_email'].'>';
	
	if ($add_default_subject)
	{
		$subject =  $_FORUM['settings_forum_name'].($title!=''?' - '.$title:'');
	}
	else
	{
		$subject =  $title;
	}

	$text = $text."\n------------------------------\n".$_FORUM['settings_forum_name']." - ".$_FORUM['settings_forum_url'];

	if (defined('CONFIG_MAIL_SEND_BCC') && (count($users)>1))
	{
		$bcc = array();
		foreach($users as $user)
		{
			$ini = IniLoad('./data/user/'.$user.".usr.ini");
			if ($ini['email'] <> '') 
			{
				$bcc[] = $ini['email'];
			}
		}
		$add_headers = "From: ".$from."\r\nBcc: ".implode(',', $bcc);
		if (defined('CONFIG_MAIL_ADDPARAMETER'))
		{
			@mail(CONFIG_MAIL_SEND_BCC, $subject, $text, $add_headers, CONFIG_MAIL_ADDPARAMETER);
		}
		else
		{
			@mail(CONFIG_MAIL_SEND_BCC, $subject, $text, $add_headers);
		}

	}
	else foreach($users as $user)
	{
		$ini = IniLoad('./data/user/'.$user.".usr.ini");
		if ($ini['email'] <> '') 
		{
			if (defined('CONFIG_MAIL_ADDPARAMETER'))
			{
				@mail($ini['email'], $subject, $text, 'From: '.$from, CONFIG_MAIL_ADDPARAMETER);
			}
			else
			{
				@mail($ini['email'], $subject, $text, 'From: '.$from);
			}
		}
	}
	return true;
}

function SendEmail($users, $subject, $message, $replace_array = '')
{
	require_once 'mail.php';
	$mail = new XMail; 
	
	GLOBAL $_FORUM, $_TEXT;

	if (!is_array($replace_array)) $replace_array = array();
	$replace_array['FORUMNAME'] = $_FORUM['settings_forum_name'];
	$replace_array['FORUMURL'] = $_FORUM['settings_forum_url'];

	$body = $_TEXT['EMAIL_TOP'].$message.$_TEXT['EMAIL_BOTTOM'];

	foreach($users as $user)
	{
		$ini = IniLoad(DIR.'data/user/'.$user.'.usr.ini');
		if ($ini['email'] <> '') 
		{
			$replace_array['NAME'] = $user;
			foreach ($replace_array as $key=>$value)
			{
				$body = str_replace('%'.$key.'%', $value, $body);
			}
			$mail->AddAddress($ini['email'], $user); 
			$mail->Subject = ($subject<>''?$subject.' | ':'').$_FORUM['settings_forum_name']; 
			$mail->Body = $body; 
			$mail->Send();
		}
	} 
}

function auth($auth, $show = true, $board2 = '')
{
	GLOBAL $_FORUM, $_TEXT, $_COUNT, $_SESSION, $LOADING_TIME, $PLUGIN_INDEXPAGE, $_SUBNAV, $MSG_ERROR;
	$user = $_SESSION['Benutzername'];
	$access = false;

	if ($board2 != '')
	{
		$board = $board2;	
	} else
	{
		$board = $_GET['board'];
	}

	if (is_numeric($board))
	{
		$access = IsMod($board, $user);
		if (!$access)
		{
			$_BOARD = IniLoad('./data/'.$board.'/board.ini');
			$groups = Group2Array($_BOARD[$auth]);
			foreach ($groups as $group)
			{
			if ($group != '')
			{
				if ($group == '*1')
				{
					if ($user != '')
					{
						$access = true;
						Break;
					}
				}
				else if ($group == '*0')
				{
					$access = true;
					Break;
				}
				else if ($user != '') 
				{
					$_GROUP = IniLoad('./data/user/'.$group.'.grp.ini');
					if (IsInGroup($_GROUP['members'], $user))
					{
						$access = true;
						Break;
					}
				}
			}
			}
		}
	}
	if ((!$access) && ($show))
	{
		if ($MSG_ERROR=='') $MSG_ERROR = $_TEXT['ACCESS_DENIED_MSG'];
		require_once 'login.php';
		Exit;
	}
	return $access;

}

function AuthUser()
{
	GLOBAL $_TEXT, $_FORUM, $LOADING_TIME_START, $_SUBNAV, $MSG_ERROR;
	if (!IsUser())
	{
		if ($MSG_ERROR=='') $MSG_ERROR = $_TEXT['ACCESS_DENIED_MSG'];
		require_once 'login.php';
		Exit;
	}
	else return true;
}

function auth_guestuser($field, $admintrue = false)
{
	GLOBAL $_FORUM, $_SESSION;
	return (($_FORUM[$field.'_guest'] && !IsUser()) || ($_FORUM[$field.'_user'] && IsUser()) || ($admintrue AND IsAdmin()));
}

function CreateBirthdayList()
{
	$ini = array();
	$users = LoadFileList(DIR.'data/user/', '.usr.ini');
	foreach ($users as $user)
	{
		$udat = IniLoad('data/user/'.$user);
		if (($udat['birthday_day'] != '') && ($udat['birthday_month'] != ''))
		{
			AddToGroup($ini[$udat['birthday_day'].'.'.$udat['birthday_month'].'.'], str_replace('.usr.ini', '', $user));
		}	
	}
	return IniSave(DIR.'data/birthday.ini', $ini);
}

function DeleteUser($username)
{
	PluginHook('functions-deleteuser');
	if (file_exists(DIR.'data/user/'.$username.'.usr.ini')) rename(DIR.'data/user/'.$username.'.usr.ini', DIR.'data/user/'.$username.'.usr.del');
	if (file_exists(DIR.'data/user/'.$username.'.usr.tmp')) rename(DIR.'data/user/'.$username.'.usr.tmp', DIR.'data/user/'.$username.'.usr.del');
	if (file_exists(DIR.'data/user/'.$username.'.gb'))
	{
		unlink(DIR.'data/user/'.$username.'.gb');
	}
	return true;
}

function EraseUser($username)
{
	PluginHook('functions-eraseuser');
	if (file_exists(DIR.'data/user/'.$username.'.gb')) unlink(DIR.'data/user/'.$username.'.gb');
	if (file_exists(DIR.'data/user/'.$username.'.usr.ini')) unlink(DIR.'data/user/'.$username.'.usr.ini');
	if (file_exists(DIR.'data/user/'.$username.'.usr.del')) unlink(DIR.'data/user/'.$username.'.usr.del');
	if (file_exists(DIR.'data/upload/av_'.$username.'.ini')) unlink(DIR.'data/upload/av_'.$username.'.ini');
	if (file_exists(DIR.'data/upload/av_'.$username.'.jpg')) unlink(DIR.'data/upload/av_'.$username.'.jpg');
	return true;
}

function ActivateUser($username)
{
	PluginHook('functions-activateuser');
	rename(DIR.'data/user/'.$username.'.usr.tmp', DIR.'data/user/'.$username.'.usr.ini');
	return true;
}

function ReactivateUser($username)
{
	PluginHook('functions-reactivateuser');
	rename(DIR.'data/user/'.$username.'.usr.del', DIR.'data/user/'.$username.'.usr.ini');
	return true;
}

//**********************************************************************************//
//   3. Format                                                                      //
//**********************************************************************************//

function format_text($text, $convert_to_html = true)
{
	GLOBAL $TRENNZEICHEN;
 	if ($convert_to_html) $text = htmlentities($text);
	$text = nl2br($text);
	$text = str_replace("\n","",$text);
	$text = str_replace(chr(13),"",$text);
	$text = str_replace($TRENNZEICHEN,"",$text);
	$text = do_ubb($text);
	$text = trim($text);
	return($text);
}

function format_string($text, $convert_to_html = false)
{
	GLOBAL $TRENNZEICHEN;
	if ($convert_to_html) $text = htmlentities($text);
	$text = str_replace($TRENNZEICHEN,"",$text);
	$text = trim($text);
	return($text);
}

function format_input($string)
{
	$string = str_replace("\"","&quot;",$string);
	return $string;
}

function format_xml($text)
{
	$text = html_entity_decode($text);
	$text = strip_tags($text);
	$text = str_replace("&","&amp;",$text);
	$text = preg_replace("!&amp;#([a_z0-9]*?);!","&$1;",$text);
	$text = trim($text);
	return $text;
}

function format_post($string)
{
	/* // /e modifier not supported
	$string = preg_replace("/\[php\](.*)\[\/php\]/esiU", "format_code('$1', 'php')", $string);
	$string = preg_replace("/\[code\](.*)\[\/code\]/esiU", "format_code('$1')", $string);
	$string = preg_replace("/\[code=([^\]]*)\](.*)\[\/code\]/esiU", "format_code('$2','$1')", $string);
	$string = preg_replace("/\[video\](.*)\[\/video\]/esiU", "format_video('$1')", $string); */
	
	$string = preg_replace_callback("/\[php\](.*)\[\/php\]/siU", "format_code_php_callback", $string);
	$string = preg_replace_callback("/\[code\](.*)\[\/code\]/siU", "format_code_callback", $string);
	$string = preg_replace_callback("/\[code=([^\]]*)\](.*)\[\/code\]/siU", "format_code_callback", $string);
	$string = preg_replace_callback("/\[video\](.*)\[\/video\]/siU", "format_video_callback", $string);
	return $string;
}

function format_code_php_callback($treffer) {
	return format_code($treffer[1], 'php');
}

function format_code_callback($treffer) {
	if(count($treffer) > 2) {
		return format_code($treffer[2], $treffer[1]);
	} else {
		return format_code($treffer[1]);
	}
}

function format_video_callback($treffer) {
	return format_video_callback($treffer[1]);
}


function format_code($code, $type = '') 
{  
	GLOBAL $codeblockid;
	$codeblockid++;
	$type = strtolower($type);
	if (!in_array($type, array('asp','autoit','csharp','css','generic','html','java','javascript','perl','php','ruby','sql','text','vbscript','xls'))) $type='text';
	$code = str_replace('\"','"',$code);
	$code = undo_ubb($code);
	$code = trim($code, " \r\n");
	$code = preg_replace("/>/","&amp;gt;",$code);
	$code = preg_replace("/</","&amp;lt;",$code);
	$height = ((preg_match_all('|\n|', $code, $temp) + 4)*16);
	if ($height<100) $height=100;
	if ($height>500) $height=500;
	return '<textarea id="codeblock_'.$codeblockid.'" class="codepress '.$type.' autocomplete-off readonly-on" style="width:97%;height:'.$height.'px;">'.$code.'</textarea>';
}

function format_video($url)
{
	$html_code = '';

	// Youtube
	if (preg_match('@youtube\.com.*v=([^&]*)@i', $url, $arr))
		$html_code = '<object width="480" height="385"><param name="movie" value="http://www.youtube-nocookie.com/v/'.$arr[1].'&fs=1&rel=0"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube-nocookie.com/v/'.$arr[1].'&fs=1&rel=0" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed></object>';
	// Dailymotion
	if (preg_match('@dailymotion\.com.*/([^/]*)$@i', $url, $arr))
		$html_code = '<object width="480" height="291"><param name="movie" value="http://www.dailymotion.com/swf/'.$arr[1].'&related=0"></param><param name="allowFullScreen" value="true"></param><param name="allowScriptAccess" value="always"></param><embed src="http://www.dailymotion.com/swf/'.$arr[1].'&related=0" type="application/x-shockwave-flash" width="480" height="291" allowFullScreen="true" allowScriptAccess="always"></embed></object>';
	// Myvideo.de
	if (preg_match('@myvideo\.de/watch/([0-9]*)/@i', $url, $arr))
		$html_code = '<object width="470" height="406"><param name="movie" value="http://www.myvideo.de/movie/'.$arr[1].'"></param><param name="AllowFullscreen" value="true"></param><param name="AllowScriptAccess" value="always"></param><embed src="http://www.myvideo.de/movie/'.$arr[1].'" width="470" height="406" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed></object>';
	// Google-Video
	if (preg_match('@video\.google\..*docid=([^&]*)@i', $url, $arr))
		$html_code = '<embed id="VideoPlayback" src="http://video.google.de/googleplayer.swf?docid='.$arr[1].'&fs=true" style="width:400px;height:326px" allowFullScreen="true" allowScriptAccess="always" type="application/x-shockwave-flash"></embed>';
	// Clipfish
	if (preg_match('@clipfish\.de/video/([^/]*)@i', $url, $arr))
		$html_code = '<object width="464" height="380"><param name="movie" value="http://www.clipfish.de/videoplayer.swf?as=0&vid='.$arr[1].'&r=1" /><param name="allowFullScreen" value="true" /><embed src="http://www.clipfish.de/videoplayer.swf?as=0&vid='.$arr[1].'&r=1" width="464" height="380" name="player" allowFullScreen="true" type="application/x-shockwave-flash"></embed></object>';
	if ($html_code == '') $html_code = 'Player-Error: <a href="'.$url.'" target="_blank">'.$url.'</a>';
	return $html_code;
}

function format_url($url, $title="", $maxwidth=60, $width1=40, $width2=-15) 
{
	if(!trim($title)) $title=$url;
	if(!preg_match("/[a-z]:\/\//si", $url)) $url = "http://$url";
	if(strlen($title)>$maxwidth && !stristr($title,"[img]")) $title = substr($title,0,$width1)."...".substr($title,$width2);
	return "<a href=\"$url\" target=\"_blank\">".str_replace("\\\"", "\"", $title)."</a>";
}

function format_img($url)
{
	GLOBAL $_FORUM;
	$style = '';
	if (defined('CONFIG_POST_IMG_MAXWIDTH'))
	{
		$size = @getimagesize($url);
		if ($size[0] > CONFIG_POST_IMG_MAXWIDTH) 
		{
			$size[0] = CONFIG_POST_IMG_MAXWIDTH;
		}
		if ($size[0] > 0) $style = 'width:'.$size[0].'px;';
	}
	if ($style == '') 
	{
		$style = 'max-width:'.(defined('CONFIG_POST_IMG_MAXWIDTH')?CONFIG_POST_IMG_MAXWIDTH.'px':'100%').';';
	}
	if ((strpos($url,'download.php?type=image') !== false) && (strpos($url,'download.php?type=image') == 0))
	{
		$id = substr($url, strpos($url, 'id=')+3, 10);
		$ini = IniLoad('data/upload/'.$id.'.ini');
		$size = @getimagesize('data/upload/'.$ini['url']);
		return '<a href="download.php?type=image_full&id='.$id.'" rel="lightbox-forum"><img src="'.$url.'" border="0" /></a>';
	}
	else
	{
		return '<a href="'.$url.'" rel="lightbox-forum"><img src="'.$url.'" style="'.$style.'" border="0" alt=""></a>';
	}
}

function format_quote($str) 
{
	GLOBAL $_TEXT; 
	$open = '<blockquote><small><b>'.$_TEXT['QUOTE'].':</b></small><hr />'; 
	$open2 = '<blockquote><small><b>$1:</b></small><hr />'; 
	$close = '</blockquote>'; 

	preg_match_all ('/\[quote([^\]]*)\]/i', $str, $matches); 
	$opentags = count($matches['0']); 
	preg_match_all ('/\[\/quote\]/i', $str, $matches); 
	$closetags = count($matches['0']); 
	$unclosed = $opentags - $closetags; 
	for ($i = 0; $i < $unclosed; $i++) 
	{ 
		$str .= $close; 
	} 
	$str = preg_replace('/\[quote\]<br \/>/i', $open, $str); 
	$str = preg_replace('/\[quote\]/i', $open, $str); 
	$str = preg_replace('/\[quote=([^\]]*)\]<br \/>/i', $open2, $str); 
	$str = preg_replace('/\[quote=([^\]]*)\]/i', $open2, $str); 
	$str = preg_replace('/\[\/quote\]<br \/>/i', $close, $str); 
	$str = preg_replace('/\[\/quote\]/i', $close, $str); 
	return $str; 
} 

function format_list($string)
{
	$string = str_replace('\"','"',$string);
	$string = str_replace("<br>[*]","<li>",$string);
	$string = str_replace("<br />[*]","<li>",$string);
	$string = str_replace("[*]","<li>",$string);
	return '<ul>'.$string.'</ul>';
}

function do_ubb($string) 
{
	GLOBAL $_FORUM, $_TEXT;
	$string = $string." ";

	if ((!($_POST['post_no_autolink']=='on')) && (strpos($_SERVER['PHP_SELF'], 'do.php') !== false))
	{
		$urlsearch[]="/([^]_a-z0-9-=\"'\/])((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\}<>]*)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\}<>\.,])/si";
		$urlsearch[]="/^((https?|ftp):\/\/|www\.)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\}<>]*)([^ \r\n\(\)\*\^\$!`\"'\|\[\]\{\}<>\.,])/si";
		$urlreplace[]="\\1[URL]\\2\\4\\5[/URL]";
		$urlreplace[]="[URL]\\1\\3\\4[/URL]";
		$emailsearch[]="/([\s>])([a-z0-9_-]+(?:\.[a-z0-9_-]+)*@(?:[0-9a-z][0-9a-z-]*[0-9a-z]\.)+(?:[a-z]{2,4}|museum))/si";
		$emailsearch[]="/^([a-z0-9_-]+(?:\.[a-z0-9_-]+)*@(?:[0-9a-z][0-9a-z-]*[0-9a-z]\.)+(?:[a-z]{2,4}|museum))/si";
		$emailreplace[]="\\1[email]\\2[/email]";
		$emailreplace[]="[email]\\0[/email]";
		$string = preg_replace($urlsearch, $urlreplace, $string);
		if (strpos($string, "@")) $string = preg_replace($emailsearch, $emailreplace, $string);
	}

	
	
	$searcharray[]='/\[b\](.*?)\[\/b\]/is';	
	$replacearray[]='<b>$1</b>';
	$searcharray[]='/\[i\](.*?)\[\/i\]/is';	
	$replacearray[]='<i>$1</i>';
	$searcharray[]='/\[u\](.*?)\[\/u\]/is';	
	$replacearray[]='<u>$1</u>';	
	$searcharray[]='/\[left\](.*?)\[\/left\]/is';	
	$replacearray[]='<div style="text-align:left;">$1</div>';
	$searcharray[]='/\[center\](.*?)\[\/center\]/is';	
	$replacearray[]='<div style="text-align:center;">$1</div>';
	$searcharray[]='/\[right\](.*?)\[\/right\]/is';	
	$replacearray[]='<div style="text-align:right;">$1</div>';
	$searcharray[]='/\[justify\](.*?)\[\/justify\]/is';	
	$replacearray[]='<div style="text-align:justify;">$1</div>';
	$searcharray[]='/\[color=(#?[a-z0-9]*)](.*?)\[\/color\]/is';
	$replacearray[]='<span style="color:$1;">$2</span>';
	$searcharray[]='/\[email=([a-z0-9_-]+(?:\.[a-z0-9_-]+)*@(?:[0-9a-z][0-9a-z-]*[0-9a-z]\.)+(?:[a-z]{2,4}|museum))\](.*?)\[\/email\]/is';
	$replacearray[]='<a href="mailto:$1">$2</a>';
	$searcharray[]='/\[email\]([a-z0-9_-]+(?:\.[a-z0-9_-]+)*@(?:[0-9a-z][0-9a-z-]*[0-9a-z]\.)+(?:[a-z]{2,4}|museum))\[\/email\]/is';
	$replacearray[]='<a href="mailto:$1">$1</a>';
	$searcharray[]='/\[youtube\]([a-z0-9_-]*?)\[\/youtube\]/is';	
	$replacearray[]='<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/$1&fs=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/$1&fs=1" type="application/x-shockwave-flash" allowfullscreen="true" width="425" height="344"></embed></object>';
	$string = preg_replace($searcharray, $replacearray, $string);
	
	/* // /e modifier not support under PHP7
	$searcharray[]="/\[url=(['\"]?)([^\"']*)\\1](.*)\[\/url\]/esiU";
	$replacearray[]="format_url('\\2','\\3')";
	$searcharray[]="/\[url]([^\"]*)\[\/url\]/esiU";	
	$replacearray[]="format_url('\\1')";
	$searcharray[]="/\[img]([^\"\]\[]*)\[\/img\]/esiU";	
	$replacearray[]="format_img('\\1')";
	$searcharray[]='/\[list\](.*)\[\/list\]/esiU';	
	$replacearray[]="format_list('\\1')";*/
	$string = preg_replace_callback("/\[url=(['\"]?)([^\"']*)\\1](.*)\[\/url\]/siU", "format_url_callback", $string);
	$string = preg_replace_callback("/\[url]([^\"]*)\[\/url\]/siU", "format_url_callback", $string);
	$string = preg_replace_callback("/\[img]([^\"\]\[]*)\[\/img\]/siU", "format_img_callback", $string);
	$string = preg_replace_callback('/\[list\](.*)\[\/list\]/siU', "format_list_callback", $string);
	

	$string = format_quote($string);

	$smilies = IniLoad('./styles/'.STYLE.'/smilies/smilies.ini');
	foreach (array_keys($smilies) as $item)
	{
		$string = str_replace($item,'<img src="styles/'.STYLE.'/smilies/'.$smilies[$item].'">',$string);
	}
	return($string);
}

function format_url_callback($treffer) {
	if(count($treffer) > 3) {
		return format_url($treffer[2], $treffer[3]);
	} else {
		return format_url($treffer[1]);
	}
}

function format_img_callback($treffer) {
	return format_img($treffer[1]);
}

function format_list_callback($treffer) {
	return format_list($treffer[1]);
}



function undo_ubb($string) 
{
	GLOBAL $_FORUM, $_TEXT;

	$pos = strpos($string, '<br /><br /><b>'.$_TEXT['EDITED'].' ');
	if (is_numeric($pos))
	{
		$string = substr($string, 0, $pos);
	} 
	$pos = strpos($string, '<br /><br /><p class="sub">');
	if (is_numeric($pos))
	{
		$string = substr($string, 0, $pos);
	} 

	$smilies = IniLoad('./styles/'.STYLE.'/smilies/smilies.ini');
	foreach (array_keys($smilies) as $item)
	{
		$string = str_replace('<img src="styles/'.STYLE.'/smilies/'.$smilies[$item].'">', $item, $string);
	}

	$searcharray = array();
	$replacearray = array();

	$searcharray[] = '/<a href="download\.php\?type=file&id=([0-9]*)"([^>]*)><img src="download\.php\?type=image(?:&amp;|&)id=\\1"(?:[^>]*)><\/a>/is';
	$replacearray[] = '[img]download.php?type=image&id=$1[/img]';
	$searcharray[] = '/<a href="download\.php\?type=image_full&id=([0-9]*)"([^>]*)><img src="download\.php\?type=image(?:&amp;|&)id=\\1"(?:[^>]*)><\/a>/is';
	$replacearray[] = '[img]download.php?type=image&id=$1[/img]';
	$searcharray[] = '/<a href="([^"]*)"(?:[^>]*)><img src="\\1"(?:[^>]*)><\/a>/is';
	$replacearray[] = '[img]$1[/img]';
	$searcharray[] = '/<img src="([^"]*)"(?:[^>]*)>/is';
	$replacearray[] = '[img]$1[/img]';
	$searcharray[] = '/<a href="mailto:([^>\[]*)">(.*?)<\/a>/is';
	$replacearray[] = '[email=$1]$2[/email]';
	$searcharray[] = '/<a href="([^"]*)" target="_blank">(.*?)<\/a>/is';
	$replacearray[] = '[url=$1]$2[/url]';
	$searcharray[] = '/<font color=(#?[a-z0-9]*)>(.*?)<\/font>/is';
	$replacearray[] = '[color=$1]$2[/color]';
	$searcharray[] = '/<span style="color:(#?[a-z0-9]*);">(.*?)<\/span>/is';
	$replacearray[] = '[color=$1]$2[/color]';
	$searcharray[] = '/<blockquote><smallfont><p>'.$_TEXT['QUOTE'].':<\/smallfont><hr>/is';
	$replacearray[] = '[quote]';
	$searcharray[] = '/<blockquote><small><b>'.$_TEXT['QUOTE'].':<\/b><\/small><hr \/>/is';
	$replacearray[] = '[quote]'."\n";
	$searcharray[] = '/<blockquote><small><b>(.*?):<\/b><\/small><hr \/>/is';
	$replacearray[] = '[quote=$1]'."\n";
	$searcharray[] = '/<hr><\/blockquote>/is';
	$replacearray[] = '[/quote]';
	$searcharray[] = '/<\/blockquote>/is';
	$replacearray[] = '[/quote]'."\n";
	$searcharray[]='/<object width="425" height="344"><param name="movie" value="http:\/\/www.youtube.com\/v\/([a-z0-9_-]*?)&fs=1"><\/param><param name="allowFullScreen" value="true"><\/param><embed src="http:\/\/www.youtube.com\/v\/([a-z0-9_-]*?)&fs=1" type="application\/x-shockwave-flash" allowfullscreen="true" width="425" height="344"><\/embed><\/object>/is';
	$replacearray[]='[video]http://www.youtube.com/watch?v=$1[/video]';	
	$searcharray[] = '/<div style="text-align:left;">(.*?)<\/div>/is';
	$replacearray[] = '[left]$1[/left]';
	$searcharray[] = '/<div style="text-align:center;">(.*?)<\/div>/is';
	$replacearray[] = '[center]$1[/center]';
	$searcharray[] = '/<div style="text-align:right;">(.*?)<\/div>/is';
	$replacearray[] = '[right]$1[/right]';
	$searcharray[] = '/<div style="text-align:justify;">(.*?)<\/div>/is';
	$replacearray[] = '[justify]$1[/justify]';

	$string = preg_replace($searcharray, $replacearray, $string);

	$string = str_replace("<br>","\n",$string);
	$string = str_replace("<br />","\n",$string);
	$string = str_replace("<b>","[b]",$string);
	$string = str_replace("</b>","[/b]",$string);
	$string = str_replace("<i>","[i]",$string);
	$string = str_replace("</i>","[/i]",$string);
	$string = str_replace("<u>","[u]",$string);
	$string = str_replace("</u>","[/u]",$string);
	$string = str_replace("<ul>","[list]",$string);
	$string = str_replace("</ul>","[/list]",$string);
	$string = str_replace("<center>","[center]",$string);
	$string = str_replace("</center>","[/center]",$string);
	$string = str_replace("<li>","\n[*]",$string);

	return($string);
} 


//**********************************************************************************//
// 4. Others
//**********************************************************************************//

function InStr($chars, $string)
{
	if ($chars == '') return true;
	return strpos($string, $chars) !== false;
}  

function DeleteDir($dir)
{
	$verz=@opendir($dir);
	@rewinddir($verz);
	while ($file = @readdir($verz)) 
	{
		if (($file!=".") && ($file!=".."))
		{
			if (is_dir($dir.$file))
			{
				DeleteDir($dir.$file.'/');
			}
			else
			{
				@unlink($dir.$file);
			}
		}
	}
	@closedir($verz);
	@rmdir($dir);
}

function LoadFileList($dir, $filter = '')
{
	$verz=opendir($dir);
	rewinddir($verz);
	$files = array();
	while (($file = readdir($verz)) !== false)
	{
		if (($file!=".") && ($file!=".."))
   		{
			if ((InStr($filter, $file)) OR ($filter==''))
				array_push($files,$file);
		}
	}
	closedir($verz);
	if (count($files)>0) natcasesort($files);
	return($files);
}

function FileLoad($filename)
{
	GLOBAL $TRENNZEICHEN;
	if (File_Exists($filename))
	{
		$data = file($filename);
		for ($i=0; $i<count($data); $i++)
		{
			$data[$i] = explode($TRENNZEICHEN, $data[$i]);
		}
		return($data);
	}
	return array();
}

function FileSave($filename, $data)
{
	GLOBAL $TRENNZEICHEN, $_TEXT;
	if ($filename <> '')
	{
		if (is_writeable($filename) OR (!file_exists($filename)))
		{
			if (!$source = fopen($filename, "w"))
			{
				LogAppend($_TEXT['ERROR_NOT_EXEC'].' (#171, '.$filename.')');
			}
			else
			{
				foreach($data as $line)
				{
					$addline = "";
					for ($i=0; $i<10; $i++)
					{
						$addline = $addline.$line[$i].$TRENNZEICHEN;
					}
					$addline = str_replace(chr(13).$TRENNZEICHEN,"",$addline);
					$addline = str_replace("\n".$TRENNZEICHEN,"",$addline);
					if (substr($addline, 0, 2) != "-".$TRENNZEICHEN)
					{
						fputs($source, $addline."\n");
					}
				}
				fclose($source);
			}
		}
		else
		{
			LogAppend($_TEXT['ERROR_NOT_EXEC'].' (#170, '.$filename.')');
		}
	}
}

function FileAppend($filename, $line)
{
	if (!file_exists($filename)) fclose(fopen($filename, "w"));
	$data = FileLoad($filename);
	array_push($data, $line);
	FileSave($filename, $data);
}

function FileDeleteLine($filename, $deleteline)
{
	$data = FileLoad($filename);
	$data[$deleteline][0] = "-";
	FileSave($filename, $data);
}

function IniLoad($filename)
{
	$RESULT = array();
	if (file_exists($filename))
	{
		$lines = false;
		$count = 0;
		while (($lines == false) AND ($count < 100))
		{
			$lines = file($filename);
			$count++;
		}
		foreach ($lines as $line)
		{
	            $pos = strpos($line, '=');
	            if (is_integer($pos))
	            {
	                $RESULT[strtolower(substr($line,0,$pos))] = trim(substr($line, $pos+1, strlen($line)));
			if (trim(substr($line, $pos+1, strlen($line))) == 'false')
				$RESULT[strtolower(substr($line,0,$pos))] = false;
	            }
		}
	}
	return $RESULT;
}

function IniSave($filename, $ini, $saveEmpty = false)
{
	GLOBAL $TRENNZEICHEN, $_TEXT;
	if (count($ini) == 0)
	{
		if ($saveEmpty)
		{
			@unlink($filename);
			return true;
		}
		else
			return false;
	}
	else if (is_writeable($filename) OR (!file_exists($filename)))
	{
		if (!$source = fopen($filename, "w"))
		{
			LogAppend($_TEXT['ERROR_NOT_EXEC'].' (#161, '.$filename.')');
		}
		else
		{
			$keys = array_keys($ini);
			natsort($keys);
			foreach ($keys as $key)
			{
				fwrite($source, $key.'='.trim($ini[$key])."\n");
			}
			fclose($source);
		}
	}
	else
	{
		LogAppend($_TEXT['ERROR_NOT_EXEC'].' (#160, '.$filename.')');
	}
	return true;
}

function Group2Array($string)
{
  GLOBAL $TRENNZEICHEN;
  $array = explode($TRENNZEICHEN, trim($string, $TRENNZEICHEN."\n"));
  if ((count($array) == 1) AND (trim($array[0]) == '')) $array = array();
  return $array;
}

function Array2Group($array, $sort=true)
{
    GLOBAL $TRENNZEICHEN;
    $array = array_unique($array);
    if ($sort) natcasesort($array);
    return trim(implode($TRENNZEICHEN, $array), $TRENNZEICHEN);
}

function IsInGroup($string, $name)
{
    return (in_array($name, Group2Array($string)) && ($name != ''));
}

function AddToGroup(&$string, $name, $sort=true)
{
    $array = Group2Array($string);
    array_push($array, $name);
    $string = Array2Group($array, $sort);
    return $string;
}

function DeleteFromGroup(&$string, $name)
{
    $array = Group2Array($string);
    $array = array_diff($array, array($name));
    $string = Array2Group($array, false);
    return $string;
}

function Group2SubGroup($group)
{
	GLOBAL $TRENNZEICHEN, $TRENNZEICHEN2;
	return str_replace($TRENNZEICHEN, $TRENNZEICHEN2, $group);
}

function SubGroup2Group($group)
{
	GLOBAL $TRENNZEICHEN, $TRENNZEICHEN2;
	return str_replace($TRENNZEICHEN2, $TRENNZEICHEN, $group);
}

function history($board, $thema, $beitrag)
{
	GLOBAL $history_filename;

	FileAppend($history_filename, array($board, $thema, $beitrag));
	// Datensätze überprüfen
	$hist = FileLoad($history_filename);
	if (count($hist) > 1000)
	{
		$hist = array_slice($hist, -1000);
	}
	FileSave($history_filename, $hist);
	history_check();
}

function history_delete($board, $thema, $beitrag)
{
	GLOBAL $history_filename;

	$hist = FileLoad($history_filename);
	for ($i = 0; $i < count($hist); $i++)
	{
		if (($hist[$i][0] == $board) && ($hist[$i][1] == $thema) && ($hist[$i][2] == $beitrag))
		{
			$hist[$i][0] = "-";
		}		
		if (($hist[$i][0] == $board) && ($hist[$i][1] == $thema) && ($hist[$i][2] > $beitrag))
		{
			$hist[$i][2]--;
		}		
	}
	FileSave($history_filename, $hist);
}

function history_move($board, $thema, $to_board, $to_thema)
{
	GLOBAL $history_filename;

	$hist = FileLoad($history_filename);
	for ($i = 0; $i < count($hist); $i++)
	{
		if (($hist[$i][0] == $board) && ($hist[$i][1] == $thema))
		{
			$hist[$i][0] = $to_board;
			$hist[$i][1] = $to_thema;
		}		
	}
	FileSave($history_filename, $hist);
}

function history_check()
{
	GLOBAL $history_filename;

	$hist = FileLoad($history_filename);
	for ($i = 0; $i < count($hist); $i++)
	{
		$post_filename = DIR.'data/'.$hist[$i][0].'/'.$hist[$i][1].'.txt';
		if (!file_exists($post_filename))
		{
			$hist[$i][0] = '-';
		}
		else
		{
			$post = file($post_filename);
			if (count($post) <= $hist[$i][2])
			{
				$hist[$i][0] = '-';
			}
		}
	}
	FileSave($history_filename, $hist);
}

function history_text($ptitel, $ptext, $length)
{
	if ($ptitel <> "") $ptext = $ptitel." - ".$ptext;
	$ptext = str_replace("<br />"," ",$ptext);
	$ptext = strip_tags($ptext);
	$ptext = str_replace("\"","&quot;",$ptext);
	$ptext = substr($ptext, 0, $length + strpos(substr($ptext." ",$length,20), " "))." ...";
	return $ptext;
}

function getBrowser($useragent)
{
	GLOBAL $_TEXT;
	$browser = '';
	if(ini_get('browscap')) 
	{
		$inf = @get_browser($useragent, true);
		if (($inf['browser'] <> 'Default Browser') && ($inf['version'] > 0))
		{
			$browser = $inf['browser'].' '.$inf['version'];
		}
	}
	if ($browser == '')
	{
		if(eregi("safari", $useragent)) $browser = "Safari";
		elseif(eregi("(opera) ([0-9]{1,2}.[0-9]{1,3}){0,1}", $useragent, $regs) || eregi("(opera/)([0-9]{1,2}.[0-9]{1,3}){0,1}", $useragent, $regs)) $browser = "Opera $regs[2]";
		elseif(eregi("(navigator)/([0-9]{1,2}.[0-9]{1,2})", $useragent, $regs)) $browser = "Netscape $regs[2]";
		elseif(eregi("(firefox)/([0-9]{1,2}.[0-9]{1,2})", $useragent, $regs)) $browser = "Firefox $regs[2]";
		elseif(eregi("(firebird)/([0-9]{1,2}.[0-9]{1,2})", $useragent, $regs)) $browser = "Firebird $regs[2]";
		elseif(eregi("(msie) ([0-9]{1,2}.[0-9]{1,3})", $useragent, $regs)) $browser = "Internet Explorer $regs[2]";
		elseif(eregi("(konqueror)/([0-9]{1,2}.[0-9]{1,3})", $useragent, $regs)) $browser = "Konqueror $regs[2]";
		elseif(eregi("(lynx)/([0-9]{1,2}.[0-9]{1,2}.[0-9]{1,2})", $useragent, $regs)) $browser = "SeaMonkey $regs[2]";
		elseif(eregi("(seamonkey)/([0-9]{1,2}.[0-9]{1,2}.[0-9]{1,2})", $useragent, $regs)) $browser = "Lynx $regs[2]";
		elseif(eregi("(netscape6)/(6.[0-9]{1,3})", $useragent, $regs)) $browser = "Netscape $regs[2]";
		elseif(eregi("(netscape)/([7-8]{1,1}.[0-9]{1,3})", $useragent, $regs)) $browser = "Netscape $regs[2]";
		elseif(eregi("(mozilla)/(4.[0-9]{1,3})", $useragent, $regs)) $browser = "Communicator $regs[2]";
		elseif(eregi("(bonecho)/([0-9]{1,2}.[0-9]{1,3})", $useragent, $regs)) $browser = "BonEcho $regs[2]";
		elseif(eregi("(k-meleon)/([0-9]{1,2}.[0-9]{1,3})", $useragent, $regs)) $browser = "K-Meleon $regs[2]";
		elseif(eregi("(phoenix)/([0-9]{1,2}.[0-9]{1,3})", $useragent, $regs)) $browser = "Phoenix $regs[2]";
		elseif(eregi("(minefield)/([0-9]{1,2}.[0-9]{1,3})", $useragent, $regs)) $browser = "Minefield $regs[2]";
		elseif(eregi("(mozilla)/5.0(.*)rv:([0-9a-z.]*)", $useragent, $regs)) $browser = "Mozilla $regs[3]";
		elseif(eregi("w3m", $useragent)) $browser = "w3m";
	}
	if ($browser == '')
	{
		$browser = '<a title="'.$useragent.'">'.$_TEXT['STAT_BROWSER_UNKNOWN'].'</a>';	
	}
	return $browser;
	
}

function RepairThreadIni($board, $thread)
{
	$ini = IniLoad(DIR.'data/'.$board.'/'.$thread.'.txt.ini');
	$data = FileLoad(DIR.'data/'.$board.'/'.$thread.'.txt');
	$gesamt = count($data);
	$ini['title'] = $data[0][2];
	$ini['answers'] = $gesamt-1;
	$ini['author'] = $data[0][1];
	$ini['lastpost_date'] = $data[$gesamt-1][0];
	$ini['lastpost_from'] = $data[$gesamt-1][1];
	$ini['lastpost_title'] = $data[$gesamt-1][2];
	$ini['attachment'] = false;
	$ini['poll'] = false;
	for ($i = 0; $i < $gesamt; $i++)
	{
		if ($data[$i][5] <> '') $ini['attachment'] = true;
		if ($data[$i][6] <> '') $ini['poll'] = true;
	}
	return IniSave(DIR.'data/'.$board.'/'.$thread.'.txt.ini', $ini);
}

function RepairBoardIni($board)
{
	$_BOARD = IniLoad(DIR.'data/'.$board.'/board.ini');
	$_BOARD['topics'] = 0;
	$_BOARD['answeres'] = 0;
	$_BOARD['lastpost'] = '';
	$_BOARD['lastpost_date'] = 0;
	$files = LoadFileList(DIR.'data/'.$board.'/', '.txt');
	foreach ($files as $file)
	{
		if (is_numeric(str_replace('.txt', '', $file)))
		{
			$lines = FileLoad(DIR.'data/'.$board.'/'.$file);
			$line = $lines[count($lines)-1]; 
			if (($line[0] > $_BOARD['lastpost_date']) && (is_numeric($line[0])))
			{
				$_BOARD['lastpost'] = str_replace('.txt', '', $file);
				$_BOARD['lastpost_date'] = $line[0];
				$_BOARD['lastpost_from'] = $line[1];		
				$_BOARD['lastpost_title'] = $line[2];	
				$_BOARD['lastpost_beitrag'] = count($lines)-1;	
			}
			$_BOARD['answeres'] += count($lines)-1;
			$_BOARD['topics']++;
		}
	}
	return IniSave(DIR.'data/'.$board.'/board.ini', $_BOARD);
}

function RepairBoardsIni()
{
	$ini = IniLoad(DIR.'data/boards.ini');
	$list = Group2Array($ini['order']);
	$last_layer = 0;
	foreach($list as $item)
	{
		if (substr($item, 0, 1) == 'c')
		{
			$ini[$item.'_layer'] = 0;
			$last_layer = 0;
		}
		else
		{
			$layer = $ini[$item.'_layer'];
			if ($layer < 1) $layer = 1;
			if ($layer > $last_layer+1) $layer = $last_layer+1;
			$ini[$item.'_layer'] = $layer;
			$last_layer = $layer;
		}
		$ini[$item.'_parent'] = '';
		$ini[$item.'_children'] = '';
	}
	$ini['index_children'] = '';
	$ini['x_children'] = '';
	if ($ini[$list[0].'_layer'] == 1)
	{
		AddToGroup($ini['index_children'], 'x', false);
		for ($j=0; $j<count($list); $j++)
		{
			if ($ini[$list[$j].'_layer'] == 1)
			{
				AddToGroup($ini['x_children'], $list[$j], false);
			}
			if ($ini[$list[$j].'_layer'] < 1)
			{
				Break;
			}
		}
			


	}
	for ($i = 0; $i<count($list); $i++)
	{
		if ($ini[$list[$i].'_layer'] == 0)
		{
			AddToGroup($ini['index_children'], $list[$i], false);
		}
		for ($j=($i+1); $j<count($list); $j++)
		{
			if (($ini[$list[$i].'_layer']+1) == $ini[$list[$j].'_layer'])
			{
				AddToGroup($ini[$list[$i].'_children'], $list[$j], false);
				$ini[$list[$j].'_parent'] = $list[$i];
			}
			if (($ini[$list[$i].'_layer']) >= $ini[$list[$j].'_layer'])
			{
				Break;
			}
		}
	}
	return IniSave(DIR.'data/boards.ini', $ini);
}

function DeleteThread($board, $thread)
{
	$data = FileLoad(DIR.'data/'.$board.'/'.$thread.'.txt');
	$_THEMA = IniLoad(DIR.'data/'.$board.'/'.$thread.'.txt.ini');
	if (IsUser($data[0][1]))
	{
		$udat = IniLoad(DIR.'data/user/'.$data[0][1].'.usr.ini');
		$udat['count_topics']--;
		$udat['count_answeres2'] = $udat['count_answeres2'] - (count($data)-1);
		if ($_THEMA['lock']) $udat['count_locked']--;
		IniSave(DIR.'data/user/'.$data[0][1].'.usr.ini', $udat);
	}
	for ($x = 0; $x<count($data); $x++)
	{
		if ($data[$x][5]<>'')
		{
			foreach (Group2Array(SubGroup2Group($data[$x][5])) as $att)
			{
				$ini = IniLoad(DIR.'data/upload/'.$att.'.ini');
				if (count($ini) > 0)
				{
					if ($ini['url'] <> '') @unlink(DIR.'data/upload/'.$ini['url']);
					if ($ini['thumbnail'] <> '') @unlink(DIR.'data/upload/'.$ini['thumbnail']);
					if ($ini['image'] <> '') @unlink(DIR.'data/upload/'.$ini['image']);
				}
				@unlink(DIR.'data/upload/'.$att.'.ini');
			}
		}
		if ($data[$x][6]<>'')
		{
			@unlink(DIR.'data/poll_'.$data[$x][6].'.ini');
		}
		if (IsUser($data[$x][1]) && ($x > 0))
		{
			$udat = IniLoad(DIR.'data/user/'.$data[$x][1].'.usr.ini');
			$udat['count_answeres']--;
			IniSave(DIR.'data/user/'.$data[$x][1].'.usr.ini', $udat);
		}
	}

	unlink(DIR.'data/'.$board.'/'.$thread.'.txt');
	unlink(DIR.'data/'.$board.'/'.$thread.'.txt.ini');
	RepairBoardIni($board);
	history_check();
	return true;
}

function DeletePost($board, $thread, $post)
{	
	$data = FileLoad(DIR.'data/'.$board.'/'.$thread.'.txt');
	$_THEMA = IniLoad(DIR.'data/'.$board.'/'.$thread.'.txt.ini');
	if (IsUser($data[0][1]))
	{
		$udat = IniLoad(DIR.'data/user/'.$data[0][1].'.usr.ini');
		$udat['count_answeres2'] = $udat['count_answeres2'] - 1;
		IniSave(DIR.'data/user/'.$data[0][1].'.usr.ini', $udat);
	}
	if (IsUser($data[$post][1]))
	{
		$udat = IniLoad(DIR.'data/user/'.$data[$post][1].'.usr.ini');
		$udat['count_answeres'] = $udat['count_answeres'] - 1;
		IniSave(DIR.'data/user/'.$data[$post][1].'.usr.ini', $udat);
	}
	if ($data[$post][5]<>'')
	{
		foreach (Group2Array(SubGroup2Group($data[$post][5])) as $att)
		{
			$ini = IniLoad(DIR.'data/upload/'.$att.'.ini');
			unlink(DIR.'data/upload/'.$ini['url']);
			if ($ini['thumbnail'] <> '') unlink(DIR.'data/upload/'.$ini['thumbnail']);
			if ($ini['image'] <> '') unlink(DIR.'data/upload/'.$ini['image']);
			unlink(DIR.'data/upload/'.$att.'.ini');
		}
	}
	if ($data[$post][6]<>'')
	{
		@unlink(DIR.'data/poll_'.$data[$post][6].'.ini');
	}
	$data[$post][0] = '-';
	FileSave(DIR.'data/'.$board.'/'.$thread.'.txt', $data);
	RepairThreadIni($board, $thread);
	RepairBoardIni($board);
	history_delete($board, $thread, $post);
	return true;
}		

function GetBoardsArray()
{
	$ini = IniLoad(DIR.'data/boards.ini');
	$arr = array();
	foreach(Group2Array($ini['order']) as $item)
	{
		if (substr($item, 0, 1) == 'b')
		{
			$arr[] = substr($item, 1, 100);
		}
	}
	return $arr;
}

function GetBoardsOptions($selected = '')
{
	$string = '';
	$ini = IniLoad(DIR.'data/boards.ini');
	$optgroup_open = false;
	foreach(Group2Array($ini['order']) as $item)
	{
		if (($ini[$item.'_layer'] == 0) && $optgroup_open)
		{
			$string .= '</optgroup>';
			$optgroup_open = false;
		}
		if (substr($item, 0, 1) == 'c')
		{
			$string .= '<optgroup label="'.$ini[$item].'">';
			$optgroup_open = true;
		}
		if (substr($item, 0, 1) == 'b')
		{
			$board_id = substr($item, 1, 100);
			if (auth('auth_show', false, $board_id))
			{
				$board_ini = IniLoad(DIR.'data/'.$board_id.'/board.ini');
				$add = '';
				for ($i = 2; $i <= $ini[$item.'_layer']; $i++) $add .= '&nbsp;&nbsp;';
				if ($ini[$item.'_layer']>1) $add .= '- ';
				$string .= '<option value="'.$board_id.'" '.(IsInGroup($selected, $board_id)?'selected="selected"':'').'>'.$add.$board_ini['title'].'</option>';
			}
		}
	}
	return $string;
}

function GetBoardParents($board)
{
	$arr = array();
	$ini = IniLoad(DIR.'data/boards.ini');
	$item = 'b'.$board;
	while($ini[$item.'_parent'] <> '')
	{
		$item = $ini[$item.'_parent'];
		if (substr($item, 0, 1) == 'c')
		{
			$arr[] = array($ini[$item], url('forum.php#'.$item), '');
		}
		if (substr($item, 0, 1) == 'b')
		{
			$_BOARD = IniLoad(DIR.'data/'.substr($item, 1, 100).'/board.ini');
			$arr[] = array($_BOARD['title'], url('board.php', substr($item, 1, 100)), 'boardS.png');
		}
	}
	return array_reverse($arr);
}

function ImageResize($src_filename, $dest_filename, $max_width, $max_height=NULL, $dest_type = NULL, $insert_resize_image = false)
{
	if (!extension_loaded('gd') && !extension_loaded('gd2')) return false;
	$src_info = getimagesize($src_filename);
	$width = $src_info[0];
	$height = $src_info[1];
	$image_type = $src_info[2];
	$bitdepth = $src_info['bits'];
	$channels = $src_info['channels'];
	if (($image_type < 1) OR ($image_type > 3)) return false;
	if (is_null($dest_type)) $dest_type = $image_type;
	if (is_null($max_height)) $max_height = $height;
	$x_ratio = $max_width / $width;
	$y_ratio = $max_height / $height;
	if(($width <= $max_width) && ($height <= $max_height))
	{
        	$tn_width = $width;
	        $tn_height = $height;
		if ((!$insert_resize_image) && ($dest_type == $image_type))
		{
			return @copy($src_filename, $dest_filename);
		}
        }
	elseif (($x_ratio * $height) < $max_height)
	{
		$tn_height = ceil($x_ratio * $height);
		$tn_width = $max_width;
        }
	else
	{
		$tn_width = ceil($y_ratio * $width);
		$tn_height = $max_height;
    	}
	if (function_exists("memory_get_usage"))
	{
		$memory_limit = @ini_get("memory_limit");
		if($memory_limit AND $memory_limit > -1)
		{
			$limit = preg_match("#^([0-9]+)\s?([kmg])b?$#i", trim(strtolower($memory_limit)), $matches);
			$memory_limit = 0;
			if($matches[1] && $matches[2])
			{
				switch($matches[2])
				{
					case "k":
						$memory_limit = $matches[1] * 1024;
						break;
					case "m":
						$memory_limit = $matches[1] * 1048576;
						break;
					case "g":
						$memory_limit = $matches[1] * 1073741824;
				}
			}
			$free_memory = $memory_limit - memory_get_usage();
			$tn_memory = round(($width * $height * $bitdepth * $channels / 8) * 5);
			$tn_memory += round(($tn_width * $tn_height * $bitdepth * $channels / 8) * 5);
			if ($tn_memory > $free_memory) return false;
		}
	}
	switch ($image_type)
	{
		case 1: $src = imagecreatefromgif($src_filename); break;
		case 2: $src = imagecreatefromjpeg($src_filename); break;
		case 3: $src = imagecreatefrompng($src_filename); break;
		default: return false;  break;
	}
	$tmp = imagecreatetruecolor($tn_width,$tn_height);
	if ($dest_type == 3)
	{
	        imagealphablending($tmp, false);
	        imagesavealpha($tmp,true);
	        $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
	        imagefilledrectangle($tmp, 0, 0, $tn_width, $tn_height, $transparent);
	}
	$trans_color = imagecolortransparent($src);
	if($trans_color >= 0 && $trans_color < imagecolorstotal($src))
	{
		$trans = imagecolorsforindex($src, $trans_colors);
		$new_trans_color = imagecolorallocate($tmp, $trans['red'], $trans['blue'], $trans['green']);
		imagefill($tmp, 0, 0, $new_trans_color);
		imagecolortransparent($tmp, $new_trans_color);
	}

	imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);
	imagedestroy($src);
	if (($insert_resize_image) && ($tn_width < $width) && ($tn_height < $height))
	{
	        imagealphablending($tmp, true);
	        imagesavealpha($tmp,true);
		$resize = ImageCreateFromPNG(DIR.'images/resize.png');
		imagecopy($tmp, $resize, $tn_width-50, 0, 0, 0, 50, 50);
		imagedestroy($resize);
	}
	switch ($dest_type)
	{
		case 1: imagegif($tmp, $dest_filename); break;
		case 2: imagejpeg($tmp, $dest_filename, 100);  break; 
 		case 3: imagepng($tmp, $dest_filename, 0); break; 
		default: return false; break;
	}
	imagedestroy($tmp);
	return true;	
}

function CreateSearchArray($string, $minlength = 1)
{
	$array = array();
	$temp = '';
	$open_quote = false;
	$save = false;
	for ($i = 0; $i < strlen($string); $i++)
	{
		$char = substr($string, $i, 1);
		if (($char == '"') && (!$open_quote))
		{
			$open_quote = true;
			$save = true;
		}
		else if (($char == '"') && ($open_quote))
		{
			$open_quote = false;
			$save = true;
		}
		else if (($char == ' ') &&  (!$open_quote))
		{
			$save = true;
		}
		else
		{
			$temp .= $char;
		}
		if (($i+1) == strlen($string))
		{
			$save = true;
			if ($open_quote) $save_with_quote = true;
		}
		if ($save)
		{
			$temp = strtolower(trim($temp));
			if (strlen($temp) >= $minlength)
			{
				$array[] = $temp;
			}
			$temp = '';
			$save = false;
		}
	}
	return $array;
}

function CreateSearchText($array)
{
	if (count($array) == 0) return '';
	foreach ($array as $key=>$value)
	{
		if (InStr(' ', $value)) $array[$key] = '"'.$value.'"';		
	}
	return implode(' ', $array);
}

function GetFileList($pattern)
{
	$list = array();
	$temp = glob(DIR.$pattern);
	if (is_array($temp)) foreach ($temp as $item)
	{
		if (!is_dir($item)) $list[] = $item;
	}
	return $list;
}
?>