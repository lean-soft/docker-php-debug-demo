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

require_once 'include/init.php';

if ((!$_FORUM['settings_design_rss']) OR (!$_FORUM['status']))
{
	echo '<rss version="2.0"></rss>';
	Exit;
}
header("Content-type: text/xml"); 
echo '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'; 
echo '
	<rss version="2.0"> 
	<channel>
	<title>'.format_xml($_FORUM['settings_forum_name']).'</title> 
  	<link>'.$_FORUM['settings_forum_url'].'</link> 
  	<description>'.format_xml($_FORUM['settings_forum_name'].' - '.$_TEXT['STAT_LAST_POSTS']).'</description> 
  	<copyright>'.format_xml($_FORUM['settings_forum_name']).'</copyright> 
  	<webMaster>'.format_xml($_FORUM['settings_forum_email']).'</webMaster> 
  	<language>de</language> 
';
	$filename = 'data/history.txt';
	$hist = FileLoad($filename);
	$i_begin = count($hist)-$_FORUM['settings_design_rss_count'];
	$i_end = count($hist)-1;
	if ($i_begin < 0) $i_begin = 0;
	if ($i_end < 0) $i_end = 0;
	for ($i=$i_end; $i >= $i_begin; $i--)
	{
		$_BOARD = IniLoad('data/'.$hist[$i][0].'/board.ini');
		if ($_BOARD['hide'])
		{
			if ($i_begin > 0)$i_begin = $i_begin-1;
		}
		else
		{
			$post_filename = 'data/'.$hist[$i][0].'/'.$hist[$i][1].'.txt';
			if (file_exists($post_filename))
			{
				$post = file($post_filename);
				if (count($post) > $hist[$i][2])
				{
					$post_array = explode($TRENNZEICHEN, $post[0]);
					$post_title = format_xml($post_array[2]);			
					$post_link = format_xml(url('thread.php', $hist[$i][0], $hist[$i][1], $hist[$i][2], false, true));
					$post_array = explode($TRENNZEICHEN, $post[$hist[$i][2]]);

					$post_array[3] = ereg_replace("src=\"styles", "src=\"".$_FORUM['settings_forum_url']."/styles", $post_array[3]);
	
					if ($post_array[2] <> '')
					{
						$post_title = format_xml($post_array[2]);
					}
					$post_text = '<![CDATA['.$post_array[3].']]>';
					$post_date = date("r", $post_array[0]);
					$post_author = format_xml($post_array[1]);
					PluginHook('rss-item');
					echo '
						<item>
						  <title>'.$post_title.'</title> 
						  <link>'.$post_link.'</link> 
						  <description>'.$post_text.'</description> 
						  <pubDate>'.$post_date.'</pubDate>
						  <author>'.$post_author.'</author> 
						</item>
					';
					
				}
			}
		}
	}
echo '
	</channel>
	</rss>
';
?>