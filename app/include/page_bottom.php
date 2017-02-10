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

IF (!defined('SICHERHEIT_FORUM')) die('Access denied.');

@include('data/advertising_bottom.txt');
$LOADING_TIME = strtok(microtime(), ' ') + strtok('') - $LOADING_TIME_START; 

PluginHook('page_bottom-content_bottom');

ob_start();
@include('styles/'.$_FORUM['settings_design_style'].'/bottom.html');
@include('styles/'.$_FORUM['settings_design_style'].'/bottom.php');
$content = ob_get_contents();
ob_end_clean();
echo style_convert($content);

echo '<!-- '.number_format($LOADING_TIME,2,',','.').' Sec. --></body></html>'; 
?>