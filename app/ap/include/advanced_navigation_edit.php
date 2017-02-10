<?PHP
$ini = IniLoad(DIR.'data/navigation.ini');
if ((!IsInGroup($ini['order'], $_GET['item'])) AND ($_GET['item'] != 'new'))
{
	require 'include/advanced_navigation.php';
}
else if ($_GET['action'] == 'save')
{
	if ($_GET['item'] == 'new')
	{
		$i = 1;
		while($ini['l'.$i.'_link'] != '') $i++;
		$_GET['item'] = 'l'.$i;
		AddToGroup($ini['order'], $_GET['item']);
	}
	$ini[$_GET['item'].'_link'] = $_POST['link'];
	$ini[$_GET['item'].'_url'] = $_POST['url'];
	$ini[$_GET['item'].'_target'] = $_POST['target'];
	$ini[$_GET['item'].'_visible'] = $_POST['visible'];
	IniSave(DIR.'data/navigation.ini', $ini);
	require 'include/advanced_navigation.php';
}
else
{
	echo '
	<h1>'.$_TEXT['AP_NAVIGATION'].'</h1>
	<fieldset>
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&item='.$_GET['item'].'&action=save" method="post">
		<table class="auto">
		<tr>
		<td style="padding:0px;">
			<table class="auto">
	 		<tr>
				<td style="width:20%;text-align:right;">'.$_TEXT['AP_NAVIGATION_LINK'].':</td>
				<td style="width:80%;"><input type="text" name="link" size="30" maxlength="50" value="'.format_input($ini[$_GET['item'].'_link']).'" style="width:300px;"></td>
			</tr>
	 		<tr>
				<td style="width:20%;text-align:right;">'.$_TEXT['AP_NAVIGATION_URL'].':</td>
				<td style="width:80%;"><input type="text" name="url" size="30" maxlength="50" value="'.format_input($ini[$_GET['item'].'_url']).'" style="width:300px;"></td>
			</tr>
	 		<tr>
				<td style="width:20%;text-align:right;">&nbsp;</td>
				<td style="width:80%;"><select name="target">
					<option value="">'.$_TEXT['AP_NAVIGATION_TARGET_SELF'].'</option>
					<option value="_blank" '.($ini[$_GET['item'].'_target']=='_blank'?'selected="selected"':'').'>'.$_TEXT['AP_NAVIGATION_TARGET_NEW'].'</option>
				</select></td>
			</tr>
	 		<tr>
				<td style="width:20%;text-align:right;">&nbsp;</td>
				<td style="width:80%;"><select name="visible">
					<option value="">'.$_TEXT['AP_NAVIGATION_VISIBLE_ALL'].'</option>
					<option value="user" '.($ini[$_GET['item'].'_visible']=='user'?'selected="selected"':'').'>'.$_TEXT['AP_NAVIGATION_VISIBLE_USER'].'</option>
				</select></td>
			</tr>
			</table>
		</td>
		</tr>
		<tr>
			<td style="text-align:center;"><INPUT TYPE="SUBMIT" name="submit" VALUE="'.$_TEXT['AP_SAVE'].'"></td>
		</tr>
		</table>
		</form>
	</fieldset>
	';
}
?>