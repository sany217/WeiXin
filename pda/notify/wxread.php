<?php
include( "../header_wx.php" );
include( "inc/utility_file.php" );
$NOTIFY_ID = intval( $_GET['NOTIFY_ID'] );
$query = "SELECT * from NOTIFY where NOTIFY_ID='".$NOTIFY_ID."' and PUBLISH='1'";
$cursor = exequery( TD::conn( ), $query );
if ( $ROW = mysql_fetch_array( $cursor ) )
{
	$FROM_DEPT = $ROW['FROM_DEPT'];
	$DEPARTMENT_ARRAY = TD::get_cache( "SYS_DEPARTMENT" );
	$FROM_DEPT_NAME = $DEPARTMENT_ARRAY[$FROM_DEPT]['DEPT_NAME'];
	$FROM_ID = $ROW['FROM_ID'];
	$SUBJECT_COLOR = $ROW['SUBJECT_COLOR'];
	$SUBJECT = $ROW['SUBJECT'];
	$ORG_SUBJECT = $SUBJECT;
	$FORMAT = $ROW['FORMAT'];
	$COMPRESS_CONTENT = @gzuncompress( $ROW['COMPRESS_CONTENT'] );
	if ( $COMPRESS_CONTENT != "" && $FORMAT != "2" )
	{
					$CONTENT = $COMPRESS_CONTENT;
	}
	else
	{
					$CONTENT = $ROW['CONTENT'];
	}
	$ATTACHMENT_ID = $ROW['ATTACHMENT_ID'];
	$ATTACHMENT_NAME = $ROW['ATTACHMENT_NAME'];
	$FORMAT = $ROW['FORMAT'];
	$READERS = $ROW['READERS'];
	$SUBJECT = htmlspecialchars( $SUBJECT );
	$TYPE_ID = $ROW['TYPE_ID'];
	$TYPE_NAME = get_code_name( $TYPE_ID, "NOTIFY" );
	$DOWNLOAD = $ROW['DOWNLOAD'];
	$SUBJECT = "<font color='".$SUBJECT_COLOR."'>".$SUBJECT."</font>";
	$BEGIN_DATE = $ROW['BEGIN_DATE'];
	$SEND_TIME = $ROW['SEND_TIME'];
	$BEGIN_DATE = date( "Y-m-d", $BEGIN_DATE );
	$query1 = "SELECT USER_NAME from USER where USER_ID='".$FROM_ID."'";
	$cursor1 = exequery( TD::conn( ), $query1 );
	if ( $ROW = mysql_fetch_array( $cursor1 ) )
	{
					$FROM_NAME = $ROW['USER_NAME'];
	}
	else
	{
					$FROM_NAME = $FROM_ID;
	}
	if ( $FORMAT == "2" )
	{
					$CONTENT = "<a href='".$CONTENT."'>{$CONTENT}</a>";
	}
	if ( !find_id( $READERS, $_SESSION['LOGIN_USER_ID'] ) )
	{
					$READERS .= $_SESSION['LOGIN_USER_ID'].",";
					$query = "update NOTIFY set READERS='".$READERS."' where NOTIFY_ID='{$NOTIFY_ID}'";
					exequery( TD::conn( ), $query );
					$query = "insert into APP_LOG(USER_ID,TIME,MODULE,OPP_ID,TYPE) values ('".$_SESSION['LOGIN_USER_ID'].( "','".$CUR_TIME."','4','{$NOTIFY_ID}','1')" );
					exequery( TD::conn( ), $query );
	}
}
else
{
	exit( );
}
echo "<body>\r\n<div class=\"container\">\r\n   <h3 class=\"read_title fix_read_title\">";
echo $SUBJECT;
echo "</h3>\r\n   <div class=\"read_detail fix_read_detail read_meta\">\r\n      ";
if ( $TYPE_NAME != "" )
{
	echo "         <span class=\"entry_meta\">";
	echo $TYPE_NAME;
	echo "</span>\r\n      ";
}
echo "</span>\r\n      <span class=\"entry_meta\">";
echo $FROM_DEPT_NAME;
echo "</span>\r\n      <span class=\"entry_meta\">";
if ( $BEGIN_DATE == substr( $SEND_TIME, 0, 10 ) )
{
	echo $SEND_TIME;
}
else
{
	echo $BEGIN_DATE;
}
echo "</span>\r\n      <span class=\"entry_meta\">";
echo $FROM_NAME;
echo "</span>\r\n   </div>\r\n   <div class=\"read_content\">";
echo $CONTENT;
echo "</div>\r\n    ";
if ( $KEYWORD != "" )
{
	echo "        <div class=\"read_detail fix_read_detail read_meta\">";
	echo _( "关键字：" ).str_replace( ",", " ", $KEYWORD );
	echo "</div>\r\n    ";
}
if ( $ATTACHMENT_ID != "" && $ATTACHMENT_NAME != "" )
{
	echo "      <div class=\"read_attach\">\r\n         ";
/*	if ( check_attach_filter( $ATTACHMENT_ID, $ATTACHMENT_NAME ) )
	{
		echo "            <div class=\"read_attach_info\">";
		echo _( "部分附件无法在移动设备上正常查看，已经过滤，如需查看请访问PC版" );
		echo "</div>\r\n         ";
	}*/
	echo "         ";
//	echo attach_link_pda( $ATTACHMENT_ID, $ATTACHMENT_NAME, $P, "", 1, 1, 1, 1, TRUE );
	echo "      </div>\r\n   ";
}
echo "</div>\r\n<script type=\"text/javascript\">\r\nvar pageCallBack = {\r\n   wxread : function(){\r\n      Util.preLoadImage().pageTitle(\"";
echo $ORG_SUBJECT;
echo "\");\r\n   }\r\n};\r\npageCallBack.wxread();\r\n</script>\r\n</body>\r\n</html>";
?>
