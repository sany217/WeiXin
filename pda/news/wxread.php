<?php
include( "../header_wx.php" );
include( "inc/utility_file.php" );
$NEWS_ID = intval( $_GET['NEWS_ID'] );
$query = "SELECT * from NEWS where NEWS_ID='".$NEWS_ID."' and PUBLISH='1'";
$cursor = exequery( TD::conn( ), $query );
if ( $ROW = mysql_fetch_array( $cursor ) )
{
				$NEWS_ID = $ROW['NEWS_ID'];
				$SUBJECT = $ROW['SUBJECT'];
				$CLICK_COUNT = $ROW['CLICK_COUNT'];
				$SUBJECT_COLOR = $ROW['SUBJECT_COLOR'];
				$ANONYMITY_YN = $ROW['ANONYMITY_YN'];
				$PROVIDER = $ROW['PROVIDER'];
				$NEWS_TIME = $ROW['NEWS_TIME'];
				$FORMAT = $ROW['FORMAT'];
				$READERS = $ROW['READERS'];
				$TYPE_ID = $ROW['TYPE_ID'];
				$KEYWORD = $ROW['KEYWORD'];
				$SUBJECT = htmlspecialchars( $SUBJECT );
				$ORG_SUBJECT = $SUBJECT;
				$TYPE_NAME = get_code_name( $TYPE_ID, "NEWS" );
				if ( $SUBJECT_COLOR != "" )
				{
								$SUBJECT = "<font color='".$SUBJECT_COLOR."'>".$SUBJECT."</font>";
				}
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
				$NEWS_TIME = substr( $NEWS_TIME, 0, 10 );
				if ( !find_id( $READERS, $_SESSION['LOGIN_USER_ID'] ) )
				{
								$READERS .= $_SESSION['LOGIN_USER_ID'].",";
								$query = "update news set READERS='".$READERS."',CLICK_COUNT='{$CLICK_COUNT}' where NEWS_ID='{$NEWS_ID}'";
				}
				else
				{
								$query = "update NEWS set CLICK_COUNT='".$CLICK_COUNT."' where NEWS_ID='{$NEWS_ID}'";
				}
				exequery( TD::conn( ), $query );
				$query1 = "SELECT USER_NAME from USER where USER_ID='".$PROVIDER."'";
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
								header( "location: ".$CONTENT );
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
echo "      <span class=\"entry_meta\">";
echo $NEWS_TIME;
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
echo "   <div class=\"read_detail fix_read_detail read_meta\"><span class=\"entry_meta\">";
echo _( "阅读" );
echo " ";
echo $CLICK_COUNT;
echo "</span></div>\r\n   ";
if ( $ATTACHMENT_ID != "" && $ATTACHMENT_NAME != "" )
{
				echo "      <div class=\"read_attach\">\r\n         ";
/*				if ( check_attach_filter( $ATTACHMENT_ID, $ATTACHMENT_NAME ) )
				{
								echo "            <div class=\"read_attach_info\">";
								echo _( "部分附件无法在移动设备上正常查看，已经过滤，如需查看请访问PC版" );
								echo "</div>\r\n         ";
				}*/
				echo "         ";
//				echo attach_link_pda( $ATTACHMENT_ID, $ATTACHMENT_NAME, $P, "", 1, 1, 1, 1, TRUE );
				echo "      </div>\r\n   ";
}
echo "</div>\r\n<script type=\"text/javascript\">\r\nvar pageCallBack = {\r\n   wxread : function(){\r\n      Util.preLoadImage().pageTitle(\"";
echo $ORG_SUBJECT;
echo "\");\r\n   }\r\n};\r\npageCallBack.wxread();\r\n</script>\r\n</body>\r\n</html>";
?>
