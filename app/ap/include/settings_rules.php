<?PHP
$filename = '../data/rules.txt';

if ($_GET['action']=='save')
{
	if ($_POST['submit'] == html_entity_decode($_TEXT['AP_DELETE']))
	{
		@unlink($filename);
	}
	else
	{
		$fp = fopen($filename, "w");
		fputs($fp, stripslashes($_POST['text']));
		fclose($fp);
	}
}

echo '
	<h1>'.$_TEXT['AP_RULES'].'</h1>
	<form action="?nav=settings&page=rules&action=save" method="post">
	<p>
		'.$_TEXT['AP_RULES_TEXT'].'
		<br /><br /><textarea name="text" style="width:100%;" rows=20>'; if (file_exists($filename)) readfile($filename); echo '</textarea>
		<br /><br /><center><input type="submit" name="submit" value="'.$_TEXT['AP_SAVE'].'"> <input type="submit" name="submit" value="'.$_TEXT['AP_DELETE'].'"> <input type="reset"  class=btn name="reset" value="'.$_TEXT['AP_CANCEL'].'"></center>
	</p>
	</form>
';
?>