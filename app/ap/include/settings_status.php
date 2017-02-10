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

if ($_GET['action'] == 'save')
{
	$_FORUM['status'] = $_POST['status'];
	IniSave('../data/forum.ini', $_FORUM);
	$fp = fopen('../data/offline.txt', "w");
	fputs($fp, stripslashes($_POST['text']));
	fclose($fp);
}

echo '
	<h1>'.$_TEXT['AP_STATUS'].'</h1>
	<form action="?nav=settings&page=status&action=save" method="post">
	<p>
			'.$_TEXT['AP_STATUS_STATUS'].': <select name="status">
				<option value="online">'.$_TEXT['AP_STATUS_ON'].'</option>
				<option value="offline"'.($_FORUM['status']=='offline'?' selected="selected"':'').'>'.$_TEXT['AP_STATUS_OFF'].'</option>
			</select>
			<br /><br />'.$_TEXT['AP_STATUS_TEXT'].'
			<br /><textarea name="text" style="width:100%;" rows=20>'; if (file_exists("../data/offline.txt")) readfile("../data/offline.txt"); echo '</textarea>
			<br /><br /><center><input type="submit" name="submit" value="'.$_TEXT['AP_SAVE'].'"> <input type="reset"  class=btn name="reset" value="'.$_TEXT['AP_CANCEL'].'"></center>
	</p>
	</form>
';
?>