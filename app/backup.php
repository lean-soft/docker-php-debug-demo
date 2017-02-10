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

if (IsAdmin())
{
	@unlink('data/backup.zip');
	$list = array();
	if ($handle = opendir('data/')) 
	{
		while (false !== ($file = readdir($handle))) 
		{
			if (!in_array($file, array('.', '..')))
			{
				if (!defined('CONFIG_BACKUP_LIGHT') OR !in_array($file, array('upload')))
					$list[] = 'data/'.$file;
			}
		}
		closedir($handle);
	}

 
	if ((!is_writeable('backup/')) OR (!is_dir('backup/')))
	{
		$filename = 'data/backup.zip';
		include 'include/pclzip/pclzip.lib.php';
	  	$archive = new PclZip($filename);
		if ($archive->create(implode(',', $list)) == 0) 
		{
			die($_TEXT['AP_BACKUP_CREATE_ERROR']." ".$archive->errorInfo(true));
			Exit;
		}
		@sleep(3);
		header("Cache-Control: public, must-revalidate");
		header("Pragma: hack");
		header("Content-Type: application/zip");
		header("Content-Length: " .(string)(filesize($filename)) );
		header('Content-Disposition: attachment; filename="backup.zip"');
		header("Content-Transfer-Encoding: binary\n");
		$fp = fopen($filename, 'rb');
		$buffer = fread($fp, filesize($filename));
		fclose($fp);
		print $buffer;
	}
	else
	{
		$filename = 'backup/'.date('Ymd_Hi_').md5(time()).'.zip';
		@unlink($filename);
		include 'include/pclzip/pclzip.lib.php';
	  	$archive = new PclZip($filename);
		if ($archive->create(implode(',', $list)) == 0) 
		{
			die($_TEXT['AP_BACKUP_CREATE_ERROR']." ".$archive->errorInfo(true));
			Exit;
		}
		header('Location: '.$_FORUM['settings_forum_url'].'/'.$filename);
	}
}
?>