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

require_once './include/init.php';

if (!((file_exists('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt')) && (is_numeric($_GET['board']))))
{
	$MSG_ERROR = $_TEXT['ERROR_TOPIC'];
	require './index.php';
	Exit;
}

auth('auth_read');

$_BOARD = IniLoad('./data/'.$_GET['board'].'/board.ini');
$_THEMA = IniLoad('./data/'.$_GET['board'].'/'.$_GET['thema'].'.txt.ini');

$_SUBNAV = array_merge($_SUBNAV, GetBoardParents($_GET['board']));
$_SUBNAV[] = array($_BOARD['title'], url('board.php', $_GET['board']), 'boardS.png');
$_SUBNAV[] = array($_THEMA['title'], url('thread.php', $_GET['board'], $_GET['thema']), 'threadS.png', $_THEMA['tag']);

require './include/page_top.php';


echo '
	<div id="content">
	<table class="main">
	<tr>
		<td class="oben">'.$_TEXT['MOVE'].'</td>
	</tr>
	<tr>
		<td class="g" style="text-align:center;"><center>
			<form action="'.url('do.php?do=move&board='.$_GET['board'].'&thema='.$_GET['thema']).'" method="post">
			<table style="width:60%;">
			<tr>
				<td>'.$_TEXT['MOVE_FROM'].':</td>
				<td><b>'.$_BOARD['title'].'</b></td>
			</tr>
			<tr>
				<td>'.$_TEXT['MOVE_TO'].':</td>
				<td>
					<select name="to_board">'.GetBoardsOptions().'</select>
				</td>
			</tr>
			<tr><td colspan="2" style="text-align:center;"><input type="submit" name="submit" value="'.$_TEXT['MOVE'].'"></td></tr>
			</table>
			</form></center>
		</td>
	</tr></table>
	</div>
';

require './include/page_bottom.php';
?>