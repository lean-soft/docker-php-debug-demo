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

AuthUser();

$_SUBNAV[] = array($_TEXT['LOGIN_PROFILE'], url('my_profile.php'));

require_once './include/page_top.php';
require_once './include/my_profile_top.php';

$options = array();
$options[] = '<b><a href="'.url('my_profile_data.php').'">'.$_TEXT['PROFILE_DATA'].'</a></b><br>'.$_TEXT['PROFILE_DATA_DESCR'];
$options[] = '<b><a href="'.url('my_profile_password.php').'">'.$_TEXT['PROFILE_PASSWORD'].'</a></b><br>'.$_TEXT['PROFILE_PASSWORD_DESCR'];
$options[] = '<b><a href="'.url('my_profile_settings.php').'">'.$_TEXT['PROFILE_SETTINGS'].'</a></b><br>'.$_TEXT['PROFILE_SETTINGS_DESCR'];
$options[] = '<b><a href="'.url('my_profile_signature.php').'">'.$_TEXT['PROFILE_SIGNATURE'].'</a></b><br>'.$_TEXT['PROFILE_SIGNATURE_DESCR'];
$options[] = '<b><a href="'.url('my_profile_notification.php').'">'.$_TEXT['PROFILE_NOTIFICATION'].'</a></b><br>'.$_TEXT['PROFILE_NOTIFICATION_DESCR'];
if (!defined('CONFIG_PROFILE_DISALLOW_AVATAR_EDIT')) $options[] = '<b><a href="'.url('my_profile_avatar.php').'">'.$_TEXT['PROFILE_AVATAR'].'</a></b><br>'.$_TEXT['PROFILE_AVATAR_DESCR'];
$options[] = '<b><a href="'.url('my_profile_points.php').'">'.$_TEXT['PROFILE_POINTS'].'</a></b><br>'.$_TEXT['PROFILE_POINTS_DESCR'];
$options[] = '<b><a href="'.url('my_profile_delete.php').'">'.$_TEXT['PROFILE_DELETE'].'</a></b><br>'.$_TEXT['PROFILE_DELETE_DESCR'];

echo '
	<table class="main">
	<tr><td class="oben">'.$_TEXT['LOGIN_PROFILE'].'</td></tr>
	<tr><td class="w">
		<table style="width:100%;">
';
for ($i = 0; $i < count($options); $i = $i+2)
{
	echo '
		<tr>
			<td style="vertical-align:top;width:50%;">'.$options[$i].'</td>
			<td style="vertical-align:top;width:50%;">'.$options[$i+1].'</td>
		</tr>
	';
}
echo '
		</table>
	</td></tr>
	</table>
';

require './include/my_profile_bottom.php';
require './include/page_bottom.php';	
?>