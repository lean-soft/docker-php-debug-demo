<?PHP
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

$_SUBNAV[] = array($_TEXT['NAV_FAQ'], url('faq.php'));

require './include/page_top.php';

echo '
	<div id="content">
		<table class="main">
		<tr><td class="oben">'.$_TEXT['NAV_FAQ'].'</td></tr>
		<tr><td class="g">
			<table class="main" style="margin-left:10px; width:300px;" align="right">
				<tr><td class="oben" colspan="2">'.$_TEXT['FAQ_SYMBOLS'].'</td></tr>
				<tr><td class="w" style="padding:3px;text-align:right;"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/admin.gif" alt=""></td><td class="w" style="padding:3px;">'.$_TEXT['ADMINISTRATOR'].'</td></tr>
				<tr><td class="w" style="padding:3px;text-align:right;"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/mod.gif" alt=""></td><td class="w" style="padding:3px;">'.$_TEXT['MODERATOR'].'</td></tr>
				<tr><td class="w" style="padding:3px;text-align:right;"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/1.png" alt=""></td><td class="w" style="padding:3px;">'.$_TEXT['FAQ_USER_WITH'].' 0-'.(($_FORUM['settings_user_ranking2'])-1).' '.$_TEXT['POINTS'].'</td></tr>
				<tr><td class="w" style="padding:3px;text-align:right;"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/2.png" alt=""></td><td class="w" style="padding:3px;">'.$_TEXT['FAQ_USER_WITH'].' '.($_FORUM['settings_user_ranking2'])."-".(($_FORUM['settings_user_ranking3'])-1).' '.$_TEXT['POINTS'].'</td></tr>
				<tr><td class="w" style="padding:3px;text-align:right;"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/3.png" alt=""></td><td class="w" style="padding:3px;">'.$_TEXT['FAQ_USER_WITH'].' '.($_FORUM['settings_user_ranking3'])."-".(($_FORUM['settings_user_ranking4'])-1).' '.$_TEXT['POINTS'].'</td></tr>
				<tr><td class="w" style="padding:3px;text-align:right;"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/4.png" alt=""></td><td class="w" style="padding:3px;">'.$_TEXT['FAQ_USER_WITH'].' '.($_FORUM['settings_user_ranking4'])."-".(($_FORUM['settings_user_ranking5'])-1).' '.$_TEXT['POINTS'].'</td></tr>
				<tr><td class="w" style="padding:3px;text-align:right;"><img src="./styles/'.$_FORUM['settings_design_style'].'/images/5.png" alt=""></td><td class="w" style="padding:3px;">'.$_TEXT['FAQ_USER_WITH_MORE'].' '.$_FORUM['settings_user_ranking5'].' '.$_TEXT['POINTS'].'</td></tr>
			</table>
';
if (file_exists('./data/faq.txt'))
{
	$file = file('./data/faq.txt');
	foreach($file as $line)
	{
		if ($line <> $file[0]) echo "<br>";
		echo do_ubb($line);
	}
}
else
	echo '<b>'.$_TEXT['FAQ_REGISTER'].'</b><br />'.$_TEXT['FAQ_REGISTER_ANSWER'].'<br /><br /><b>'.$_TEXT['FAQ_PROFILE'].'</b><br />'.$_TEXT['FAQ_PROFILE_ANSWER'].'<br /><br />[...]<br /><br /><a href="http://www.frank-karau.de" target="_blank">'.$_TEXT['FAQ_LINK'].'</a>';

echo '

		</td></tr>
		</table>
	</div>
';

require './include/page_bottom.php'; 
?>