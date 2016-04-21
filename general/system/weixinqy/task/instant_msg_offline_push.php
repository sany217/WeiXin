<?
	include_once("inc/conn.php");
	include_once("inc/utility_all.php");
	
	define("MSGCHECKTIME","WEIXINQY_MSGCHECK_TIME");

	$CUR_TIME = time( );
	
	$PARA_ARRAY = get_sys_para( MSGCHECKTIME, FALSE );
	$MSG_CHK_TIME = intval( trim( $PARA_ARRAY[MSGCHECKTIME] ) );
	$BEGIN_TIME = $MSG_CHK_TIME <= 0 ? $CUR_TIME : $MSG_CHK_TIME;

	$query = "SELECT FROM_UID,TO_UID,CONTENT,SEND_TIME FROM MESSAGE where REMIND_FLAG='1' and MSG_TYPE='1' and
			 FROM_UID!=0 and TO_UID!=0 and SEND_TIME>'{$BEGIN_TIME}' and SEND_TIME<='{$CUR_TIME}' order by TO_UID,FROM_UID,SEND_TIME asc";
			
	$cursor = exequery(TD::conn(),$query);

	if ( !$cursor )
	{
		echo "-ERR ";
		exit( );
	}

	while ( $ROW = mysql_fetch_array( $cursor ) )
	{
		$FROM_UID = $ROW['FROM_UID'];

		include_once("inc/utility_cache.php");
		$FROM_USER_NAME = getuserinfobyuid( $FROM_UID, "USER_NAME" );

		$TO_UID = $ROW['TO_UID'];
		$CONTENT = $ROW['CONTENT'];

		include_once( "inc/itask/itask.php" );
		mobile_push_notification($TO_UID, $FROM_USER_NAME._( "：" ).$CONTENT._( "【即时通讯离线消息】" ), "msg");
	}

	set_sys_para( array(MSGCHECKTIME => $CUR_TIME) );

	$CUR_TIME_FORMAT = date("Y-m-d H:i:s", $CUR_TIME);
	$qry="UPDATE OFFICE_TASK SET LAST_EXEC='{$CUR_TIME_FORMAT}',EXEC_FLAG='1',EXEC_MSG='{$CUR_TIME_FORMAT}' WHERE TASK_CODE='inst_msg_offl_push'";
	exequery(TD::conn(),$qry);

	echo "+OK";
?>