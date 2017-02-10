<?PHP
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

$_BREADCRUMBS_IMAGE = '';
$_BREADCRUMBS = '';
if ((is_object($PLUGIN_INDEXPAGE)) && in_array(basename($_SERVER['PHP_SELF']), array('', 'index.php')))
{
	$_BREADCRUMBS_IMAGE = '<a href="'.url('index.php').'"><img src="styles/'.STYLE.'/images/homeS.png" border="0" alt="'.$_FORUM['settings_forum_name'].'" style="vertical-align:middle;"></a>';
	$_BREADCRUMBS = '<a href="'.url('index.php').'" title="'.$_FORUM['settings_forum_name'].'">'.$PLUGIN_INDEXPAGE->PageName().'</a>';
}
else 
{
	$_BREADCRUMBS_IMAGE = '<a href="'.url('forum.php').'"><img src="styles/'.STYLE.'/images/homeS.png" border="0" alt="'.$_FORUM['settings_forum_name'].'" style="vertical-align:middle;"></a>';
	$_BREADCRUMBS = '<a href="'.url('forum.php').'" title="'.$_FORUM['settings_forum_name'].'">'.$_TEXT['FORUM'].'</a> ';
	foreach ($_SUBNAV as $item)
	{
		// 0=Text | 1=URL | 2=IMG | 3=TAG
		$_BREADCRUMBS .= '&rsaquo;&nbsp;';
		if ($item[3] <> '') $_BREADCRUMBS .= '<font class="tag">[ '.$item[3].' ]</font> ';
		$_BREADCRUMBS .= ($item[1]<>''?'<a href="'.$item[1].'">':'').$item[0].($item[1]<>''?'</a>':'').' ';
	}
}
PluginHook('page_top-breadcrumbs');

require_once 'include/count.php';

if ((auth('auth_read', false)) && ($_GET['board'] <> ''))
{
	$_BOARD = IniLoad('data/'.$_GET['board'].'/board.ini');
	$_HEADER_TITLE = '';
	foreach(GetBoardParents($_GET['board']) as $item)
	{
		$_HEADER_TITLE .= $item[0].' &rsaquo; ';
	}
	$_HEADER_TITLE .= $_BOARD['title'];
	if (file_exists('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt'))
	{
		$arr = FileLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt');
		$_HEADER_TITLE .= ' &rsaquo; '.$arr[0][2];
		$_HEADER_DESCRIPTION = history_text($arr[0][2], $arr[0][3], 300);
		$seperator = " <>,.!?:'\"_#+*()\\/\n\r\t";
		$arr_keywords = array();
		$tok = strtok(html_entity_decode(history_text($arr[0][2], $arr[0][3], 1000)), $seperator);
		while ($tok !== false) 
		{
			if (strlen($tok) > 4) array_push($arr_keywords, strtolower($tok));
			$tok = strtok($seperator);
		}
		$arr_keywords = array_unique($arr_keywords);
		$_HEADER_KEYWORDS = implode(', ', $arr_keywords);
	}
}
else
{
	$url2title = array(
		'reg.php' => $_TEXT['NAV_REGISTER'],
		'login.php' => $_TEXT['NAV_LOGIN'],
		'members.php' => $_TEXT['NAV_MEMBERS'],
		'search.php' => $_TEXT['NAV_SEARCH'],
		'rules.php' => $_TEXT['NAV_RULES'],
		'faq.php' => $_TEXT['NAV_FAQ'],
		'imprint.php' => $_TEXT['NAV_IMPRESSUM'],
		'login.php' => $_TEXT['LOGIN_LOGIN'],
		'whoisonline.php' => $_TEXT['NAV_WHOISONLINE'],
		'user.php' => $_TEXT['PROFILE'].': '.$user
	);
	$_HEADER_TITLE = $url2title[basename($_SERVER['SCRIPT_NAME'])];
}
$_HEADER_TITLE = $_HEADER_TITLE.($_HEADER_TITLE <> ''?' | ':'').$_FORUM['settings_forum_name'];
PluginHook('page_top-header_vars');

$highlight_keys = array();
$ref = utf8_decode(urldecode(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY)));
$keystring = '';
if ($ref != '')
{
	foreach(explode('&',$ref) as $item)
	{
		$items = explode('=',$item);
		if (in_array($items[0], array('p','q','s','query')))
		{
			$keystrings = $items[1];
			Break;
		}
	}
	if ($keystrings != '')
	{
		$tok = strtok($keystrings, " +");
		while ($tok !== false) 
		{
		    	if (strlen($tok)>1) $highlight_keys[] = $tok;
		    	$tok = strtok(" +");
		}	
		
	}
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta http-equiv="cache-control" content="no-cache">
	<meta name="Author" content="www.frank-karau.de">
	<meta name="Keywords" content="'.$_HEADER_KEYWORDS.'">
	<meta name="Description" content="'.$_HEADER_DESCRIPTION.'">
	<meta name="Software" content="phpFK - PHP Forum ohne MySQL '.$_FORUM['version'].'">
	'.($_FORUM['settings_system_shorturls']?'<base href="'.$_FORUM['settings_forum_url'].'/">':'').'
	<meta name="robots" content="'.(in_array(basename($_SERVER['SCRIPT_NAME']), array('backup.php', 'do.php', 'do_move.php', 'login.php', 'post.php', 'reg.php', 'whoisonline.php'))?'no':'').'index, follow">
	<title>'.$_HEADER_TITLE.'</title>
	'.(file_exists('styles/'.STYLE.'/head.html')?style_convert(file_get_contents('./styles/'.STYLE.'/head.html')):'').'
	'.($_FORUM['settings_design_rss']?'<link rel="alternate" type="application/atom+xml" title="'.$_FORUM['settings_forum_name'].'" href="'.$_FORUM['settings_forum_url'].'/rss.php">':'').'
	<link rel="icon" href="'.($_FORUM['settings_system_shorturls']?$_FORUM['settings_forum_url'].'/':'').'styles/'.STYLE.'/favicon.ico" type="image/x-icon"> 
	<link rel="shortcut icon" href="'.($_FORUM['settings_system_shorturls']?$_FORUM['settings_forum_url'].'/':'').'styles/'.STYLE.'/favicon.ico" type="image/x-icon"> 
	<script type="text/javascript" src="include/jquery.js"></script>
	<script type="text/javascript" src="include/scripts.js"></script>
	<script type="text/javascript" src="include/codepress/codepress.js"></script>
	<script type="text/javascript" src="include/slimbox/slimbox2.js"></script>
	<link rel="stylesheet" href="include/slimbox/slimbox2.css" type="text/css" media="screen" />
	<script type="text/javascript" src="include/markitup/jquery.markitup.js"></script>
	<script type="text/javascript" src="include/markitup/sets/bbcode/set.js"></script>
	<link rel="stylesheet" type="text/css" href="include/markitup/sets/bbcode/style.css" />
	<link rel="stylesheet" type="text/css" href="include/markitup/skins/style.css" />
	<link rel="stylesheet" type="text/css" href="include/forum.css">
	<link rel="stylesheet" type="text/css" href="styles/'.STYLE.'/text.css">
';
PluginHook('page_top-head');
echo '</head>
<body>
'.(count($highlight_keys)>0?'
	<script type="text/javascript">
	<!--
		$(document).ready(function()	
		{
			Highlight(\''.implode(' ',$highlight_keys).'\');
		});
	-->
	</script>
':'');

if (in_array(basename($_SERVER['PHP_SELF']), array('my_profil.php', 'members.php', 'search.php', 'rules.php', 'faq.php', 'impressum.php', 'post.php')))
	$url = url(basename($_SERVER['PHP_SELF']).($_SERVER['QUERY_STRING']<>''?'?'.$_SERVER['QUERY_STRING']:''));
else if (in_array(basename($_SERVER['PHP_SELF']), array('thread.php', 'board.php')))
	$url = url(basename($_SERVER['PHP_SELF']), $_GET['board'], $_GET['thema'], ($_GET['page']*10)-1);
else if (in_array(basename($_SERVER['PHP_SELF']), array('user.php')))
	$url = url(basename($_SERVER['PHP_SELF']), $_GET['user']);
else
	$url = url('index.php');
echo '
	<div id="login-panel">
		<form action="'.$url.'" name="login" method="post">
		<table width="100%" cellspacing="0">
			<tr><td><img src="./styles/'.STYLE.'/images/loginS.png" alt="'.$_TEXT['LOGIN_LOGIN'].'"> <b>'.$_TEXT['LOGIN_LOGIN'].'</b></td><td></td><td style="text-align:right;"><a href="Javascript:hideObj(\'login-panel\');">'.$_TEXT['CLOSE_WINDOW'].'</a></td></tr>
			<tr><td><input type="hidden" name="action" value="login" /><label for="name">'.$_TEXT['LOGIN_USERNAME'].':</label> <input type="text" id="name" NAME="name" value="'.$_COOKIE['loginname'].'"></td><td><label for="passwd">'.$_TEXT['LOGIN_PASSWORD'].':</label> <input type="password" name="passwd" id="passwd"></td><td><input type="submit" name="submit" value="'.$_TEXT['LOGIN_LOGIN'].'"></td></tr>
			<tr><td colspan="3"><input type="checkbox" name="cookie" checked="checked" id="cookie"> <label for="cookie">'.$_TEXT['LOGIN_COOKIE'].'</label> | <a href="'.url('login.php').'">'.$_TEXT['LOGIN_LOST_PASSWORD'].'</a></td></tr>
		</table>
		</form>
	</div>
';

if ($_FORUM['settings_loading'] == 'off')
{
	echo '
	<div id="loadingbox" style="visibility:none;height:0;width:0;"></div>
	';
}
else
{
	echo '
	<div id="loadingbox" class="'.($_FORUM['settings_loading']=='premium'?'premium':'').'" style="display:none;" align="center">
		<table style=""><tr><td>'.$_TEXT['LOADING'].'</td></tr></table>
	</div>
	';
}

ob_start();
@include('styles/'.STYLE.'/top.php');
@include('styles/'.STYLE.'/top.html');
$content = ob_get_contents();
ob_end_clean();
echo style_convert($content);

@include('data/advertising_top.txt');

//* Status: Offline *//
if ($_FORUM['status'] == 'offline')
{
	PluginHook('page_top-offline');
	echo '<div class="error"><b>'.$_TEXT['FORUM_OFFLINE'].':</b><br />'.format_text(@file_get_contents('data/offline.txt'), false).'</div>';
	if  (!IsAdmin($_SESSION['Benutzername']))
	{
		require 'include/page_bottom.php';
		Exit;
	}
}

//* Subnavigation *//
echo '
	<div class="subnav">
	<table>
	<tr>
		<td>
			'.$_BREADCRUMBS_IMAGE.'&nbsp;<div class="breadcrumbs">'.$_BREADCRUMBS.'</div>
			'.($_SUBNAV_BOTTOM<>''?'<p class="sub">'.$_SUBNAV_BOTTOM.'</p>':'').'
		</td>
		'.($_SUBNAV_RIGHT<>''?'<td style="text-align:right;">'.$_SUBNAV_RIGHT.'</td>':'').'
	</tr>
	</table>
	</div>
';

GLOBAL $MSG_ERROR, $MSG_NOTICE, $MSG_CONFIRM;
if ($MSG_ERROR <> '') echo '<div class="error">'.$MSG_ERROR.'</div>';
if ($MSG_NOTICE <> '') echo '<div class="notice">'.$MSG_NOTICE.'</div>';
if ($MSG_CONFIRM <> '') echo '<div class="confirm">'.$MSG_CONFIRM.'</div>';

if (count($highlight_keys) > 0)
{
	echo '
			<div class="notice">
					'.MultiReplace($_TEXT['SEARCH_HIGHLIGHT'], implode(' ', $highlight_keys)).' [<a href="'.$_SERVER['REQUEST_URI'].'">'.$_TEXT['HIDE'].'</a>]
			</div>
	';
}

PluginHook('page_top-content_top');
?>