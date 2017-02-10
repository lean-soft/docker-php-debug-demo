<?PHP
$filename = '../data/infotext.txt';

if ($_GET['action']=='delete')
{
	@unlink($filename);
}
if ($_GET['action']=='save')
{
	$fp = fopen($filename, "w");
	fputs($fp, stripslashes($_POST['text']));
	fclose($fp);
}

echo '
	<h1>'.$_TEXT['AP_INFOTEXT'].'</h1>
	<form action="?nav=settings&page=infotext&action=save" method="post">
	<p>
		'.$_TEXT['AP_INFOTEXT_TEXT'].'
		<br /><br /><textarea name="text" style="width:100%;" rows=20>'; if (file_exists($filename)) readfile($filename); echo '</textarea>
		<br /><br /><center><input type="submit" name="submit" value="'.$_TEXT['AP_SAVE'].'"></form>&nbsp;<form action="?nav=settings&page=infotext&action=delete" method="post"><input type="submit" name="submit" value="'.$_TEXT['AP_DELETE'].'"></form></center>
	</p>
	</form>
';
?>