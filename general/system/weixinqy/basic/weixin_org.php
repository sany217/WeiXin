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
$HTML_PAGE_TITLE = _( "基础功能 - 微信组织架构管理" );
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
echo "\"></script>\r\n	\r\n<div>\r\n    <fieldset>\r\n        <legend><h5>";
echo _( "基础功能 - 微信组织架构管理" );
echo "</h5></legend>\r\n    </fieldset>\r\n    \r\n    <div class=\"func-item\">\r\n        <button class=\"btn btn-small btn-primary\"  data-module=\"list\"><i class=\"icon-home icon-white\"></i>";
echo _( "微信部门管理" );
echo "</button>\r\n    </div>\r\n    \r\n    <div id=\"mod-list\" class=\"mod-func well\" style=\"display:block;\">\r\n       <iframe class=\"iframes\" src=\"weixin_dept_list.php\" frameborder=\"0\"></iframe>\r\n    </div>\r\n</div>\r\n</body>\r\n</html>";
?>
