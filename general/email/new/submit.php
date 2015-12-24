<?php
function email_fw_webmail_box( $USER_ID )
{
	$EMAIL_FW_WEBMAIL_BOX = "";
	$FROM_MAIL_ID = "";
	$FROM_WEBMAIL_BOX_DEFAULT = "";
	$EMAIL_FW_WEBMAIL_BOX_ARRAY = array( );
	$query = "select * from webmail where USER_ID='".$USER_ID."' and EMAIL_PASS!='' order by IS_DEFAULT desc";
	$cursor = exequery( TD::conn( ), $query );
	while ( $ROW = mysql_fetch_array( $cursor ) )
	{
		$MAIL_ID = $ROW['MAIL_ID'];
		$EMAIL = $ROW['EMAIL'];
		$IS_DEFAULT = $ROW['IS_DEFAULT'];
		$RECV_FW = $ROW['RECV_FW'];
		if ( $FROM_WEBMAIL_BOX_DEFAULT == "" )
		{
			$FROM_WEBMAIL_BOX_DEFAULT = $EMAIL;
			$FROM_MAIL_ID = $MAIL_ID;
		}
		if ( $RECV_FW == 1 )
		{
			$EMAIL_FW_WEBMAIL_BOX .= $EMAIL.",";
		}
	}
	$EMAIL_FW_WEBMAIL_BOX_ARRAY[] = $FROM_WEBMAIL_BOX_DEFAULT;
	$EMAIL_FW_WEBMAIL_BOX_ARRAY[] = $EMAIL_FW_WEBMAIL_BOX;
	$EMAIL_FW_WEBMAIL_BOX_ARRAY[] = $FROM_MAIL_ID;
	return $EMAIL_FW_WEBMAIL_BOX_ARRAY;
}

include_once( "inc/auth.inc.php" );
include_once( "inc/utility_all.php" );
include_once( "inc/utility_file.php" );
include_once( "inc/utility_org.php" );
include_once( "inc/utility_sms1.php" );
include_once( "inc/utility_sms2.php" );
include_once( "../check_capacity_tip.php" );
include_once( "inc/utility_cache.php" );
include_once( "inc/utility_email.php" );
include_once( "inc/td_core.php" );
include_once( "inc/utility_email_audit.php" );
$TO_WEBMAIL = str_replace( "(", "<", $TO_WEBMAIL );
$TO_WEBMAIL = str_replace( ")", ">", $TO_WEBMAIL );
$EXCLUDE_UID_STR = "";
$TO_ID = strip_tags( $TO_ID );
$COPY_TO_ID = strip_tags( $COPY_TO_ID );
$SECRET_TO_ID = strip_tags( $SECRET_TO_ID );
$TO_ID_MERGE = $TO_ID.$COPY_TO_ID.$SECRET_TO_ID;
if ( $EXCLUDE_UID_STR != "" )
{
	$EXCLUDE_USER_ID_STR = getuserinfobyuid( $EXCLUDE_UID_STR, "USER_ID" );
	$TO_ID = check_id( $EXCLUDE_USER_ID_STR, $TO_ID, FALSE );
	$COPY_TO_ID = check_id( $EXCLUDE_USER_ID_STR, $COPY_TO_ID, FALSE );
	$SECRET_TO_ID = check_id( $EXCLUDE_USER_ID_STR, $SECRET_TO_ID, FALSE );
	$TO_ID_MERGE2 = $TO_ID.$COPY_TO_ID.$SECRET_TO_ID;
	if ( $TO_ID_MERGE2 != "" )
	{
		$TO_ID_MERGE_NOT = check_id( $TO_ID_MERGE2, $TO_ID_MERGE, FALSE );
	}
	if ( $TO_ID_MERGE_NOT != "" )
	{
		$TO_NAME_NOT_STR = td_trim( getusernamebyid( $TO_ID_MERGE_NOT ) );
	}
	if ( $TO_ID_MERGE_NOT != "" )
	{
		if ( $TO_ID == "" )
		{
			$MSG1 = sprintf( _( "您不能给%s 发送邮件，不在其通讯范围内" ), $TO_NAME_NOT_STR );
			message( _( "提示" ), $MSG1 );
			echo "    \r\n   <center>\r\n      <input type=\"button\" value=\"";
			echo _( "返回" );
			echo "\" class=\"BigButton\" onClick=\"location='../outbox/?BOX_ID=0&FIELD=";
			echo $FIELD;
			echo "&ASC_DESC=";
			echo $ASC_DESC;
			echo "'\">\r\n   </center>\r\n   ";
			exit( );
		}
		$MSG2 = sprintf( _( "您不能给%s 发送邮件，不在其通讯范围内。其他用户邮件已发送。" ), $TO_NAME_NOT_STR );
		message( _( "提示" ), $MSG2 );
	}
}
$HTML_PAGE_TITLE = _( "发送邮件" );
include_once( "inc/header.inc.php" );
echo "\r\n\r\n\r\n<body class=\"bodycolor\">\r\n<div class=\"PageHeader\">\r\n   <div class=\"title\"></div>\r\n</div>\r\n";
if ( !( $BODY_ID != "" ) && $COPY_TIME != "" || $BODY_ID == 0 )
{
	$BODY_ID = "";
}
$NEW_SMS_HTML = "<object id='sms_sound' classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='".MYOA_JS_SERVER."/static/js/swflash.cab' width='0' height='0'><param name='movie' value='".MYOA_STATIC_SERVER."/static/wav/10.swf'><param name=quality value=high><embed id='sms_sound' src='".MYOA_STATIC_SERVER."/static/wav/10.swf' width='0' height='0' quality='autohigh' wmode='opaque' type='application/x-shockwave-flash' plugspace='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash'></embed></object>";
$SUBJECT = strip_tags( $SUBJECT );
if ( trim( $SUBJECT ) == "" )
{
	$SUBJECT = _( "[无主题]" );
}
if ( $SMS_REMIND == "on" )
{
	$SMS_REMIND = "1";
}
else
{
	$SMS_REMIND = "0";
}
$SEND_TIME = time( );
$BODY_OLD_ID = $BODY_ID;
$IS_WF = $IS_WF == "on" ? "1" : "0";
$EMAIL_STR = "";
if ( $IS_WF == 1 )
{
	$TO_ID_STR = $TO_ID.$COPY_TO_ID.$SECRET_TO_ID;
	$TOK_STR = strtok( $TO_ID_STR, "," );
	while ( $TOK_STR != "" )
	{
		if ( $TOK_STR == "" || find_id( $strSEND_STR, $TOK_STR ) )
		{
			$TOK_STR = strtok( "," );
		}
		else
		{
			$strSEND_STR .= $TOK_STR.",";
			$EMAIL = getuserinfobyuid( userid2uid( $TOK_STR ), "EMAIL" );
			if ( $EMAIL != "" )
			{
				$EMAIL_STR .= $EMAIL.",";
			}
			$TOK_STR = strtok( "," );
		}
	}
}
if ( $SEND_FLAG == 1 && $TO_WEBMAIL != "" )
{
	insert_to_address( $TO_WEBMAIL );
}
if ( $IS_WF == 1 && $EMAIL_STR != "" )
{
	$TO_WEBMAIL = $EMAIL_STR.$TO_WEBMAIL;
}
else
{
	$TO_WEBMAIL = $TO_WEBMAIL;
}
$TO_WEBMAIL = check_email( $TO_WEBMAIL );
$COPY_TO_WEBMAIL = check_email( $COPY_TO_WEBMAIL );
$SECRET_TO_WEBMAIL = check_email( $SECRET_TO_WEBMAIL );
list( $TO_WEBMAIL, $INVALID_WEBMAIL ) = $TO_WEBMAIL;
$COPY_TO_WEBMAIL = $COPY_TO_WEBMAIL[0];
$SECRET_TO_WEBMAIL = $SECRET_TO_WEBMAIL[0];
if ( $TO_WEBMAIL == "" && $COPY_TO_WEBMAIL == "" && $SECRET_TO_WEBMAIL == "" )
{
	$FROM_WEBMAIL_ID = 0;
	$FROM_WEBMAIL = "";
	$WEBMAIL_CONTENT = "";
}
else
{
	$FROM_WEBMAIL_ID = substr( $FROM_WEBMAIL, 0, strpos( $FROM_WEBMAIL, "," ) );
	$WEBMAIL_CONTENT = mysql_escape_string( $CONTENT );
	$query = "SELECT MAIL_ID FROM WEBMAIL WHERE EMAIL = '".$FROM_WEBMAIL."' AND USER_ID = '".$_SESSION['LOGIN_USER_ID']."'";
	$cursor = exequery( TD::conn( ), $query );
	if ( $rs = mysql_fetch_array( $cursor ) )
	{
		$FROM_WEBMAIL_ID = $rs['MAIL_ID'];
	}
}
$RECEIPT = $RECEIPT == "on" ? "1" : "0";
if ( 1 < count( $_FILES ) )
{
	$ATTACHMENTS = upload( "ATTACHMENT", "", FALSE );
	if ( is_string( $ATTACHMENTS ) && $ATTACHMENTS != "" )
	{
		message( _( "错误" ), $ATTACHMENTS );
		$CONTENT = stripslashes( $CONTENT );
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
		if ( $BODY_ID == "" )
		{
						$query = "INSERT INTO EMAIL_BODY(FROM_ID,TO_ID2,COPY_TO_ID,SECRET_TO_ID,SUBJECT,CONTENT,SEND_TIME,ATTACHMENT_ID,ATTACHMENT_NAME,SEND_FLAG,SMS_REMIND,IMPORTANT,SIZE,FROM_WEBMAIL_ID,FROM_WEBMAIL,TO_WEBMAIL,COMPRESS_CONTENT,WEBMAIL_CONTENT,IS_WEBMAIL,IS_WF,COPY_TO_WEBMAIL,SECRET_TO_WEBMAIL) values ('".$_SESSION['LOGIN_USER_ID'].( "','".$TO_ID."','{$COPY_TO_ID}','{$SECRET_TO_ID}','{$SUBJECT}','{$CONTENT_STRIP}','{$SEND_TIME}','{$ATTACHMENT_ID}','{$ATTACHMENT_NAME}',0,'{$SMS_REMIND}','{$IMPORTANT}','{$SIZE}','{$FROM_WEBMAIL_ID}','{$FROM_WEBMAIL}','{$TO_WEBMAIL}',{$COMPRESS_CONTENT},compress('{$WEBMAIL_CONTENT}'),'{$IS_WEBMAIL}','{$IS_WF}','{$COPY_TO_WEBMAIL}','{$SECRET_TO_WEBMAIL}')" );
		}
		else
		{
						$BODY_ID = intval( $BODY_ID );
						$query = "UPDATE EMAIL_BODY set FROM_ID='".$_SESSION['LOGIN_USER_ID'].( "',TO_ID2='".$TO_ID."',COPY_TO_ID='{$COPY_TO_ID}',SECRET_TO_ID='{$SECRET_TO_ID}',SUBJECT='{$SUBJECT}',CONTENT='{$CONTENT_STRIP}',SEND_TIME='{$SEND_TIME}',SEND_FLAG=0,SMS_REMIND='{$SMS_REMIND}',IMPORTANT='{$IMPORTANT}',SIZE='{$SIZE}',FROM_WEBMAIL_ID='{$FROM_WEBMAIL_ID}',FROM_WEBMAIL='{$FROM_WEBMAIL}',TO_WEBMAIL='{$TO_WEBMAIL}',COMPRESS_CONTENT={$COMPRESS_CONTENT},WEBMAIL_CONTENT=compress('{$WEBMAIL_CONTENT}'),IS_WEBMAIL = '{$IS_WEBMAIL}',IS_WF='{$IS_WF}',COPY_TO_WEBMAIL='{$COPY_TO_WEBMAIL}',SECRET_TO_WEBMAIL='{$SECRET_TO_WEBMAIL}' where BODY_ID='{$BODY_ID}'" );
		}
		exequery( TD::conn( ), $query );
		if ( $BODY_ID == "" )
		{
						$BODY_ID = mysql_insert_id( );
						if ( $BODY_ID == "" || $BODY_ID == 0 )
						{
										message( _( "错误" ), _( "邮件发送错误，请重新发送！" ) );
										exit( );
						}
		}
		message( _( "提示" ), _( "由于附件未上传成功，邮件已保存到草稿箱,请重新发送！" ) );
		echo "\t   \t<center>\r\n\t   <input type=\"button\" id=\"close4\"  value=\"";
		echo _( "查看草稿" );
		echo "\"  class=\"BigButton\" onClick=\"window.location='index.php?BODY_ID=";
		echo $BODY_ID;
		echo "&BTN_CLOSE=";
		echo $BTN_CLOSE;
		echo "'\" title=\"";
		echo _( "返回该封邮件继续写信" );
		echo "\">\r\n\t\t<input type=\"button\" id=\"close3\"  value=\"";
		echo _( "关闭" );
		echo "\"  class=\"BigButton\" onClick=\"window.close();\" title=\"";
		echo _( "关闭窗口" );
		echo "\">\r\n\t\t<input type=\"button\" id=\"back3\"  value=\"";
		echo _( "继续写信" );
		echo "\"  class=\"BigButton\" onClick=\"window.location='../new/'\" title=\"";
		echo _( "返回" );
		echo "\">\r\n\t\t</center>\r\n\t\t<script>\r\n\t\tif(window.parent==self)\r\n\t\t{\r\n\t\t\tdocument.getElementById('back3').style.display=\"none\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tdocument.getElementById('close3').style.display=\"none\"; \r\n\t\t}\r\n\r\n\t\tif(parent && typeof(parent.getBoxCount) == 'function')\r\n\t\t{\r\n\t\t   var tmp = parent.getBoxCount('outbox');\r\n\t\t   parent.setBoxCount('outbox',tmp+1);\r\n\t\t}\r\n\t\t</script>\r\n\t";
		exit( );
	}
	if ( substr( $ATTACHMENT_ID_OLD, -1 ) != "," && td_trim( $ATTACHMENTS['ID'] ) != "" )
	{
					$ATTACHMENT_ID_OLD .= ",";
	}
	if ( substr( $ATTACHMENT_NAME_OLD, -1 ) != "*" && substr( $ATTACHMENTS['NAME'], 1 ) != "*" )
	{
					$ATTACHMENT_NAME_OLD .= "*";
	}
	$CONTENT = replaceimagesrc( $CONTENT, $ATTACHMENTS );
	$ATTACHMENT_ID = $ATTACHMENT_ID_OLD.$ATTACHMENTS['ID'];
	$ATTACHMENT_NAME = $ATTACHMENT_NAME_OLD.$ATTACHMENTS['NAME'];
}
else
{
				$ATTACHMENT_ID = $ATTACHMENT_ID_OLD;
				$ATTACHMENT_NAME = $ATTACHMENT_NAME_OLD;
}
$CONTENT = strip_unsafe_tags( $CONTENT );
$C = preg_match( "/<img.*?\\ssrc=\\\\\"\\/inc\\/attach.php\\?(.*)MODULE=upload_temp/i", $CONTENT );
$CONTENT = replace_attach_url( $CONTENT );
$CENSOR_SUBJECT = censor( $SUBJECT, "0" );
$CENSOR_CONTENT = censor( strip_tags( $CONTENT ), "0" );
if ( $CENSOR_SUBJECT == "BANNED" || $CENSOR_CONTENT == "BANNED" )
{
				button_back( );
				exit( );
}
if ( $CENSOR_SUBJECT == "MOD" || $CENSOR_CONTENT == "MOD" )
{
				$SEND_FLAG = "0";
				$CENSOR_FLAG = 1;
}
$ATTACHMENT_ID .= copy_sel_attach( $ATTACH_NAME, $ATTACH_DIR, $DISK_ID );
$ATTACHMENT_NAME .= $ATTACH_NAME;
if ( $C == 1 )
{
				$ATTACHMENT_ID = move_attach( $ATTACHMENT_ID, $ATTACHMENT_NAME, "", "", TRUE ).",";
}
if ( substr( $ATTACHMENT_NAME, 0, 1 ) == "*" )
{
				$ATTACHMENT_NAME = substr( $ATTACHMENT_NAME, 1 );
}
if ( substr( $ATTACHMENT_ID, 0, 1 ) == "," )
{
				$ATTACHMENT_ID = substr( $ATTACHMENT_ID, 1 );
}
if ( td_trim( $ATTACHMENT_ID ) != "" )
{
				$ATTACHMENT_ID_ARRAY = explode( ",", $ATTACHMENT_ID );
				$ATTACHMENT_NAME_ARRAY = explode( "*", $ATTACHMENT_NAME );
				$I = 0;
				for ( ;	$I < sizeof( $ATTACHMENT_ID_ARRAY ) - 1;	++$I	)
				{
								$SIZE += attach_size( $ATTACHMENT_ID_ARRAY[$I], $ATTACHMENT_NAME_ARRAY[$I] );
				}
}
$CONTENT = stripslashes( $CONTENT );
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
if ( $SEND_FLAG == "1" )
{
	$email_audit_flag = check_email_audit( 3 );
	if ( $email_audit_flag )
	{
		$PARA_ARRAY = get_sys_para( "EMAIL_FREE_AUDIT_MAN" );
		$EMAIL_FREE_AUDIT_MAN = $PARA_ARRAY['EMAIL_FREE_AUDIT_MAN'];
		if ( !find_id( $EMAIL_FREE_AUDIT_MAN, $_SESSION['LOGIN_USER_ID'] ) )
		{
						$SEND_FLAG = 2;
		}
		else
		{
						$SEND_FLAG = 1;
		}
	}
	$SUBJECT1 = $SUBJECT;
	$TO_ID2 = $TO_ID;
	$TO_ID_STR = $TO_ID.$COPY_TO_ID.$SECRET_TO_ID;
	$IS_WEBMAIL = td_trim( $TO_ID_STR ) == "" && $TO_WEBMAIL != "" ? "1" : "0";
	if ( td_trim( $TO_ID_STR ) == "" && $TO_WEBMAIL == "" )
	{
					message( _( "提示" ), _( "内外部收件人不能同时为空" ), "", $BUTTON_BACK );
					exit( );
	}
	if ( $ATTACHMENT_ID != "" && substr( $ATTACHMENT_ID, 0, 1 ) == "," )
	{
					$ATTACHMENT_ID = substr( $ATTACHMENT_ID, 1 );
	}
	if ( $BODY_ID == "" )
	{
					$query = "INSERT INTO EMAIL_BODY(FROM_ID,TO_ID2,COPY_TO_ID,SECRET_TO_ID,SUBJECT,CONTENT,SEND_TIME,ATTACHMENT_ID,ATTACHMENT_NAME,SEND_FLAG,SMS_REMIND,IMPORTANT,SIZE,FROM_WEBMAIL_ID,FROM_WEBMAIL,TO_WEBMAIL,COMPRESS_CONTENT,WEBMAIL_CONTENT,IS_WEBMAIL,IS_WF,SECRET_LEVEL,COPY_TO_WEBMAIL,SECRET_TO_WEBMAIL) values ('".$_SESSION['LOGIN_USER_ID'].( "','".$TO_ID."','{$COPY_TO_ID}','{$SECRET_TO_ID}','{$SUBJECT}','{$CONTENT_STRIP}','{$SEND_TIME}','{$ATTACHMENT_ID}','{$ATTACHMENT_NAME}','{$SEND_FLAG}','{$SMS_REMIND}','{$IMPORTANT}','{$SIZE}','{$FROM_WEBMAIL_ID}','{$FROM_WEBMAIL}','{$TO_WEBMAIL}',{$COMPRESS_CONTENT},compress('{$WEBMAIL_CONTENT}'),'{$IS_WEBMAIL}','{$IS_WF}','{$SECRET_LEVEL}','{$COPY_TO_WEBMAIL}','{$SECRET_TO_WEBMAIL}')" );
	}
	else
	{
					$BODY_ID = intval( $BODY_ID );
					$query = "UPDATE EMAIL_BODY set FROM_ID='".$_SESSION['LOGIN_USER_ID'].( "',TO_ID2='".$TO_ID."',COPY_TO_ID='{$COPY_TO_ID}',SECRET_TO_ID='{$SECRET_TO_ID}',SUBJECT='{$SUBJECT}',CONTENT='{$CONTENT_STRIP}',SEND_TIME='{$SEND_TIME}',ATTACHMENT_ID='{$ATTACHMENT_ID}',ATTACHMENT_NAME='{$ATTACHMENT_NAME}',SEND_FLAG='{$SEND_FLAG}',SMS_REMIND='{$SMS_REMIND}',IMPORTANT='{$IMPORTANT}',SIZE='{$SIZE}',FROM_WEBMAIL_ID='{$FROM_WEBMAIL_ID}',FROM_WEBMAIL='{$FROM_WEBMAIL}',TO_WEBMAIL='{$TO_WEBMAIL}',COMPRESS_CONTENT={$COMPRESS_CONTENT},WEBMAIL_CONTENT=compress('{$WEBMAIL_CONTENT}'),IS_WEBMAIL = '{$IS_WEBMAIL}',IS_WF='{$IS_WF}',SECRET_LEVEL='{$SECRET_LEVEL}',COPY_TO_WEBMAIL='{$COPY_TO_WEBMAIL}',SECRET_TO_WEBMAIL='{$SECRET_TO_WEBMAIL}' where BODY_ID='{$BODY_ID}'" );
	}
	exequery( TD::conn( ), $query );
	if ( $BODY_ID == "" )
	{
					$BODY_ID = mysql_insert_id( );
					if ( $BODY_ID == "" || $BODY_ID == 0 )
					{
									message( _( "错误" ), _( "邮件发送错误，请重新发送！" ) );
									exit( );
					}
	}
	if ( $SEND_FLAG == 2 )
	{
					$Audit_STR = getdeptauditmansbydeptid( $_SESSION['LOGIN_DEPT_ID'] );
					$REMIND_URL = "email/audit/read_email.php?BODY_ID=".$BODY_ID."&FIELD=&ASC_DESC=&BTN_CLOSE=1&EMAIL_ID=".$ROW_ID;
					$SMS_CONTENT = _( "请审核我的邮件！" )."\n"._( "主题：" ).csubstr( $SUBJECT1, 0, 100 );
					send_sms( "", $_SESSION['LOGIN_USER_ID'], $Audit_STR, 2, $SMS_CONTENT, $REMIND_URL );
	}
	$TOK = strtok( $TO_ID_STR, "," );
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
			if ( $BODY_ID == "" || $BODY_ID == 0 )
			{
				message( _( "错误" ), _( "邮件发送错误，请重新发送！" ) );
				exit( );
			}
			if ( $SEND_FLAG == 1 )
			{
				$query = "insert into EMAIL(TO_ID,READ_FLAG,DELETE_FLAG,BODY_ID,RECEIPT) values ('".$TOK."','0','0','{$BODY_ID}','{$RECEIPT}')";
				exequery( TD::conn( ), $query );
				$ROW_ID = mysql_insert_id( );
				if ( $SMS_REMIND == "1" )
				{
								$REMIND_URL = "email/inbox/read_email/read_email.php?BOX_ID=0&BTN_CLOSE=1&FROM=1&EMAIL_ID=".$ROW_ID;
								$SMS_CONTENT = _( "请查收我的邮件！" )."\n"._( "主题：" ).csubstr( $SUBJECT1, 0, 100 );
								send_sms( "", $_SESSION['LOGIN_USER_ID'], $TOK, 2, $SMS_CONTENT, $REMIND_URL );
				}
				$WX_NEED_USER_ID_ARR[] = $TOK;
				include_once( "inc/itask/itask.php" );
				mobile_push_notification( userid2uid( $TOK ), $_SESSION['LOGIN_USER_NAME']._( "：" )._( "请查收我的邮件！" )._( "主题：" ).csubstr( $SUBJECT1, 0, 20 ), "email" );
			}
			$EMAIL_FW_WEBMAIL_BOX_ARRAY = email_fw_webmail_box( $TOK );
			if ( $EMAIL_FW_WEBMAIL_BOX_ARRAY[1] != "" && $EMAIL_FW_WEBMAIL_BOX_ARRAY[0] != "" && $SEND_FLAG == 1 )
			{
				$WEBMAIL_CONTENT_FW = mysql_escape_string( $CONTENT );
				$FROM_WEBMAIL1 = $EMAIL_FW_WEBMAIL_BOX_ARRAY[0];
				$TO_EMAIL_FW_WEBMAIL_BOX = $EMAIL_FW_WEBMAIL_BOX_ARRAY[1];
				if ( $ATTACHMENT_ID != "" && $ATTACHMENT_NAME != "" )
				{
					$ATTACHMENT_ID = copy_attach( $ATTACHMENT_ID, $ATTACHMENT_NAME, "", "", TRUE );
					if ( $ATTACHMENT_ID != "" )
					{
									$ATTACHMENT_ID .= ",";
					}
					$ATTACHMENT_ID_ARRAY = explode( ",", $ATTACHMENT_ID );
					$ATTACHMENT_NAME_ARRAY = explode( "*", $ATTACHMENT_NAME );
					$I = 0;
					for ( ;	$I < sizeof( $ATTACHMENT_ID_ARRAY ) - 1;	++$I	)
					{
									$SIZE += attach_size( $ATTACHMENT_ID_ARRAY[$I], $ATTACHMENT_NAME_ARRAY[$I] );
					}
				}
				$SUBJECT = $_SESSION['LOGIN_USER_NAME'].":".$SUBJECT;
				$querys = "INSERT INTO EMAIL_BODY(FROM_ID,SUBJECT,CONTENT,SEND_TIME,ATTACHMENT_ID,ATTACHMENT_NAME,SEND_FLAG,SMS_REMIND,IMPORTANT,SIZE,FROM_WEBMAIL_ID,FROM_WEBMAIL,TO_WEBMAIL,COMPRESS_CONTENT,WEBMAIL_CONTENT,IS_WEBMAIL,SECRET_LEVEL) values ('".$TOK."','{$SUBJECT}','{$CONTENT_STRIP}','{$SEND_TIME}','{$ATTACHMENT_ID}','{$ATTACHMENT_NAME}','{$SEND_FLAG}','{$SMS_REMIND}','{$IMPORTANT}','{$SIZE}','{$EMAIL_FW_WEBMAIL_BOX_ARRAY['2']}','{$FROM_WEBMAIL1}','{$TO_EMAIL_FW_WEBMAIL_BOX}',{$COMPRESS_CONTENT},compress('{$WEBMAIL_CONTENT_FW}'),'1','{$SECRET_LEVEL}')";
				exequery( TD::conn( ), $querys );
				$BODY_ID_FW = mysql_insert_id( );
				if ( $ATTACHMENT_ID != "" )
				{
					$ATTACHMENT_ID_ARRAY = explode( ",", $ATTACHMENT_ID );
					$ATTACHMENT_NAME_ARRAY = explode( "*", $ATTACHMENT_NAME );
					$ARRAY_COUNT = sizeof( $ATTACHMENT_ID_ARRAY );
					$I = 0;
					for ( ;	$I < $ARRAY_COUNT;	++$I	)
					{
						if ( !( $ATTACHMENT_ID_ARRAY[$I] == "" ) )
						{
							if ( $ATTACHMENT_NAME_ARRAY[$I] == "" )
							{
											break;
							}
						}
						else
						{
										continue;
						}
						$filename = attach_real_path( $ATTACHMENT_ID_ARRAY[$I], $ATTACHMENT_NAME_ARRAY[$I] );
						if ( !$filename )
						{
						}
						else
						{
										$tmp_filename = $filename.".tdecrypt";
										decrypt_attach( $filename, $tmp_filename );
						}
					}
				}
				if ( $SEND_FLAG == 1 )
				{
					proxy_mail( "1", $BODY_ID_FW, $IMPORTANT );
					$queryw = "insert into EMAIL(TO_ID,READ_FLAG,DELETE_FLAG,BODY_ID,RECEIPT) values ('__WEBMAIL__".$BODY_ID_FW."','0','0','{$BODY_ID_FW}','{$RECEIPT}')";
					exequery( TD::conn( ), $queryw );
				}
			}
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
						"content" => $_SESSION['LOGIN_USER_NAME']._( "：" )._( "请查收我的邮件！" )._( "主题：" ).$SUBJECT1,
						"params" => array(
										"BODY_ID" => $BODY_ID
						)
		);
		wxqy_sms( $WX_OPTIONS );
	}
	if ( ( $TO_WEBMAIL != "" || $COPY_TO_WEBMAIL != "" || $SECRET_TO_WEBMAIL != "" ) && $SEND_FLAG == 1 )
	{
					$query = "insert into EMAIL(TO_ID,READ_FLAG,DELETE_FLAG,BODY_ID,RECEIPT) values ('__WEBMAIL__".$BODY_ID."','0','0','{$BODY_ID}','{$RECEIPT}')";
					exequery( TD::conn( ), $query );
	}
	if ( $SMS2_REMIND == "on" && $SEND_FLAG == 1 )
	{
					$MSG3 = sprintf( _( "OA邮件,来自%s:%s" ), $_SESSION['LOGIN_USER_NAME'], $SUBJECT1 );
					$SMS_CONTENT = $MSG3;
					send_mobile_sms_user( "", $_SESSION['LOGIN_USER_ID'], $TO_ID_STR, $SMS_CONTENT, 2 );
	}
	if ( $IS_R == 1 )
	{
					$query = "update EMAIL set IS_R='".$IS_R."' where EMAIL_ID='{$EMAIL_ID}'";
					exequery( TD::conn( ), $query );
	}
	if ( $IS_F == 1 )
	{
					$query = "update EMAIL set IS_F='".$IS_F."' where EMAIL_ID='{$EMAIL_ID}'";
					exequery( TD::conn( ), $query );
	}
	if ( ( $TO_WEBMAIL != "" || $COPY_TO_WEBMAIL != "" || $SECRET_TO_WEBMAIL != "" ) && $FROM_WEBMAIL != "" )
	{
		if ( $ATTACHMENT_ID != "" )
		{
			$ATTACHMENT_ID_ARRAY = explode( ",", $ATTACHMENT_ID );
			$ATTACHMENT_NAME_ARRAY = explode( "*", $ATTACHMENT_NAME );
			$ARRAY_COUNT = sizeof( $ATTACHMENT_ID_ARRAY );
			$I = 0;
			for ( ;	$I < $ARRAY_COUNT;	++$I	)
			{
				if ( !( $ATTACHMENT_ID_ARRAY[$I] == "" ) )
				{
								if ( $ATTACHMENT_NAME_ARRAY[$I] == "" )
								{
												break;
								}
				}
				else
				{
								continue;
				}
				$filename = attach_real_path( $ATTACHMENT_ID_ARRAY[$I], $ATTACHMENT_NAME_ARRAY[$I] );
				if ( !$filename )
				{
				}
				else
				{
								$tmp_filename = $filename.".tdecrypt";
								decrypt_attach( $filename, $tmp_filename );
				}
			}
		}
		if ( $SEND_FLAG == 1 )
		{
			$result = proxy_mail( "1", $BODY_ID, $IMPORTANT );
			if ( $result === FALSE )
			{
				message( _( "外部邮件发送失败" ), socket_strerror( socket_last_error( ) ) );
				exit( );
			}
			if ( $INVALID_WEBMAIL == "" )
			{
				echo $NEW_SMS_HTML;
				if ( $TO_ID == "" )
				{
					message( _( "提示" ), _( "外部邮件已提交至后台发送" ), "", array(
									array(
													"value" => _( "查看发送状态" ),
													"href" => "../sentbox"
									)
					) );
					exit( );
				}
				message( _( "提示" ), _( "内部邮件发送成功，外部邮件已提交至后台发送" ), "", array(
								array(
												"value" => _( "查看发送状态" ),
												"href" => "../sentbox"
								)
				) );
				exit( );
			}
			$MSG4 = sprintf( _( "邮件地址 %s 无效，其它外部邮件已提交至后台发送" ), $INVALID_WEBMAIL );
			message( _( "提示" ), $MSG4 );
			exit( );
		}
	}
}
else
{
	if ( $BODY_ID == "" )
	{
		$query = "insert into EMAIL_BODY(FROM_ID,TO_ID2,COPY_TO_ID,SECRET_TO_ID,SUBJECT,CONTENT,SEND_TIME,ATTACHMENT_ID,ATTACHMENT_NAME,SEND_FLAG,SMS_REMIND,IMPORTANT,SIZE,FROM_WEBMAIL_ID,FROM_WEBMAIL,TO_WEBMAIL,COMPRESS_CONTENT,WEBMAIL_CONTENT,IS_WF,SECRET_LEVEL,COPY_TO_WEBMAIL,SECRET_TO_WEBMAIL) values ('".$_SESSION['LOGIN_USER_ID'].( "','".$TO_ID."','{$COPY_TO_ID}','{$SECRET_TO_ID}','{$SUBJECT}','{$CONTENT_STRIP}','{$SEND_TIME}','{$ATTACHMENT_ID}','{$ATTACHMENT_NAME}','{$SEND_FLAG}','{$SMS_REMIND}','{$IMPORTANT}','{$SIZE}','{$FROM_WEBMAIL_ID}','{$FROM_WEBMAIL}','{$TO_WEBMAIL}',{$COMPRESS_CONTENT},compress('{$WEBMAIL_CONTENT}'),'{$IS_WF}','{$SECRET_LEVEL}','{$COPY_TO_WEBMAIL}','{$SECRET_TO_WEBMAIL}')" );
	}
	else
	{
		$query = "update EMAIL_BODY set FROM_ID='".$_SESSION['LOGIN_USER_ID'].( "',TO_ID2='".$TO_ID."',COPY_TO_ID='{$COPY_TO_ID}',SECRET_TO_ID='{$SECRET_TO_ID}',SUBJECT='{$SUBJECT}',CONTENT='{$CONTENT_STRIP}',SEND_TIME='{$SEND_TIME}',ATTACHMENT_ID='{$ATTACHMENT_ID}',ATTACHMENT_NAME='{$ATTACHMENT_NAME}',SEND_FLAG='{$SEND_FLAG}',SMS_REMIND='{$SMS_REMIND}',IMPORTANT='{$IMPORTANT}',SIZE='{$SIZE}',FROM_WEBMAIL_ID='{$FROM_WEBMAIL_ID}',FROM_WEBMAIL='{$FROM_WEBMAIL}',TO_WEBMAIL='{$TO_WEBMAIL}',COMPRESS_CONTENT={$COMPRESS_CONTENT},WEBMAIL_CONTENT=compress('{$WEBMAIL_CONTENT}'),IS_WF='{$IS_WF}',SECRET_LEVEL='{$SECRET_LEVEL}',COPY_TO_WEBMAIL='{$COPY_TO_WEBMAIL}',SECRET_TO_WEBMAIL='{$SECRET_TO_WEBMAIL}' where BODY_ID='{$BODY_ID}'" );
	}
	exequery( TD::conn( ), $query );
	if ( $BODY_ID == "" )
	{
					$BODY_ID = mysql_insert_id( );
	}
	if ( $BODY_ID == "" || $BODY_ID == 0 )
	{
					message( _( "错误" ), _( "邮件发送错误，请重新发送！" ) );
					exit( );
	}
	if ( $CENSOR_FLAG == 1 )
	{
		$CENSOR_DATA['BODY_ID'] = $BODY_ID;
		$CENSOR_DATA['FROM_ID'] = $_SESSION['LOGIN_USER_ID'];
		$CENSOR_DATA['TO_ID'] = $TO_ID;
		$CENSOR_DATA['SUBJECT'] = $SUBJECT;
		$CENSOR_DATA['CONTENT'] = $CENSOR_CONTENT;
		$CENSOR_DATA['SEND_TIME'] = $SEND_TIME;
		$CENSOR_DATA['RECEIPT'] = $RECEIPT;
		censor_data( "0", $CENSOR_DATA );
		button_back( );
		exit( );
	}
}
if ( ( $SEND_FLAG == 1 || $SEND_FLAG == 2 ) && ( $TO_ID_STR != "" || $TO_WEBMAIL != "" ) )
{
	if ( $TO_ID_STR != "" )
	{
		if ( $SEND_FLAG == 1 )
		{
						message( _( "提示" ), _( "内部邮件发送成功" ) );
		}
		else
		{
						message( _( "提示" ), _( "内部邮件已转到相关人员审核" ) );
		}
	}
	if ( $TO_WEBMAIL != "" && $TO_ID_STR == "" )
	{
		if ( $SEND_FLAG == 1 )
		{
						message( _( "提示" ), _( "外部邮件发送成功" ) );
		}
		else
		{
						message( _( "提示" ), _( "外部邮件已转到相关人员审核" ), "" );
		}
	}
	$free_falg = $email_audit_flag && find_id( $EMAIL_FREE_AUDIT_MAN, $_SESSION['LOGIN_USER_ID'] );
	if ( $SEND_FLAG == 2 || $free_falg )
	{
		if ( $SECRET_LEVEL == "1" )
		{
						$SECRET_LEVEL_DESC = _( "【非涉密】" );
		}
		else if ( $SECRET_LEVEL == "2" )
		{
						$SECRET_LEVEL_DESC = _( "【秘密(一般)】" );
		}
		else if ( $SECRET_LEVEL == "3" )
		{
						$SECRET_LEVEL_DESC = _( "【机密(重要)】" );
		}
		else if ( $SECRET_LEVEL == "4" )
		{
						$SECRET_LEVEL_DESC = _( "【绝密(非常重要)】" );
		}
		else
		{
						$SECRET_LEVEL_DESC = "";
		}
		$log_data = array(
						"to_id" => td_trim( getusernamebyid( $TO_ID_STR ) ),
						"to_wid" => td_trim( $TO_WEBMAIL ),
						"subject" => $SUBJECT,
						"content" => $CONTENT,
						"select" => $SECRET_LEVEL_DESC,
						"auitstr" => td_trim( getusernamebyid( $Audit_STR ) ),
						"attachmentname" => $ATTACHMENT_NAME,
						"body_id" => $BODY_ID
		);
		$AuditLog = 80;
		if ( $free_falg )
		{
						$AuditLog = 81;
		}
		addemailauditlog( $AuditLog, $_SESSION['LOGIN_UID'], $log_data );
	}
	$query = "SELECT UID,EMAIL_RECENT_LINKMAN from USER_EXT where UID='".$_SESSION['LOGIN_UID']."'";
	$cursor = exequery( TD::conn( ), $query );
	if ( $ROW = mysql_fetch_array( $cursor ) )
	{
					$UID_EXT = $ROW['UID'];
					$RECENT_LINKMAN = $ROW['EMAIL_RECENT_LINKMAN'];
	}
	$TO_ID_STR_ADD = "";
	$TO_ID_STR_RECENT = $TO_ID_STR.td_trim( $RECENT_LINKMAN );
	$TO_ID_STR_RECENT_ARRAY = explode( ",", td_trim( $TO_ID_STR_RECENT ) );
	$TO_ID_STR_RECENT_COUNT = count( $TO_ID_STR_RECENT_ARRAY );
	$I = 0;
	for ( ;	$I < $TO_ID_STR_RECENT_COUNT;	++$I	)
	{
		if ( !find_id( $TO_ID_STR_ADD, $TO_ID_STR_RECENT_ARRAY[$I] ) )
		{
			$TO_ID_STR_ADD .= $TO_ID_STR_RECENT_ARRAY[$I].",";
		}
	}
	if ( isset( $UID_EXT ) )
	{
		$query = "update USER_EXT  set EMAIL_RECENT_LINKMAN='".$TO_ID_STR_ADD."' where UID='".$_SESSION['LOGIN_UID']."'";
	}
	else
	{
		$query = "insert into USER_EXT  (UID,USER_ID,EMAIL_RECENT_LINKMAN) values ('".$_SESSION['LOGIN_UID']."','".$_SESSION['LOGIN_USER_ID'].( "','".$TO_ID_STR_ADD."')" );
	}
	exequery( TD::conn( ), $query );
	echo $NEW_SMS_HTML;
}
if ( $SEND_FLAG == 1 || $SEND_FLAG == 2 )
{
	if ( $SEND_FLAG == 1 )
	{
		echo "<script type=\"text/javascript\">\r\nif(parent && typeof(parent.getBoxCount) == 'function')\r\n{\r\n   var tmp = parent.getBoxCount('sentbox');\r\n   parent.setBoxCount('sentbox',tmp+1)\r\n}\r\n</script>\r\n";
	}
	else
	{
		echo "\t<script type=\"text/javascript\">\r\n\tif(parent && typeof(parent.getBoxCount) == 'function'){\r\n\t   var tmp = parent.getBoxCount('waitbox');\r\n\t   parent.setBoxCount('waitbox',tmp+1);\r\n\t}\r\n\t</script>\r\n";
	}
	if ( $BODY_OLD_ID != "" )
	{
		echo "   <script type=\"text/javascript\">\r\n\tif(parent && typeof(parent.getBoxCount) == 'function')\r\n\t{\r\n\t\tvar tmp1 = parent.getBoxCount('outbox');\r\n\t\tif(tmp1>=1)\r\n\t\t{\r\n\t\t  parent.setBoxCount('outbox',tmp1-1);\r\n\t   }\r\n\t}\r\n</script>\r\n ";
	}
	if ( $BTN_CLOSE == 1 )
	{
		echo "      \r\n\t\t<center>\r\n\t\t<input type=\"button\" id=\"close2\"  value=\"";
		echo _( "关闭" );
		echo "\"  class=\"BigButton\" onClick=\"window.close();\" title=\"";
		echo _( "关闭窗口" );
		echo "\">\r\n\t\t<input type=\"button\" id=\"back1\"  value=\"";
		echo _( "继续写信" );
		echo "\"  class=\"BigButton\" onClick=\"window.location='../new/'\" title=\"";
		echo _( "返回" );
		echo "\">\r\n\t\t</center>\r\n\t\t<script>\r\n\t\tif(window.parent==self)\r\n\t\t{\r\n\t\t\tdocument.getElementById('back1').style.display=\"none\";\r\n\t\t}\r\n\t\telse\r\n\t\t{\r\n\t\t\tdocument.getElementById('close2').style.display=\"none\"; \r\n\t\t}\r\n\t\t</script>\r\n";
	}
	else if ( $FROM_FLAG == 1 )
	{
		echo "      <center><input type=\"button\" value=\"";
		echo _( "继续写信" );
		echo "\" class=\"BigButton\" onClick=\"window.location='../new/'\"> &nbsp;\r\n      ";
	}
	else
	{
		echo "      <center><input type=\"button\" value=\"";
		echo _( "继续写信" );
		echo "\" class=\"BigButton\" onClick=\"window.location='../new/'\">&nbsp;\r\n";
	}
}
else
{
	if ( $BODY_OLD_ID == "" )
	{
		echo "<script type=\"text/javascript\">\r\nif(parent && typeof(parent.getBoxCount) == 'function')\r\n{\r\n   var tmp = parent.getBoxCount('outbox');\r\n   parent.setBoxCount('outbox',tmp+1);\r\n}\r\n</script>\r\n   \r\n";
	}
	if ( $OP == "1" )
	{
		echo "<script type=\"text/javascript\">\r\n   location=\"index.php?BODY_ID=";
		echo $BODY_ID;
		echo "&BTN_CLOSE=";
		echo $BTN_CLOSE;
		echo "&IS_MAIN=1&FROM=";
		echo $FROM;
		echo "\";\r\n</script> \r\n";
	}
	else
	{
		echo "<script type=\"text/javascript\">\r\n   location=\"index.php?BODY_ID=";
		echo $BODY_ID;
		echo "&SAVE=1&BTN_CLOSE=";
		echo $BTN_CLOSE;
		echo "&IS_MAIN=1&FROM=";
		echo $FROM;
		echo "\";\r\n</script>\r\n";
	}
}
echo "</div>\r\n</body>\r\n</html>";
?>
