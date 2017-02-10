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

echo '
<html><head>
<title>'.$_TEXT['NAV_RULES'].' | '.$_FORUM['settings_forum_name'].'</title>
<link rel=stylesheet type="text/css" href="styles/'.$_FORUM['settings_design_style'].'/text.css">
</head><body style="margin:0px;">
<div id="forum_page" style="border:0px;background:transparent;width:100%;padding:0px;margin:0px;"><table>
<tr><td class="w">
<p><b>'.$_FORUM['settings_forum_name'].' - '.$_TEXT['NAV_RULES'].'</b><hr /></p>
<p style="text-align:left;">		
';
if (file_exists('./data/rules.txt'))
{
	$file = file('./data/rules.txt');
	foreach($file as $line)
	{
		if ($line <> $file[0]) echo "<br />";
		echo do_ubb($line);
	}
}
echo '
</p><hr />
</td></tr></body></html>
';
?>