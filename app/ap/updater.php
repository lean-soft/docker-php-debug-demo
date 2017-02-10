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

define('DIR', '../');
define('SICHERHEIT_FORUM', true);
require_once '../include/functions.php';

ini_set('register_globals', '0');
session_name('sid');
session_start();

if (IsAdmin() && ($_GET['order'] != ''))
{
	if ($_GET['page'] == 'boards')
	{
		$_BOARDS = IniLoad('../data/boards.ini');
		$_BOARDS['order'] = str_replace('|', $TRENNZEICHEN, $_GET['order']);
		IniSave('../data/boards.ini', $_BOARDS);
		RepairBoardsIni();
	}
	if ($_GET['page'] == 'navigation')
	{
		$ini = IniLoad(DIR.'data/navigation.ini');
		$ini['order'] = str_replace('|', $TRENNZEICHEN, $_GET['order']);
		IniSave(DIR.'data/navigation.ini', $ini);
	}
}

?>