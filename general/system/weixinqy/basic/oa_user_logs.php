<?php
include_once( "inc/auth.inc.php" );
include_once( "inc/utility_all.php" );
$HTML_PAGE_TITLE = _( "数据导入 - 人员 - 导入日志" );
$HTML_PAGE_BASE_STYLE = FALSE;
include_once( "inc/header.inc.php" );
require_once( "inc/weixinqy/class/weixinqy.funcs.php" );
//( );
$rs = array( );
$WeiXinQY = new WeiXinQY( );
$rs = $WeiXinQY->showlogs( "user_import" );
echo "<body class=\"abody\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/js/bootstrap/css/bootstrap.css\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/modules/weixinqy/style.css\">\r\n<div>\r\n";
if ( count( $rs ) == 0 )
{
				echo "    <p>";
				echo _( "暂无日志" );
				echo "</p>\r\n";
				exit( );
}
echo "    <table class=\"table table-striped\">\r\n        <colgroup>\r\n            <col width=\"150\"></col>\r\n            <col></col>\r\n            <col width=\"100\"></col>\r\n        </colgroup>\r\n        <thead>\r\n            <tr>\r\n                <th>";
echo _( "时间" );
echo "</th>\r\n                <th>";
echo _( "结果" );
echo "</th>\r\n                <th>";
echo _( "操作" );
echo "</th>\r\n            </tr>\r\n        </thead>\r\n        <tbody>\r\n            ";
foreach ( $rs as $key => $value )
{
				echo "                <tr>\r\n                    <td>";
				echo date( "Y-m-d H:i", $key );
				echo "</td>\r\n                    <td>";
				echo $value;
				echo "</td>\r\n                    <td><a href=\"oa_user_logs_show.php?file=";
				echo $key;
				echo "\">";
				echo _( "详情" );
				echo "</a></td>\r\n                </tr>\r\n            ";
}
echo "        </tbody>\r\n    </table>\r\n</div>\r\n</body>\r\n</html>";
?>
