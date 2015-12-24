<?php
include_once( "inc/auth.inc.php" );
include_once( "inc/utility_all.php" );
require_once( "inc/weixinqy/class/weixinqy.department.funcs.php" );
$web_action = $_GET['action'] ? addslashes( $_GET['action'] ) : addslashes( $_POST['action'] );
if ( $web_action != "" )
{
	ob_clean( );
	switch ( $web_action )
	{
		case "getList" :
			//( );
			$department = new WeiXinQYDepartment( );
			$xtree = $department->getDepartmentList( intval( $_GET['dept_id'] ) );
			echo $xtree;
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
$query = "SELECT COUNT(DEPT_ID) FROM DEPARTMENT WHERE WEIXIN_DEPT_ID > 0";
$cursor = exequery( TD::conn( ), $query, TRUE );
$row = mysql_fetch_row( $cursor );
$HTML_PAGE_TITLE = _( "数据导入 - 组织架构初始化" );
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
echo "\"></script>\r\n<script type=\"text/javascript\">\r\n\$(function(){\r\n    \$(\".func-item button\").on(\"click\", function(){\r\n        \$(\".func-item button\").removeClass(\"btn-primary\");\r\n        \$(\".func-item button i\").removeClass(\"icon-white\");\r\n        \$(\".mod-func\").hide();\r\n        var module = \$(this).attr(\"data-module\");\r\n        \$(this).addClass(\"btn-primary\");\r\n        \$(this).find(\"i\").addClass(\"icon-white\");\r\n        \$(\"#mod-\" + module).show();\r\n    });\r\n\r\n    \$(\"#btn-init\").click(function(){\r\n        \$(\"#dept-iframe\").contents().find(\"#btn-sync-dept\").click();\r\n        \$(this).off().html('<i class=\"icon-refresh icon-white\"></i>";
echo _( "正在初始化..." );
echo "');\r\n    });\r\n});\r\n</script>\r\n<div>\r\n    <fieldset>\r\n        <legend><h5>";
echo _( "数据导入 - 组织架构初始化" );
echo "</h5></legend>\r\n    </fieldset>\r\n    \r\n    <div class=\"func-item\">\r\n        ";
if ( 0 < $row[0] )
{
	echo "            ";
	echo _( "组织架构职能初始化一次，初始化完毕后，要变更组织架构，请到微信企业号官方网站变更" );
	echo "        ";
}
else
{
	echo "        <button id=\"btn-init\" class=\"btn btn-small btn-primary\" data-module=\"list\"><i class=\"icon-home icon-white\"></i>";
	echo _( "一键初始化" );
	echo "</button>\r\n        ";
}
echo "    </div>\r\n    \r\n    <div id=\"mod-list\" class=\"mod-func well\" style=\"display:block;\">\r\n        <iframe id=\"dept-iframe\" class=\"iframes\" src=\"oa_dept_sync.php\" frameborder=\"0\"></iframe>\r\n    </div>\r\n</div>\r\n</body>\r\n</html>";
?>
