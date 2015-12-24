<?php
include_once( "inc/auth.inc.php" );
include_once( "inc/utility_all.php" );
if ( $_SERVER['REQUEST_METHOD'] == "POST" && $_POST['appId'] != "" )
{
	$appId = intval( $_POST['appId'] );
	set_sys_para( array(
					"WEIXINQY_APP_SMS" => "{$appId}"
	) );
}
$PARA_ARRAY = get_sys_para( "WEIXINQY_APP_SMS" );
$WEIXINQY_APP_SMS = $PARA_ARRAY['WEIXINQY_APP_SMS'];
$HTML_PAGE_TITLE = _( "应用设置 - 事务提醒" );
$HTML_PAGE_BASE_STYLE = FALSE;
include_once( "inc/header.inc.php" );
echo "<body>\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/js/bootstrap/css/bootstrap.css\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/modules/weixinqy/style.css\">\r\n<script type=\"text/javascript\" src=\"";
echo MYOA_JS_SERVER;
echo "/static/js/jquery-1.10.2/jquery.min.js";
echo $GZIP_POSTFIX;
echo "\"></script>\r\n<script type=\"text/javascript\">\r\n\r\n</script>\r\n<div>\r\n    <fieldset>\r\n        <legend><h5>";
echo _( "应用设置 - 事务提醒" );
echo "</h5></legend>\r\n    </fieldset>\r\n\r\n    <form class=\"form-horizontal\" method=\"POST\" action=\"#\">\r\n        <div class=\"control-group\">\r\n            <label class=\"control-label\" for=\"inputEmail\">应用ID</label>\r\n            <div class=\"controls\">\r\n                <input type=\"text\" id=\"appId\" name=\"appId\" placeholder=\"应用ID\" value=\"";
echo $WEIXINQY_APP_SMS;
echo "\">\r\n            </div>\r\n        </div>\r\n\r\n        <div class=\"control-group\">\r\n            <div class=\"controls\">\r\n                <button type=\"submit\" class=\"btn btn-primary\">";
echo _( "确定" );
echo "</button>\r\n            </div>\r\n        </div>\r\n    </form>\r\n\r\n</div>\r\n</body>\r\n</html>";
?>
