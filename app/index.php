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

if (is_object($PLUGIN_INDEXPAGE)) 
{
	$PLUGIN_INDEXPAGE->PageInit();
	require 'include/page_top.php';
	$PLUGIN_INDEXPAGE->PageContent();
	require 'include/page_bottom.php';
	Exit;
}

if ($_GET['plugin'] <> '')
{
	$inst = $PLUGINS[$_GET['plugin']];
	if (is_object($inst))
	{
		if ($inst->IsSinglePage())
		{
			$_SUBNAV[] = array($inst->PageName(), url('index.php', $_GET['plugin']));
			$inst->PageInit();
			require 'include/page_top.php';
			$inst->PageContent();
			require 'include/page_bottom.php';
			Exit;
		}
	}

}

require 'forum.php';
?>