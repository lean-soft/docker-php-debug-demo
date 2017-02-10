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

$filename = '../data/backup.zip';


echo '
	<h1>'.$_TEXT['AP_BACKUP'].'</h1>
	<p>'.$_TEXT['AP_BACKUP_TEXT'].'</p>
	<p><a href="../backup.php?'.session_name().'='.session_id().'">'.$_TEXT['AP_BACKUP_CREATE'].'</a></p>
';
?>