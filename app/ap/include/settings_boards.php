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
?>
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
			document.getElementById('updater').src = 'updater.php?page=boards&order=' + ToolMan.junkdrawer().serializeList(document.getElementById('sortlist'));
	        })
        }
	//-->
</script>
<?php
$_GET['page'] = 'boards';

echo '<h1>'.$_TEXT['AP_BOARDS'].'</h1>';

$_BOARDS = IniLoad('../data/boards.ini');
$list = Group2Array($_BOARDS['order']);

if ($_GET['action'] == 'delete_confirm')
{
	echo '<div class="notice">'.$_TEXT['AP_BOARDS_DELETE_CONFIRM'].'<p class="buttons"><a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=delete&id='.$_GET['id'].'">'.$_TEXT['CONFIRM_YES'].'</a> <a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'">'.$_TEXT['CONFIRM_NO'].'</a></div>';
}

if (($_GET['action'] == 'delete') && (is_numeric($_GET['id'])))
{
	$item = $list[$_GET['id']];
	DeleteFromGroup($_BOARDS['order'], $item);
	if (substr($item, 0, 1) == 'b')
	{
		$board = substr($item, 1, 100);
		$inis = LoadFileList('../data/'.$board.'/', '.txt.ini');
		foreach ($inis as $ini)
		{
			DeleteThread($board, str_replace('.txt.ini', '', $ini));
		}
		unlink('../data/'.$board.'/board.ini');
	}
	else
	{
		$_BOARDS[$item] = '';
	}
	IniSave('../data/boards.ini', $_BOARDS);
	RepairBoardsIni();
}

if (($_GET['action'] == 'sort') && ($_GET['id'] >= 1) && ($_GET['id'] <= count($list)-1))
{
	$temp = $list[$_GET['id']];
	$list[$_GET['id']] = $list[$_GET['id']-1];
	$list[$_GET['id']-1] = $temp;
	$_BOARDS['order'] = Array2Group($list, false);
	IniSave('../data/boards.ini', $_BOARDS);
	RepairBoardsIni();
}

if (($_GET['action'] == 'layer_left') && (is_numeric($_GET['id'])))
{
	$_BOARDS['b'.$_GET['id'].'_layer']--;
	IniSave('../data/boards.ini', $_BOARDS);
	RepairBoardsIni();
}

if (($_GET['action'] == 'layer_right') && (is_numeric($_GET['id'])))
{
	$_BOARDS['b'.$_GET['id'].'_layer']++;
	IniSave('../data/boards.ini', $_BOARDS);
	RepairBoardsIni();
}

if (($_GET['action'] == 'add_group') && ($_POST['caption'] != ''))
{
	$new_id = 0;
	while(($new_id == 0) OR (IsInGroup($_BOARDS['order'], 'c'.$new_id))) $new_id++;
	$_BOARDS['c'.$new_id] = $_POST['caption'];
	AddToGroup($_BOARDS['order'], 'c'.$new_id, false);
	IniSave('../data/boards.ini', $_BOARDS);
}

echo '

	<table class="auto">
		<tr><td colspan="2">	
			<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=add_group" name="add" method="post">
			<b>'.$_TEXT['AP_BOARDS_GROUP'].'</b> <input type="text" name="caption" maxlength="50" value=""> <input type="submit" name="submit" value="'.$_TEXT['AP_ADD'].'"/> 
			</form>
		</td>
		<td colspan="3" style="text-align:center;">
			<input type="button" value="'.$_TEXT['AP_BOARDS_NEW'].'" onClick="location=\'index.php?nav=settings&page=boards_edit&id=new\';" />
		</td></tr>
	</table>
	
	<ul id="sortlist" class="boxy">
';

$_BOARDS = IniLoad('../data/boards.ini');
$list = Group2Array($_BOARDS['order']);
$last_layer = 0;
for($i = 0; $i < count($list); $i++)
{
	$item = $list[$i];
	if (substr($item, 0, 1) == 'c')
	{
		echo '
			<li itemID="'.$item.'"><table class="auto">
			<tr>
				<td style="width:14px;text-align:center;">
		';
				if ($i > 0) echo '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=sort&id='.$i.'"><img src="images/ap_up.gif" /></a> ';
				  else echo '<img src="images/ap_up_dis.gif" /> ';
				echo '<br />';
				if ($i < (count($list)-2)) echo '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=sort&id='.($i+1).'"><img src="images/ap_down.gif" /></a>';
				  else echo '<img src="images/ap_down_dis.gif" />'; 
	  	echo '
				</td>
				<td><b>'.$_BOARDS[$item].'</b></td>
				<td style="text-align:right;"><a href="?nav=settings&page=boards_group_edit&id='.substr($item, 1, 100).'">'.$_TEXT['AP_EDIT'].'</a> | <a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=delete&id='.$i.'">'.$_TEXT['AP_DELETE'].'</a></td>
			</tr>
			</table></li>
		';
		$last_layer = 0;
	}
	else if (substr($item, 0, 1) == 'b')
	{
		if (is_numeric(substr($item, 1, 10)))
		{
			$board = substr($item, 1, 10);
			$_BOARD = IniLoad('../data/'.$board.'/board.ini');
			$layer = $_BOARDS[$item.'_layer'];
			echo '
				<li itemID="'.$item.'"><table class="auto">
				<tr>
					<td style="text-align:center;width:14px;">
			';
					if ($i > 0) echo '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=sort&id='.$i.'"><img src="images/ap_up.gif" /></a> ';
					  else echo '<img src="images/ap_up_dis.gif" /> ';
					echo '<br />'; 
					if ($i < (count($list)-2)) echo '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=sort&id='.($i+1).'"><img src="images/ap_down.gif"></a>';
					  else echo '<img src="images/ap_down_dis.gif">'; 
			echo '
					</td>
					<td style="text-align:center;width:14px;">
			';
					if ($layer > 1) echo '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=layer_left&id='.$board.'"><img src="images/ap_left.gif" /></a> ';
					  else echo '<img src="images/ap_left_dis.gif" /> ';
					echo '<br />'; 
					if ($layer <= $last_layer) echo '<a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=layer_right&id='.$board.'"><img src="images/ap_right.gif"></a>';
					  else echo '<img src="images/ap_right_dis.gif">'; 
			echo '
					</td>
					<td style="width:auto;padding-left:'.(5+($layer-1)*20).'px;">'.($_BOARD['hide']?'<img src="images/ap_no.gif" title="'.$_TEXT['AP_BOARDS_HIDE'].'" /> ':'').'<b>'.$_BOARD['title'].'</b> - '.$_BOARD['description'].'</td>
					<td style="text-align:left; width:85px;"><small>'.$_TEXT['TOPICS'].': '.$_BOARD['topics'].'<br />'.$_TEXT['ANSWERES'].': '.$_BOARD['answeres'].'</td>
					<td style="text-align:right; width:150px;">
						<a href="?nav=settings&page=boards_edit&id='.$board.'">'.$_TEXT['AP_EDIT'].'</a>
						| <a href="?nav='.$_GET['nav'].'&page='.$_GET['page'].'&action=delete_confirm&id='.$i.'">'.$_TEXT['AP_DELETE'].'</a>
					</td>
				</tr>
				</table></li>
			';
			$last_layer = $_BOARDS[$item.'_layer'];
		}
	}
}

echo '
	</ul>
	<iframe id="updater" src="updater.php" width="1" height="1" border="0" style="display:none;"/></iframe>
';
?>