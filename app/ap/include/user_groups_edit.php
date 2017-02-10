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

$_GROUP = IniLoad('../data/user/'.$_GET['group'].'.grp.ini');

if ($_GET['action'] == 'group_delete')
{
	DeleteFromGroup($_GROUP['members'], $_POST['user_delete']);
	IniSave('../data/user/'.$_GET['group'].'.grp.ini', $_GROUP);
}
if ($_GET['action'] == 'group_add')
{
	AddToGroup($_GROUP['members'], $_POST['user_add']);
	IniSave('../data/user/'.$_GET['group'].'.grp.ini', $_GROUP);
}

echo '
	<h1>'.$_TEXT['AP_USER_GROUPS'].' - '.$_TEXT['AP_USER_GROUPS_EDIT'].'</h1>
	<p>'.$_TEXT['AP_USER_GROUPS_GROUP'].': <b>'.$_GET['group'].'</b></p>	
	<p>'.$_TEXT['AP_USER_GROUPS_MEMBERS'].': '.implode(', ', Group2Array($_GROUP['members'])).'</p>	
	<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=group_delete&group='.$_GET['group'].'" method="post">

';
	echo '<select name="user_delete" style="width:150px;" class="txt">';
	foreach (Group2Array($_GROUP['members']) as $item)
		echo '<option value="'.$item.'">'.$item.'</option>';
echo '</select> <input type="submit" name="submit" value="'.$_TEXT['AP_DELETE'].'">
	</form>
	<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=group_add&group='.$_GET['group'].'" method="post">
';
	$users = LoadFileList('../data/user/', '.usr.ini');
	echo '<select name="user_add" style="width:150px;" class="txt">';
	foreach ($users as $item)
	{
		$item = str_replace('.usr.ini', '', $item);
		if (!IsInGroup($_GROUP['members'], $item))
			echo '<option value="'.$item.'">'.$item.'</option>';
	}
	echo '</select> <input type="submit" name="submit" value="'.$_TEXT['AP_ADD'].'"> ';

echo '
	</form>
';
?>