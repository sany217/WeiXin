<?php
include_once( "inc/auth.inc.php" );
include_once( "inc/utility_sms1.php" );
include_once( "inc/check_type.php" );
include_once( "inc/utility_all.php" );
include_once( "inc/utility_org.php" );
include_once( "inc/utility_file.php" );
include_once( "inc/utility_cache.php" );
include_once( "sql_inc.php" );
$HTML_PAGE_TITLE = _( "修改公告通知" );
include_once( "inc/header.inc.php" );
echo "\r\n\r\n<script type=\"text/javascript\" src=\"";
echo MYOA_JS_SERVER;
echo "/static/js/utility.js\"></script>\r\n<script  Language=\"JavaScript\">\r\n  function close_this()\r\n  {\r\n  \tif(window.opener)\r\n  \t{\r\n    window.opener.location.reload();\r\n  }\r\n    TJF_window_close();\r\n  }\r\n</script>\r\n\r\n<body class=\"bodycolor\">\r\n";
$POST_PRIV = getuserinfobyuid( $_SESSION['LOGIN_UID'], "POST_PRIV" );
$CUR_DATE = date( "Y-m-d", time( ) );
$BEGIN_DATE1 = $BEGIN_DATE;
if ( $BEGIN_DATE == "" )
{
				$BEGIN_DATE = $CUR_DATE;
				$BEGIN_DATE1 = $CUR_DATE;
}
if ( $END_DATE == "" )
{
				$END_DATE = "0000-00-00";
}
$BEGIN_DATE = strtotime( $BEGIN_DATE );
$END_DATE = strtotime( $END_DATE );
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
$CONTENT = strip_unsafe_tags( $CONTENT );
if ( $FORMAT == "2" )
{
				$CONTENT = $URL_ADD;
				if ( $IS_AU == 0 )
				{
								$AUDITER = "";
				}
}
$CONTENT = strip_unsafe_tags( $CONTENT );
if ( $PRINT == "on" )
{
				$PRINT = "1";
}
else
{
				$PRINT = "0";
}
if ( $DOWNLOAD == "on" )
{
				$DOWNLOAD = "1";
}
else
{
				$DOWNLOAD = "0";
}
if ( $TOP == "on" )
{
				$TOP = "1";
}
else
{
				$TOP = "0";
				$TOP_DAYS = "";
}
$CUR_TIME = date( "Y-m-d H:i:s", time( ) );
if ( $FORMAT != 2 )
{
				$CONTENT = stripslashes( $CONTENT );
				$COMPRESS_CONTENT = bin2hex( gzcompress( $CONTENT ) );
				$CONTENT = mysql_escape_string( strip_tags( $CONTENT ) );
				$UPDATE_DATA = "COMPRESS_CONTENT=0x".$COMPRESS_CONTENT.",";
				if ( $IS_AU == 0 )
				{
								$AUDITER = "";
				}
}
if ( $FROM == 2 )
{
				$PUBLISH_TMP = $PUBLISH;
				if ( $PUBLISH == 4 )
				{
								$PUBLISH = 1;
				}
				if ( $PUBLISH == 5 )
				{
								$PUBLISH = 2;
				}
				$AUDITER = $_SESSION['LOGIN_USER_ID'];
}
if ( $SEND_TIME == "" )
{
				$SEND_TIME = date( "Y-m-d H:i:s", time( ) );
}
$UPDATE_DATA .= "LAST_EDITOR='".$_SESSION['LOGIN_USER_ID'].( "',LAST_EDIT_TIME='".$CUR_TIME."',SEND_TIME='{$SEND_TIME}',TO_ID='{$TO_ID}',SUBJECT='{$SUBJECT}',SUMMARY='{$SUMMARY}',CONTENT='{$CONTENT}',BEGIN_DATE='{$BEGIN_DATE}',END_DATE='{$END_DATE}',ATTACHMENT_ID='{$ATTACHMENT_ID}',ATTACHMENT_NAME='{$ATTACHMENT_NAME}',PRINT='{$PRINT}',DOWNLOAD='{$DOWNLOAD}',FORMAT='{$FORMAT}',TOP='{$TOP}',TOP_DAYS='{$TOP_DAYS}',PRIV_ID='{$PRIV_ID}',USER_ID='{$COPY_TO_ID}',TYPE_ID='{$TYPE_ID}',PUBLISH='{$PUBLISH}',AUDITER='{$AUDITER}',READERS='',SUBJECT_COLOR='{$SUBJECT_COLOR}',KEYWORD='{$KEYWORD}'" );
$WHERE = "where NOTIFY_ID='".$NOTIFY_ID."'";
if ( $_SESSION['LOGIN_USER_PRIV'] != "1" && $POST_PRIV != "1" && $IS_AUDITING_EDIT != "1" )
{
				$WHERE .= " and FROM_ID='".$_SESSION['LOGIN_USER_ID']."'";
}
update_notify( $UPDATE_DATA, $WHERE );
if ( $READERS_OLD != "" )
{
				delete_reader( $NOTIFY_ID );
}
if ( isset( $PUBLISH_TMP ) )
{
				$PUBLISH = $PUBLISH_TMP;
}
if ( ( $PUBLISH == "1" || $PUBLISH == "4" || $PUBLISH_OLD == "1" ) && $OP != "0" )
{
				$USER_NAME = $_SESSION['LOGIN_USER_NAME'];
				$SMS_CONTENT = _( "请查看公告通知！" )."\n"._( "标题：" ).csubstr( $SUBJECT, 0, 100 );
				if ( $SUMMARY )
				{
								$SMS_CONTENT .= "\n"._( "内容简介：" ).$SUMMARY;
				}
				if ( compare_date( $BEGIN_DATE1, $CUR_DATE ) == 1 )
				{
								$SEND_TIME = $BEGIN_DATE1." 08:00:00";
				}
				else
				{
								$SEND_TIME = $CUR_TIME;
				}
				if ( $TO_ID == "ALL_DEPT" )
				{
								$query = "select USER_ID from USER where (NOT_LOGIN = 0 or NOT_MOBILE_LOGIN = 0)";
				}
				else
				{
								$query = "select USER_ID from USER where (NOT_LOGIN = 0 or NOT_MOBILE_LOGIN = 0) and (find_in_set(DEPT_ID,'".$TO_ID."') or find_in_set(USER_PRIV,'{$PRIV_ID}') or find_in_set(USER_ID,'{$COPY_TO_ID}'))";
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
				$USER_ID_STR_ARRAY = explode( ",", $USER_ID_STR );
				$USER_ID_STR_ARRAY_COUNT = sizeof( $USER_ID_STR_ARRAY );
				$I = 0;
				for ( ;	$I < $USER_ID_STR_ARRAY_COUNT;	++$I	)
				{
								if ( !( $USER_ID_STR_ARRAY[$I] == "" ) )
								{
												$FUNC_ID_STR = getfunmenubyuserid( $USER_ID_STR_ARRAY[$I] );
												if ( !find_id( $FUNC_ID_STR, 4 ) )
												{
																$USER_ID_STR = str_replace( $USER_ID_STR_ARRAY[$I], "", $USER_ID_STR );
												}
								}
				}
				if ( $PUBLISH == "4" )
				{
								$SUBJECT = str_replace( "'", "\\'", $SUBJECT );
								$REMIND_URL = "1:notify/manage/index.php";
								$SMS_CONTENT1 = sprintf( _( "您提交的公告通知，标题：%s被审人" ).$_SESSION['LOGIN_USER_NAME']._( "修改并批准" ), csubstr( $SUBJECT, 0, 100 ) );
								send_sms( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $FROM_ID, 1, $SMS_CONTENT1, $REMIND_URL );
				}
				$REMIND_URL = "1:notify/show/read_notify.php?NOTIFY_ID=".$NOTIFY_ID;
				if ( $SMS_REMIND1 == "on" && $USER_ID_STR != "" )
				{
								send_sms( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $USER_ID_STR, 1, $SMS_CONTENT, $REMIND_URL );
				}
				if ( $SMS2_REMIND1 == "on" )
				{
								$SMS_CONTENT = sprintf( _( "OA公告,来自%s标题:%s" ), $USER_NAME, $SUBJECT );
								if ( $SUMMARY )
								{
												$SMS_CONTENT .= _( "内容简介:" ).$SUMMARY;
								}
								if ( $USER_ID_STR != "" )
								{
												send_mobile_sms_user( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $USER_ID_STR, $SMS_CONTENT, 1 );
								}
				}
				include_once( "inc/itask/itask.php" );
				mobile_push_notification( userid2uid( $USER_ID_STR ), $_SESSION['LOGIN_USER_NAME']._( "：" )._( "请查看公告通知" )._( "标题：" ).csubstr( $SUBJECT, 0, 20 ), "notify" );
				$WX_OPTIONS = array(
								"module" => "notify",
								"module_action" => "notify.read",
								"user" => $USER_ID_STR,
								"content" => $_SESSION['LOGIN_USER_NAME']._( "：" )._( "请查看公告通知" )._( "标题：" ).csubstr( $SUBJECT, 0, 20 ),
								"params" => array(
												"NOTIFY_ID" => $NOTIFY_ID
								)
				);
				wxqy_sms( $WX_OPTIONS );
}
if ( $PUBLISH == "2" )
{
				$SMS_CONTENT = _( "请审批公告通知！" )."\n"._( "标题：" ).csubstr( $SUBJECT, 0, 100 );
				if ( compare_date( $BEGIN_DATE1, $CUR_DATE ) == 1 )
				{
								$SEND_TIME = $BEGIN_DATE1;
				}
				$REMIND_URL = "1:notify/auditing/unaudited.php";
				if ( $SMS_REMIND == "on" && $AUDITER != "" && $AUDITER != $_SESSION['LOGIN_USER_ID'] )
				{
								send_sms( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $AUDITER, 1, $SMS_CONTENT, $REMIND_URL );
				}
				if ( $SMS2_REMIND == "on" )
				{
								$SMS_CONTENT = sprintf( _( "请审批OA公告,来自%s" ), $_SESSION['LOGIN_USER_NAME'].":".$SUBJECT );
								if ( $SUMMARY )
								{
												$SMS_CONTENT .= _( "内容简介:" ).$SUMMARY;
								}
								if ( $AUDITER != "" && $AUDITER != $_SESSION['LOGIN_USER_ID'] )
								{
												send_mobile_sms_user( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $AUDITER, $SMS_CONTENT, 1 );
								}
				}
}
if ( $OP == "0" && ( $PUBLISH == "0" || $PUBLISH == "5" ) )
{
				if ( $OP1 == 1 )
				{
								header( "location: modify.php?NOTIFY_ID=".$NOTIFY_ID."&FROM={$FROM}" );
				}
				else
				{
								message( "", _( "公告保存成功！" ) );
				}
				echo "\t <br><center><input type=\"button\" value=\"";
				echo _( "返回" );
				echo "\" class=\"BigButton\" onClick=\"location.href='modify.php?NOTIFY_ID=";
				echo $NOTIFY_ID;
				echo "&FROM=";
				echo $FROM;
				echo "';\"><!--<br><center><input type=\"button\" value=\"";
				echo _( "关闭" );
				echo "\" class=\"BigButton\" onClick=\"close_this();\"></center>-->     \r\n";
}
if ( $PUBLISH == "5" && $OP1 != 1 )
{
				echo "<script>\r\n window.close();\r\n window.opener.location.reload();\t\r\n</script>\r\n";
}
if ( $OP != "0" )
{
				if ( $PUBLISH == "2" )
				{
								message( "", _( "公告已提交审批！" ) );
				}
				else
				{
								message( "", _( "公告发布成功！" ) );
				}
				if ( $PUBLISH == "4" )
				{
								echo "       <br><center><input type=\"button\" value=\"";
								echo _( "关闭" );
								echo "\" class=\"BigButton\" onClick=\"close_this();\"></center>   \r\n\r\n  ";
				}
				else
				{
								$HEAD_URL = "index1.php?start=".$start;
								echo "<br><center><input type=\"button\" value=\"";
								echo _( "返回" );
								echo "\" class=\"BigButton\" onClick=\"location.href='index1.php?start=";
								echo $start;
								echo "&IS_MAIN=1'\"></center> \r\n  <!--<br><center><input type=\"button\" value=\"";
								echo _( "关闭" );
								echo "\" class=\"BigButton\" onClick=\"close_this()\"></center>-->     \r\n";
				}
}
echo "</body>\r\n</html>\r\n";
?>
