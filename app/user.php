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

require 'include/init.php';

$user = $_GET['user'];

if (!IsUser($_GET['user']))
{
	$MSG_ERROR = MultiReplace($_TEXT['ERROR_NO_REG_USER'], '<b>'.format_string($_GET['user'], true).'</b>');
	include 'index.php';
	Exit; 
}

$_SUBNAV[] = array($_TEXT['PROFILE'].': '.$user, url('user.php', $user));

if (!IsUser() && defined('CONFIG_PROFILE_SHOW_ONLY_USERS') && CONFIG_PROFILE_SHOW_ONLY_USERS == 1)
{
	$MSG_ERROR = $_TEXT['ACCESS_DENIED_MSG'];
	require_once 'login.php';
	Exit;
}

require 'include/page_top.php';


$data = IniLoad('data/user/'.$user.'.usr.ini');

// Themen pro Tag
$anz_bei = $data['count_topics'];
$anz_tag = ceil((time()-$data['register_date'])/86400);
$perday = round(($anz_bei*10)/($anz_tag));
$themen_day = ' ('.($perday/10).' '.$_TEXT['A_DAY'].')';

// Antworten pro Tag
$anz_bei = $data['count_answeres'];
$anz_tag = ceil((time()-$data['register_date'])/86400);
$perday = round(($anz_bei*10)/($anz_tag));
$antworten_day = ' ('.($perday/10).' '.$_TEXT['A_DAY'].')';

// Locked in Prozent
if ($data['count_locked']>0) $locked = " (".round($data['count_locked']*100/$data['count_topics'])."%)";

echo '

	<div id="content">
		<table class="main">
		<tr><td class="g" style="vertical-align:top; width:70%;">
			<table class="main">
				<tr><td class="oben" colspan="2">'.$_TEXT['PROFILE_DATA_INFORMATION'].'</td></tr>
';
$list = array();
$class = 'g';
$list[] = array($_TEXT['LOGIN_USERNAME'], user($user));
$list[] = array($_TEXT['RANKING'], fnum(user_points($user)).' '.$_TEXT['POINTS']);
$list[] = array($_TEXT['TOPICS'], fnum($data['count_topics']).$themen_day);	
$list[] = array($_TEXT['ANSWERES'], fnum($data['count_answeres']).$antworten_day);
$list[] = array($_TEXT['LOCKED_TOPICS'], fnum($data['count_locked']).$locked);
$list[] = array($_TEXT['TIME_OF_REGISTRATION'], ftime($data['register_date']));
$list[] = array($_TEXT['TIME_OF_LAST_VISIT'], ftime($data['lastonline_date'], true, true));
PluginHook('user-data_information');
foreach ($list as $line)
{
	echo '
				<tr>
					<td class="'.$class.'" style="text-align:right;width:30%;">'.$line[0].':</td>
					<td class="'.$class.'" style="width:70%;">'.$line[1].'</td>
				</tr>
	';
	$class = ($class=='w'?'g':'w');
}
echo '
			</table>
';

$list = array();
$class = 'g';
if (($data['name'] <> '') && ($data['name'] <> $user))
{
	$list[] = array($_TEXT['PROFILE_DATA_NAME'], $data['name']);
}
$months = explode(',', $_TEXT['MONTHS']);
if (($data['birthday_month']>0) && ($data['birthday_month']<=12))
{
	$birthday = '';
	if ($data['birthday_day'] != '')
	{ 
		$birthday .= $data['birthday_day'].'. ';
	}
	$birthday .= $months[$data['birthday_month']-1].' ';
	if ($data['birthday_year'] != '')
	{ 
		$birthday .= $data['birthday_year'].' ';
	}
	if (($data['birthday_day'] != '') && ($data['birthday_year'] != ''))
	{	
		$age = date('Y') - $data['birthday_year'];
		if (mktime(0, 0, 0, $data['birthday_month'], $data['birthday_day'], date('Y')) >= time())
		{
			$age--;
		}
		if ($age > 0)
		{
			$birthday .= '('.$age.')';
		}
	}
	$list[] = array($_TEXT['PROFILE_DATA_BIRTHDAY'], $birthday);
}
if ($data['sex'] == '1')
{
	$list[] = array($_TEXT['PROFILE_DATA_SEX'], $_TEXT['PROFILE_DATA_SEX_MALE']);
}
if ($data['sex'] == '2')
{
	$list[] = array($_TEXT['PROFILE_DATA_SEX'], $_TEXT['PROFILE_DATA_SEX_FEMALE']);
}
if ($data['location'] != '')
{
	$location = '';
	if ($data['zip'] != '') $location .= $data['zip'].' ';
	$location .= $data['location'];
	$list[] = array($_TEXT['PROFILE_DATA_LOCATION'], $location);
}
if ($data['country'] <> '')
{
	$list[] = array($_TEXT['PROFILE_DATA_COUNTRY'], $data['country']);
}
PluginHook('user-data_personal');
if (count($list) > 0)
{
	echo '
			&nbsp;
			<table class="main">
				<tr><td class="oben" colspan="2">'.$_TEXT['PROFILE_DATA_PERSONAL'].'</td></tr>
	';
	foreach ($list as $line)
	{
		echo '
				<tr>
					<td class="'.$class.'" style="text-align:right;width:30%;">'.$line[0].':</td>
					<td class="'.$class.'" style="width:70%;">'.$line[1].'</td>
				</tr>
		';
		$class = ($class=='w'?'g':'w');
	}
	echo '
			</table>
	';
}
if (trim($data['text'])!="")
{
	echo '
			&nbsp;
			<table class="main">
				<tr><td class="oben"><b>'.$_TEXT['PROFILE_DATA_TEXT'].'</b></td></tr>
				<tr><td class="w">'.$data['text'].'</td></tr>
			</table>
	';
}

PluginHook('user-col_left');

echo '
		</td><td class="g" style="vertical-align:top; width:30%; padding-left:0px;">
';
if ($data['avatar'] != '')
{
	echo '
			<table class="main">
				<tr><td class="oben"><b>'.$_TEXT['PROFILE_AVATAR'].'</b></td></tr>
				<tr><td class="w" style="text-align:center;"><img src="'.$data['avatar'].'" border="0" ></td></tr>
			</table>
			&nbsp;
	';
}
echo '
			<table class="main">
				<tr><td class="oben"><b>'.$_TEXT['PROFILE_DATA_CONTACT'].'</b></td></tr>

';

$list = array();
if (IsUser())
{
	array_push($list, array($_TEXT['PM_SEND'], '<a href="'.url('pm.php?send/'.$user).'"><img src="styles/'.STYLE.'/images/mailS.png" style="vertical-align:bottom;" border="0" alt="'.$_TEXT['PM_SEND'].'"></a> <a href="'.url('pm.php?send/'.$user).'">'.$_TEXT['PM_SEND'].'</a>'));
	if ($data['show_email']) array_push($list, array($_TEXT['EMAIL'], '<a href="mailto:'.format_input($data['email']).'"><img src="styles/'.STYLE.'/images/mailS.png" style="vertical-align:bottom;" border="0" alt="'.format_input($data['email']).'"></a> <a href="mailto:'.format_input($data['email']).'">'.fhtml($data['email']).'</a>'));
}
if ($data['homepage']!="") 
{
	if (!is_integer(strpos($data['homepage'],"://")))
		$data['homepage'] = 'http://'.$data['homepage'];
	array_push($list, array($_TEXT['HOMEPAGE'], '<a href="'.format_input($data['homepage']).'" target="_blank"><img src="styles/'.STYLE.'/images/homeS.png" style="vertical-align:bottom;" border="0"  alt="'.$_TEXT['HOMEPAGE'].'"></a> <a href="'.format_input($data['homepage']).'" target="_blank">'.fhtml($data['homepage']).'</a>'));
}
if ($data['icq']<>"")
{
	array_push($list, array($_TEXT['ICQ'], '<a href="http://web.icq.com/whitepages/message_me?uin='.format_input($data['icq']).'&action=message" target="_blank"><img src="http://status.icq.com/online.gif?icq='.$data['icq'].'&img=5" border="0" alt="'.$_TEXT['ICQ'].'" style="width:12px;height:12px;" /></a> <a href="http://web.icq.com/whitepages/message_me?uin='.format_input($data['icq']).'&action=message" target="_blank">'.fhtml($data['icq']).'</a>'));
}
if ($data['skype']<>"")
{
	array_push($list, array($_TEXT['SKYPE'], '<a href="callto://'.format_input($data['skype']).'"><img src="styles/'.STYLE.'/images/skypeS.png" style="vertical-align:bottom;" border="0" alt="'.$_TEXT['SKYPE'].'"></a> <a href="callto://'.format_input($data['skype']).'">'.fhtml($data['skype']).'</a>'));
}
if ($data['msn']<>"")
{
	array_push($list, array($_TEXT['MSN'], '<a href="http://members.msn.com/?mem='.format_input($data['msn']).'" target="_blank"><img src="styles/'.STYLE.'/images/msnS.png" border="0" alt="'.$_TEXT['MSN'].'"></a> <a href="http://members.msn.com/?mem='.format_input($data['msn']).'" target="_blank">'.fhtml($data['msn']).'</a>'));
}
if ($data['aim']<>"")
{
	array_push($list, array($_TEXT['AIM'], '<a href="aim:GoIM?screenname='.format_input($data['aim']).'" target="_blank"><img src="styles/'.STYLE.'/images/aimS.png" border="0" alt="'.$_TEXT['AIM'].'" /></a> <a href="aim:GoIM?screenname='.format_input($data['aim']).'" target="_blank">'.fhtml($data['aim']).'</a>'));
}
if ($data['yahoo']<>"")
{
	array_push($list, array($_TEXT['YAHOO'], '<a href="http://messenger.yahoo.com/edit/send/?.target='.format_input($data['yahoo']).'"><img src="styles/'.STYLE.'/images/yahooS.png" border="0" alt="'.$_TEXT['YAHOO'].'" /></a> <a href="http://messenger.yahoo.com/edit/send/?.target='.format_input($data['yahoo']).'">'.fhtml($data['yahoo']).'</a>'));
}
$class = 'g';
foreach ($list as $line)
{
	echo '
			<tr>
				<td class="'.$class.'">'.$line[1].'</td>
			</tr>
	';
	$class = ($class=='w'?'g':'w');
}
echo '
			</table>
';

PluginHook('user-col_right');

echo '
		</td></tr>
		</table>
	</div>
';

PluginHook('user-middle');

if ($_FORUM['settings_user_guestbook'])
{
	echo '
	<div id="content">
		<table class="main">
		<tr><td class="oben" colspan="2">'.$_TEXT['GUESTBOOK'].'</td></tr>
		<tr><td class="w" style="width:50%;padding:0px;vertical-align:top;">
			<table>
	';

	$datei = 'data/user/'.$user.'.gb';
	
	if ($_POST['submit'] == $_TEXT['GUESTBOOK_SUBMIT'])
	{
		if (IsUser() && ($_POST['text'] != ''))
		{
			$line[0] = $_SESSION['Benutzername'];
			$line[1] = time();
			$line[2] = format_text($_POST['text']);
			FileAppend($datei, $line);
			SendMessage(array($user), $_TEXT['GUESTBOOK'], $_SESSION['Benutzername']." ".$_TEXT['MSG_WROTE_GUESTBOOK']."\n------------------------------\n".$_POST['text']);
		}
	}
	
	if (($_GET['submit'] == 'del') && (($user == $_SESSION['Benutzername']) OR IsAdmin($_SESSION['Benutzername'])))
	{
		FileDeleteLine($datei, $_GET['del']);
	}
	
	$eintrag = FileLoad($datei);
	$gesamt = count($eintrag);
	$pages = ceil(($gesamt)/5);
	if ($pages == 0) $pages=1;
	if ($page>$pages) $page=$pages;
	if ($page<"1") $page="1";
	

	
	if ($gesamt == 0)
	{ 
		echo '
				<tr><td class="w">'.$_TEXT['GUESTBOOK_NO_ENTRIES'].'</td></tr>
		';
	}
	else
	{
		$page = $_GET['page'];
		if ($page < 1) $page=1;
		$eintrag = array_reverse($eintrag);
		$von = (($page-1)*5);
		$bis = ($page*5);
		if ($von<1) $von=0;
		if ($bis>$gesamt) $bis=$gesamt;
		for ($i=$von; $i<$bis; $i++)
		{
			$gdat = $eintrag[$i];
			echo '
				<tr><td class="w">
					<p class="sub">'.ftime($gdat[1]).' '.$_TEXT['FROM'].' '.user($gdat[0]).((($user == $_SESSION['Benutzername']) OR IsAdmin($_SESSION['Benutzername']))?' [<a href="'.url('user.php?user='.$user.'&page='.$page.'&submit=del&del='.($gesamt-$i-1)).'">'.$_TEXT['DELETE'].'</a>]':'').'</p>
					<p style="margin-left:10px;margin-top:3px;">"<i>'.$gdat[2].'</i>"</p>
				</td></tr>
			';
		}

		echo '<tr><td class="w"><img src=styles/'.STYLE.'/images/gotoS.png border=0>';
		for ($i="1";$i<($pages+1);$i++)
		{
		 if ($i==$page)
		  echo "$i&nbsp;";
		 else
		  echo "<a href=user.php?user=$user&page=$i>$i</a>&nbsp;";
		}
		if ($page!=$pages) echo "<a href=user.php?user=$user&page=".($page+1).">&raquo;</a>";
		echo '</td></tr>';
	}
	echo '
				</table>
			</td>
	';
	if (IsUser())
	{
	echo '
			<td class="g" style="width:50%;vertical-align:top;">
				<form action="user.php?user='.$user.'&page=1" method="post" name="gbook">
				<table style="width:100%;">
				<tr>
					<td style="width:20%;">'.$_TEXT['GUESTBOOK_USERNAME'].':</td>
					<td style="width:80%;"><b>'.$_SESSION['Benutzername'].'<b></td>
				</tr>
				<tr>
					<td style="vertical-align:top;">'.$_TEXT['GUESTBOOK_TEXT'].':</td>
					<td><textarea  name="text" style="width:100%;" rows="7" cols="25"></textarea></td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:center;"><INPUT TYPE="SUBMIT" NAME="submit" VALUE="'.$_TEXT['GUESTBOOK_SUBMIT'].'"></td>
				</tr>
				</table>
				</form>
			</td>
	';
	}
	echo '
			</tr>
		</table>
	</div>
	';
}

PluginHook('user-bottom');

require 'include/page_bottom.php';  
?>