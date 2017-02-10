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

if ($_GET['action'] == 'logdelete')
{
	LogDelete();
}

echo '
	<h1>'.$_TEXT['AP_INFO'].'</h1>
	<fieldset>
		<legend>'.$_TEXT['AP_INFO'].'</legend>
		<table class="auto">
		<tr><td style="width:40%;text-align:right;">'.$_TEXT['AP_INFO_FORUM'].':</td><td style="width:60%;">'.$_FORUM['version'].'</td></tr>
		<tr><td style="width:40%;text-align:right;">'.$_TEXT['AP_INFO_PHP'].':</td><td>'.phpversion().'</td></tr>
		<tr><td style="width:40%;text-align:right;">'.$_TEXT['AP_INFO_SAFEMOD'].':</td><td><img src="images/ap_'.(ini_get("safe_mode")=='1'?'yes':'no').'.gif" border="0"></td></tr>
		<tr><td style="width:40%;text-align:right;">'.$_TEXT['AP_INFO_IMAGES'].':</td><td><img src="images/ap_'.((extension_loaded('gd') OR extension_loaded('gd2'))?'yes':'no').'.gif" border="0"></td></tr>
		</table>
	</fieldset>


	<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=logdelete" method="post">
	<fieldset>
		<legend>'.$_TEXT['AP_INFO_LOG'].'</legend>
		<textarea style="width:100%;height:200px;">'.@file_get_contents(DIR.'data/admin.log').'</textarea>
		<br /><input type="submit" name="submit" value="'.$_TEXT['AP_DELETE'].'">
	</fieldset>
	</form>
';
?>