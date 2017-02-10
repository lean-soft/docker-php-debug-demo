<?PHP
echo '
	<h1>'.$_TEXT['AP_USER_GROUPS'].'</h1>
';

if ($_GET['action'] == "delete")
{
	if (File_exists("../data/user/".$_GET['selected'].".grp.ini")) unlink("../data/user/".$_GET['selected'].".grp.ini");
	echo '<div class="confirm">'.str_replace("%GROUP%",$_GET['selected'],$_TEXT['AP_USER_GROUPS_DELETE_OK']).'</div>';
}
if (($_GET['action'] == "new") && ($_POST['selected'] <> ''))
{
	$_GROUP['members'] = '';
	IniSave('../data/user/'.$_POST['selected'].'.grp.ini', $_GROUP);
}

$groups = LoadFileList('../data/user/', '.grp.ini');

echo '
	<table class="box" style="width:400px;">
';

foreach ($groups as $group)
{
	echo '
		<tr>
			<td>'.str_replace('.grp.ini', '', $group).'</td>
			<td><a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'_edit&group='.str_replace('.grp.ini', '', $group).'">'.$_TEXT['AP_USER_GROUPS_EDIT'].'</a></td>
			<td>'.($group <> 'Admins.grp.ini'?'<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=delete&selected='.str_replace('.grp.ini', '', $group).'">'.$_TEXT['AP_DELETE'].'</a>':'').'</td>
		</tr>
	';
}

echo '
	</table>

	<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=new" method="post">
	<p>'.$_TEXT['AP_USER_GROUPS_NEW'].' <input type="text" name="selected" /> <input type="submit" name="submit" value="'.$_TEXT['AP_CREATE'].'" /></p>
	</form>
';
?>