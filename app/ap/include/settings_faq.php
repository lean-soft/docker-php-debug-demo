<?PHP
$filename = '../data/faq.txt';

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
	<h1>'.$_TEXT['AP_FAQ'].'</h1>
	<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=save" method="post">
	<p>
		'.$_TEXT['AP_FAQ_TEXT'].'
		<br /><br /><textarea name="text" style="width:100%;" rows=20>'; if (file_exists($filename)) readfile($filename); echo '</textarea>
		<br /><br /><center><input type="submit" name="submit" value="'.$_TEXT['AP_SAVE'].'"> <input type="submit" name="submit" value="'.$_TEXT['AP_DELETE'].'"> <input type="reset"  class=btn name="reset" value="'.$_TEXT['AP_CANCEL'].'"></center>
	</p>
	</form>
';
?>