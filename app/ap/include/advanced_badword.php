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

$filename = '../data/badword.ini';

$_LIST = IniLoad($filename);

if (($_GET['action']=='change') && ($_POST['word'] <> '') && ($_POST['replace'] <> ''))
{
	$_LIST[$_POST['word']] = $_POST['replace'];
	IniSave($filename, $_LIST);
	$_LIST = IniLoad($filename);
}

if ($_GET['delkey'] <> '')
{
	unset($_LIST[$_GET['delkey']]);
	IniSave($filename, $_LIST, true);
	$_LIST = IniLoad($filename);
}

echo '
	<h1>'.$_TEXT['AP_BADWORD'].'</h1>

	<table class="auto"><tr><td style="padding:0px;width:50%;vertical-align:top;">
	<fieldset>
		<legend>'.$_TEXT['AP_BADWORD'].'</legend>
		<table>
';
		foreach (array_keys($_LIST) as $key)
		{
			echo '<tr><td>'.$key.'</td><td>'.$_LIST[$key].'</td><td class="w">[<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&key='.$key.'">'.$_TEXT['AP_EDIT'].'</a>] [<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&delkey='.$key.'">'.$_TEXT['AP_DELETE'].'</a>]</tr>';
		}
echo '
		</table>
	</fieldset>
	</td><td style="padding:0px;width:50%;vertical-align:top;">
	<fieldset>
		<legend>'.$_TEXT['AP_ADD'].' / '.$_TEXT['AP_EDIT'].'</legend>
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=change" method="post">
		<table class="auto">
			<tr><td><label for="word">'.$_TEXT['AP_BADWORD_WORD'].':</label></td><td><input type="text" name="word" id="word" value="'.format_input($_GET['key']).'" /></td></tr>
			<tr><td><label for="replace">'.$_TEXT['AP_BADWORD_REPLACE'].':</label></td><td><input type="text" name="replace" id="replace" value="'.format_input($_LIST[$_GET['key']]<>''?$_LIST[$_GET['key']]:'***').'" /></td></tr>
			<tr><td colspan="2" style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['AP_SAVE'].'"> <input type="reset" name="reset" value="'.$_TEXT['AP_CANCEL'].'"></td></tr>
		</table>
		</form>
	</fieldset>
	</td></tr></table>

';
?>