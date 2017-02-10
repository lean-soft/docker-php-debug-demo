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

AuthUser();

$_SUBNAV[] = array($_TEXT['LOGIN_PROFILE'], url('my_profile.php'));
$_SUBNAV[] = array($_TEXT['PROFILE_NOTIFICATION'], url('my_profile_notification.php'));

require 'include/page_top.php';
require 'include/my_profile_top.php';

$udat = IniLoad('./data/user/'.$_SESSION['Benutzername'].'.usr.ini');

if (($_GET['action'] == 'off') OR ($_GET['action'] == 'on'))
{
	$file = '';
	if ((is_numeric($_GET['board'])) && ($_GET['board'] != '') && auth('auth_read', $_GET['board'], false))
	{
		if ((is_numeric($_GET['thread'])) && ($_GET['thread'] != ''))
		{
			$file = 'data/'.$_GET['board'].'/'.$_GET['thread'].'.txt.ini';
		}
		else
		{
			$file = 'data/'.$_GET['board'].'/board.ini';
		}
		if (file_exists($file))
		{
			$ini = IniLoad($file);
			if ($_GET['action'] == 'off') DeleteFromGroup($ini['notification'], $_SESSION['Benutzername']);
			else if ($_GET['action'] == 'on') AddToGroup($ini['notification'], $_SESSION['Benutzername']);
			IniSave($file, $ini);
		}
	}
}

echo '
	<table class="main">
	<tr><td class="oben">'.$_TEXT['PROFILE_NOTIFICATION'].'</td></tr>
	<tr><td>
		<table>
			<tr>
				<td class="g" colspan="3">'.$_TEXT['PROFILE_NOTIFICATION_BOARDS'].'</td>
			</tr>
';
$_BOARDS = IniLoad(DIR.'data/boards.ini');
$list = Group2Array($_BOARDS['order']);
for($i = 0; $i < count($list); $i++)
{
	$item = $list[$i];
	if (substr($item, 0, 1) == 'c')
	{
		echo '
			<tr>
				<td colspan="3" class="w"><b>'.$_BOARDS[$item].'</b></td>
			</tr>
		';
	}		
	else if ((substr($item, 0, 1) == 'b') AND (is_numeric(substr($item, 1))) AND auth('auth_read', false, substr($item, 1)))
	{
		$item = substr($item, 1, 10);
		$ini = IniLoad('data/'.$item.'/board.ini');
		$notify = IsInGroup($ini['notification'], $_SESSION['Benutzername']);
		echo '
			<tr>
				<td class="w" style="text-align:left; width:5%;"><a href="'.url('board.php',$item).'"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/board.png" alt="'.$ini['title'].'" /></a></td>
				<td class="w" style="width:60%;padding-left:'.(5+($_BOARDS['b'.$item.'_layer']-1)*20).'px;"><a href="'.url('board.php',$item).'">'.($notify?'<b>':'').$ini['title'].($notify?'</b>':'').'</a></td>
				<td class="w" style="width:35%;">
		';
		if ($notify)
		{
			echo '<a href="'.url('my_profile_notification.php?board='.$item.'&action=off',$item).'">'.$_TEXT['NOTIFICATION_OFF'].'</a>';

		}
		else
		{
			echo '<a href="'.url('my_profile_notification.php?board='.$item.'&action=on',$item).'">'.$_TEXT['NOTIFICATION_ON'].'</a>';

		}
		echo '
				</td>
			</tr>
		';
	}
}
echo '
	<tr>
		<td class="g" colspan="3">
			'.$_TEXT['PROFILE_NOTIFICATION_THREADS'].'
   		</td>
	</tr>
';
foreach(GetBoardsArray() as $item) 
{
	if (($item != '') && (is_numeric($item)))
	{
	   $ini = IniLoad('data/'.$item.'/board.ini');
	   $list = LoadFileList('data/'.$item.'/', '.txt.ini');
	   foreach ($list as $file)
	   {
		$ini2 = IniLoad('data/'.$item.'/'.$file);
		$thread = str_replace('.txt.ini', '', $file);
		if (IsInGroup($ini2['notification'], $_SESSION['Benutzername']))
		{
			echo '
				<tr>
					<td class="w" style="text-align:center;"><a href="'.url('thread.php',$item,$thread).'"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/thema.png" alt="'.$ini['title'].'" /></a></td>
					<td class="w">
						<a href="'.url('thread.php',$item,$thread).'">'.$ini2['title'].'</a>
						<p class="sub">';
						$parents = GetBoardParents($item);
						if (count($parents) > 0) foreach ($parents as $parent)
						{
							echo '<a href="'.$parent[1].'"><img src="styles/'.STYLE.'/images/boardS.png"></a>&nbsp;<a href="'.$parent[1].'">'.$parent[0].'</a> &raquo; ';
						}
						echo '<a href="'.url('board.php', $item).'"><img src="styles/'.STYLE.'/images/boardS.png"></a>&nbsp;<a href="'.url('board.php', $item).'">'.$ini['title'].'</a>
						</p>
					</td>
					<td class="w"><a href="'.url('my_profile_notification.php?board='.$item.'&thread='.$thread.'&action=off',$item).'">'.$_TEXT['NOTIFICATION_OFF'].'</a></td>
				</tr>
			';
		}
	   }
	}
}

echo '
		</table>
		</td></tr>
	</table>
';
require './include/my_profile_bottom.php';
require './include/page_bottom.php';
?>