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

define('DIR', '../');
define('SICHERHEIT_FORUM', true);

$history_filename = '../data/history.txt';

require_once '../include/functions.php';

ini_set('register_globals', '0');
session_name('sid');
session_start();

$_FORUM = IniLoad('../data/forum.ini');

if ($_GET['action'] == 'logout')
{ 
	if ($_COOKIE['loginname'] != '')
	{
	 	setcookie("loginpasswd", "n/a", time()+100);
		$_COOKIE['loginname'] = '';
		$_COOKIE['loginpasswd'] = '';
	}
	$_SESSION['Benutzername'] = '';
	$_SESSION['new_posts_date'] = '';
	session_destroy();
}

if ($_GET['action'] == 'login')
{
	if (val_user($_POST['name'], $_POST['passwd']) && (IsAdmin($_POST['name'])))
	{
		$_SESSION['Benutzername'] = $_POST['name'];
	}
}
$STYLE = (file_exists(DIR.'styles/'.$_FORUM['settings_design_style'].'/text.css')?
	$_FORUM['settings_design_style']:'default');
PluginHook('ap-init_style');
define('STYLE', $STYLE);

// Load Language //
$list = LoadFileList("../languages/",".php");
$LANGUAGES = array();
$i = 0;
foreach ($list as $item)
{
  if (strlen($item)<=6)
  {
	include('../languages/'.$item);
	$LANGUAGES[$i]['name'] = $_TEXT['AP_LANGUAGE_NAME'];
	$LANGUAGES[$i]['file'] = $item;
	$i++;
  }
}
if ($_POST['language'] <> '') 
{
	$_SESSION['language'] = $_POST['language'];
}
else if ($_SESSION['language'] == '')
{
	$_SESSION['language'] = $_FORUM['settings_forum_language'];
}

if (!(file_exists('../languages/'.$_SESSION['language'])) OR ($_SESSION['language'] == ''))
{
	$_SESSION['language'] = 'de.php';
}
require '../languages/'.$_SESSION['language'];


echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="Author" content="phpFK - PHP Forum ohne MySQL Datenbank - www.frank-karau.de">
	<meta http-equiv="cache-control" content="no-cache">
	<meta name="robots" content="noindex, nofollow">
	<script type="text/javascript" src="../include/jquery.js"></script>
	<script type="text/javascript" src="../include/markitup/jquery.markitup.js"></script>
	<script type="text/javascript" src="../include/markitup/sets/bbcode/set.js"></script>
	<link rel=stylesheet type="text/css" href="style.css">
	<link rel="stylesheet" type="text/css" href="../include/markitup/sets/bbcode/style.css" />
	<link rel="stylesheet" type="text/css" href="../include/markitup/skins/style.css" />
	<title>'.$_TEXT['AP'].' '.$_FORUM['version'].' ('.$_FORUM['settings_forum_name'].')</title>
';
PluginHook('ap-head');
echo '
</head>
<body style="margin:0px;">

<div style="width:100%; height:100%, padding:0px; margin:0px;"><table style="width:100%; height:100%; margin:0x; table-layout:fixed;">
	<tr><td style="height:70px;background:url(\'images/bg_top.jpg\') #3666A8;padding:0px;">
		<table class="auto" style="heigth:70px;width:800px;">
		<tr>
			<td style="height:70px;color:#FFFFFF;font-size:12pt;padding-left:20px;font-weight:bold;">'.$_TEXT['AP'].' '.$_FORUM['version'].'<br><small>'.$_FORUM['settings_forum_name'].'</small></td>
			<td style="height:70px;color:#FFFFFF;font-size:8pt;font-weight:normal;text-align:right;padding-bottom:5px;vertical-align:bottom;"><a href="../" class="white" target="_blank">'.$_TEXT['AP_TO_FORUM'].'</a></td>
		</tr>
		</table>
	</td></tr>
';

if (IsAdmin())
{
	if (!in_array($_GET['nav'], array('settings', 'user', 'advanced')))
	{
		$_GET['nav'] = 'settings';
	}

	echo '
	<tr><td style="background: url(\'images/bg_nav.jpg\') #EEEEEE;height:30px;padding:0px;">
		<table style="height:30px;width:800px;"><tr>
			<td style="width:199px; text-align:center;">'.($_GET['nav']=='settings'?'<b>':'').'<a href="?nav=settings">'.$_TEXT['AP_CONFIGURATION'].'</a>'.($_GET['nav']=='settings'?'</b>':'').'</td><td style="width:1px; padding:0px;"><img src="images/nav.jpg" border="0" /></td>
			<td style="width:199px; text-align:center;">'.($_GET['nav']=='user'?'<b>':'').'<a href="?nav=user">'.$_TEXT['AP_USER'].'</a>'.($_GET['nav']=='user'?'</b>':'').'</td><td style="width:1px; padding:0px;"><img src="images/nav.jpg" border="0" /></td>
			<td style="width:199px; text-align:center;">'.($_GET['nav']=='advanced'?'<b>':'').'<a href="?nav=advanced">'.$_TEXT['AP_ADVANCED'].'</a>'.($_GET['nav']=='advanced'?'</b>':'').'</td><td style="width:1px; padding:0px;"><img src="images/nav.jpg" border="0" /></td>
			<td style="width:199px; text-align:center;"><a href="?action=logout">'.$_TEXT['LOGIN_LOGOUT'].'</a></td><td style="width:1px; padding:0px;"><img src="images/nav.jpg" border="0" /></td>
		</tr></table>
	</td></tr>

	<tr><td style="padding:5px; vertical-align:top; height:100%;">
		<table style="width:795px; height: 100%; border-right:1px #3666A8 dotted;" cellspacing="0">
		<tr>
		<td style="width:23%; height:100%; background:#EEEEEE; vertical-align:top;">
			<ul class="subnav">
	';
	$links = array();
	if ($_GET['nav'] == 'settings')
	{
		$default_page = 'info';
		$links[] = array('?nav=settings&page=info', $_TEXT['AP_INFO']);
		$links[] = array('?nav=settings&page=settings', $_TEXT['AP_SETTINGS']);
		$links[] = array('?nav=settings&page=boards', $_TEXT['AP_BOARDS']);
		$links[] = array('?nav=settings&page=infotext', $_TEXT['AP_INFOTEXT']);
		$links[] = array('?nav=settings&page=rules', $_TEXT['AP_RULES']);
		$links[] = array('?nav=settings&page=faq', $_TEXT['AP_FAQ']);
		$links[] = array('?nav=settings&page=imprint', $_TEXT['AP_IMPRINT']);
		$links[] = array('?nav=settings&page=status', $_TEXT['AP_STATUS']);
		PluginHook('ap-nav_settings');
	}
	if ($_GET['nav'] == 'user')
	{
		$default_page = 'admin';
		$links[] = array('?nav=user&page=admin', $_TEXT['AP_USER_ADMIN']);
		$links[] = array('?nav=user&page=new', '- '.$_TEXT['AP_USER_ADMIN_NEW']);
		$links[] = array('?nav=user&page=groups', $_TEXT['AP_USER_GROUPS']);
		$links[] = array('?nav=user&page=newsletter', $_TEXT['AP_USER_EMAIL']);
		$links[] = array('?nav=user&page=newsletterlist', $_TEXT['AP_USER_NEWSLETTERLIST']);
		PluginHook('ap-nav_user');
	}
	if ($_GET['nav'] == 'advanced')
	{
		$default_page = 'navigation';
		$links[] = array('?nav=advanced&page=navigation', $_TEXT['AP_NAVIGATION']);
		$links[] = array('?nav=advanced&page=plugins', $_TEXT['AP_PLUGINS']);
		$links[] = array('?nav=advanced&page=backup', $_TEXT['AP_BACKUP']);
		$links[] = array('?nav=advanced&page=badword', $_TEXT['AP_BADWORD']);
		$links[] = array('?nav=advanced&page=blacklist', $_TEXT['AP_BLACKLIST']);
		$links[] = array('?nav=advanced&page=advertising', $_TEXT['AP_ADVERTISING']);
		PluginHook('ap-nav_advanced');
	}
	if (!file_exists('include/'.$_GET['nav'].'_'.$_GET['page'].'.php'))
	{
		$_GET['page'] = $default_page;
	}
	foreach ($links as $link) echo '<li><a href="'.$link[0].'">'.$link[1].'</a></li>';
	echo '
			</ul>
		</td>
		<td style="width:77%; padding:15px; vertical-align:top;">
	';
	if ($_GET['plugin'] <> '')
	{
		$inst = $PLUGINS[$_GET['plugin']];
		if (!is_null($inst))
		{
			$inst->ApSettings();
		}
	}
	else require 'include/'.$_GET['nav'].'_'.$_GET['page'].'.php';
	echo '
		</td>
		</tr></table>
	</td></tr>
	';
} 
else
{
	echo '
	<tr><td style="padding:5px; vertical-align:top; height:100%;">
		<table style="width:795px; height:100%; border-right:1px #0A1C86 dotted;"><tr><td style="height:100%; padding:10px; vertical-align:top; text-align:center;">

		<form action="index.php?action=login" method="post">
		<center><table class="box" style="width:400;margin-top:100px; margin-bottom:150px;align:center;">
		<tr><td class="top">'.$_TEXT['NAV_LOGIN'].'</td></tr>
		<tr><td>
			'.($_GET['action']=='login'?'<div class="error">'.$_TEXT['ERROR_WRONG_PASSWORD'].'</div>':'').'
			<center><table style="width:90%;">
			<tr>
				<td style="text-align:right;"><label for="name">'.$_TEXT['LOGIN_USERNAME'].':</label></td>
				<td><input type="text" id="name" name="name" value="'.$_COOKIE['loginname'].'" maxlength="20" /></td>
			</tr>
			<tr>
				<td style="text-align:right;"><label for="passwd">'.$_TEXT['LOGIN_PASSWORD'].':</label></td>
				<td><input type="password" id="passwd" name="passwd" maxlength="20" /></td>
			</tr>
			<tr>
				<td style="text-align:right;"><label for="language">'.$_TEXT['AP_LANGUAGE'].':</label></td>
				<td><select id="language" name="language" />
	';
				foreach ($LANGUAGES as $item)
				{
					echo '<option'.($item['file']==$_SESSION['language']?' selected="selected"':'').' value="'.$item['file'].'">'.$item['name'].'</option>';
				}
	echo '
				</select></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['LOGIN_LOGIN'].'"></td>
			</tr>
			</table>
		</td></tr>
		</table></center>
		</form>
		</td></tr></table>
	</td></tr>
	';
}

echo '

	</table></div>	

</body>
</html>
';
?>