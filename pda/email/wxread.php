<?php
include( "../header_wx.php" );
include_once( "inc/utility_file.php" );
$exists = TRUE;
$BODY_ID = intval( $_GET['BODY_ID'] );
$query = "SELECT * from EMAIL,EMAIL_BODY where EMAIL_BODY.BODY_ID=EMAIL.BODY_ID and EMAIL.BODY_ID='".$BODY_ID."' and (EMAIL.DELETE_FLAG='' or EMAIL.DELETE_FLAG='0' or EMAIL.DELETE_FLAG='2')";
$cursor = exequery( TD::conn( ), $query );
if ( $ROW = mysql_fetch_array( $cursor ) )
{
	$EMAIL_ID = $ROW['EMAIL_ID'];
	$FROM_ID = $ROW['FROM_ID'];
	$SUBJECT = $ROW['SUBJECT'];
	$CONTENT = $ROW['COMPRESS_CONTENT'] == "" ? $ROW['CONTENT'] : gzuncompress( $ROW['COMPRESS_CONTENT'] );
	$SEND_TIME = $ROW['SEND_TIME'];
	$IMPORTANT = $ROW['IMPORTANT'];
	$ATTACHMENT_ID = $ROW['ATTACHMENT_ID'];
	$ATTACHMENT_NAME = $ROW['ATTACHMENT_NAME'];
	$READ_FLAG = $ROW['READ_FLAG'];
	$KEYWORD = $ROW['KEYWORD'];
	$RECV_FROM = $ROW['RECV_FROM'];
	$RECV_FROM_NAME = $ROW['RECV_FROM_NAME'];
	$RECV_TO = $ROW['RECV_TO'];
	$SUBJECT = htmlspecialchars( $SUBJECT );
	$CONTENT = stripslashes( $CONTENT );
	$IS_WEBMAIL = $ROW['IS_WEBMAIL'];
	if ( $IS_WEBMAIL != "0" )
	{
					$FROM_MAIL = $RECV_FROM_NAME.$RECV_FROM;
					$TO_MAIL = $RECV_TO;
	}
	if ( $IMPORTANT == "0" || $IMPORTANT == "" )
	{
					$IMPORTANT_DESC = "";
	}
	else if ( $IMPORTANT == "1" )
	{
					$IMPORTANT_DESC = "<span style='color:red'>"._( "重要" )."</span>";
	}
	else if ( $IMPORTANT == "2" )
	{
					$IMPORTANT_DESC = "<span style='color:red'>"._( "非常重要" )."</span>";
	}
	$query1 = "SELECT UID,USER_NAME from USER where USER_ID='".$FROM_ID."'";
	$cursor1 = exequery( TD::conn( ), $query1 );
	if ( $ROW = mysql_fetch_array( $cursor1 ) )
	{
					$UID = $ROW['UID'];
					$FROM_NAME = $ROW['USER_NAME'];
	}
	else
	{
					$FROM_NAME = $FROM_ID;
	}
	if ( $IS_WEBMAIL != "0" )
	{
					$FROM_NAME = $FROM_MAIL;
	}
	if ( $READ_FLAG == 0 )
	{
		$query = "update EMAIL set READ_FLAG = 1 where EMAIL_ID='".$EMAIL_ID."'";
		exequery( TD::conn( ), $query );
		$query = "insert into APP_LOG(USER_ID,TIME,MODULE,OPP_ID,TYPE) values ('".$_SESSION['LOGIN_USER_ID']."','".date( "Y-m-d H:i:s" ).( "','1','".$EMAIL_ID."','1')" );
		exequery( TD::conn( ), $query );
	}
}
else
{
	$exists = FALSE;
}
echo "\r\n";
if ( !$exists )
{
				echo "    <div class=\"page_message_box\">\r\n        <div class=\"page_message_icon iconfont\">&#xf00b6;</div>\r\n        <div class=\"page_message_content\">";
				echo _( "邮件不存在或已删除！" );
				echo "</div>\r\n    </div>\r\n";
				exit( );
}
echo "\r\n<div id=\"body\" class=\"container email\">\r\n    <div class=\"read_detail fix_read_detail read_meta\">\r\n        <span class=\"entry_meta\">";
echo _( "发件人：" );
echo "</span><span class=\"entry_meta\">";
echo $FROM_NAME;
echo "</span>\r\n    </div>\r\n    ";
if ( $IS_WEBMAIL != "0" )
{
				echo "    <div class=\"read_detail fix_read_detail read_meta\">\r\n        <span class=\"entry_meta\">";
				echo _( "收件人：" );
				echo "</span><span class=\"entry_meta\">";
				echo $TO_MAIL;
				echo "</span>\r\n    </div>\r\n    ";
}
echo "    <div class=\"read_detail read_line\"></div>\r\n    <h3 class=\"read_title fix_read_title\">";
echo $SUBJECT;
echo " ";
echo $IMPORTANT_DESC;
echo "</h3>\r\n    <div class=\"read_detail fix_read_detail read_meta\">\r\n        <span class=\"entry_meta\">";
echo date( "Y"._( "年" )."n"._( "月" )."j"._( "日" )." H:i", $SEND_TIME );
echo "</span>\r\n    </div>\r\n\r\n    <div class=\"read_content\">";
echo $CONTENT;
echo "</div>\r\n\r\n    ";
if ( $KEYWORD != "" )
{
				echo "        <div class=\"read_detail fix_read_detail read_meta\">";
				echo _( "关键字：" ).str_replace( ",", " ", $KEYWORD );
				echo "</div>\r\n    ";
}
echo "\r\n    ";
if ( $ATTACHMENT_ID != "" && $ATTACHMENT_NAME != "" )
{
/*	echo "        <div class=\"read_attach\">\r\n            ";
	if ( check_attach_filter( $ATTACHMENT_ID, $ATTACHMENT_NAME ) )
	{
					echo "                <div class=\"read_attach_info\">";
					echo _( "部分附件无法在移动设备上正常查看，已经过滤，如需查看请访问PC版" );
					echo "</div>\r\n            ";
	}*/
	echo "            ";
//				echo attach_link_pda( $ATTACHMENT_ID, $ATTACHMENT_NAME, $P, "", 1, 1, 1, 1, TRUE );
	echo "        </div>\r\n    ";
}
echo "\r\n\r\n</div>\r\n<script type=\"text/javascript\">\r\nvar pageCallBack = {\r\n   wxread : function(){\r\n      Util.preLoadImage().pageTitle(\"";
echo $SUBJECT;
echo "\");\r\n   }\r\n};\r\npageCallBack.wxread();\r\n</script>\r\n</body>\r\n</html>";
?>
