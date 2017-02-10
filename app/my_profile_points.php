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

require './include/init.php';

AuthUser();

$_SUBNAV[] = array($_TEXT['LOGIN_PROFILE'], url('my_profile.php'));
$_SUBNAV[] = array($_TEXT['PROFILE_POINTS'], url('my_profile_points.php'));

require './include/page_top.php';
require './include/my_profile_top.php';

$udat = IniLoad('./data/user/'.$_SESSION['Benutzername'].'.usr.ini');

// Berechnungsfehler beheben
if (($udat['count_answeres'] < 0 ) OR (!is_numeric($udat['count_answeres']))) $udat['count_answeres'] = 0;
if (($udat['count_answeres2'] < 0 ) OR (!is_numeric($udat['count_answeres2']))) $udat['count_answeres2'] = 0;
if (($udat['count_locked'] < 0 ) OR (!is_numeric($udat['count_locked']))) $udat['count_locked'] = 0;
if (($udat['count_topics'] < 0 ) OR (!is_numeric($udat['count_topics']))) $udat['count_topics'] = 0;
IniSave('./data/user/'.$_SESSION['Benutzername'].'.usr.ini', $udat);

echo '
	<table class="main">
	<tr><td class="oben">'.$_TEXT['PROFILE_POINTS'].'</td></tr>
	<tr><td class="w">
		<table style="width:100%;">
		<tr><td style="vertical-align:top;"><b>&raquo; '.$_TEXT['PROFILE_POINTS_TOPICS'].'</b><br>'.$_TEXT['PROFILE_POINTS_TOPICS_DESCR'].'</td><td style="text-align:right;"><b>'.fnum($udat['count_topics']).'</b></td><td><nobr>x 5 '.$_TEXT['POINTS'].'</td><td>=</td><td style="text-align:right;"><nobr>'.fnum($udat['count_topics']*5).' '.$_TEXT['POINTS'].'</td></tr>
		<tr><td style="vertical-align:top;"><b>&raquo; '.$_TEXT['PROFILE_POINTS_ANSWERES'].'</b><br>'.$_TEXT['PROFILE_POINTS_ANSWERES_DESCR'].'</td><td style="text-align:right;"><b>'.fnum($udat['count_answeres']).'</b></td><td><nobr>x 2 '.$_TEXT['POINTS'].'</td><td>=</td><td style="text-align:right;"><nobr>'.fnum($udat['count_answeres']*2).' '.$_TEXT['POINTS'].'</td></tr>
		<tr><td style="vertical-align:top;"><b>&raquo; '.$_TEXT['PROFILE_POINTS_ANSWERES2'].'</b><br>'.$_TEXT['PROFILE_POINTS_ANSWERES2_DESCR'].'</td><td style="text-align:right;"><b>'.fnum($udat['count_answeres2']).'</b></td><td><nobr>x 1 '.$_TEXT['POINTS'].'</td><td>=</td><td style="text-align:right;"><nobr>'.fnum($udat['count_answeres2']).' '.$_TEXT['POINTS'].'</td></tr>
		<tr><td style="vertical-align:top;"><b>&raquo; '.$_TEXT['PROFILE_POINTS_LOCKED'].'</b><br>'.$_TEXT['PROFILE_POINTS_LOCKED_DESCR'].'</td><td style="text-align:right;"><b>'.fnum($udat['count_locked']).'</b></td><td><nobr>x -2 '.$_TEXT['POINTS'].'</td><td>=</td><td style="text-align:right;"><nobr>-'.fnum($udat['count_locked']*2).' '.$_TEXT['POINTS'].'</td></tr>
		<tr><td class="g" colspan="4"><b>&raquo; '.$_TEXT['PROFILE_POINTS_SUM'].'</b></td><td class="g" style="text-align:right;"><nobr><b>'.fnum(user_points($_SESSION['Benutzername'])).' '.$_TEXT['POINTS'].'</b></td></tr>
		</table>
    	</td></tr>
	</table>
';
require './include/my_profile_bottom.php';
require './include/page_bottom.php';
?>