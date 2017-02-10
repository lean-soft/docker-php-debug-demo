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

$_SUBNAV[] = array($_TEXT['NAV_IMPRESSUM'], url('imprint.php'));

require './include/page_top.php';

echo '
	<div id="content">
		<table class="main">
		<tr><td class="oben">'.$_TEXT['NAV_IMPRESSUM'].'</td></tr>
		<tr><td class="g">
';
if (file_exists('./data/impressum.txt'))
{
	$file = file('./data/impressum.txt');
	foreach($file as $line)
	{
		if ($line <> $file[0]) echo "<br>";
		echo do_ubb($line);
	}
}
echo '


<br><br><br><br><li>Sponsoren</li> zinsgünstige Kredite: <a
href="http://www.finanz-forum.eu/" target="_blank">Link zur Hompage</a> Schülerjobs in Berlin: <a
href="http://www.stellenangebote-forum.de/about84094.html" target="_blank">hier geht´s zum Forum für Schülerjobs in Berlin</a> Jobs für Werkschutzfachkräfte:<a
href="http://www.0049-jobs.de/stellen-Werkschutzfachkraft.html" target="_blank">http://www.0049-jobs.de/stellen-Werkschutzfachkraft.html</a> 

		</td></tr>
		</table>
	</div>
';

require './include/page_bottom.php'; 
?>