<?php
include_once( "inc/auth.inc.php" );
include_once( "inc/check_type.php" );
include_once( "inc/utility_file.php" );
include_once( "inc/utility_org.php" );
include_once( "inc/utility_sms1.php" );
include_once( "inc/utility_sms2.php" );
include_once( "inc/utility_all.php" );
$HTML_PAGE_TITLE = _( "发布新闻" );
include_once( "inc/header.inc.php" );
echo "\r\n<body class=\"bodycolor\">\r\n";
$query = "SELECT POST_PRIV FROM USER where UID='".$_SESSION['LOGIN_UID']."'";
$cursor = exequery( TD::conn( ), $query );
if ( $ROW = mysql_fetch_array( $cursor ) )
{
				$POST_PRIV = $ROW['POST_PRIV'];
}
if ( $SEND_TIME != "" && !is_date_time( $SEND_TIME ) )
{
				message( _( "错误" ), sprintf( _( "发布时间格式不对，应形如%s" ), date( "Y-m-d H:i:s", time( ) ) ) );
				button_back( );
				exit( );
}
$CUR_TIME = $SEND_TIME ? $SEND_TIME : date( "Y-m-d H:i:s", time( ) );
$CUR_TIME1 = date( "Y-m-d H:i:s", time( ) );
if ( 1 < count( $_FILES ) )
{
				$ATTACHMENTS = upload( );
				$CONTENT = replaceimagesrc( $CONTENT, $ATTACHMENTS );
				$ATTACHMENT_ID = $ATTACHMENT_ID_OLD.$ATTACHMENTS['ID'];
				$ATTACHMENT_NAME = $ATTACHMENT_NAME_OLD.$ATTACHMENTS['NAME'];
}
else
{
				$ATTACHMENT_ID = $ATTACHMENT_ID_OLD;
				$ATTACHMENT_NAME = $ATTACHMENT_NAME_OLD;
}
$ATTACHMENT_ID .= copy_sel_attach( $ATTACH_NAME, $ATTACH_DIR, $DISK_ID );
$ATTACHMENT_NAME .= $ATTACH_NAME;
if ( $FORMAT == "2" )
{
				$CONTENT = $URL_ADD;
}
if ( $FORMAT == "1" )
{
				$CONTENT = "";
}
$CONTENT = str_replace( "http://".$HTTP_HOST."/inc/attach.php?", "/inc/attach.php?", $CONTENT );
$CONTENT = str_replace( "http://".$HTTP_HOST."/module/editor/plugins/smiley/images/", "/module/editor/plugins/smiley/images/", $CONTENT );
$C = preg_match( "/<img.*?\\ssrc=\\\\\"\\/inc\\/attach.php\\?(.*)MODULE=upload_temp/i", $CONTENT );
if ( $C == 1 )
{
				$CONTENT = replace_attach_url( $CONTENT );
				$ATTACHMENT_ID = move_attach( $ATTACHMENT_ID, $ATTACHMENT_NAME, "", "" ).",";
}
if ( $TOP == "on" )
{
				$TOP = "1";
}
else
{
				$TOP = "0";
}
$CONTENT = strip_unsafe_tags( $CONTENT );
if ( $FORMAT != 2 )
{
				$CONTENT = stripslashes( $CONTENT );
				$COMPRESS_CONTENT = bin2hex( gzcompress( $CONTENT ) );
				$CONTENT = mysql_escape_string( strip_tags( $CONTENT ) );
				$query = "update NEWS set SUBJECT='".$SUBJECT."',SUMMARY='{$SUMMARY}',KEYWORD='{$KEYWORD}',CONTENT='{$CONTENT}',TOP='{$TOP}',SUBJECT_COLOR='{$SUBJECT_COLOR}',COMPRESS_CONTENT=0x{$COMPRESS_CONTENT},TO_ID='{$TO_ID}',PRIV_ID='{$PRIV_ID}',USER_ID='{$COPY_TO_ID}',";
}
else
{
				$query = "update NEWS set SUBJECT='".$SUBJECT."',SUMMARY='{$SUMMARY}',SUBJECT_COLOR='{$SUBJECT_COLOR}',KEYWORD='{$KEYWORD}',CONTENT='{$CONTENT}',TOP='{$TOP}',TO_ID='{$TO_ID}',PRIV_ID='{$PRIV_ID}',USER_ID='{$COPY_TO_ID}',";
}
if ( $PUBLISH == "1" )
{
				$query .= "PROVIDER='".$_SESSION['LOGIN_USER_ID'].( "',PUBLISH='".$PUBLISH."'," );
}
$query .= "ATTACHMENT_ID='".$ATTACHMENT_ID."',NEWS_TIME='{$CUR_TIME}',ATTACHMENT_NAME='{$ATTACHMENT_NAME}',ANONYMITY_YN='{$ANONYMITY_YN}',FORMAT='{$FORMAT}',TYPE_ID='{$TYPE_ID}',LAST_EDITOR='".$_SESSION['LOGIN_USER_ID'].( "',LAST_EDIT_TIME='".$CUR_TIME1."' where NEWS_ID='{$NEWS_ID}'" );
if ( $_SESSION['LOGIN_USER_PRIV'] != "1" && $POST_PRIV != "1" )
{
				$query .= " and PROVIDER='".$_SESSION['LOGIN_USER_ID']."'";
}
exequery( TD::conn( ), $query );
if ( $PUBLISH == "1" && $OP != "0" )
{
	if ( $SMS_REMIND == "on" || $SMS2_REMIND == "on" )
	{
		$query = "select USER_ID from USER where (NOT_LOGIN = 0 or NOT_MOBILE_LOGIN = 0)";
		if ( $TO_ID != "ALL_DEPT" )
		{
			$query .= " and (find_in_set(DEPT_ID,'".$TO_ID."') or find_in_set(USER_PRIV,'{$PRIV_ID}') or find_in_set(USER_ID,'{$COPY_TO_ID}'))";
		}
		$cursor = exequery( TD::conn( ), $query );
		while ( $ROW = mysql_fetch_array( $cursor ) )
		{
			$USER_ID_STR .= $ROW['USER_ID'].",";
		}
		$MY_ARRAY = explode( ",", $PRIV_ID );
		$ARRAY_COUNT = sizeof( $MY_ARRAY );
		$I = 0;
		for ( ;	$I < $ARRAY_COUNT;	++$I	)
		{
			if ( $MY_ARRAY[$I] == "" )
			{
				continue;
			}
			$query = "select USER_ID from USER where (NOT_LOGIN = 0 or NOT_MOBILE_LOGIN = 0) and find_in_set('".$MY_ARRAY[$I]."',USER_PRIV_OTHER)";
			$cursor = exequery( TD::conn( ), $query );
			while ( $ROW = mysql_fetch_array( $cursor ) )
			{
				if ( !find_id( $USER_ID_STR, $ROW['USER_ID'] ) )
				{
					$USER_ID_STR .= $ROW['USER_ID'].",";
				}
			}
		}
		$MY_ARRAY_DEPT = explode( ",", $TO_ID );
		$ARRAY_COUNT_DEPT = sizeof( $MY_ARRAY_DEPT );
		$I = 0;
		for ( ;	$I < $ARRAY_COUNT_DEPT;	++$I	)
		{
			if ( $MY_ARRAY_DEPT[$I] == "" )
			{
				continue;
			}
			$query_d = "select USER_ID from USER where (NOT_LOGIN = 0 or NOT_MOBILE_LOGIN = 0) and find_in_set('".$MY_ARRAY_DEPT[$I]."',DEPT_ID_OTHER)";
			$cursor_d = exequery( TD::conn( ), $query_d );
			while ( $ROWD = mysql_fetch_array( $cursor_d ) )
			{
				if ( !find_id( $USER_ID_STR, $ROWD['USER_ID'] ) )
				{
					$USER_ID_STR .= $ROWD['USER_ID'].",";
				}
			}
		}
	}
	$SEND_TIME = $CUR_TIME1;
	$USER_ID_STR_ARRAY = explode( ",", $USER_ID_STR );
	$USER_ID_STR_ARRAY_COUNT = sizeof( $USER_ID_STR_ARRAY );
	$I = 0;
	for ( ;	$I < $USER_ID_STR_ARRAY_COUNT;	++$I	)
	{
		if ( !( $USER_ID_STR_ARRAY[$I] == "" ) )
		{
			$FUNC_ID_STR = getfunmenubyuserid( $USER_ID_STR_ARRAY[$I] );
			if ( !find_id( $FUNC_ID_STR, 147 ) )
			{
				$USER_ID_STR = str_replace( $USER_ID_STR_ARRAY[$I], "", $USER_ID_STR );
			}
		}
	}
	if ( $SMS_REMIND == "on" )
	{
		$REMIND_URL = "1:news/show/read_news.php?NEWS_ID=".$NEWS_ID;
		$SMS_CONTENT = _( "请查看新闻！" )."\n"._( "标题：" ).csubstr( $SUBJECT, 0, 80 );
		if ( $SUMMARY )
		{
						$SMS_CONTENT .= "\n"._( "内容简介：" ).$SUMMARY;
		}
		if ( $USER_ID_STR != "" )
		{
						send_sms( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $USER_ID_STR, 14, $SMS_CONTENT, $REMIND_URL );
		}
		include_once( "inc/itask/itask.php" );
		mobile_push_notification( userid2uid( $USER_ID_STR ), $_SESSION['LOGIN_USER_NAME']._( "：" )._( "请查看新闻！" )._( "标题：" ).csubstr( $SUBJECT, 0, 20 ), "news" );
		$WX_OPTIONS = array(
						"module" => "news",
						"module_action" => "news.read",
						"user" => $USER_ID_STR,
						"content" => $_SESSION['LOGIN_USER_NAME']._( "：" )._( "请查看新闻！" )._( "标题：" ).csubstr( $SUBJECT, 0, 20 ),
						"params" => array(
										"NEWS_ID" => $NEWS_ID
						)
		);
		wxqy_sms( $WX_OPTIONS );
	}
	if ( $SMS2_REMIND == "on" )
	{
		$SMS_CONTENT = sprintf( _( "OA新闻,来自%s" ), $_SESSION['LOGIN_USER_NAME'].":".$SUBJECT );
		if ( $USER_ID_STR != "" )
		{
			send_mobile_sms_user( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $USER_ID_STR, $SMS_CONTENT, 14 );
		}
	}
}
if ( $OP == 0 )
{
	header( "location: modify.php?NEWS_ID=".$NEWS_ID."&start={$start}&IS_MAIN=1" );
}
else
{
	header( "location: index1.php?start=".$start."&IS_MAIN=1" );
}
echo "\r\n</body>\r\n</html>\r\n";
?>
