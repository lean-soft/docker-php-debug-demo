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


echo '<h1>'.$_TEXT['AP_USER_ADMIN'].'</h1>';	

if ($_GET['action'] == "new")
{
	$error = user_register($_POST['user'], $_POST['email']);
	if ($error == '')
	{
		echo '<div class="confirm">'.MultiReplace($_TEXT['AP_USER_ADMIN_NEW_OK'], $_POST['user']).'</div>';
	}			
	else
	{	
		echo '<div class="error">'.MultiReplace($_TEXT['AP_USER_ADMIN_NEW_ERROR'], $_POST['user']).'<br /><br />'.$_TEXT[$error].'</div>';
	}
}


echo '
		<fieldset>
		<legend>'.$_TEXT['AP_USER_ADMIN_NEW'].' </legend>
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=new" method="post">
		<table style="width:100%;">
		<tr>
			<td style="width:20%;text-align:right;">'.$_TEXT['LOGIN_USERNAME'].':</td>
			<td style="width:80%;"><input type="text" name="user" value="'.format_input($_POST['user']).'" style="width:250px;" /></td>
		</tr>
		<tr>
			<td style="width:20%;text-align:right;">'.$_TEXT['EMAIL'].':</td>
			<td style="width:80%;"><input type="text" name="email" value="'.format_input($_POST['email']).'" style="width:250px;" /></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center;"><input type="submit" value="'.$_TEXT['AP_USER_ADMIN_NEW'].'" /></td>
		</tr>
		</table>
		</form>
		</fieldset>
';
?>