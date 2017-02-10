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

echo '<h1>'.$_TEXT['AP_USER_ADMIN'].'</h1>';
	
$search_user = CreateSearchArray($_POST['search_user']<>''?$_POST['search_user']:$_GET['search_user']);
$search_email = CreateSearchArray($_POST['search_email']<>''?$_POST['search_email']:$_GET['search_email']);

echo '
		<fieldset>
		<legend>'.$_TEXT['AP_USER_ADMIN_SEARCH'].' </legend>
		<form action="?nav='.$_GET['nav'].'&page='.$_GET['page'].'" method="post">
		<table style="width:100%;">
		<tr>
			<td style="width:20%;text-align:right;">'.$_TEXT['LOGIN_USERNAME'].':</td>
			<td style="width:80%;"><input type="text" name="search_user" value="'.format_input(CreateSearchText($search_user)).'" style="width:250px;" /></td>
		</tr>
		<tr>
			<td style="width:20%;text-align:right;">'.$_TEXT['EMAIL'].':</td>
			<td style="width:80%;"><input type="text" name="search_email" value="'.format_input(CreateSearchText($search_email)).'" style="width:250px;" /></td>
		</tr>
		<tr>
			<td colspan="2" style="text-align:center;"><input type="submit" value="'.$_TEXT['AP_USER_ADMIN_SEARCH'].'" /></td>
		</tr>
		</table>
		</form>
		</fieldset>
';

$url = '?nav=user&page=admin&search_user='.urlencode(CreateSearchText($search_user)).'&search_email='.urlencode(CreateSearchText($search_email));

if (
	file_exists('../data/user/'.$_GET['user'].'.usr.ini')
     OR
	file_exists('../data/user/'.$_GET['user'].'.usr.tmp')
   )
{
	if ($_GET['action'] == 'delete_confirm')
	{
		echo '<div class="notice">'.MultiReplace($_TEXT['AP_USER_ADMIN_DELETE_CONFIRM'], $_GET['user']).'<p class="buttons"><a href="'.$url.'&action=delete&user='.$_GET['user'].'">'.$_TEXT['CONFIRM_YES'].'</a> <a href="'.$url.'">'.$_TEXT['CONFIRM_NO'].'</a></p></div>';
	}
	if ($_GET['action'] == 'delete')
	{
		DeleteUser($_GET['user']);
		// echo '<div class="confirm">'.MultiReplace($_TEXT['AP_USER_ADMIN_DELETE_OK'], $_GET['user']).'</div>';
	}
}
if (file_exists('../data/user/'.$_GET['user'].'.usr.tmp'))
{
	if ($_GET['action'] == 'activate')
	{
		ActivateUser($_GET['user']);
	}
}
if (file_exists('../data/user/'.$_GET['user'].'.usr.del'))
{
	if ($_GET['action'] == 'reactivate')
	{
		ReactivateUser($_GET['user']);
	}
	if ($_GET['action'] == 'erase_confirm')
	{
		echo '<div class="notice">'.MultiReplace($_TEXT['AP_USER_ADMIN_ERASE_CONFIRM'], $_GET['user']).'<p class="buttons"><a href="'.$url.'&action=erase&user='.$_GET['user'].'">'.$_TEXT['CONFIRM_YES'].'</a> <a href="'.$url.'">'.$_TEXT['CONFIRM_NO'].'</a></p></div>';
	}
	if ($_GET['action'] == 'erase')
	{
		EraseUser($_GET['user']);
		echo '<div class="confirm">'.MultiReplace($_TEXT['AP_USER_ADMIN_ERASE_OK'], $_GET['user']).'</div>';
	}
}


if ((count($search_user) > 0) OR (count($search_email) > 0))
{
	$results = array();

	$files = GetFileList('data/user/*.usr.*');
	foreach ($files as $file)
	{
		$ini = IniLoad($file);
		$arr = array();
		$path_info = pathinfo($file);

		$user = substr($path_info['basename'], 0, -8);
		$arr['username'] = $user;
		$arr['email'] = $ini['email'];
		$arr['status'] = substr($path_info['basename'], -3);
		$insert = true;
		if (count($search_user) > 0)
		{
			foreach ($search_user as $item)
			{
				if (!InStr(strtolower($item), strtolower($arr['username'])))
				{
					$insert = false;
					Break;
				}
			}
		}
		if ($insert && (count($search_email) > 0))
		{
			foreach ($search_email as $item)
			{
				if (!InStr(strtolower($item), strtolower($arr['email'])))
				{
					$insert = false;
					Break;
				}
			}
		}
		if ($insert) array_push($results, $arr);
	}

	function sort_list($a, $b) 
	{
		$sort = 'username'; 
		$sort_direction = 'asc';
	    	if (strtolower($a[$sort]) == strtolower($b[$sort])) return 0;
	    	$result = (strtolower($a[$sort]) > strtolower($b[$sort])) ? 1 : -1;
		if ($sort_direction == 'desc') $result = 0 - $result;
		return $result;
	}
	usort($results, 'sort_list');

	if (count($results) == 0)
	{
		echo '<div class="error">'.$_TEXT['AP_USER_ADMIN_SEARCH_NO_RESULTS'].'</div>';
	}
	else
	{
		echo '
			<table class="auto" style="margin-top:20px;">
			<tr>
				<td class="top">'.$_TEXT['LOGIN_USERNAME'].'</td>
				<td class="top">'.$_TEXT['EMAIL'].'</td>
				<td class="top">&nbsp;</td>
				<td class="top">&nbsp;</td>
			</tr>
		';
		$_color = 1;
		foreach ($results as $result)
		{
			$_color = -$_color;
			$color = ($_color==1?'#EEEEEE':'#FFFFFF');
			if ($result['status'] == 'del') $color = '#FF6F7D';
			if ($result['status'] == 'tmp') $color = '#AAAAAA';
			echo '
				<tr style="background:'.$color.'">
					<td>'.$result['username'].'</td>
					<td>'.$result['email'].'</td>
					<td><small>
						<a href="?nav=user&page=edit&user='.$result['username'].'">'.$_TEXT['AP_EDIT'].'</a>
					</small></td>
					<td><small>
			';
			if ($result['status'] == 'tmp')
			{
				echo '<a href="'.$url.'&user='.$result['username'].'&action=activate">'.$_TEXT['AP_USER_ADMIN_ACTIVATE'].'</a><br>';
			}
			if ($result['status'] == 'del')
			{
				echo '<a href="'.$url.'&user='.$result['username'].'&action=reactivate">'.$_TEXT['AP_USER_ADMIN_REACTIVATE'].'</a><br>';
				echo '<a href="'.$url.'&user='.$result['username'].'&action=erase_confirm">'.$_TEXT['AP_USER_ADMIN_ERASE'].'</a>';
			}
			if (($result['status'] == 'tmp') OR ($result['status'] == 'ini'))
			{
				echo '<a href="'.$url.'&user='.$result['username'].'&action=delete_confirm">'.$_TEXT['AP_DELETE'].'</a>';
			}
			echo '
					</small></td>
				</tr>
			';
		}
		echo '
			</table>
		';
	}
}
?>