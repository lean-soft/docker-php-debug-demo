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

echo '
	<h1>'.$_TEXT['AP_USER_NEWSLETTERLIST'].'</h1>
	<p>
';
foreach(GetFileList('data/user/*.usr.ini') as $file)
{
	$udat = IniLoad($file);
	if ($udat['newsletter'])
	{
		echo $udat['email']."; ";
	}
}
echo '
	</p>
';
?>