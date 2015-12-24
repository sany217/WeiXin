<?php
include_once( "inc/auth.inc.php" );
include_once( "inc/utility_all.php" );
$HTML_PAGE_TITLE = _( "������������" );
$HTML_PAGE_BASE_STYLE = FALSE;
include_once( "inc/header.inc.php" );
if ( $_SERVER['REQUEST_METHOD'] == "GET" && $_GET['action'] == "connect" )
{
	ob_clean( );
	include_once( "inc/weixinqy/class/weixinqy.base.funcs.php" );
	//( );
	$WeiXinQY = new WeiXinQY( );
	if ( $WeiXinQY->tokens != "" )
	{
		echo "ok";
		exit( );
	}
	echo "failed";
	exit( );
}
if ( $_SERVER['REQUEST_METHOD'] == "POST" )
{
	if ( $_POST['WEIXINQY_OAURL'] != "" )
	{
		$WEIXINQY_OAURL = htmlspecialchars( $_POST['WEIXINQY_OAURL'] );
		set_sys_para( array(
						"WEIXINQY_OAURL" => "{$WEIXINQY_OAURL}"
		) );
	}
	if ( $_POST['WEIXINQY_SECRET'] != "" && $_POST['WEIXINQY_CORPID'] != "" )
	{
		$WEIXINQY_CORPID = htmlspecialchars( $_POST['WEIXINQY_CORPID'] );
		$WEIXINQY_SECRET = htmlspecialchars( $_POST['WEIXINQY_SECRET'] );
		set_sys_para( array(
						"WEIXINQY_SECRET" => "{$WEIXINQY_SECRET}",
						"WEIXINQY_CORPID" => "{$WEIXINQY_CORPID}"
		) );
		include_once( "inc/utility_cache.php" );
		$WEIXINQY_TOKENS = TD::get_cache( "WEIXINQY_TOKENS" );
		if ( $WEIXINQY_TOKENS !== FALSE )
		{
						TD::set_cache( "WEIXINQY_TOKENS", NULL );
		}
	}
}

include_once( "inc/conn.php" );
include_once( "inc/utility_update.php" );
if(!field_exists( "DEPARTMENT", "WEIXIN_DEPT_ID" ))
{
	$query = "ALTER TABLE `department` ADD COLUMN `WEIXIN_DEPT_ID`  int(11) NOT NULL DEFAULT 0 AFTER `DEPT_EMAIL_AUDITS_IDS`";
	exequery( TD::conn( ), $query, TRUE );
	add_sys_para( array( "WEIXINQY_SECRET" => "", "WEIXINQY_CORPID" => "", "WEIXINQY_OAURL" => "", "WEIXINQY_APP_SMS" => "" ) );
	cache_sys_para( );
}

$PARA_ARRAY = get_sys_para( "WEIXINQY_CORPID,WEIXINQY_SECRET,WEIXINQY_OAURL" );
$WEIXINQY_CORPID = $PARA_ARRAY['WEIXINQY_CORPID'];
$WEIXINQY_SECRET = $PARA_ARRAY['WEIXINQY_SECRET'];
$WEIXINQY_OAURL = $PARA_ARRAY['WEIXINQY_OAURL'];
echo "<body>\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_JS_SERVER;
echo "/static/js/bootstrap/css/bootstrap.css\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_JS_SERVER;
echo "/static/modules/weixinqy/style.css\">\r\n<script type=\"text/javascript\" src=\"";
echo MYOA_JS_SERVER;
echo "/static/js/jquery-1.10.2/jquery.min.js";
echo $GZIP_POSTFIX;
echo "\"></script>\r\n<script type=\"text/javascript\">\r\n\$(function(){\r\n    \$(\"#connect-btn\").click(function(){\r\n        \$.get(\"setting.php\", {action: \"connect\", time: new Date().getTime()}, function(msg){\r\n            if(msg == \"ok\"){\r\n                \$(\"#connect-msg\").addClass(\"text-success\").html(\"";
echo _( "���ӳɹ���" );
echo "\");\r\n            }else{\r\n                \$(\"#connect-msg\").removeClass(\"text-success\").addClass(\"text-error\").html(\"";
echo _( "����ʧ�ܣ���5���Ӻ��ԣ�" );
echo "\");\r\n            }\r\n        })\r\n    });\r\n});\r\n</script>\r\n<div>\r\n    <form class=\"form-horizontal\" method=\"POST\" action=\"#\">\r\n        <fieldset>\r\n            <legend><h5>";
echo _( "�������� - OA���ʵ�ַ" );
echo "</h5></legend>\r\n            <div class=\"control-group\">\r\n                <label class=\"control-label\" for=\"inputCorpID\">";
echo _( "OA������ַ" );
echo "</label>\r\n                <div class=\"controls\">\r\n                    <input type=\"text\" name=\"WEIXINQY_OAURL\" id=\"inputOaUrl\" placeholder=\"www.yourdomain.com\" value=\"";
echo $WEIXINQY_OAURL;
echo "\">\r\n                    <span class=\"help-block\">OA��������ַ��ʽΪ: <code>www.yourdomain.com</code> ���� ����ip <code>123.23.12.XX</code> ����Ҫhttpǰ׺</span>\r\n                </div>\r\n            </div>\r\n        </fieldset>\r\n\r\n        <fieldset>\r\n            <legend><h5>";
echo _( "�������� - ΢�ſ���ƾ֤" );
echo "</h5></legend>\r\n            <div class=\"control-group\">\r\n                <label class=\"control-label\" for=\"inputCorpID\">CorpID</label>\r\n                <div class=\"controls\">\r\n                    <input type=\"text\" name=\"WEIXINQY_CORPID\" id=\"inputCorpID\" placeholder=\"CorpID\" value=\"";
echo $WEIXINQY_CORPID;
echo "\">\r\n                </div>\r\n            </div>\r\n            <div class=\"control-group\">\r\n                <label class=\"control-label\" for=\"inputSecret\">Secret</label>\r\n                <div class=\"controls\">\r\n                    <input type=\"text\" name=\"WEIXINQY_SECRET\" class=\"span6\" id=\"inputSecret\" placeholder=\"Secret\" value=\"";
echo $WEIXINQY_SECRET;
echo "\">\r\n                </div>\r\n            </div>\r\n            <div class=\"control-group\">\r\n                <div class=\"controls\">\r\n                    <button type=\"submit\" class=\"btn\">";
echo _( "����" );
echo "</button>\r\n                    ";
if ( $WEIXINQY_CORPID != "" && $WEIXINQY_SECRET )
{
	echo "                        <button id=\"connect-btn\" type=\"button\" class=\"btn btn-warning\">";
	echo _( "��������" );
	echo "</button>\r\n                    ";
}
echo "                    <span id=\"connect-msg\"></span>\r\n                </div>\r\n            </div>\r\n        </fieldset>\r\n        <div class=\"well\">\r\n            <p>�������ҵ� CorpID & Secret��</p>\r\n            <p class=\"muted\">���������½ https://qy.weixin.qq.com/ ����΢��ɨһɨ�Ҳ�Ķ�ά�룬���������������̨����� ��ࡰ���á�������ҳ�浽���·����������鿪����ƾ�ݡ��·����� <code>CorpID</code> �� <code>Secret</code></p>\r\n        </div>\r\n    </form>\r\n\r\n\r\n</div>\r\n</body>\r\n</html>";
?>
