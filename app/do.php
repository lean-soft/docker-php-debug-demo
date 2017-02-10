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

$_BOARD = IniLoad('data/'.$_GET['board'].'/board.ini');

function upload_file()
{
	GLOBAL $_POST, $_FORUM, $_FILES, $_TEXT, $MSG_ERROR, $java_showoption;

	$java_showoption = 'attachment';

	if (($_FILES['upload']['size'] > 0) && ($_FORUM['settings_system_upload_file']) && (auth('auth_upload', false)))
	{
		$info = pathinfo($_FILES['upload']['name']);
		if (!in_array(strtolower($info['extension']), Group2Array($_FORUM['settings_system_upload_file_formats'])))
		{
			$MSG_ERROR = MultiReplace($_TEXT['ERROR_UPLOAD_TYPE'], '<b>'.$info['extension'].'</b>');
			$loadfrompost = true;
			$preview = false;
			require 'post.php';
			Exit;
		} 
		if ($_FILES['upload']['size'] > ($_FORUM['settings_system_upload_file_size']*1024)) 
		{
			$MSG_ERROR = MultiReplace($_TEXT['ERROR_UPLOAD_SIZE'], '<b>'.round($_FILES['upload']['size']/1024).' kB</b>');
			$loadfrompost = true;
			$preview = false;
			require 'post.php';
			Exit;
		}
		$id = time();
		$path = strtr(getcwd(), "\\", "/").'/data/upload/';
		$filename = $id.'_file.'.$info['extension'];
		if(!copy($_FILES['upload']['tmp_name'],$path.$filename))
		{
			$MSG_ERROR = $_TEXT['ERROR_UPLOAD'];
			$loadfrompost = true;
			$preview = false;
			require 'post.php';
			Exit;
		} 
		else 
		{
			$ini = array();
			$ini['filename'] = $_FILES['upload']['name'];
			$ini['url'] = $filename;
			$ini['type'] = $_FILES['upload']['type'];
			$ini['size'] = $_FILES['upload']['size'];
			$ini['board'] = $_GET['board'];
			$ini['thema'] = $_GET['thema'];
			$ini['user'] = $_POST['name'];
			$ini['ip'] = $_SESSION['IP'];
			if (ImageResize($_FILES['upload']['tmp_name'], $path.$id.'_thumb.'.$info['extension'], 50, 50, NULL, false))
			{
				$ini['thumbnail'] = $id.'_thumb.'.$info['extension'];
			}
			if (!defined('CONFIG_POST_IMG_MAXWIDTH'))
			{
				define('CONFIG_POST_IMG_MAXWIDTH', 500);
			}
			if (ImageResize($_FILES['upload']['tmp_name'], $path.$id.'_image.'.$info['extension'], CONFIG_POST_IMG_MAXWIDTH, NULL, NULL, true))
			{
				$ini['image'] = $id.'_image.'.$info['extension'];
			}
			IniSave('data/upload/'.$id.'.ini', $ini);
		}
		AddToGroup($_POST['attachment'], $id);
	}
}

function PollCreate()
{
	GLOBAL $java_showoption;
	if (auth('auth_poll', false))
	{
		$java_showoption = 'poll';
		$nr = time();
		$ini = array();
		for ($i = 1; $i <= 10; $i++) $ini['v'.$i] = 0;
		IniSave('data/poll_'.$nr.'.ini', $ini);
		return $nr;
	}
	else
	{
		return '';
	}
}

function PollSave()
{
	GLOBAL $_POST;
	if (auth('auth_poll', false) && ($_POST['poll'] <> ''))
	{
		$filename = 'data/poll_'.$_POST['poll'].'.ini';
		if ($_POST['poll_delete'] == 'on')
		{
			$_POST['poll'] = '';
			@unlink($filename);
		}
		else
		{
			$ini = IniLoad($filename);
			$ini['q'] = format_text($_POST['poll_q']);
			for ($i = 1; $i <= 10; $i++) $ini['a'.$i] = format_text($_POST['poll_a'.$i]);
			$ini['c'] = ($_POST['poll_c'] == 'on');
			IniSave($filename, $ini);
		}
	}
}

if ($_POST['emailben'] == 'on') $_POST['emailben'] = 'true';

//$_POST['text'] = preg_replace("/\[([^\]]*)\]\[\/([^\]]*)\]/is", "", $_POST['text']);

if (($_POST['text'] <> '') && (file_exists('./data/badword.ini')))
{	
	$_LIST = IniLoad('./data/badword.ini');
	foreach ($_LIST as $word => $replace)
	{
		if (version_compare(phpversion(), "5.0.0") <> -1)
		{
			$_POST['text'] = str_ireplace($word, $replace, $_POST['text']);
			$_POST['titel'] = str_ireplace($word, $replace, $_POST['titel']);
		}
		else
		{
			$_POST['text'] = str_replace($word, $replace, $_POST['text']);
			$_POST['titel'] = str_replace($word, $replace, $_POST['titel']);
		}
	}
}

if (
	(($_GET['do'] == "newthread") OR ($_GET['do'] == "reply") OR ($_GET['do'] == "edit"))
   AND
   	(!in_array($_POST['submit'], array($_TEXT['POLL_CREATE'], $_TEXT['UPLOAD'], $_TEXT['PREVIEW'])))
   AND
   	(((strtolower($_POST['new_code']) != strtolower($_SESSION['new_code2'])) OR ($_POST['new_code'] == "")) && (extension_loaded('gd')) && auth_guestuser('settings_post_code'))
   )
{
	$MSG_ERROR = $_TEXT['ERROR_REG_CODE'];
	$loadfrompost = true;
	require './post.php';
	Exit;
}

if ($_GET['do'] == 'newthread')
{
	auth('auth_topic');

	if (!((file_exists('./data/'.$_GET['board'].'/')) && (is_numeric($_GET['board']))))
	{
		$MSG_ERROR = $_TEXT['ERROR_BOARD'];
		require './include/index.php';
		Exit;
	}

	if ($_SESSION['Benutzername'])
	{
		$_POST['name'] = $_SESSION['Benutzername'];
	}
	else
	{
		if (strlen(trim($_POST['name']))<3)
		{
			$MSG_ERROR = $_TEXT['ERROR_NAME_TOO_SHORT'];
			$loadfrompost = true;
			require './post.php';
			Exit;
		}
	 	
		if (IsUser($_POST['name']))
		{
			$MSG_ERROR = $_TEXT['ERROR_USER_ALREADY_EXISTS'];
			$loadfrompost = true;
			require './post.php';
			Exit;
		} 
	}

	if ($_POST['submit'] == $_TEXT['POLL_CREATE'])
	{
		if ($_POST['poll'] == '') $_POST['poll'] = PollCreate();
		$loadfrompost = true;
		$preview = false;
		require './post.php';
		Exit;
	}
	PollSave();

	if ($_POST['submit'] == $_TEXT['UPLOAD'])
	{
		upload_file();
		$loadfrompost = true;
		$preview = false;
		require './post.php';
		Exit;
	}

	if (trim($_POST['titel']) == '')
	{
		$MSG_ERROR = $_TEXT['ERROR_TITLE_MISSING'];
		$loadfrompost = true;
		require './post.php';
		Exit;
	} 

	if ((strlen($_POST['text']) < 10) OR (strlen($_POST['text']) > (CONFIG_POST_MAXCHAR+100)))
	{
		$MSG_ERROR = MultiReplace($_TEXT['ERROR_TEXT_LENGTH'], CONFIG_POST_MAXCHAR);
		$loadfrompost = true;
		require './post.php';
		Exit;
	}

	if ($_POST['submit'] == $_TEXT['PREVIEW'])
	{
		$loadfrompost = true;
		$preview = true;
		require './post.php';
		Exit;
	}

	if (!IsUser($_POST['name']))
	{
		$_POST['name'] = '@'.$_POST['name'];
	}

	$i=1;
	for (;;)
	{
		if (!(file_exists('./data/'.$_GET['board'].'/'.$i.'.txt'))) Break;
		$i++;
	}

	// Board-Ini aktualisieren
	$_BOARD['topics']++;
	$_BOARD['lastpost'] = $i;
	$_BOARD['lastpost_date'] = time();
	$_BOARD['lastpost_title'] = format_string($_POST['titel']);
	$_BOARD['lastpost_from'] = $_POST['name'];
	$_BOARD['lastpost_beitrag'] = '0';
	IniSave('./data/'.$_GET['board'].'/board.ini', $_BOARD);

	$fp = fopen('./data/'.$_GET['board'].'/'.$i.'.txt',"w+");
	fwrite($fp, time().$TRENNZEICHEN.format_string($_POST['name']).$TRENNZEICHEN.format_string($_POST['titel']).$TRENNZEICHEN.format_text($_POST['text']).$TRENNZEICHEN.$_SESSION['IP'].$TRENNZEICHEN.Group2SubGroup($_POST['attachment']).$TRENNZEICHEN.format_string($_POST['poll']).$TRENNZEICHEN."\n");
	fclose($fp);

	$_THEMA = array();
	$_THEMA['answers'] = 0;
	$_THEMA['author'] = $_POST['name'];
	$_THEMA['lastpost_date'] = time();
	$_THEMA['lastpost_from'] = $_POST['name'];
	$_THEMA['lastpost_title'] = format_string($_POST['titel']);
	$_THEMA['lastpost_ip'] = $_SESSION['IP'];
	$_THEMA['lock'] = false;
	$_THEMA['pin'] = false;
	$_THEMA['title'] = format_string($_POST['titel']);
	$_THEMA['views'] = 0;
	$_THEMA['attachment'] = ($_POST['attachment'] <> '');
	$_THEMA['poll'] = ($_POST['poll'] <> '');
	if ($_POST['emailben']) $_THEMA['notification'] = $_POST['name'];
	PluginHook('do-newthread_threadini');
	IniSave('data/'.$_GET['board'].'/'.$i.'.txt.ini', $_THEMA);

	if (IsUser($_POST['name']))
	{
		$udat = IniLoad('./data/user/'.$_POST['name'].'.usr.ini');
		$udat['count_topics']++;
		IniSave('./data/user/'.$_POST['name'].'.usr.ini', $udat);
	}


	DeleteFromGroup($_BOARD['notification'], $_POST['name']);
	SendMessage(Group2Array($_BOARD['notification']), format_string($_POST['titel']), $_POST['name']." ".$_TEXT['MSG_WROTE_TOPIC']."\n".url('thread.php', $_GET['board'], $i, 0, false, true)."\n".$_TEXT['TITLE'].": ".format_string($_POST['titel'])."\n------------------------------\n".$_POST['text']);
	history($_GET['board'], $i, 0);
	session_write_close(); 
	if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
	{
		$_GET['thema'] = $i;
		require 'thread.php';
	}
	else header("Location: ".url('thread.php', $_GET['board'], $i, null, true, true));
	exit;
}

if ($_GET['do'] == 'reply')
{
	auth('auth_answere');

	if (!((file_exists('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')) && (is_numeric($_GET['board']))))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require './index.php';
		Exit;
	}

	if (IsUser())
	{
		$_POST['name'] = $_SESSION['Benutzername'];
	}
	else
	{
		if (strlen(trim($_POST['name']))<3)
		{
			$MSG_ERROR = $_TEXT['ERROR_NAME_TOO_SHORT'];
			$loadfrompost = true;
			require './post.php';
			Exit;
		}
	 	
		if (IsUser($_POST['name']))
		{
			$MSG_ERROR = $_TEXT['ERROR_USER_ALREADY_EXISTS'];
			$loadfrompost = true;
			require './post.php';
			Exit;
		} 
	}

	$_THEMA = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');

	if ($_THEMA['lock'])
	{
		require 'thread.php';
		Exit;
	}

	if ($_POST['submit'] == $_TEXT['POLL_CREATE'])
	{
		if ($_POST['poll'] == '') $_POST['poll'] = PollCreate();
		$loadfrompost = true;
		$preview = false;
		require './post.php';
		Exit;
	}
	PollSave();

	if ($_POST['submit'] == $_TEXT['UPLOAD'])
	{
		upload_file();
		$loadfrompost = true;
		$preview = false;
		require './post.php';
		Exit;
	}

	if ((strlen($_POST['text']) < 10) OR (strlen($_POST['text']) > (CONFIG_POST_MAXCHAR+100)))
	{
		$MSG_ERROR = MultiReplace($_TEXT['ERROR_TEXT_LENGTH'], CONFIG_POST_MAXCHAR);
		$loadfrompost = true;
		require './post.php';
		Exit;
	}

	if ($_POST['submit'] == $_TEXT['PREVIEW'])
	{
		$loadfrompost = true;
		$preview = true;
		require './post.php';
		Exit;
	}

	if (($_FORUM['settings_iplock']) AND ($_THEMA['lastpost_ip']==$_SESSION['IP']))
	{
		$MSG_ERROR = $_TEXT['ERROR_SEC_POST'];
		$loadfrompost = true;
		require './post.php';
		Exit;
	}

	if (!IsUser($_POST['name']))
	{
		$_POST['name'] = '@'.$_POST['name'];
	}

	$to = array_merge(Group2Array($_BOARD['notification']),Group2Array($_THEMA['notification']));
	$group = Array2Group($to);
	DeleteFromGroup($group, $_POST['name']);
	SendMessage(Group2Array($group), format_string($_POST['titel']), $_POST['name']." ".$_TEXT['MSG_WROTE_TO_TOPIC']."\n".url('thread.php', $_GET['board'], $_GET['thema'], $_THEMA['answers']+1, false, true)."\n".$_TEXT['TITLE'].": ".format_string($_POST['titel'])."\n------------------------------\n".$_POST['text']);

	$_BOARD['answeres']++;
	$_BOARD['lastpost'] = $_GET['thema'];
	$_BOARD['lastpost_date'] = time();
	$_BOARD['lastpost_title'] = format_string($_POST['titel']);
	$_BOARD['lastpost_from'] = $_POST['name'];
	$_BOARD['lastpost_beitrag'] = $_THEMA['answers']+1;
	IniSave('./data/'.$_GET['board'].'/board.ini', $_BOARD);

	$_THEMA['answers']++;
	$_THEMA['lastpost_date'] = time();
	$_THEMA['lastpost_from'] = $_POST['name'];
	$_THEMA['lastpost_title'] = format_string($_POST['titel']);
	$_THEMA['lastpost_ip'] = $_SESSION['IP'];
	if ($_POST['attachment'] <> '') $_THEMA['attachment'] = true;
	if ($_POST['poll'] <> '') $_THEMA['poll'] = true;
	if ($_POST['emailben']) 
	{
		AddToGroup($_THEMA['notification'], $_POST['name']);
	}
	else
	{
		DeleteFromGroup($_THEMA['notification'], $_POST['name']);
	}
	IniSave('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $_THEMA);

	$fp = fopen('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt',"a");
	fwrite($fp, time().$TRENNZEICHEN.$_POST['name'].$TRENNZEICHEN.format_string($_POST['titel']).$TRENNZEICHEN.format_text($_POST['text']).$TRENNZEICHEN.$_SESSION['IP'].$TRENNZEICHEN.Group2SubGroup($_POST['attachment']).$TRENNZEICHEN.format_string($_POST['poll']).$TRENNZEICHEN."\n");
	fclose($fp);
		
	
	if (IsUser($_THEMA['author']))
	{
		$udat = IniLoad('./data/user/'.$_THEMA['author'].'.usr.ini');
		$udat['count_answeres2']++;
		IniSave('./data/user/'.$_THEMA['author'].'.usr.ini', $udat);
	}
	if (IsUser($_POST['name']))
	{
		$udat = IniLoad('./data/user/'.$_POST['name'].'.usr.ini');
		$udat['count_answeres']++;
		IniSave('./data/user/'.$_POST['name'].'.usr.ini', $udat);
	}
	history($_GET['board'], $_GET['thema'], $_THEMA['answers']);
	session_write_close(); 
	if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
	{
		$_GET['page'] = 'last';
		require 'thread.php';
	}
	else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], $_THEMA['answers'], true, true));
	exit;
}

if ($_GET['do'] == 'edit')
{
	if (!((file_exists('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')) && (is_numeric($_GET['board']))))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require './index.php';
		Exit;
	}
	
	if (
		(!(IsMod($_GET['board']) && $_FORUM['settings_admin_edit']))
	    AND 
		(!($_SESSION['Benutzername'] == $_POST['name']))
	   )
	{
		$MSG_ERROR = $_TEXT['ERROR_WRONG_LOGIN'];
		$loadfrompost = true;
		require 'post.php';
		Exit;
	}

	$_THEMA = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');

	if (
		($_THEMA['lock'])
	    AND
		(!(IsMod($_GET['board']) && $_FORUM['settings_admin_edit']))
	   )
	{
		require 'thread.php';
		Exit;
	}

	if ($_POST['submit'] == $_TEXT['POLL_CREATE'])
	{
		if ($_POST['poll'] == '') $_POST['poll'] = PollCreate();
		$loadfrompost = true;
		$preview = false;
		require 'post.php';
		Exit;
	}
	PollSave();

	if ($_POST['submit'] == $_TEXT['UPLOAD'])
	{
		upload_file();
		$loadfrompost = true;
		$preview = false;
		require 'post.php';
		Exit;
	}

	if ((strlen($_POST['text']) < 10) OR (strlen($_POST['text']) > (CONFIG_POST_MAXCHAR+100)))
	{
		$MSG_ERROR = MultiReplace($_TEXT['ERROR_TEXT_LENGTH'], CONFIG_POST_MAXCHAR);
		$loadfrompost = true;
		require 'post.php';
		Exit;
	}

	if ($_POST['submit'] == $_TEXT['PREVIEW'])
	{
		$loadfrompost = true;
		$preview = true;
		require 'post.php';
		Exit;
	}
	
	$data = FileLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt');
	if (!(IsMod($_GET['board'],$_SESSION['Benutzername']) && $_FORUM['settings_admin_edit']))
	if ($data[$_GET['beitrag']][1]<>$_POST['name'])
	{
		$MSG_ERROR = $_TEXT['ERROR_WRONG_LOGIN'];
		$loadfrompost = true;
		require 'post.php';
		Exit;
	}
	if ($data[$_GET['beitrag']][1]==$_SESSION['Benutzername'])
	{
		$data[$_GET['beitrag']][3]=format_text($_POST['text']).'<br /><br /><p class="sub">'.$_TEXT['EDITED'].' '.ftime(time(), false).'</p>';
	}
	else
	{
		$data[$_GET['beitrag']][3]=format_text($_POST['text']);
	}
	$data[$_GET['beitrag']][2]=format_string($_POST['titel']);
	$data[$_GET['beitrag']][5]=Group2SubGroup($_POST['attachment']);
	$data[$_GET['beitrag']][6]=format_string($_POST['poll']);
	FileSave('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt', $data);

	if (($_GET['thema'] == $_BOARD['lastpost']) && ($_GET['beitrag'] == count($data)-1))
	{
		$_BOARD['lastpost_title'] = format_string($_POST['titel']);
		IniSave('./data/'.$_GET['board'].'/board.ini', $_BOARD);
	}
	if ($_POST['emailben']) 
	{
		AddToGroup($_THEMA['notification'], $_POST['name']);
	}
	else
	{
		DeleteFromGroup($_THEMA['notification'], $_POST['name']);
	}
	IniSave('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $_THEMA);

	PluginHook('do-edit');

	RepairThreadIni($_GET['board'], $_GET['thema']);
	session_write_close(); 
	if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
	{
		$_GET['page'] = ceil(($_GET['beitrag']+1)/10);
		require 'thread.php';
	}
	else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], $_GET['beitrag'], true, true));
	Exit;
}

if ($_GET['do'] == 'del')
{
	if (!((file_exists('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')) && (is_numeric($_GET['board']))))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require 'index.php';
		Exit;
	}

	$allow = IsMod($_GET['board'],$_SESSION['Benutzername']);

	if (!$allow)
	{
		$data = FileLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt');
		if ($_SESSION['Benutzername'] == $data[$_GET['beitrag']][1])
		{
			if (($_GET['beitrag'] > 0) OR (count($data) == 1))
			{
				$allow=true;
			}
		}
	}

	if ($allow)
	{
		if (!$_GET['delete_confirm'])
		{
			$_BOARD = IniLoad('./data/'.$_GET['board'].'/board.ini');
			$_THEMA = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
			if ($_GET['beitrag']==0)
			{
				$text = MultiReplace($_TEXT['CONFIRM_DELETE_THREAD'], $_THEMA['title']);
			}
			else
			{
				$data = FileLoad('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt');
				$text = MultiReplace($_TEXT['CONFIRM_DELETE'], $data[$_GET['beitrag']][2]);
			}
			$MSG_NOTICE = $text.'<p class="buttons"><a href="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&beitrag='.$_GET['beitrag'].'&delete_confirm=true&do=del').'">'.$_TEXT['CONFIRM_YES'].'</a> <a href="javascript:window.history.back()">'.$_TEXT['CONFIRM_NO'].'</a></p>';
			$_GET['page'] = ceil(($_GET['beitrag'])/10);
			require_once 'thread.php';
			Exit;
		}
		if ($_GET['beitrag']==0)
		{
			DeleteThread($_GET['board'], $_GET['thema']);
			session_write_close(); 
			if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
			{
				require 'board.php';
			}
			else header("Location: ".url('board.php', $_GET['board'], null, null, null, true));
			Exit;
		}
		else
		{
			DeletePost($_GET['board'], $_GET['thema'], $_GET['beitrag']);
			session_write_close(); 
			if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
			{
				$_GET['page'] = ceil(($_GET['beitrag'])/10);
				require 'thread.php';
			}
			else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], ($_GET['beitrag']-1), true, true));
			Exit;
		}
	}
	session_write_close(); 
	if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
	{
		$_GET['page'] = ceil(($_GET['beitrag']+1)/10);
		require 'thread.php';
	}
	else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], $_GET['beitrag'], true, true));
	Exit;
}

if ($_GET['do'] == 'del_file')
{
	if (file_exists('./data/upload/'.$_GET['id'].'.ini'))
	{
		$ini = IniLoad('./data/upload/'.$_GET['id'].'.ini');
		if (($ini['board'] <> '') AND (IsMod($ini['board'], $_SESSION['Benutzername']) OR ($_SESSION['Benutzername'] == $ini['user'])))
		{
			if (!$_GET['delete_confirm'])
			{
				$MSG_NOTICE = MultiReplace($_TEXT['CONFIRM_DELETE_FILE'], '<b>'.$ini['filename'].'</b>').'<p class="buttons"><a href="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&id='.$_GET['id'].'&delete_confirm=true&do=del_file').'">'.$_TEXT['CONFIRM_YES'].'</a> <a href="javascript:window.history.back()">'.$_TEXT['CONFIRM_NO'].'</a></p>';
				require_once 'include/page_top.php';
				require_once 'include/page_bottom.php';
				Exit;
			}

			unlink('data/upload/'.$ini['url']);
			if ($ini['thumbnail'] <> '') unlink('data/upload/'.$ini['thumbnail']);
			if ($ini['image'] <> '') unlink('data/upload/'.$ini['image']);
			unlink('data/upload/'.$_GET['id'].'.ini');

			$data = FileLoad('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt');
			$has_att = false;
			for ($i = 0; $i < count($data); $i++)
			if (strlen($data[$i][5]) > 8)
			{
				$group = SubGroup2Group($data[$i][5]);
				DeleteFromGroup($group, $_GET['id']);
				$data[$i][5] = Group2SubGroup($group);
				if (strlen($data[$i][5]) > 8) $has_att = true;
			}
			FileSave('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt', $data);

			$_THEMA = IniLoad('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
			$_THEMA['attachment'] = $has_att;
			IniSave('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $_THEMA);
			session_write_close(); 
			if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
			{
				$_GET['page'] = ceil(($_GET['beitrag']+1)/10);
				require 'thread.php';
			}
			else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], $_GET['beitrag'], true, true));
			Exit;
		}
	}
}

if ($_GET['do'] == 'notification')
{
	if (!(file_exists('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require './index.php';
		Exit;
	}
	if ($_SESSION['Benutzername'] <> '')
	{
		$_THEMA = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
		AddToGroup($_THEMA['notification'], $_SESSION['Benutzername']);
		IniSave('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $_THEMA);
		session_write_close(); 
		if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
		{
			require 'thread.php';
		}
		else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], null, true, true));
		Exit;
	}
}

if ($_GET['do'] == 'notificationoff')
{
	if (!(file_exists('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require 'index.php';
		Exit;
	}
	if ($_SESSION['Benutzername'] <> '')
	{
		$_THEMA = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
		DeleteFromGroup($_THEMA['notification'], $_SESSION['Benutzername']);
		IniSave('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $_THEMA);
		session_write_close(); 
		if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
		{
			require 'thread.php';
		}
		else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], null, true, true));
		Exit;
	}
}

if ($_GET['do'] == 'notification_board_on')
{
	if (!((file_exists('data/'.$_GET['board'].'/')) && (is_numeric($_GET['board']))))
	{
		$MSG_ERROR = $_TEXT['ERROR_BOARD'];
		require 'index.php';
		Exit;
	}
	if (IsUser() && auth('auth_read', false, $_GET['board']))
	{
		$_BOARD = IniLoad('data/'.$_GET['board'].'/board.ini');
		AddToGroup($_BOARD['notification'], $_SESSION['Benutzername']);
		IniSave('data/'.$_GET['board'].'/board.ini', $_BOARD);
		session_write_close(); 
		if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
		{
			require 'board.php';
		}
		else header("Location: ".url('board.php', $_GET['board'], null, null, true, true));
		Exit;
	}
}

if ($_GET['do'] == 'notification_board_off')
{
	if (!((file_exists('./data/'.$_GET['board'].'/')) && (is_numeric($_GET['board']))))
	{
		$MSG_ERROR = $_TEXT['ERROR_BOARD'];
		require './index.php';
		Exit;
	}
	if (IsUser())
	{
		$_BOARD = IniLoad('./data/'.$_GET['board'].'/board.ini');
		DeleteFromGroup($_BOARD['notification'], $_SESSION['Benutzername']);
		IniSave('./data/'.$_GET['board'].'/board.ini', $_BOARD);
		session_write_close(); 
		if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
		{
			require 'board.php';
		}
		else header("Location: ".url('board.php', $_GET['board'], null, null, true, true));
		Exit;
	}
}

if ($_GET['do'] == 'lock')
{
	if (!(file_exists('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require './index.php';
		Exit;
	}
	if (IsMod($_GET['board'],$_SESSION['Benutzername']))
	{
		$data = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
		if (!$data['lock'])
		{
			$data['lock'] = true;
			$data['lock_time'] = time();
			$data['lock_user'] = $_SESSION['Benutzername'];
			IniSave('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $data);
	
			if (IsUser($data['author']))
			{
				$udat = IniLoad('./data/user/'.$data['author'].'.usr.ini');
				$udat['count_locked']++;
				Inisave('./data/user/'.$data['author'].'.usr.ini', $udat);
			}
		}
		session_write_close(); 
		if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
		{
			require 'thread.php';
		}
		else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], null, true, true));
		Exit;
	}
}

if ($_GET['do'] == 'unlock')
{
	if (!(file_exists('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require './index.php';
		Exit;
	}
	if (IsMod($_GET['board'],$_SESSION['Benutzername']))
	{
		$data = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
		if ($data['lock'])
		{
			$data['lock'] = false;
			$data['lock_time'] = time();
			$data['lock_user'] = $_SESSION['Benutzername'];
			IniSave('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $data);
	
			if (IsUser($data['author']))
			{
				$udat = IniLoad('./data/user/'.$data['author'].'.usr.ini');
				$udat['count_locked']--;
				Inisave('./data/user/'.$data['author'].'.usr.ini', $udat);
			}
		}
		session_write_close(); 
		if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
		{
			require 'thread.php';
		}
		else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], null, true, true));
		Exit;
	}
}

if ($_GET['do'] == 'pin')
{
	if (!(file_exists('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require './index.php';
		Exit;
	}
	if (IsMod($_GET['board'],$_SESSION['Benutzername']))
	{
		$data = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
		if (!$data['pin'])
		{
			$data['pin'] = true;
			$data['pin_time'] = time();
			$data['pin_user'] = $_SESSION['Benutzername'];
			IniSave('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $data);
		}
		session_write_close(); 
		if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
		{
			require 'thread.php';
		}
		else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], null, true, true));
		Exit;
	}
}

if ($_GET['do'] == 'unpin')
{
	if (!(file_exists('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require './index.php';
		Exit;
	}
	if (IsMod($_GET['board'],$_SESSION['Benutzername']))
	{
		$data = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
		if ($data['pin'])
		{
			$data['pin'] = false;
			$data['pin_time'] = time();
			$data['pin_user'] = $_SESSION['Benutzername'];
			IniSave('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $data);
		}
		session_write_close(); 
		if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
		{
			require 'thread.php';
		}
		else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], null, true, true)); 
		Exit;
	}
}

if ($_GET['do'] == 'edit_tag')
{
	if (!(file_exists('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require './index.php';
		Exit;
	}
	if (IsMod($_GET['board'],$_SESSION['Benutzername']))
	{
		$_THEMA = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');
		if ($_POST['action'] <> 'save')
		{
			$MSG_NOTICE = '
					<form action="'.url('do.php?board='.$_GET['board'].'&thema='.$_GET['thema'].'&do=edit_tag').'" method="post">
					<input type="hidden" name="action" value="save" />
					<b>'.$_TEXT['EDIT_TAG'].':</b> <input type="text" name="tag" value="'.format_input($_THEMA['tag']).'" /> <input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" />
					</form>
			';
			require_once 'thread.php';
			Exit;
		}
		else
		{
			$_THEMA['tag'] = format_string($_POST['tag']);
			IniSave('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $_THEMA);
			session_write_close(); 
			if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
			{
				require 'thread.php';
			}
			else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], null, true, true));
			Exit;
		}
	}
}

if ($_GET['do'] == 'rating')
{
	if (!((file_exists('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')) && (is_numeric($_GET['board']))))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require './index.php';
		Exit;
	}
	
	$_THEMA = IniLoad('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');

	if (!(
		((IsInGroup($_THEMA['rating_ips'], $_SESSION['IP'])) 
	   OR
		((IsInGroup($_THEMA['rating_user'], $_SESSION['Benutzername']) && IsUser())))
	   ))
	{
		if (($_GET['vote'] >= 1) && ($_GET['vote'] <= 5))
		{
			$_THEMA['rating_count']++;
			$_THEMA['rating_points'] += $_GET['vote'];
			if (IsUser()) 
			{
				AddToGroup($_THEMA['rating_user'],$_SESSION['Benutzername']); 
			}
			else
			{
				AddToGroup($_THEMA['rating_ips'],$_SESSION['IP']); 
			}
			IniSave('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', $_THEMA);
			session_write_close(); 
			if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
			{
				require 'thread.php';
			}
			else header("Location: ".url('thread.php', $_GET['board'], $_GET['thema'], null, true, true));
			Exit;
		}
	}
}


if ($_GET['do'] == 'move')
{
	if (!(file_exists('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')))
	{
		$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
		require 'index.php';
		Exit;
	}
	if ($_GET['board'] == $_POST['to_board'])
	{
		$MSG_ERROR = $_TEXT['ERROR_BOARD_INVALID'];
		require 'do_move.php';
		Exit;
	}
	if (IsMod($_GET['board'],$_SESSION['Benutzername']))
	{
		$i=1;
		for (;;)
		{
			if (!(file_exists('data/'.$_POST['to_board'].'/'.$i.'.txt'))) Break;
			$i++;
		}

		$data = FileLoad('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt');
		foreach ($data as $line)
		{
			foreach (Group2Array(SubGroup2Group($line[5])) as $att)
			{
				$ini = IniLoad('./data/upload/'.$att.'.ini');
				$ini['board'] = $_POST['to_board'];
				IniSave('./data/upload/'.$att.'.ini', $ini);
			}
		}
		rename('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt', 'data/'.$_POST['to_board'].'/'.$i.'.txt');
		rename('data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini', 'data/'.$_POST['to_board'].'/'.$i.'.txt.ini');
		RepairBoardIni($_GET['board']);
		RepairBoardIni($_POST['to_board']);
		history_move($_GET['board'], $_GET['thema'], $_POST['to_board'], $i);
		session_write_close(); 
		if (defined('CONFIG_DISALLOW_HEADER_REDIRECT'))
		{
			require 'board.php';
		}
		else header("Location: ".url('board.php', $_GET['board'], null, null, true, true));
		Exit;
	}
}

$MSG_ERROR = $_TEXT['ERROR_NOT_EXEC'].' (#150)';
require './index.php';
?>