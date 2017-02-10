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

if ($_GET['type'] == 'avatar') 
{
	$ini = IniLoad('data/upload/av_'.$_GET['id'].'.ini');
	$ini['url'] = 'data/upload/'.$ini['url'];
	if ((!file_exists($ini['url'])) OR ($ini['url'] == 'data/upload/') OR (!IsUser($_GET['id'])))
	{
		$ini['type'] = 'image/gif';
		$ini['url'] = 'include/blank.gif';
		$ini['filename'] = 'blank.gif';
	}
	header("Cache-Control: public, must-revalidate");
	header("Pragma: hack");
	header("Content-Type: ".$ini['type']);
	header("Content-Length: " .(string)(filesize($ini['url'])) );
	header('Content-Disposition: attachment; filename="'.$ini['filename'].'"');
	header("Content-Transfer-Encoding: binary\n");
	$fp = fopen($ini['url'], 'rb');
	$buffer = fread($fp, filesize($ini['url']));
	fclose ($fp);
	print $buffer;
}

if (($_GET['type'] == 'file') OR ($_GET['type'] == 'image') OR ($_GET['type'] == 'image_full') OR ($_GET['type'] == 'thumbnail'))
{
	if (file_exists('data/upload/'.$_GET['id'].'.ini'))
	{
		$ini = IniLoad('data/upload/'.$_GET['id'].'.ini');
		if ($ini['board'] <> '')
		{
			$url = '';
			if ($_GET['type'] == 'thumbnail')
			{
				if ($ini['thumbnail'] <> '')
				{
					$url = 'data/upload/'.$ini['thumbnail'];
					$ini['filename'] = 'thumbnail_'.$ini['filename'];
				}
			}
			else if ($_GET['type'] == 'image')
			{
				if (auth('auth_download', false, $ini['board']))
				{
					if ($ini['image'] != '')
					{
						$url = 'data/upload/'.$ini['image'];
						$ini['filename'] = 'preview_'.$ini['filename'];
					}
				}
				else
				{
					$url = 'images/image_no_permission.png';
					$ini['type'] = 'images/png';
					$ini['filename'] = 'image_no_permission.png';
				}
			}
			else if ($_GET['type'] == 'image_full')
			{
				if (auth('auth_download', false, $ini['board']))
				{
					$url = 'data/upload/'.$ini['url'];
				}
				else
				{
					$url = 'images/image_no_permission.png';
					$ini['type'] = 'images/png';
					$ini['filename'] = 'image_no_permission.png';
				}
			}
			if ($url == '')
			{
				auth('auth_download', true, $ini['board']);
				$ini['count']++;
				IniSave('data/upload/'.$_GET['id'].'.ini', $ini);
				$url = 'data/upload/'.$ini['url'];
			}
			header("Cache-Control: public, must-revalidate");
			header("Pragma: hack");
			header("Content-Type: ".$ini['type']);
			header("Content-Length: " .(string)(filesize($url)) );
			header('Content-Disposition: attachment; filename="'.$ini['filename'].'"');
			header("Content-Transfer-Encoding: binary\n");
			$fp = fopen($url, 'rb');
			$buffer = fread($fp, filesize($url));
			fclose ($fp);
			print $buffer;
			Exit;
		}
	}
	echo $_TEXT['ERROR_NOT_EXEC'].' (#110)';
}
?>