<?php
include_once( "../auth.php" );
include_once( "inc/utility_all.php" );
include_once( "inc/utility_sms1.php" );
ob_clean( );
$TO_ID = td_iconv( htmlspecialchars( $TO_ID ), "utf-8", MYOA_CHARSET );
$CS_ID = td_iconv( htmlspecialchars( $CS_ID ), "utf-8", MYOA_CHARSET );
$WEBMAIL = td_iconv( htmlspecialchars( $TO_NAME2 ), "utf-8", MYOA_CHARSET );
$SUBJECT = td_iconv( htmlspecialchars( $SUBJECT ), "utf-8", MYOA_CHARSET );
$CONTENT = td_iconv( htmlspecialchars( $CONTENT ), "utf-8", MYOA_CHARSET );
$SEND_TIME = time( );
if ( $WEBMAIL != "" )
{
				$query = "SELECT * from WEBMAIL where USER_ID='".$_SESSION['LOGIN_USER_ID']."' and EMAIL_PASS!='' limit 1";
				$cursor = exequery( TD::conn( ), $query );
				if ( $ROW = mysql_fetch_array( $cursor ) )
				{
								$EMAIL = $ROW['EMAIL'];
								$SMTP_SERVER = $ROW['SMTP_SERVER'];
								$LOGIN_TYPE = $ROW['LOGIN_TYPE'];
								$SMTP_PASS = $ROW['SMTP_PASS'];
								$SMTP_PORT = $ROW['SMTP_PORT'];
								$SMTP_SSL = $ROW['SMTP_SSL'] == "1" ? "ssl" : "";
								$EMAIL_PASS = $ROW['EMAIL_PASS'];
								$EMAIL_PASS = td_authcode( $EMAIL_PASS, "DECODE" );
								if ( $LOGIN_TYPE == "1" )
								{
												$SMTP_USER = substr( $EMAIL, 0, strpos( $EMAIL, "@" ) );
								}
								else
								{
												$SMTP_USER = $EMAIL;
								}
								if ( $SMTP_PASS == "yes" )
								{
												$SMTP_PASS = $EMAIL_PASS;
								}
								else
								{
												$SMTP_PASS = "";
								}
								$result = send_mail( $EMAIL, $WEBMAIL, $SUBJECT, $CONTENT, $SMTP_SERVER, $SMTP_USER, $SMTP_PASS, TRUE, $_SESSION['LOGIN_USER_NAME'], $REPLY_TO, $CC, $BCC, $ATTACHMENT, TRUE, $SMTP_PORT, $SMTP_SSL );
								if ( $result === TRUE )
								{
												echo _( "外部邮件发送成功" );
												exit( );
								}
								$query = "update EMAIL_BODY set SEND_FLAG='0' where BODY_ID=".intval( $BODY_ID );
								exequery( TD::conn( ), $query );
								echo _( "外部邮件发送失败" );
								exit( );
				}
				echo _( "您没有定义Internet邮箱！" );
				exit( );
}
if ( $TO_ID == "" )
{
				echo _( "无此OA用户！" );
				exit( );
}
$CONTENT = stripslashes( $CONTENT );
$CONTENT = str_replace( "\n", "<br>", $CONTENT );
$CONTENT = str_replace( "\r", "<br>", $CONTENT );
$CONTENT_STRIP = strip_tags( $CONTENT );
$COMPRESS_CONTENT = bin2hex( gzcompress( $CONTENT ) );
$CONTENT_SIZE = strlen( $CONTENT );
$CONTENT_SIZE1 = strlen( $CONTENT_STRIP );
$COMPRESS_CONTENT_SIZE = strlen( $COMPRESS_CONTENT );
if ( $CONTENT_SIZE < $CONTENT_SIZE1 + $COMPRESS_CONTENT_SIZE )
{
				$CONTENT_STRIP = mysql_escape_string( $CONTENT );
				$COMPRESS_CONTENT = "''";
}
else
{
				$CONTENT_STRIP = mysql_escape_string( $CONTENT_STRIP );
				$COMPRESS_CONTENT = "0x".$COMPRESS_CONTENT;
}
$query = "insert into EMAIL_BODY(FROM_ID,TO_ID2,COPY_TO_ID,SUBJECT,CONTENT,SEND_TIME,SEND_FLAG,SMS_REMIND,FROM_WEBMAIL,TO_WEBMAIL,COMPRESS_CONTENT) values ('".$_SESSION['LOGIN_USER_ID'].( "','".$TO_ID."','{$CS_ID}','{$SUBJECT}','{$CONTENT_STRIP}','{$SEND_TIME}','1','1','{$EMAIL}','{$WEBMAIL}',{$COMPRESS_CONTENT})" );
exequery( TD::conn( ), $query );
$BODY_ID = mysql_insert_id( );
$TO_ID .= $CS_ID.",";
$TOK = strtok( $TO_ID, "," );
while ( $TOK != "" )
{
				if ( $TOK == "" )
				{
								$TOK = strtok( "," );
				}
				else
				{
								$query = "insert into EMAIL(TO_ID,READ_FLAG,DELETE_FLAG,BODY_ID) values ('".$TOK."','0','0','{$BODY_ID}')";
								exequery( TD::conn( ), $query );
								$ROW_ID = mysql_insert_id( );
								$REMIND_URL = "email/inbox/read_email/read_email.php?BOX_ID=0&BTN_CLOSE=1&FROM=1&EMAIL_ID=".$ROW_ID;
								$SMS_CONTENT = sprintf( _( "请查收我的邮件！%s主题：" ), "\n" ).csubstr( $SUBJECT1, 0, 100 );
								send_sms( "", $_SESSION['LOGIN_USER_ID'], $TOK, 2, $SMS_CONTENT, $REMIND_URL );
								$WX_NEED_USER_ID_ARR[] = $TOK;
								$TOK = strtok( "," );
				}
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
echo _( "邮件发送成功" );
exit( );
?>
