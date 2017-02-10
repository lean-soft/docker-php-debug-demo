<?PHP
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
 
$robots_index = true; 
require 'include/init.php';

if ((!$_FORUM['settings_design_ranking_guest']) AND (!($_FORUM['settings_design_ranking_user'] && ($_SESSION['Benutzername'] <> ''))))
{
	require 'index.php';
	Exit;
}

$_SUBNAV[] = array($_TEXT['NAV_MEMBERS'], url('members.php'));

require 'include/page_top.php';

$search = CreateSearchArray($_GET['search']<>''?$_GET['search']:($_POST['search']<>''?$_POST['search']:''));
$sort_array = explode('.', $_GET['sort']<>''?$_GET['sort']:($_POST['sort']<>''?$_POST['sort']:''));
$sort = $sort_array[0];
if (!in_array($sort, array('username', 'points', 'register_date', 'lastonline_date'))) $sort = 'points';
$sort_direction = $sort_array[1];
if (!in_array($sort_direction, array('asc', 'desc'))) $sort_direction = (in_array($sort, array('points', 'register_date', 'lastonline_date'))?'desc':'asc');

$users = LoadFileList('data/user/', '.usr.ini');
$show_user = array();
foreach ($users as $user)
{
	$ini = IniLoad('data/user/'.$user);
	$arr = array();
	$arr['register_date'] = (is_numeric($ini['register_date'])?$ini['register_date']:'');
	$arr['lastonline_date'] = (is_numeric($ini['lastonline_date'])?$ini['lastonline_date']:'');
	$arr['username'] = str_replace('.usr.ini', '', $user);
	$arr['points'] = user_points($arr['username']);
	$arr['homepage'] = $ini['homepage'];
	$arr['email'] = ($ini['show_email']?$ini['email']:'');
	$insert = true;
	if (count($search) > 0)
	{
		foreach ($search as $item)
		{
			if (
				(!InStr(strtolower($item), strtolower($arr['username'])))
			     &&
				(!InStr(strtolower($item), strtolower($arr['homepage'])))
			     &&
				(!InStr(strtolower($item), strtolower($arr['email'])))
			     &&
				(!InStr(strtolower($item), strtolower($ini['name'])))
			   ) 
			{
				$insert = false;
			}
		}
	}
	if ($insert) array_push($show_user, $arr);
}


function sort_list($a, $b) 
{
	GLOBAL $sort, $sort_direction;
    	if (strtolower($a[$sort]) == strtolower($b[$sort])) return 0;
    	$result = (strtolower($a[$sort]) > strtolower($b[$sort])) ? 1 : -1;
	if ($sort_direction == 'desc') $result = 0 - $result;
	return $result;
}
usort($show_user, 'sort_list');

$pages = ceil(count($show_user)/20);
if ($_GET['page']>$pages) $_GET['page']=$pages;
if ($_GET['page']<1) $_GET['page']=1;
$von = ($_GET['page']-1)*20;
$bis = ($_GET['page']*20)-1;
if ($bis>=count($show_user)) $bis=count($show_user)-1;

echo '
<div id="content">
	<table class="main">
	<tr>
		<td class="oben" style="width:30%;"><a href="members.php?search='.CreateSearchText($search).'&sort=username'.($sort=='username'?($sort_direction=='asc'?'.desc':''):'').'">'.$_TEXT['LOGIN_USERNAME'].'</a>'.($sort=='username'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
		<td class="oben" style="width:5%;">&nbsp;</td>
		<td class="oben" style="width:5%;">&nbsp;</td>
		<td class="oben" style="width:15%;"><a href="members.php?search='.CreateSearchText($search).'&sort=points'.($sort=='points'?($sort_direction=='desc'?'.asc':''):'').'">'.$_TEXT['POINTS'].'</a>'.($sort=='points'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
		<td class="oben" style="width:25%;"><a href="members.php?search='.CreateSearchText($search).'&sort=register_date'.($sort=='register_date'?($sort_direction=='desc'?'.asc':''):'').'">'.$_TEXT['TIME_OF_REGISTRATION'].'</a>'.($sort=='register_date'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
		<td class="oben" style="width:20%;"><a href="members.php?search='.CreateSearchText($search).'&sort=lastonline_date'.($sort=='lastonline_date'?($sort_direction=='desc'?'.asc':''):'').'">'.$_TEXT['TIME_OF_LAST_VISIT'].'</a>'.($sort=='lastonline_date'?' <img src="styles/'.STYLE.'/images/sort_'.$sort_direction.'_arrow.gif" />':'').'</td>
	</tr>
	<tr>
		<td class="tb" colspan="4"><form action="'.url('members.php').'" method="post"><input type="hidden" name="sort" value="'.format_input($sort).'"><input type="text" name="search" value="'.format_input(CreateSearchText($search)).'" id="search" style="width:200px;"> <input type="submit" value="'.$_TEXT['SEARCH_SUBMIT'].'"></form></td>
		<td class="tb" colspan="2" style="text-align:right;">
';
			show_pages($pages, $_GET['page'], 'members.php?search='.CreateSearchText($search).'&sort='.$_GET['sort']);
echo '
		</td>
	</tr>
';

	$color='g';
	if (count($show_user) == 0)
	{
		echo '<tr><td colspan="6" class="'.$color.'">'.$_TEXT['ERROR_SEARCH_USER_NO_RESULT'].'</td></tr>';
	}
	else
	{
		for ($i=$von; $i<=$bis; $i++)
		{
			if (($show_user[$i]['homepage']<>'') && !InStr('://', $show_user[$i]['homepage']))
				$show_user[$i]['homepage'] = 'http://'.$show_user[$i]['homepage'];
			echo '
				<tr>
					<td class="'.$color.'">'.user($show_user[$i]['username']).'</td>
					<td class="'.$color.'">'.($show_user[$i]['email']<>''?'<a href="mailto:'.format_input($show_user[$i]['email']).'"><img src="styles/'.STYLE.'/images/mailS.png" style="vertical-align:bottom;" border="0"  title="'.format_input($show_user[$i]['email']).'"></a>':'').'</td>
					<td class="'.$color.'">'.($show_user[$i]['homepage']<>''?'<a href="'.format_input($show_user[$i]['homepage']).'" target="_blank"><img src="styles/'.STYLE.'/images/homeS.png" style="vertical-align:bottom;" border="0"  title="'.format_input($show_user[$i]['homepage']).'"></a>':'').'</td>
					<td class="'.$color.'" style="text-align:right;">'.fnum($show_user[$i]['points']).' '.$_TEXT['POINTS'].'</td>
					<td class="'.$color.'">'.ftime($show_user[$i]['register_date']).'</td>
					<td class="'.$color.'">'.ftime($show_user[$i]['lastonline_date'], true, true).'</td>
				</tr>		
			';
			$color = ($color=='w'?'g':'w');
		}
	}

	
echo '
	<tr>
		<td class="tb" colspan="4"><form action="'.url('members.php').'" method="post"><input type="hidden" name="sort" value="'.format_input($sort).'"><input type="text" name="search" value="'.format_input(CreateSearchText($search)).'" id="search" style="width:200px;"> <input type="submit" value="'.$_TEXT['SEARCH_SUBMIT'].'"></form></td>
		<td class="tb" colspan="2" style="text-align:right;">
';
			show_pages($pages, $_GET['page'], 'members.php?search='.CreateSearchText($search).'&sort='.$_GET['sort']);
echo '

		</td>
	</tr>
	</table>
</div>
';
require 'include/page_bottom.php';
?>