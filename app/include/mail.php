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

if (!defined('SICHERHEIT_FORUM')) die('Access denied.');

require 'phpmailer/class.phpmailer.php';

class XMail extends PHPMailer 
{   
	var $Mailer   = 'mail';                         
	var $WordWrap = 75;

	function XMail()
	{
		GLOBAL $_FORUM;
		$this->SetFrom($_FORUM['settings_forum_email'], $_FORUM['settings_forum_name']);
	}

}





?>