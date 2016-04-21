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

	$query = "select FUNC_ID_STR from user_priv where USER_PRIV = '1'";
	$cursor = exequery( TD::conn( ), $query );
	if ( ( $ROW = mysql_fetch_array( $cursor ) ) && !find_id( $ROW['FUNC_ID_STR'], $func_id ) )
	{
		$update = "update user_priv set FUNC_ID_STR = '".$ROW['FUNC_ID_STR']."{$func_id},' where USER_PRIV = '1'";
		exequery( TD::conn( ), $update );
	}
	cache_menu( );
}


//Add instant message offline push task
$taskcode = "inst_msg_offl_push";
$taskfile = dirname($_SERVER['PHP_SELF'])."/task/instant_msg_offline_push.php";
add_task($taskfile, $taskcode);


$UPDATE_TIPS = "<div class=\"update_tips\">"._( "安装 微信企业号功能包 成功！" )."</div>";
echo $UPDATE_TIPS;
ob_end_clean( );



////////////////////////////////////////////
function add_task($file, $code)
{
	$qry = "SELECT * FROM office_task WHERE TASK_CODE='{$code}'";
	$csr = exequery(TD::conn(),$qry);
	if($row = mysql_fetch_array($csr))
	{
	}
	else
	{
		$qry = "INSERT INTO `office_task` (`TASK_TYPE`, `INTERVAL`, `EXEC_TIME`, `LAST_EXEC`,
				`EXEC_FLAG`, `EXEC_MSG`, `TASK_URL`, `TASK_NAME`, `TASK_DESC`, `TASK_CODE`, `USE_FLAG`,
				`SYS_TASK`, `EXT_DATA`) VALUES(
				'0',
				1,
				'00:00:00',
				'0000-00-00 00:00:00',
				1,
				'0000-00-00 00:00:00',
				'{$file}',
				'即时通讯离线消息推送',
				'定时将OA精灵离线消息推送到微信企业号',
				'{$code}',
				'1',
				'0',
				'')";
		exequery(TD::conn(),$qry);

		//Add system parameter
		include_once("inc/utility_all.php");
		add_sys_para( array("WEIXINQY_MSGCHECK_TIME" => "") );
	}
}
?>