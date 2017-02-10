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

$list = array();
$list[] = array('my_profile_data.php', $_TEXT['PROFILE_DATA']);
$list[] = array('my_profile_password.php', $_TEXT['PROFILE_PASSWORD']);
$list[] = array('my_profile_settings.php', $_TEXT['PROFILE_SETTINGS']);
$list[] = array('my_profile_signature.php', $_TEXT['PROFILE_SIGNATURE']);
$list[] = array('my_profile_notification.php', $_TEXT['PROFILE_NOTIFICATION']);
if (!defined('CONFIG_PROFILE_DISALLOW_AVATAR_EDIT')) $list[] = array('my_profile_avatar.php', $_TEXT['PROFILE_AVATAR']);
$list[] = array('my_profile_points.php', $_TEXT['PROFILE_POINTS']);
$list[] = array('my_profile_delete.php', $_TEXT['PROFILE_DELETE']);

echo '
<div id="content">

	<table style="width:100%;">
	<tr><td style="width:25%;padding:0px;padding-right:20px;vertical-align:top;">
		
		<ul class="subnav">
';
foreach($list as $item)	echo '<li '.(basename($_SERVER['PHP_SELF'])==$item[0]?'class="current"':'').'><a href="'.url($item[0]).'">'.$item[1].'</a></li>';
echo '
		</ul>
	</td><td style="width:75%;padding:0px;vertical-align:top;">
';
?>