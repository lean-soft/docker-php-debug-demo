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


echo '
	<h1>'.$_TEXT['AP_PLUGINS'].'</h1>
';
@chmod(DIR.'plugins', 0777);
if (!is_writeable(DIR.'plugins'))
{
	echo '<div class="error">'.$_TEXT['AP_PLUGINS_CHMOD'].'</div>';
}
else 
{
	echo '
		<table class="box"><tr><td style="padding:7px;">
			<form enctype="multipart/form-data" action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'" method="post">
				<b>'.$_TEXT['AP_PLUGINS_IMPORT'].':</b>
				<input name="plugin_upload" type="file">
				<input type="submit" value="'.$_TEXT['UPLOAD'].'">
			</form>
		</td></tr></table>
	';
}

if ($_FILES['plugin_upload']['size'] > 0)
{
	$ERROR = true;
	$pluginname = '';
	include_once(DIR.'include/pclzip/pclzip.lib.php');
	$zip = new PclZip($_FILES['plugin_upload']['tmp_name']);
	$list = $zip->listContent(); 
	if (count($list) > 0)
	{
		$ERROR = false;
		$has_class_file = false;
		$pluginname = substr($list[0][filename], 0, strpos($list[0]['filename'], '/'));
		if ($pluginname != '') foreach($list as $file)
		{
			if ($pluginname == substr($file[filename], 0, strpos($file['filename'], '/')))
			{
				if (!$has_class_file) $has_class_file = ($file[filename] == $pluginname.'/class.'.$pluginname.'.php');
			}
			else
			{
				$ERROR = true;
			}
		}
		if (!$has_class_file) $ERROR = true;
	}
	if ($ERROR)
	{
		echo '
			<div class="error">'.MultiReplace($_TEXT['AP_PLUGINS_IMPORT_ERROR'], ($pluginname!=''?$pluginname:$_FILES['plugin_upload']['name'])).'</div>
		';	
	}
	else
	{
		if ($zip->extract(PCLZIP_OPT_PATH, '../plugins') == 0) 
		{
			echo  '<div class="error">'.$archive->errorInfo(true).'</div>';
		}
	}
}


if ($_GET['item'] <> '')
{
	if ($_GET['action'] == 'delete')
	{
		echo '
			<div class="notice">
				'.MultiReplace($_TEXT['AP_PLUGINS_DELETE'], strip_tags($_GET['item'])).'
				<br />
				<br /><center><a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&item='.$_GET['item'].'&action=delete_ok">'.$_TEXT['CONFIRM_YES'].'</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'">'.$_TEXT['CONFIRM_NO'].'</a></center>
			</div>
		';
	}
	if ($_GET['action'] == 'delete_ok')
	{
		DeleteDir(DIR.'plugins/'.$_GET['item'].'/');
	}
}

if (is_dir(DIR.'plugins/'))
{
	$itemlist = array();
	$verz=opendir(DIR.'plugins/');
	rewinddir($verz);
	while ($item = readdir($verz)) 
	{
		if (($item!=".") && ($item!="..") && is_dir(DIR.'plugins/'.$item))
   		{
			$itemlist[] = $item;
		}
	}
	closedir($verz);
	sort($itemlist);
	foreach($itemlist as $item)
	{
			if (file_exists(DIR.'plugins/'.$item.'/class.'.$item.'.php'))
			{
				$editable = (is_writeable(DIR.'plugins/'.$item.'/'));
				require_once DIR.'plugins/'.$item.'/class.'.$item.'.php';
				$inst = $PLUGINS[$item];
				if (is_null($inst))
				{
					eval('$inst = new '.$item.';');
				}
				if ($_GET['item'] == $item)
				{
					if (($_GET['action'] == 'activate') && !IsInGroup($_FORUM['plugins'], $item))
					{
						AddToGroup($_FORUM['plugins'], $item);
						IniSave(DIR.'data/forum.ini', $_FORUM);
						$inst->Activate();
					}
					if (($_GET['action'] == 'deactivate') && IsInGroup($_FORUM['plugins'], $item))
					{
						DeleteFromGroup($_FORUM['plugins'], $item);
						IniSave(DIR.'data/forum.ini', $_FORUM);
						$inst->Deactivate();
					}
				}
				$info = $inst->GetInfo();
				$links = array();
				$links[] = (IsInGroup($_FORUM['plugins'], $item)?'<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&item='.$item.'&action=deactivate">'.$_TEXT['AP_PLUGINS_DEACTIVATE'].'</a>':'<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&item='.$item.'&action=activate">'.$_TEXT['AP_PLUGINS_ACTIVATE'].'</a>');
				$links[] = ($editable?'<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&item='.$item.'&action=delete">'.$_TEXT['AP_DELETE'].'</a>':'');
				PluginHook('ap-plugins-links');
				echo '
						<table class="box" style="border-left:6px solid '.(IsInGroup($_FORUM['plugins'], $item)?'green':'red').'"><tr>
							<td><b>'.$info['name'].'</b><br />'.$info['description'].'<br /><small>'.$_TEXT['AP_AUTHOR'].': '.$info['author'].' | '.$_TEXT['AP_VERSION'].': '.$info['version'].'</small></td>
							<td style="width:150px; text-align:right;">'.implode(' | ', $links).'</td>
						</tr></table>
				';
			}
	}
}
?>