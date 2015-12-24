<?php
include_once( "inc/auth.inc.php" );
include_once( "inc/utility_sms1.php" );
include_once( "inc/utility_sms2.php" );
include_once( "inc/check_type.php" );
include_once( "inc/utility_all.php" );
include_once( "inc/utility_org.php" );
include_once( "inc/utility_file.php" );
include_once( "inc/utility_cache.php" );
include_once( "sql_inc.php" );
$HTML_PAGE_TITLE = _( "发布公告通知" );
include_once( "inc/header.inc.php" );
echo "\r\n\r\n<script type=\"text/javascript\" src=\"";
echo MYOA_JS_SERVER;
echo "/static/js/utility.js\"></script>\r\n<script language=\"javascript\">\r\n  function close_this()\r\n  {\r\n  \tvar url_ole=window.opener.location.href;\r\n  \tvar url_search=window.opener.location.search;\r\n  \tif(url_ole.indexOf(\"?IS_MAIN=1\")>0 || url_search.indexOf(\"&IS_MAIN=1\")>0)\r\n  \t   window.opener.location.reload();\r\n  \telse\r\n  \t{\r\n  \t\t if(url_search==\"\")\r\n  \t\t   window.opener.location.href=url_ole+\"?IS_MAIN=1\";\r\n  \t\t esle\r\n  \t\t   window.opener.location.href=url_ole+\"&IS_MAIN=1\";\r\n  \t\t      \r\n  \t\t   \r\n  \t}   \r\n    //window.opener.location.reload();\r\n     TJF_window_close();\r\n\r\n  }\r\n </script>  \r\n\r\n<body class=\"bodycolor\">\r\n";
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
$SUBJECT1 = $SUBJECT;
$SEND_TIME = date( "Y-m-d H:i:s", time( ) );
$CONTENT = strip_unsafe_tags( $CONTENT );
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
if ( $IS_AU == 0 )
{
				$AUDITER = "";
}
if ( $FORMAT != 2 )
{
				$CONTENT = strip_unsafe_tags( $CONTENT );
				$CONTENT = stripslashes( $CONTENT );
				$COMPRESS_CONTENT = bin2hex( gzcompress( $CONTENT ) );
				$CONTENT = mysql_escape_string( strip_tags( $CONTENT ) );
				$DATA = "FROM_DEPT,FROM_ID,TO_ID,SUBJECT,SUMMARY,CONTENT,SEND_TIME,BEGIN_DATE,END_DATE,ATTACHMENT_ID,ATTACHMENT_NAME,PRINT,FORMAT,TOP,TOP_DAYS,PRIV_ID,USER_ID,TYPE_ID,PUBLISH,AUDITER,COMPRESS_CONTENT,DOWNLOAD,SUBJECT_COLOR,KEYWORD";
				$DATA_VALUE = "'".$_SESSION['LOGIN_DEPT_ID']."','".$_SESSION['LOGIN_USER_ID'].( "','".$TO_ID."','{$SUBJECT}','{$SUMMARY}','{$CONTENT}','{$SEND_TIME}','{$BEGIN_DATE}','{$END_DATE}','{$ATTACHMENT_ID}','{$ATTACHMENT_NAME}','{$PRINT}','{$FORMAT}','{$TOP}','{$TOP_DAYS}','{$PRIV_ID}','{$COPY_TO_ID}','{$TYPE_ID}','{$PUBLISH}','{$AUDITER}',0x{$COMPRESS_CONTENT},'{$DOWNLOAD}','{$SUBJECT_COLOR}','{$KEYWORD}'" );
				$NOTIFY_ID = insert_notify( $DATA, $DATA_VALUE );
}
else
{
				$DATA = "FROM_DEPT,FROM_ID,TO_ID,SUBJECT,SUMMARY,CONTENT,SEND_TIME,BEGIN_DATE,END_DATE,ATTACHMENT_ID,ATTACHMENT_NAME,PRINT,FORMAT,TOP,TOP_DAYS,PRIV_ID,USER_ID,TYPE_ID,PUBLISH,AUDITER,DOWNLOAD,SUBJECT_COLOR,KEYWORD";
				$DATA_VALUE = "'".$_SESSION['LOGIN_DEPT_ID']."','".$_SESSION['LOGIN_USER_ID'].( "','".$TO_ID."','{$SUBJECT}','{$SUMMARY}','{$URL_ADD}','{$SEND_TIME}','{$BEGIN_DATE}','{$END_DATE}','{$ATTACHMENT_ID}','{$ATTACHMENT_NAME}','{$PRINT}','{$FORMAT}','{$TOP}','{$TOP_DAYS}','{$PRIV_ID}','{$COPY_TO_ID}','{$TYPE_ID}','{$PUBLISH}','{$AUDITER}',',{$DOWNLOAD}','{$SUBJECT_COLOR}','{$KEYWORD}'" );
				$NOTIFY_ID = insert_notify( $DATA, $DATA_VALUE );
}
if ( $PUBLISH == "1" && $OP != "0" && ( $SMS_REMIND1 == "on" || $SMS2_REMIND1 == "on" ) )
{
				$SMS_CONTENT = _( "请查看公告通知！" )."\n"._( "标题：" ).csubstr( $SUBJECT1, 0, 100 );
				if ( $SUMMARY )
				{
								$SMS_CONTENT .= "\n"._( "内容简介：" ).$SUMMARY;
				}
				if ( compare_date( $BEGIN_DATE1, $CUR_DATE ) == 1 )
				{
								$SEND_TIME = $BEGIN_DATE1."08:00:00";
				}
				if ( $TO_ID == "ALL_DEPT" )
				{
								$query = "select USER_ID from USER where (NOT_LOGIN = 0 or NOT_MOBILE_LOGIN = 0) ";
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
				if ( $TO_ID != "ALL_DEPT" )
				{
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
				$REMIND_URL = "1:notify/show/read_notify.php?NOTIFY_ID=".$NOTIFY_ID;
				if ( $SMS_REMIND1 == "on" && $USER_ID_STR != "" )
				{
								send_sms( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $USER_ID_STR, 1, $SMS_CONTENT, $REMIND_URL );
				}
				if ( $SMS2_REMIND1 == "on" )
				{
								$SMS_CONTENT = sprintf( _( "OA公告,来自%s标题:%s" ), $_SESSION['LOGIN_USER_NAME'], $SUBJECT1 );
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
				mobile_push_notification( userid2uid( $USER_ID_STR ), $_SESSION['LOGIN_USER_NAME']._( "：" )._( "请查看公告通知" )._( "标题：" ).csubstr( $SUBJECT1, 0, 20 ), "notify" );
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
if ( $PUBLISH == "2" && ( $SMS_REMIND == "on" || $SMS2_REMIND == "on" ) )
{
				$SMS_CONTENT = _( "请审批公告通知！" )."\n"._( "标题：" ).csubstr( $SUBJECT1, 0, 100 );
				$REMIND_URL = "1:notify/auditing/unaudited.php";
				if ( $SMS_REMIND == "on" && $AUDITER != "" )
				{
								send_sms( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $AUDITER, 1, $SMS_CONTENT, $REMIND_URL );
				}
				if ( $SMS2_REMIND == "on" )
				{
								$SMS_CONTENT = sprintf( _( "请审批OA公告,来自%s标题:%s" ), $_SESSION['LOGIN_USER_NAME'], $SUBJECT1 );
								if ( $SUMMARY )
								{
												$SMS_CONTENT .= _( "内容简介:" ).$SUMMARY;
								}
								if ( $AUDITER != "" )
								{
												send_mobile_sms_user( $SEND_TIME, $_SESSION['LOGIN_USER_ID'], $AUDITER, $SMS_CONTENT, 1 );
								}
				}
}
if ( $OP == 0 )
{
				if ( $OP1 == 1 )
				{
								header( "location: modify.php?NOTIFY_ID=".$NOTIFY_ID."&FROM=1" );
				}
				else
				{
								message( "", _( "公告保存成功！" ) );
				}
				echo " <br><center><input type=\"button\" value=\"";
				echo _( "返回" );
				echo "\" class=\"BigButton\" onClick=\"location.href='modify.php?NOTIFY_ID=";
				echo $NOTIFY_ID;
				echo "&FROM=1';\"><!--<input type=\"button\" value=\"";
				echo _( "关闭" );
				echo "\" class=\"BigButton\" onClick=\"close_this();\">--></center>   \r\n ";
}
else
{
				if ( $PUBLISH == "2" )
				{
								message( "", _( "公告已提交审批！" ) );
				}
				else
				{
								message( "", _( "公告发布成功！" ) );
				}
				echo "   \r\n   <br><center><input type=\"button\" value=\"";
				echo _( "返回" );
				echo "\" class=\"BigButton\" onClick=\"location.href='index1.php?start=";
				echo $start;
				echo "&IS_MAIN=1'\"></center> \r\n   \r\n   <!--<br><center><input type=\"button\" value=\"";
				echo _( "关闭" );
				echo "\" class=\"BigButton\" onClick=\"close_this()\"></center> -->\r\n\t \r\n";
}
echo "</body>\r\n</html>\r\n";
?>
