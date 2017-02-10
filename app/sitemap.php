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

require_once './include/init.php'; 

echo '<?xml version="1.0" encoding="UTF-8"?>
	<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';
$datei = "./data/forum.dat";
if (!file_exists($datei)) fclose(fopen($datei, "w"));
$data = file($datei);

foreach(GetBoardsArray() as $board)
{
	if (($board != '') && (is_numeric($board)))
	{
		if (auth('auth_read', false, $board))
		{
			$ini = IniLoad('data/'.$board.'/board.ini');
			if (!$ini['hide'])
			{
				$themen = LoadFileList('./data/'.$board.'/', '.txt');
				foreach ($themen as $thema)
				{
					if (is_numeric(str_replace('.txt','',$thema)))
					{
						$beitraege = @file('./data/'.$board.'/'.$thema);
						for ($i = 1; $i <= ceil((count($beitraege))/10); $i++)
						{
							$post_link = format_xml(url('thread.php', $board, str_replace('.txt','',$thema), ($i-1)*10, false, true));
							$post_array = explode($TRENNZEICHEN, $beitraege[count($beitraege)-1]);
							if (is_numeric($post_array[0]))
								$post_lastmod = date("Y-m-d", $post_array[0]);
							else 
								$post_lastmod = "2002-01-01";

							echo '<url><loc>'.$post_link.'</loc><lastmod>'.$post_lastmod.'</lastmod><changefreq>daily</changefreq><priority>0.8</priority></url>';
						}
					}
				}
			}
		}
	}
}
echo '	</urlset>';
?>