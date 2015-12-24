<?php
/*********************/
/*                   */
/*  Version : 5.1.0  */
/*  Author  : RM     */
/*  Comment : 071223 */
/*                   */
/*********************/

include_once( "mobile/auth_mobi.php" );
include_once( "inc/utility_all.php" );
include_once( "inc/utility_file.php" );
include_once( "mobile/inc/funcs.php" );
include_once( "inc/utility_sms1.php" );
include_once( "inc/utility_cache.php" );
ob_clean( );
$TO_ID = td_iconv( $to_id, "utf-8", MYOA_CHARSET );
$CS_ID = td_iconv( $cs_id, "utf-8", MYOA_CHARSET );
$WEBMAIL = td_iconv( $webmail, "utf-8", MYOA_CHARSET );
$SUBJECT = td_iconv( $subject, "utf-8", MYOA_CHARSET );
$CONTENT = td_iconv( $content, "utf-8", MYOA_CHARSET );
$CONTENT = str_replace( "\n", "<br />\n", $CONTENT );
$CONTENT = str_replace( "\r", "<br />\r", $CONTENT );
$EMAIL_ID = intval( $EMAIL_ID );
$ATYPE = strip_tags( $ATYPE );
$SEND_TIME = time( );
if ( isset( $EMAIL_ID ) && $EMAIL_ID != "" )
{
				$query = "select * from EMAIL,EMAIL_BODY where EMAIL.BODY_ID=EMAIL_BODY.BODY_ID and EMAIL_ID='".$EMAIL_ID."'";
				$cursor = exequery( TD::conn( ), $query );
				if ( $ROW = mysql_fetch_array( $cursor ) )
				{
								$BODY_ID = $ROW['BODY_ID'];
								$FROM_ID = $ROW['FROM_ID'];
								$TO_ID1 = $ROW['TO_ID'];
								$TO_ID2 = $ROW['TO_ID2'];
								$COPY_TO_ID = $ROW['COPY_TO_ID'];
								$SECRET_TO_ID = $ROW['SECRET_TO_ID'];
								$SUBJECT1 = $ROW['SUBJECT'];
								$IMPORTANT = $ROW['IMPORTANT'];
								$SECRET_LEVEL = $ROW['SECRET_LEVEL'];
								$IS_WEBMAIL = $ROW['IS_WEBMAIL'];
								$COPY_TO_WEBMAIL = $ROW['COPY_TO_WEBMAIL'];
								$SECRET_TO_WEBMAIL = $ROW['SECRET_TO_WEBMAIL'];
								$CONTENT1 = $ROW['COMPRESS_CONTENT'] == "" ? $ROW['CONTENT'] : gzuncompress( $ROW['COMPRESS_CONTENT'] );
								$SEND_TIME1 = date( "Y-m-d H:i:s", $ROW['SEND_TIME'] );
								$ATTACHMENT_ID1 = $ROW['ATTACHMENT_ID'];
								$ATTACHMENT_NAME1 = $ROW['ATTACHMENT_NAME'];
								$FROM_NAME = getuserinfobyuid( userid2uid( $FROM_ID ), "USER_NAME" );
								if ( $TO_ID2 != "" )
								{
												$TO_ID_NAME = getuserinfobyuid( userid2uid( $TO_ID2 ), "USER_NAME" );
								}
								if ( $COPY_TO_ID != "" )
								{
												$COPY_TO_NAME = getuserinfobyuid( userid2uid( $COPY_TO_ID ), "USER_NAME" );
								}
								if ( $IS_WEBMAIL == 1 )
								{
												$querys = "select TO_MAIL,CC_MAIL,FROM_MAIL from WEBMAIL_BODY where BODY_ID='".$BODY_ID."'";
												$cursors = exequery( TD::conn( ), $querys );
												if ( $ROWs = mysql_fetch_array( $cursors ) )
												{
																$TO_ID_NAME = $ROWs['TO_MAIL'];
																$COPY_TO_NAME = $ROWs['CC_MAIL'];
																$FROM_NAME = $ROWs['FROM_MAIL'];
												}
								}
								$MSG1 .= "<br><br><div style='height:0px;border-bottom:1px #c0c2cf solid;margin:5px auto'></div>";
								$MSG1 .= "<div  style='padding:5px 15px;border-bottom:1px #cccccc solid;background:#edf6db;font-size:12px;'>";
								$MSG1 .= "<span style='line-height:16px;'><b>"._( "发件人：" )."</b>&nbsp;".$FROM_NAME."</span><br>";
								$MSG1 .= "<span style='line-height:16px;'><b>"._( "收件人：" )."</b>&nbsp;".$TO_ID_NAME."</span><br>";
								if ( $COPY_TO_NAME != "" )
								{
												$MSG1 .= "<span style='line-height:16px;'><b>"._( "抄送人：" )."</b>&nbsp;".$COPY_TO_NAME."</span><br>";
								}
								$MSG1 .= "<span style='line-height:16px;'><b>"._( "发送时间：" )."</b>&nbsp;".$SEND_TIME1."</span><br>";
								$MSG1 .= "<span style='line-height:16px;'><b>"._( "主题：" )."</b>&nbsp;".$SUBJECT1."</span><br>";
								$MSG1 .= "</div>";
								$CONTENT1 = "<div style='padding:10px 20px;'>".$CONTENT1."</div>";
								$MSG1 = $MSG1.$CONTENT1."<br>";
				}
}
if ( $ATYPE == "fw" || $ATYPE == "rp" || $ATYPE == "rp_all" || $ATYPE == "sfw" )
{
				$CONTENT = $CONTENT."<br>".$MSG1;
}
$ATTACHMENTS = mobile_upload( "", "email" );
$ATTACHMENT_ID = $ATTACHMENTS['ID'];
$ATTACHMENT_NAME = $ATTACHMENTS['NAME'];
$ATTACHMENT_NAME = td_iconv( urldecode( $ATTACHMENT_NAME ), "utf-8", MYOA_CHARSET );
if ( ( $ATYPE == "fw" || $ATYPE == "sfw" ) && $ATTACHMENT_ID1 != "" && $ATTACHMENT_NAME1 != "" )
{
				$ATTACHMENT_ID1 = copy_attach( $ATTACHMENT_ID1, $ATTACHMENT_NAME1, "", "", TRUE );
				if ( $ATTACHMENT_ID1 != "" )
				{
								$ATTACHMENT_ID1 .= ",";
				}
				$ATTACHMENT_ID .= $ATTACHMENT_ID1;
				$ATTACHMENT_NAME .= $ATTACHMENT_NAME1;
}
if ( $ATYPE == "mt" )
{
				$ATTACHMENT_ID_ARRAY = explode( ",", $ATTACHMENT_ID );
				$ATTACHMENT_NAME_ARRAY = explode( "*", $ATTACHMENT_NAME );
				$ARRAY_COUNT = sizeof( $ATTACHMENT_ID_ARRAY );
				$I = 0;
				for ( ;	$I < $ARRAY_COUNT;	++$I	)
				{
								if ( !( $ATTACHMENT_ID_ARRAY[$I] == "" ) )
								{
												$ATTACHMENT_IDe = $ATTACHMENT_ID_ARRAY[$I];
												$YM = substr( $ATTACHMENT_IDe, 0, strpos( $ATTACHMENT_IDe, "_" ) );
												if ( $YM )
												{
																$ATTACHMENT_IDe = substr( $ATTACHMENT_IDe, strpos( $ATTACHMENT_IDe, "_" ) + 1 );
												}
												$ATTACHMENT_ID_ENCODED = attach_id_encode( $ATTACHMENT_IDe, $ATTACHMENT_NAME_ARRAY[$I] );
												if ( is_image( $ATTACHMENT_NAME_ARRAY[$I] ) )
												{
																if ( $flag == 1 )
																{
																				$mt_url = "http://".$_SERVER[SERVER_ADDR].":".$_SERVER[SERVER_PORT]."/mobile/email/get_mtdata.php?rid=".$rid."&type=".$type."&flag=".$flag."&YM=".$YM."&ATTACHMENT_ID=".$ATTACHMENT_ID_ENCODED."&ATTACHMENT_NAME=".$ATTACHMENT_NAME_ARRAY[$I];
																				break;
																}
																else
																{
																				$attach_link .= "<img src=\"/inc/attach.php?MODULE=email&YM=".$YM."&ATTACHMENT_ID=".$ATTACHMENT_ID_ENCODED."&ATTACHMENT_NAME=".$ATTACHMENT_NAME_ARRAY[$I]."\" />";
																}
												}
								}
				}
				if ( $mt_url == "" )
				{
								$mt_url = "http://".$_SERVER[SERVER_ADDR].":".$_SERVER[SERVER_PORT]."/mobile/email/get_mtdata.php?rid=".$rid."&type=".$type."&flag=".$flag;
				}
				if ( $flag == 1 )
				{
								$info = file_get_contents( $mt_url );
				}
				else
				{
								$info = "<br><div id=\"container\">".$attach_link."</div>";
				}
				$CONTENT = $CONTENT."<br>".$info;
}
$WEBMAIL_CONTENT = mysql_escape_string( $CONTENT );
$CONTENT = stripslashes( $CONTENT );
$CONTENT_STRIP = strip_tags( $CONTENT );
$COMPRESS_CONTENT = bin2hex( gzcompress( $CONTENT ) );
$CONTENT_SIZE = strlen( $CONTENT );
$CONTENT_SIZE1 = strlen( $CONTENT_STRIP );
$COMPRESS_CONTENT_SIZE = strlen( $COMPRESS_CONTENT );
if ( $CONTENT_SIZE < $CONTENT_SIZE1 + $COMPRESS_CONTENT_SIZE )
{
				$CONTENT_STRIP = mysql_escape_string( $CONTENT );
				$COMPRESS_CONTENT = "0x".$COMPRESS_CONTENT;
}
else
{
				$CONTENT_STRIP = mysql_escape_string( $CONTENT_STRIP );
				$COMPRESS_CONTENT = "0x".$COMPRESS_CONTENT;
}
$IS_WEBMAIL1 = "0";
if ( $WEBMAIL != "" )
{
				$query = "SELECT * from WEBMAIL where USER_ID='".$_SESSION['LOGIN_USER_ID']."' and EMAIL_PASS!='' limit 1";
				$cursor = exequery( TD::conn( ), $query );
				if ( $ROW = mysql_fetch_array( $cursor ) )
				{
								$EMAIL = $ROW['EMAIL'];
								$FROM_WEBMAIL_ID = $ROW['MAIL_ID'];
				}
				else
				{
								echo _( "您没有定义Internet邮箱！" );
								exit( );
				}
				if ( $TO_ID == "" && $CS_ID == "" )
				{
								$IS_WEBMAIL1 = "1";
				}
				else
				{
								$IS_WEBMAIL1 = "0";
				}
}
else
{
				$FROM_WEBMAIL_ID = "";
				$EMAIL = "";
}
$query = "insert into EMAIL_BODY(FROM_ID,TO_ID2,COPY_TO_ID,SUBJECT,CONTENT,SEND_TIME,ATTACHMENT_ID,ATTACHMENT_NAME,SEND_FLAG,SMS_REMIND,FROM_WEBMAIL,TO_WEBMAIL,COMPRESS_CONTENT,WEBMAIL_CONTENT,FROM_WEBMAIL_ID,IS_WEBMAIL) values ('".$_SESSION['LOGIN_USER_ID'].( "','".$TO_ID."','{$CS_ID}','{$SUBJECT}','{$CONTENT_STRIP}','{$SEND_TIME}','{$ATTACHMENT_ID}','{$ATTACHMENT_NAME}','1','1','{$EMAIL}','{$WEBMAIL}',{$COMPRESS_CONTENT},compress('{$WEBMAIL_CONTENT}'),'{$FROM_WEBMAIL_ID}','{$IS_WEBMAIL1}')" );
exequery( TD::conn( ), $query );
$BODY_ID = mysql_insert_id( );
$IMPORTANT = "0";
if ( $WEBMAIL != "" )
{
				$query = "insert into EMAIL(TO_ID,READ_FLAG,DELETE_FLAG,BODY_ID) values ('__WEBMAIL__".$BODY_ID."','0','0','{$BODY_ID}')";
				exequery( TD::conn( ), $query );
				$result = proxy_mail( "1", $BODY_ID, $IMPORTANT );
}
$TO_ID .= ",".$CS_ID.",";
$TOK = strtok( $TO_ID, "," );
$strSEND = "";
while ( $TOK != "" )
{
				if ( $TOK == "" || find_id( $strSEND, $TOK ) )
				{
								$TOK = strtok( "," );
				}
				else
				{
								$strSEND .= $TOK.",";
								$query = "insert into EMAIL(TO_ID,READ_FLAG,DELETE_FLAG,BODY_ID) values ('".$TOK."','0','0','{$BODY_ID}')";
								exequery( TD::conn( ), $query );
								$ROW_ID = mysql_insert_id( );
								$REMIND_URL = "email/inbox/read_email/read_email.php?BOX_ID=0&BTN_CLOSE=1&FROM=1&EMAIL_ID=".$ROW_ID;
								$SMS_CONTENT = sprintf( _( "请查收我的邮件！" )."\n"._( "主题：%s" ), csubstr( $SUBJECT, 0, 100 ) );
								send_sms( "", $_SESSION['LOGIN_USER_ID'], $TOK, 2, $SMS_CONTENT, $REMIND_URL );
								$WX_NEED_USER_ID_ARR[] = $TOK;
								$TOK = strtok( "," );
				}
}
if ( $ATYPE == "fw" )
{
				$queryfw = "update EMAIL set IS_F='1' where EMAIL_ID='".$EMAIL_ID."'";
				exequery( TD::conn( ), $queryfw );
}
if ( $ATYPE == "rp" )
{
				$queryre = "update EMAIL set IS_R='1' where EMAIL_ID='".$EMAIL_ID."'";
				exequery( TD::conn( ), $queryre );
}
if ( 0 < count( $WX_NEED_USER_ID_ARR ) )
{
	include_once( "inc/itask/itask.php" );
	$WX_OPTIONS = array(
					"module" => "email",
					"module_action" => "email.read",
					"user" => $WX_NEED_USER_ID_ARR,
					"content" => $_SESSION['LOGIN_USER_NAME']._( "：" )._( "请查收我的邮件！" )._( "主题：" ).$SUBJECT,
					"params" => array(
									"BODY_ID" => $BODY_ID
					)
	);
	wxqy_sms( $WX_OPTIONS );
}
echo "OK";
exit( );
?>
