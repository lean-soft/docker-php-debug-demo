<script language="JavaScript" type="text/javascript" src="../include/tool-man/core.js"></script><script language="JavaScript" type="text/javascript" src="../include/tool-man/events.js"></script><script language="JavaScript" type="text/javascript" src="../include/tool-man/css.js"></script><script language="JavaScript" type="text/javascript" src="../include/tool-man/coordinates.js"></script><script language="JavaScript" type="text/javascript" src="../include/tool-man/drag.js"></script><script language="JavaScript" type="text/javascript" src="../include/tool-man/dragsort.js"></script><script language="JavaScript" type="text/javascript" src="../include/tool-man/cookies.js"></script><script language="JavaScript" type="text/javascript"><!--
	var dragsort = ToolMan.dragsort()
	var junkdrawer = ToolMan.junkdrawer()
	window.onload = function() {
		dragsort.makeListSortable(document.getElementById("sortlist"),verticalOnly,saveOrder)
	}
	function verticalOnly(item) {
		item.toolManDragGroup.verticalOnly()
	}
	function saveOrder(item) {
 		var group = item.toolManDragGroup
	        group.register('dragend', function() {
			document.getElementById('updater').src = 'updater.php?page=navigation&order=' + ToolMan.junkdrawer().serializeList(document.getElementById('sortlist'));
	        })
        }
	//-->
</script>
<?php
echo '
	<h1>'.$_TEXT['AP_NAVIGATION'].'</h1>
';

$ini = IniLoad(DIR.'data/navigation.ini');
$nav = Group2Array($ini['order']);

if ($_GET['action'] == 'add')
{
	$nav[] = $_POST['item'];
	$ini['order'] = Array2Group($nav, false);
	IniSave(DIR.'data/navigation.ini', $ini);
}

if ($_GET['action'] == 'up')
{
	$temp = $nav[$_GET['id']];
	$nav[$_GET['id']] = $nav[$_GET['id']-1];
	$nav[$_GET['id']-1] = $temp;
	$ini['order'] = Array2Group($nav, false);
	IniSave(DIR.'data/navigation.ini', $ini);
}

if ($_GET['action'] == 'delete')
{
	DeleteFromGroup($ini['order'], $nav[$_GET['id']]);
	$ini[$nav[$_GET['id']].'_link'] = '';
	$ini[$nav[$_GET['id']].'_url'] = '';
	$ini[$nav[$_GET['id']].'_target'] = '';
	$ini[$nav[$_GET['id']].'_visible'] = '';
	IniSave(DIR.'data/navigation.ini', $ini);
}


$list = array();
if (!IsInGroup($ini['order'], '*0')) $list[] = array('*0', $_TEXT['NAV_FORUM']);
if (!IsInGroup($ini['order'], '*1')) $list[] = array('*1', $_TEXT['NAV_REGISTER']);
if (!IsInGroup($ini['order'], '*2')) $list[] = array('*2', $_TEXT['NAV_MEMBERS']);
if (!IsInGroup($ini['order'], '*3')) $list[] = array('*3', $_TEXT['NAV_SEARCH']);
if (!IsInGroup($ini['order'], '*4')) $list[] = array('*4', $_TEXT['NAV_RULES']);
if (!IsInGroup($ini['order'], '*5')) $list[] = array('*5', $_TEXT['NAV_FAQ']);
if (!IsInGroup($ini['order'], '*6')) $list[] = array('*6', $_TEXT['NAV_IMPRESSUM']);

echo '
	<fieldset>
		<legend>'.$_TEXT['AP_ADD'].'</legend>
		<table class="auto">
		<tr>
';

if (count($list) > 0)
{
	echo '
		<td><form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=add" method="post">
			<select name="item" id="item">
	';
	foreach ($list as $i) echo '<option value="'.$i[0].'">'.$i[1].'</option>';
	echo '
			</select>
			<input type="submit" name="save" value="'.$_TEXT['AP_ADD'].'" />
		</form></td>
	';
}
echo '
		<td style="text-align:right;"><form action="?nav='.$_GET['nav'].'&page=navigation_edit&item=new" method="post">
			<input type="submit" value="'.$_TEXT['AP_NAVIGATION_CREATE'].'" />
		</form></td>
		</tr>
		</table>
	</fieldset>
	<br />
	<ul id="sortlist" class="boxy">
';


$nav = Group2Array($ini['order']);

for($i=0;$i<count($nav);$i++)
{
	$item = $nav[$i];
	echo '
		<li itemID="'.$item.'"><table class="auto">
		<tr>
			<td style="width:8%;">
	';
	if ($i == 0) echo '<img src="./images/ap_up_dis.gif"> ';
	else echo '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.$i.'&action=up"><img src="./images/ap_up.gif"></a> ';
	if ($i+1 == count($nav)) echo '<img src="./images/ap_down_dis.gif">';
	else echo '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.($i+1).'&action=up"><img src="./images/ap_down.gif"></a>';
	echo '
			</td>
	';
	if ($item == '*0') echo '<td style="width:62%;"><b>'.$_TEXT['NAV_FORUM'].'</b></td><td style="width:30%;text-align:right;">';
	else if ($item == '*1') echo '<td style="width:62%;"><b>'.$_TEXT['NAV_REGISTER'].'</b></td><td style="width:30%;text-align:right;">';
	else if ($item == '*2') echo '<td style="width:62%;"><b>'.$_TEXT['NAV_MEMBERS'].'</b></td><td style="width:30%;text-align:right;">';
	else if ($item == '*3') echo '<td style="width:62%;"><b>'.$_TEXT['NAV_SEARCH'].'</b></td><td style="width:30%;text-align:right;">';
	else if ($item == '*4') echo '<td style="width:62%;"><b>'.$_TEXT['NAV_RULES'].'</b></td><td style="width:30%;text-align:right;">';
	else if ($item == '*5') echo '<td style="width:62%;"><b>'.$_TEXT['NAV_FAQ'].'</b></td><td style="width:30%;text-align:right;">';
	else if ($item == '*6') echo '<td style="width:62%;"><b>'.$_TEXT['NAV_IMPRESSUM'].'</b></td><td style="width:30%;text-align:right;">';
	else
	{
		echo '
			<td style="width:62%;"><b>'.$ini[$item.'_link'].'</b> &raquo; '.$ini[$item.'_url'].'</td>
			<td style="width:30%;text-align:right;">
				<a href="?nav='.$_GET['nav'].'&page=navigation_edit&item='.$item.'">'.$_TEXT['AP_EDIT'].'</a> | 

		';
	}
	echo '
				<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&id='.$i.'&action=delete">'.$_TEXT['AP_DELETE'].'</a>
			</td>
		</tr>
		</table></li>
	';
}
echo '
	</ul>
	<iframe id="updater" src="updater.php" width="1" height="1" border="0" style="display:none;"/></iframe>
';
?>