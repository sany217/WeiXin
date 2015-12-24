<?php
include_once( "inc/conn.php" );
include_once( "inc/utility.php" );
include_once( "inc/utility_update.php" );

$mn = array(
			"mid" => "z013",
			"name" => "微信企业号管理",
			"code" => "system/weixinqy"
			);

if ( !sys_func_exists($mn) )
{
	$func_id = 0;
	$sql = "select MAX(FUNC_ID) from sys_function";
	$cursor = exequery( TD::conn( ), $sql );
	$ROW = mysql_fetch_array( $cursor );
	$func_id = $ROW[0];
	$func_id += 1;
	$sql_office = "INSERT INTO SYS_FUNCTION (FUNC_ID,MENU_ID,FUNC_NAME,FUNC_CODE) VALUES ('{$func_id}','{$mn['mid']}','{$mn['name']}','{$mn['code']}');";
	exequery( TD::conn( ), $sql_office );
	$func_id_str = "";
	$query = "select FUNC_ID_STR from user_priv where USER_PRIV = '1'";
	$cursor = exequery( TD::conn( ), $query );
	if ( ( $ROW = mysql_fetch_array( $cursor ) ) && !find_id( $ROW['FUNC_ID_STR'], $func_id ) )
	{
		$update = "update user_priv set FUNC_ID_STR = '".$ROW['FUNC_ID_STR']."{$func_id},' where USER_PRIV = '1'";
		exequery( TD::conn( ), $update );
	}
	cache_menu( );
}
$UPDATE_TIPS = "<div class=\"update_tips\">"._( "安装 微信企业号功能包 成功！" )."</div>";
echo $UPDATE_TIPS;
ob_end_clean( );
?>