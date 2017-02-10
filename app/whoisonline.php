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

require 'include/init.php';

if (!auth_guestuser('settings_design_showstat', true)) 
{
	require 'index.php';
	Exit;
}

$_SUBNAV[] = array($_TEXT['NAV_WHOISONLINE'], url('whoisonline.php'));

require 'include/page_top.php';

echo '
	<div id="content">
		<table class="main">
			<tr><td colspan="2" class="oben">'.$_TEXT['NAV_WHOISONLINE'].'</td></tr>
';

$class = 'g';

function selfsort($a, $b) {
   return ($a[0] < $b[0]) ? 1 : -1;
}

usort($count_data, 'selfsort');

foreach ($count_data as $item)
{
	echo '
			<tr>
				<td class="'.$class.'" width="140" style="width:140px;">'.($item[2]<>''?user($item[2]):$_TEXT['STAT_GUEST']).'<p class="sub">'.(IsAdmin()&&$item[1]<>''?$item[1].'<br />':'').$item[5].'</p></td>
				<td class="'.$class.'" width="*" style="width:auto;">'.$item[7].'</td>
			</tr>
	';
	$class = ($class=='w'?'g':'w');
}

echo '
		</table>
	</div>
';

if (IsAdmin())
{

echo '
	<div id="content">
		<table class="main">
			<tr><td colspan="3" class="oben">'.$_TEXT['STAT_BOTS'].'</td></tr>
';

$_COUNT_BOTS = IniLoad('./data/count_bots.ini');
$class = 'g';

function bot($bot)
{
	GLOBAL $_COUNT_BOTS, $class;
	if ($_COUNT_BOTS[$bot[1].'_count'] <> '')
	{
		echo '
			<tr>
				<td class="'.$class.'" style="width:140px;">'.$bot[2].' ('.$_COUNT_BOTS[$bot[1].'_count'].'x)</td>
				<td class="'.$class.'" style="width:100px;">'.ftime($_COUNT_BOTS[$bot[1].'_lastonline']).'</td>
				<td class="'.$class.'" style="width:auto;">'.$_COUNT_BOTS[$bot[1].'_breadcrumbs'].'</td>
			</tr>
		';
		$class = ($class=='w'?'g':'w');
	}
}
foreach ($_BOTLIST as $bot) bot($bot);

echo '
		</table>
	</div>
';
}

require 'include/page_bottom.php';
?>