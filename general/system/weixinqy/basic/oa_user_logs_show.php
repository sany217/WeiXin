<?php
include_once( "inc/auth.inc.php" );
$HTML_PAGE_TITLE = _( "���ݵ��� - ��Ա - �鿴��־" );
$HTML_PAGE_BASE_STYLE = FALSE;
include_once( "inc/header.inc.php" );
require_once( "inc/weixinqy/class/weixinqy.funcs.php" );
$file = htmlspecialchars( $_GET['file'] );
//( );
$rs = array( );
$WeiXinQY = new WeiXinQY( );
$rs = $WeiXinQY->logsDetail( "user_import", $file );
$style = array(
				"failed" => array(
								"error",
								_( "ʧ��" )
				),
				"success" => array(
								"success",
								_( "�ɹ�" )
				),
				"exists" => array(
								"warning",
								_( "����" )
				)
);
echo "<body>\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/js/bootstrap/css/bootstrap.css\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/modules/weixinqy/style.css\">\r\n<script type=\"text/javascript\">\r\nfunction goback(){\r\n    location.href = 'oa_user_logs.php';\r\n}\r\n</script>\r\n<div>\r\n    <div class=\"back-bar\"><button class=\"btn btn-small\" type=\"button\" onclick=\"goback()\">";
echo _( "����" );
echo "</button></div>\r\n    <table class=\"table table-bordered\">\r\n        <colgroup>\r\n            <col width=\"150\"></col>\r\n            <col></col>\r\n            <col></col>\r\n            <col></col>\r\n            <col width=\"80\"></col>\r\n        </colgroup>\r\n        <thead>\r\n            <tr>\r\n                <th>";
echo _( "ʱ��" );
echo "</th>\r\n                <th>";
echo _( "�ɹ�" );
echo "</th>\r\n                <th>";
echo _( "ʧ��" );
echo "</th>\r\n                <th>";
echo _( "����" );
echo "</th>\r\n                <th>";
echo _( "�ܼ�" );
echo "</th>\r\n            </tr>\r\n        </thead>\r\n        <tbody>\r\n            <tr>\r\n                <td>";
echo date( "Y-m-d H:i" );
echo "</td>\r\n                <td>";
echo count( $rs['success'] );
echo "</td>\r\n                <td>";
echo count( $rs['failed'] );
echo "</td>\r\n                <td>";
echo count( $rs['exists'] );
echo "</td>\r\n                <td>";
echo count( $rs['success'] ) + count( $rs['failed'] ) + count( $rs['exists'] );
echo "</td>\r\n            </tr>\r\n        </tbody>\r\n    </table>\r\n    <br>\r\n    <table class=\"table table-bordered\">\r\n        <colgroup>\r\n            <col></col>\r\n            <col width=\"150\"></col>\r\n        </colgroup>\r\n        <thead>\r\n            <tr>\r\n                <th>";
echo _( "�û��������ţ�" );
echo "</th>\r\n                <th>";
echo _( "���" );
echo "</th>\r\n            </tr>\r\n        </thead>\r\n        <tbody>\r\n            ";
foreach ( $rs as $key => $value )
{
				echo "                ";
				foreach ( $value as $v )
				{
								echo "                    <tr class=\"";
								echo $style[$key][0];
								echo "\">\r\n                        <td>";
								echo $v;
								echo "</td>\r\n                        <td>";
								echo $style[$key][1];
								echo "</td>\r\n                    </tr>\r\n                ";
				}
				echo "            ";
}
echo "        </tbody>\r\n    </table>\r\n\r\n    <p class=\"text-error\">";
echo _( "ʧ�ܿ���ԭ��Ҫ������û�������д��������ֻ��ţ�" );
echo "</p>\r\n    <div class=\"back-bar\"><button class=\"btn btn-small\" type=\"button\" onclick=\"goback()\">";
echo _( "����" );
echo "</button></div>\r\n</div>\r\n</body>\r\n</html>";
?>
