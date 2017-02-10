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

$_BOTLIST = array();
$_BOTLIST[] = array('baidu', 'baidu', '<a href="http://www.baidu.com/search/spider.htm" target="_blank">Baiduspider</a>');
$_BOTLIST[] = array('cazoodle', 'cazoodle', '<a href="http://www.cazoodle.com/cazoodlebot" target="_blank">CazoodleBot Crawler</a>');
$_BOTLIST[] = array('dotbot', 'dotbot', '<a href="http://www.dotnetdotcom.org/" target="_blank">DotBot</a>');
$_BOTLIST[] = array('euripbot', 'euripbot', '<a href="http://www.eurip.com" target="_blank">EuripBot</a>');
$_BOTLIST[] = array('exabot.com', 'exabot', '<a href="http://www.exabot.com/go/robot" target="_blank">Exabot</a>');
$_BOTLIST[] = array('gigablast', 'gigablast', '<a href="http://www.gigablast.com/spider.html" target="_blank">Gigabot</a>');
$_BOTLIST[] = array('googlebot', 'google', '<a href="http://www.google.com/bot.html" target="_blank">Googlebot</a>');
$_BOTLIST[] = array('Mediapartners-Google', 'Mediapartners-Google', '<a href="http://www.google.com/adsense/" target="_blank">Google AdSense</a>');
$_BOTLIST[] = array('libwww-perl', 'libwww-perl', 'libwww-perl');
$_BOTLIST[] = array('msiecrawler', 'msiecrawler', 'MSIECrawler');
$_BOTLIST[] = array('msnbot', 'msn', '<a href="http://help.live.com/help.aspx?mkt=de-DE&project=wl_webmasters" target="_blank">MSNBot für Bing</a>');
$_BOTLIST[] = array('radian6', 'radian6', '<a href="httP//www.radian6.com/crawler" target="_blank">Radian6</a>');
$_BOTLIST[] = array('seekbot', 'seekbot', '<a href="http://www.seekbot.net/bot.html" target="_blank">Seekbot</a>');
$_BOTLIST[] = array('speedy spider', 'speedyspider', '<a href="http://www.entireweb.com/about/search_tech/speedy_spider/" target="_blank">Speedy Spider</a>');
$_BOTLIST[] = array('aggregator:Spinn3r', 'spinn3r', '<a href="http://spinn3r.com/robot" target="_blank">Spinn3r</a>');
$_BOTLIST[] = array('twiceler', 'twiceler', '<a href="http://www.cuill.com/twiceler/robot.html" target="_blank">Twiceler</a>');
$_BOTLIST[] = array('webalta crawler', 'webalta', '<a href="http://www.webalta.net/ru/about_webmaster.html" target="_blank">WebAlta</a>');
$_BOTLIST[] = array('yahoo! slurp', 'yahoo', '<a href="http://help.yahoo.com/help/us/ysearch/slurp" target="_blank">Yahoo! Slurp</a>');


$_COUNT = IniLoad('data/count.ini');
if (($_COUNT['entire'] == '') && file_exists('data/count.tmp.ini'))
{
	$_COUNT = IniLoad('data/count.tmp.ini');
}
if ($_COUNT['day'] != date("d.m.y", time()))
{
	$_COUNT['day'] = date("d.m.y", time());
	$_COUNT['today'] = '';
	$_COUNT['today_user'] = '';
	IniSave('data/count.ini', $_COUNT);
	IniSave('data/count.tmp.ini', $_COUNT);
	@unlink('data/count_online.txt');
	@unlink('data/count_bots.ini');
}
$count_online_user = array();
for ($i=0; $i<1000; $i++)
{
	$user_in_board[$i] = array();
	$user_in_thema[$i] = array();
	$guests_in_board[$i] = 0;
	$guests_in_thema[$i] = 0;
}
function count_bot($bot)
{
	GLOBAL $_GET, $_BREADCRUMBS;
	$_COUNT_BOTS = IniLoad('data/count_bots.ini');
	$_COUNT_BOTS[$bot.'_count']++;
	$_COUNT_BOTS[$bot.'_lastonline'] =  time();
	$_COUNT_BOTS[$bot.'_breadcrumbs'] = $_BREADCRUMBS;
	IniSave('data/count_bots.ini', $_COUNT_BOTS);
}
$is_bot = false;
foreach($_BOTLIST as $bot)
{
	if (eregi($bot[0], $_SERVER['HTTP_USER_AGENT']))
	{
		count_bot($bot[1]);
		$is_bot = true;
	} 
} 
if (!$is_bot) 
	if ((eregi('robot', $_SERVER['HTTP_USER_AGENT'])) OR
	 (eregi('feedfetcher', $_SERVER['HTTP_USER_AGENT'])) OR
	 (eregi('crawl', $_SERVER['HTTP_USER_AGENT'])) OR
	 (eregi('spider', $_SERVER['HTTP_USER_AGENT'])) OR
	 (eregi('bot-', $_SERVER['HTTP_USER_AGENT'])) OR
	 (eregi('bot/', $_SERVER['HTTP_USER_AGENT'])))
	{
		count_bot('unknown');
		$is_bot = true;
	} 
if (!$is_bot) 
{
	$count_data = FileLoad('data/count_online.txt');
	$hidestatus = false;
	if (IsUser())
	{
		$ini = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');
		$hidestatus = ($ini['settings_hidestatus'] && ($_FORUM['settings_user_hidestatus'] OR IsAdmin()));
	}
	
	$count_gefunden = false;
	for ($i=0; $i<count($count_data); $i++)
	{
		if ($count_data[$i][0] < (time() - 300))
		{
			$count_data[$i][0] = '-';
		}
		else 
		{
			if (IsUser() && ($count_data[$i][6] == $_SESSION['Benutzername']))
			{
				$count_data[$i][0] = time();
				if ($count_gefunden) $count_data[$i][0] = "-";
				$count_data[$i][1] = $_SESSION['IP'];
				$count_data[$i][2] = ($hidestatus ? '' : $_SESSION['Benutzername']);
				if (($_GET['board'] <> '') && (auth('auth_read', false)))
				{
					$count_data[$i][3] = $_GET['board'];
					$count_data[$i][4] = $_GET['thema'];
				}
				else
				{
					$count_data[$i][3] = '';
					$count_data[$i][4] = '';
				}
				$count_data[$i][5] = getBrowser($_SERVER['HTTP_USER_AGENT']);
				$count_data[$i][7] = $_BREADCRUMBS;
				$count_gefunden = true;
			}
			else if ($count_data[$i][1] == $_SESSION['IP'])
			{
				$count_data[$i][0] = time();
				if ($count_gefunden) $count_data[$i][0] = "-";
				$count_data[$i][2] = ($hidestatus ? '' : $_SESSION['Benutzername']);
				if (($_GET['board'] <> '') && (auth('auth_read', false)))
				{
					$count_data[$i][3] = $_GET['board'];
					$count_data[$i][4] = $_GET['thema'];
				}
				else
				{
					$count_data[$i][3] = '';
					$count_data[$i][4] = '';
				}
				$count_data[$i][5] = getBrowser($_SERVER['HTTP_USER_AGENT']);
				$count_data[$i][6] = $_SESSION['Benutzername'];
				$count_data[$i][7] = $_BREADCRUMBS;
				$count_gefunden = true;
			}
			if ($count_data[$i][2] != '')
			{
				array_push($count_online_user, $count_data[$i][2]);
				if (!IsInGroup($_COUNT['today_user'], $count_data[$i][2]))
				{
					AddToGroup($_COUNT['today_user'], $count_data[$i][2]);
					IniSave('data/count.ini', $_COUNT);
					IniSave('data/count.tmp.ini', $_COUNT);
				}
			}
			if (is_numeric($count_data[$i][3]))
			{
				if ($count_data[$i][2] != '')
				{
					@array_push($user_in_board[$count_data[$i][3]], $count_data[$i][2]);
				}
				else
				{
					$guests_in_board[$count_data[$i][3]]++;
				}
				if ($count_data[$i][3] == $_GET['board'])
				{
					if ($count_data[$i][2] != '')
					{
						@array_push($user_in_thema[$count_data[$i][4]], $count_data[$i][2]);
					}
					else
					{
						$guests_in_thema[$count_data[$i][4]]++;
					}
				}
			}
		}
	}

	if (!$count_gefunden)
	{
		array_push($count_data, array(time(), $_SESSION['IP'], ($hidestatus ? '' : $_SESSION['Benutzername']), $_GET['board'], $_GET['thema'], getBrowser($_SERVER['HTTP_USER_AGENT']), $_SESSION['Benutzername'], $_BREADCRUMBS));
		if (($_SESSION['Benutzername'] <> '') && (!$hidestatus))
		{
			array_push($count_online_user, $_SESSION['Benutzername']);
			AddToGroup($_COUNT['today_user'], $_SESSION['Benutzername']);
		}
		$_COUNT['entire']++;
		$_COUNT['today']++;
		IniSave('data/count.ini', $_COUNT);
	}
	FileSave('data/count_online.txt', $count_data);
}
$count_data = FileLoad('data/count_online.txt');
$_COUNT['online'] = count($count_data);

?>