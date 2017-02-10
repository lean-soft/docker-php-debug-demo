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

if ($_GET['action'] == 'group_delete')
{
	DeleteFromGroup($_FORUM['settings_blacklist'], $_POST['item_delete']);
	IniSave('../data/forum.ini', $_FORUM, true);
}
if ($_GET['action'] == 'group_add')
{
	AddToGroup($_FORUM['settings_blacklist'], $_POST['item_add']);
	IniSave('../data/forum.ini', $_FORUM);
}


echo '
	<h1>'.$_TEXT['AP_BLACKLIST'].'</h1>
	
	<table class="auto">
		<tr><td>'.$_TEXT['AP_BLACKLIST_TEXT'].'
			<br /><fieldset><legend>'.$_TEXT['AP_BLACKLIST'].'</legend>'.implode(', ', Group2Array($_FORUM['settings_blacklist'])).'</fieldset>
			<br /><form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=group_delete" method="post"><select style="width:180px;" name="item_delete"><option>---</option>
';
			foreach (Group2Array($_FORUM['settings_blacklist']) as $item) echo '<option>'.$item.'</option>';
echo '
			</select> <input type="submit" name="submit" value="'.$_TEXT['AP_DELETE'].'" /></form>&nbsp;<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=group_add" method="post"><input type="text" name="item_add" style="width:180px;" /> <input type="submit" name="submit" value="'.$_TEXT['AP_ADD'].'" /></form>
		</td></tr>

	</table>
	</div>
';
?>