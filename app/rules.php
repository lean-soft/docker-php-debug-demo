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

$_SUBNAV[] = array($_TEXT['NAV_RULES'], url('rules.php'));

require './include/page_top.php';

echo '
	<div id="content">
		<table class="main">
		<tr><td class="oben">'.$_TEXT['NAV_RULES'].'</td></tr>
		<tr><td class="g">
';
if (file_exists('./data/rules.txt'))
{
	$file = file('./data/rules.txt');
	foreach($file as $line)
	{
		if ($line <> $file[0]) echo "<br>";
		echo do_ubb($line);
	}
}
echo '
		</td></tr>
		</table>
	</div>
';

require './include/page_bottom.php'; 
?>