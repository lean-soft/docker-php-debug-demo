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

$ITEMS = array(
		'top',
		'bottom',
		'boardafter1',
		'boardevery5',
		'threadafter1',
		'threadevery3'
	      );

echo '
	<h1>'.$_TEXT['AP_ADVERTISING'].'</h1>
';

if ($_GET['action']=='save')
{
	foreach ($ITEMS as $item)
	{
		$filename = DIR.'data/advertising_'.$item.'.txt';	
		$text = trim(stripslashes($_POST['advertising_'.$item]));
		if ($text == '')
		{
			@unlink($filename);
		}
		else
		{
			$fp = fopen($filename, "w");
			fputs($fp, $text);
			fclose($fp);
		}
	}
	echo '<div class="confirm">'.$_TEXT['AP_SAVED'].'</div>';
}
echo '
	<form action="?nav=advanced&page=advertising&action=save" method="post">
	<p>'.$_TEXT['AP_ADVERTISING_TEXT'].'</p>
';
foreach ($ITEMS as $item)
{
	$filename = DIR.'data/advertising_'.$item.'.txt';	
	echo '
		<fieldset>
			<legend>'.$_TEXT['AP_ADVERTISING_'.strtoupper($item)].'</legend>
			<textarea name="advertising_'.$item.'" style="width:100%;" rows="4">'; if (file_exists($filename)) readfile($filename); echo '</textarea>
		</fieldset>
	';
}
echo '
	<p style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['AP_SAVE'].'"> <input type="reset"  class=btn name="reset" value="'.$_TEXT['AP_CANCEL'].'"></p>
	</form>
';
?>