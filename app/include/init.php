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

if (isset($_GET['debug']) && $_GET['debug'] == 'debug') define('DEBUG', TRUE);
define('DEBUG', TRUE);
if (!defined('DEBUG')) {error_reporting(E_ERROR);ini_set('error_reporting','E_ERROR');}
else { error_reporting(E_ERROR | E_WARNING | E_PARSE);  }



$LOADING_TIME_START = strtok(microtime(), ' ') + strtok('');
if (!in_array(basename($_SERVER['PHP_SELF']), array('download.php', 'backup.php'))) 
	ob_start("ob_gzhandler");
if (!file_exists('data/forum.ini')) {header('Location: install/index.php');Exit;}

// ******************************************************************************** //

define('DIR', '');
define('SICHERHEIT_FORUM', true);
@include 'config.ini.php';
if (!defined('CONFIG_POST_MAXCHAR')) DEFINE('CONFIG_POST_MAXCHAR', 5000);
require_once 'include/functions.php';

// ******************************************************************************** //


ini_set('url_rewriter.tags','');
ini_set('register_globals', '0');
@ini_set('memory_limit', '1G');
@set_time_limit(240); 
header('Cache-control: private');
session_name('sid');
session_start();

// ******************************************************************************** //

$_FORUM = array();
while ((count($_FORUM) < 10) && ($counter < 5))
{
	$_FORUM = IniLoad('data/forum.ini');
	$counter++;
}
if (count($_FORUM) < 10)
{
	$_FORUM = IniLoad('data/forum.tmp.ini');
	if (count($_FORUM) > 10) IniSave('data/forum.ini', $_FORUM);
}
$_BOARDS = IniLoad('data/boards.ini');
$_ADMINS = IniLoad('data/user/Admins.grp.ini');
$history_filename = 'data/history.txt';
$check_new_posts = false;
$_SUBNAV = array();
$_SUBNAV_RIGHT = '';
$_SUBNAV_BOTTOM = '';
$_HEADER_TITLE = '';
$_HEADER_KEYWORDS = $_FORUM['settings_forum_keywords'];
$_HEADER_DESCRIPTION = $_FORUM['settings_forum_description'];

// ******************************************************************************** //

@include('update.php');

// ******************************************************************************** //

$STYLE = (file_Exists('styles/'.$_FORUM['settings_design_style'].'/text.css')?
	$_FORUM['settings_design_style']:'default');
$LANGUAGE = ((file_Exists('languages/'.$_FORUM['settings_forum_language']) 
	AND ($_FORUM['settings_forum_language'] != ''))?
	$_FORUM['settings_forum_language']:'de.php');

// ******************************************************************************** //

PluginHook('init-start');

// ******************************************************************************** //

define('STYLE', $STYLE);
require_once 'languages/de.php';
require_once 'languages/'.$LANGUAGE;

// ******************************************************************************** //

if ($_SESSION['IP'] == '') $_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];
if ((($_GET['action'] == 'logout') OR ($_GET['action'] == 'delete2')) && !($_POST['action']=='login'))
{ 
	if ($_COOKIE['loginname'] != '')
	{
	 	setcookie("loginpasswd", "n/a", time()+100, '/');
		$_COOKIE['loginname'] = '';
		$_COOKIE['loginpasswd'] = '';
	}
	$_SESSION['Benutzername'] = '';
	session_destroy();
}
else if (($_GET['action'] == 'login') OR ($_POST['action'] == 'login'))
{
	$login_timeout = false;
	if (file_exists('./data/user/'.$_POST['name'].'.usr.log'))
	{
		$ini = IniLoad('./data/user/'.$_POST['name'].'.usr.log');
		if ($ini['timeout'] != '')
		{
			if ($ini['timeout'] > time())
			{
				$login_timeout = true;
			}
			else
			{
				@unlink('./data/user/'.$_POST['name'].'.usr.log');
			}
		}
	}
	if ($login_timeout)
	{
		$MSG_ERROR = $_TEXT['ERROR_LOGIN_TIMEOUT'];
	}
	else
	{
		if (file_exists('data/user/'.$_POST['name'].'.usr.tmp'))
		{
			$ini = IniLoad('data/user/'.$_POST['name'].'.usr.tmp');
			if (md5($_POST['passwd']) == $ini['password'])
			{
				if (rename('data/user/'.$_POST['name'].'.usr.tmp', 'data/user/'.$_POST['name'].'.usr.ini'))
				{
					$_FORUM['newest_user'] = $_POST['name'];
					IniSave('./data/forum.ini', $_FORUM);
					if ($_FORUM['settings_admin_notification'])
					{
						SendMessage(Group2Array($_ADMINS['members']), '', $_POST['name'].' '.$_TEXT['MSG_NEW_USER']);
					}
				}
			}			
		}
		if (val_user($_POST['name'], $_POST['passwd']))
		{
			$_SESSION['Benutzername'] = $_POST['name'];
			if ($_POST['cookie'] == 'on')
			{	
		  		setcookie('loginpasswd', md5($_POST['passwd']), time()+9999999, '/');
			}
			setcookie('loginname', $_POST['name'], time()+9999999, '/');
			$check_new_posts = true;
			@unlink('data/user/'.$_POST['name'].'.usr.log');
			PluginHook('init-login', $_SESSION['Benutzername'], $_SESSION['IP']);
		}
		else
		{
			if (file_exists('data/user/'.$_POST['name'].'.usr.ini'))
			{
				$ini = IniLoad('data/user/'.$_POST['name'].'.usr.log');
				$ini['wrong_login']++;
				if ($ini['wrong_login'] > 3)
				{
					$ini['timeout'] = time()+600;
					$MSG_ERROR = $_TEXT['ERROR_LOGIN_TIMEOUT'];
				}
				IniSave('data/user/'.$_POST['name'].'.usr.log', $ini);
			}
			if ($MSG_ERROR == '') $MSG_ERROR = $_TEXT['ERROR_WRONG_LOGIN'];
		}
	}
}
else  
{
	if ((!$_SESSION['Benutzername']) && (val_user_md5($_COOKIE['loginname'], $_COOKIE['loginpasswd'])) && ($_GET['action'] != 'logout'))
	{
		$_SESSION['Benutzername'] = $_COOKIE['loginname'];
		$check_new_posts = true;
		PluginHook('init-login_cookie', $_SESSION['Benutzername'], $_SESSION['IP']);
	}
	if (!IsUser()) $_SESSION['Benutzername'] = '';
}

// ******************************************************************************** //

if (($check_new_posts) && (file_exists("data/user/".$_SESSION['Benutzername'].".usr.ini")))
{
	$udat = IniLoad("data/user/".$_SESSION['Benutzername'].".usr.ini");
	if ($udat['lastonline_date'] <> '')
	{
		$_SESSION['new_posts_date'] = $udat['lastonline_date'];
		$_SESSION['new_posts_seen'] = '';
		$_SESSION['new_posts_boards_seen'] = '';
     	}

}

// ******************************************************************************** //

if (IsUser())
{
	$udat = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');
	if ((count($udat) > 0) AND 
	   (
		(($udat['lastonline_date']+100) < time())
	     OR
		(basename($_SERVER['PHP_SELF']) == 'do.php')
	   ))
	{
		$udat['lastonline_date'] = time();
		IniSave('data/user/'.$_SESSION['Benutzername'].'.usr.ini', $udat);
	}
} 

// ******************************************************************************** //

if (($_FORUM['system_lastcheck']+86400 < time()) OR ($_GET['system_lastcheck'] == 'true'))
{
	$_FORUM['system_lastcheck'] = time();
	IniSave('data/forum.ini', $_FORUM);
	if (count($_FORUM) > 10) IniSave('data/forum.tmp.ini', $_FORUM);
	$tmpusers = LoadFileList('data/user/', '.usr.tmp');
	foreach ($tmpusers as $user)
	{
		$ini = IniLoad('data/user/'.$user);
		if ($ini['register_date']+86400 < time()) @unlink('data/user/'.$user);
	}
	CreateBirthdayList();
	$tmpfiles = LoadFileList('data/', 'cache_');
	foreach ($tmpfiles as $tmpfile)
	{
		if (filemtime('data/'.$tmpfile)+3600 < time()) @unlink('data/'.$tmpfile);
	}
	PluginHook('init-cronjob');
}	

// ******************************************************************************** //
?>