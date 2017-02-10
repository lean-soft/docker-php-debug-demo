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

if ((!$_FORUM['settings_design_javascript']) OR (!$_FORUM['status']))
{
	echo "document.write(\"---\");";
	Exit;
}
$filename = $history_filename;
$is_last_post = false;
$hist = FileLoad($filename);
$i_begin = count($hist)-$_FORUM['settings_design_javascript_count'];
$i_end = count($hist)-1;
if ($i_begin < 0) $i_begin = 0;
if ($i_end < 0) $i_end = 0;
for ($i=$i_end; $i >= $i_begin; $i--)
{
	$_BOARD = IniLoad('./data/'.$hist[$i][0].'/board.ini');
	if ($_BOARD['hide'])
	{
		if ($i_begin > 0) $i_begin = $i_begin-1;
	}
	else
	{
		$post_filename = './data/'.$hist[$i][0].'/'.$hist[$i][1].'.txt';
		if (file_exists($post_filename))
		{
			$post = file($post_filename);
			if (count($post) > $hist[$i][2])
			{
				$post_link = url('thread.php',$hist[$i][0], $hist[$i][1], $hist[$i][2], false, true);  
				$post_array = explode($TRENNZEICHEN, $post[$hist[$i][2]]);
				$post_text = history_text($post_array[2], $post_array[3], 50);
				$post_date = $post_array[0];
				echo "document.write(\"<li><a href=$post_link title='".ftime($post_date)."'>".$post_text."</a>\");\n";
				$is_last_post = true;
			}
		}
	}
}
if (!$is_last_post) echo "document.write(\"---\");";
?>