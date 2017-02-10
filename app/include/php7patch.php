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

if(!function_exists('eregi')) {
	function eregi($pattern, $string) {
		return preg_match("%{$pattern}%i", $string);
	}
}

if(!function_exists('ereg')) {
	function ereg($pattern, $string) {
		return preg_match("%{$pattern}%", $string);
	}
}

if(!function_exists('eregi_replace')) {
	function eregi_replace($pattern, $replacement, $string) {
		return preg_replace("%{$pattern}%i", $replacement, $string);
	}
}

if(!function_exists('ereg_replace')) {
	function ereg_replace($pattern, $replacement, $string) {
		return preg_replace("%{$pattern}%", $replacement, $string);
	}
}