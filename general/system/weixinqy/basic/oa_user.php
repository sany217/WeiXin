<?php
include_once( "inc/auth.inc.php" );
include_once( "inc/utility_all.php" );
require_once( "inc/weixinqy/class/weixinqy.user.funcs.php" );
$web_action = $_GET['action'] ? addslashes( $_GET['action'] ) : addslashes( $_POST['action'] );
if ( $web_action != "" )
{
	ob_clean( );
	switch ( $web_action )
	{
	case "importUser" :
					$user_id = htmlspecialchars( urldecode( $_GET['user_id'] ) );
					//( );
					$user = new WeiXinQYUser( );
					$msg = $user->createUser( $user_id );
					echo json_encode( $msg );
					exit( );
	case "deleteDept" :
					//( );
					$department = new WeiXinQYDepartment( );
					$rs = $department->deleteDept( intval( $_GET['dept_id'] ) );
					echo json_encode( $rs );
					exit( );
	case "createDept" :
					//( );
					$department = new WeiXinQYDepartment( );
					$rs = $department->createDept( $_GET['dept_id'] );
					echo $rs;
					exit( );
	case "updateDept" :
					//( );
					$department = new WeiXinQYDepartment( );
					$rs = $department->updateDept( array(
									"id" => intval( $_POST['dept_id'] ),
									"name" => td_iconv( addslashes( $_POST['dept_name'] ), "UTF-8", MYOA_CHARSET ),
									"parentid" => intval( $_POST['dept_parentid'] )
					) );
					echo json_encode( $rs );
					exit( );
	case "getDept" :
					//( );
					$department = new WeiXinQYDepartment( );
					$rs = $department->getDept( intval( $_GET['dept_id'] ) );
	}
	exit( );
}
$HTML_PAGE_TITLE = _( "数据导入 - 用户管理" );
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
echo "\"></script>\r\n<script type=\"text/javascript\">\r\n\$(function(){\r\n    \$(\".func-item button\").on(\"click\", function(){\r\n        \$(\".func-item button\").removeClass(\"btn-primary\");\r\n        \$(\".func-item button i\").removeClass(\"icon-white\");\r\n        \$(\".mod-func\").hide();\r\n        var module = \$(this).attr(\"data-module\");\r\n        \$(this).addClass(\"btn-primary\");\r\n        \$(this).find(\"i\").addClass(\"icon-white\");\r\n        \$(\"#mod-\" + module).show();\r\n    });\r\n});\r\n</script>\r\n<div>\r\n    <fieldset>\r\n        <legend><h5>";
echo _( "数据导入 - 用户管理" );
echo "</h5></legend>\r\n    </fieldset>\r\n    \r\n    <div class=\"func-item\">\r\n        <button class=\"btn btn-small btn-primary\"  data-module=\"sync\"><i class=\"icon-user icon-white\"></i>";
echo _( "按人员导入" );
echo "</button>\r\n        <button class=\"btn btn-small\"  data-module=\"logs\"><i class=\"icon-list\"></i>";
echo _( "导入日志" );
echo "</button>\r\n    </div>\r\n    \r\n    <div id=\"mod-sync\" class=\"mod-func well\" style=\"display:block;\">\r\n        <iframe class=\"iframes\" src=\"oa_user_sync.php\" frameborder=\"0\"></iframe>\r\n    </div>\r\n    <div id=\"mod-logs\" class=\"mod-func well\">\r\n        <iframe class=\"iframes\" src=\"oa_user_logs.php\" frameborder=\"0\"></iframe>\r\n    </div>\r\n</div>\r\n</body>\r\n</html>";
?>
