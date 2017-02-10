<?PHP
$_BOARDS = IniLoad('../data/boards.ini');
if (!is_numeric($_GET['id'])) 
{
	require 'include/settings_boards.php';
}
else if ($_GET['action'] == 'save')
{
	$_BOARDS['c'.$_GET['id']] = $_POST['title'];
	IniSave('../data/boards.ini', $_BOARDS);
	require 'include/settings_boards.php';
}
else
{
	echo '
	<h1>'.$_TEXT['AP_BOARDS'].'</h1>
	<fieldset>
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.$_GET['id'].'&action=save" method="post">
		<table class="auto">
		<tr>
		<td style="padding:0px;">
			<table class="auto">
	 		<tr>
				<td>'.$_TEXT['TITLE'].'</td>
				<td><input type="text" name="title" size="30" maxlength="50" value="'.format_input($_BOARDS['c'.$_GET['id']]).'"></td>
			</tr>
			</table>
		</td>
		</tr>
		<tr>
			<td style="text-align:center;"><INPUT TYPE="SUBMIT" name="submit" VALUE="'.$_TEXT['AP_SAVE'].'"></td>
		</tr>
		</table>
		</form>
	</fieldset>
	';
}
?>