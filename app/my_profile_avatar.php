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

AuthUser();

if (defined('CONFIG_PROFILE_DISALLOW_AVATAR_EDIT'))
{
	require 'index.php';	
	Exit;
}

if ($_GET['action'] == 'change')
{
	if (file_exists('data/user/'.$_SESSION['Benutzername'].'.usr.ini'))
	{
		if ($_FILES['f1']['size'] > 0)
		{
			$info = pathinfo($_FILES['f1']['name']);
			if (!in_array(strtolower($info['extension']), Group2Array($_FORUM['settings_system_upload_avatar_formats'])))
			{
				$MSG_ERROR = MultiReplace($_TEXT['ERROR_UPLOAD_TYPE'], '<b>'.$info['extension'].'</b>');
				$_GET['action'] = '';
				include 'my_profile_avatar.php';
				Exit;
			} 
			if (!ImageResize($_FILES['f1']['tmp_name'], $_FILES['f1']['tmp_name'], $_FORUM['settings_system_upload_avatar_pixel'], $_FORUM['settings_system_upload_avatar_pixel']))
			{
				if ($_FILES['f1']['size'] > ($_FORUM['settings_system_upload_avatar_size']*1024)) 
				{
					$MSG_ERROR = MultiReplace($_TEXT['ERROR_UPLOAD_SIZE'], '<b>'.round($_FILES['f1']['size']/1024).' kB</b>');
					$_GET['action'] = '';
					include 'my_profile_avatar.php';
					Exit;
				}
		   		$size=getimagesize($_FILES['f1']['tmp_name']);
				if (($size[0]>$_FORUM['settings_system_upload_avatar_pixel']) OR ($size[1]>$_FORUM['settings_system_upload_avatar_pixel']))
		   		{
					$MSG_ERROR = MultiReplace($_TEXT['ERROR_UPLOAD_PIXEL'], '<b>'.$size[0].' x '.$size[1].'</b>');
					$_GET['action'] = '';
					include 'my_profile_avatar.php';
					Exit;
				}
			}
			$path = strtr(getcwd(), "\\", "/").'/data/upload/';
			$filename = 'av_'.$_SESSION['Benutzername'].'.jpg';
			if(!copy ($_FILES['f1']['tmp_name'],$path.$filename))
			{
				$MSG_ERROR = $_TEXT['ERROR_UPLOAD'];
				$_GET['action'] = '';
				include 'my_profile_avatar.php';
				Exit;
			} 
			else 
			{
				$ini = array();
				$ini['filename'] = $_FILES['f1']['name'];
				$ini['url'] = $filename;
				$ini['type'] = $_FILES['f1']['type'];
				IniSave('data/upload/av_'.$_SESSION['Benutzername'].'.ini', $ini);
				$_POST['rb_avatar'] = 'upload';
			}
		}
		$udat = IniLoad('./data/user/'.$_SESSION['Benutzername'].'.usr.ini');
		if ($_POST['rb_avatar'] == 'upload') 
			$udat['avatar'] = 'download.php?type=avatar&id='.$_SESSION['Benutzername'];
		else if ($_POST['rb_avatar'] == 'no') 
			$udat['avatar'] = '';
		else
			$udat['avatar'] = 'avatar/'.$_POST['rb_avatar'];
		IniSave('data/user/'.$_SESSION['Benutzername'].'.usr.ini',$udat);
		include 'my_profile.php';
		Exit;
	}
	else
	{
		$MSG_ERROR = $_TEXT['ERROR_SAVE_DATA'];
		include 'my_profile.php';
		Exit;
	}	
}

$_SUBNAV[] = array($_TEXT['LOGIN_PROFILE'], url('my_profile.php'));
$_SUBNAV[] = array($_TEXT['PROFILE_AVATAR'], url('my_profile_avatar.php'));

require 'include/page_top.php';
require 'include/my_profile_top.php';

$udat = IniLoad('data/user/'.$_SESSION['Benutzername'].'.usr.ini');

echo '
	<table class="main">
	<tr><td class="oben">'.$_TEXT['PROFILE_AVATAR'].'</td></tr>
	<tr><td class="w">
		<form enctype="multipart/form-data" action="'.url('my_profile_avatar.php?action=change').'" method="post" onSubmit="showLoading();">
		<table style="width:100%;">
			<tr>
				<td colspan="3" class="g">'.$_TEXT['PROFILE_AVATAR_TEXT'].'</td>
			</tr>
			<tr>
				<td style="text-align:center;width:33%;"><input type="radio" name="rb_avatar" '.($udat['avatar']==''?'checked="checked" ':'').' value="no"> '.$_TEXT['PROFILE_AVATAR_NO'].'</td>
';
$checked = false;
$avatars = LoadFileList('avatar/', '.');
$counter = 1;
foreach ($avatars as $avatar)
{
	echo '<td style="text-align:center;width:33%;"><img src="./avatar/'.$avatar.'"><br /><input type="radio" name="rb_avatar" ';
	if ("avatar/".$avatar == $udat['avatar'])
	{
		echo 'checked="checked" ';
		$checked = true;
	}
	echo 'value="'.$avatar.'"> '.$avatar.'</td>';
	$counter++;
	if ($counter == 3)
	{
		$counter = 0;
		echo '
			</tr>
			<tr>
		';
	}
}
echo '
			</tr>
';
if ($_FORUM['settings_system_upload_avatar'])
{
	$checked_upload = ($udat['avatar'] == 'download.php?type=avatar&id='.$_SESSION['Benutzername']);
	echo '
			<tr>
				
				'.(file_exists('data/upload/av_'.$_SESSION['Benutzername'].'.ini')?'<td class="g"><input type=radio name="rb_avatar" '.($checked_upload?'checked="checked" ':'').'value="upload"> <img src="'.url('download.php?type=avatar&id='.$_SESSION['Benutzername']).'" style="vertical-align:middle;" /></td><td class="g" colspan="2">':'<td colspan="3" class="g">').'
						<b>'.$_TEXT['UPLOAD_NEW'].':</b>
						<br /><input name="f1" type="file"> <input type="submit" value="'.$_TEXT['UPLOAD'].'">
						<br /><small>'.$_TEXT['UPLOAD_TYPE'].': '.implode(', ', Group2Array($_FORUM['settings_system_upload_avatar_formats'])).(!extension_loaded('gd')?' | '.$_TEXT['UPLOAD_SIZE'].': '.$_FORUM['settings_system_upload_avatar_size'].' kB | '.$_TEXT['UPLOAD_PIXEL'].': '.$_FORUM['settings_system_upload_avatar_pixel'].' x '.$_FORUM['settings_system_upload_avatar_pixel']:'').' | '.$_TEXT['UPLOAD_NEW2'].'</small>
				</td>
			</tr>
	';
}
echo '
			<tr>
				<td colspan="3" style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['SAVE'].'" /></td>
			</tr>
		</table>
    	</td></tr>
	</table>
';
require 'include/my_profile_bottom.php';
require 'include/page_bottom.php';
?>