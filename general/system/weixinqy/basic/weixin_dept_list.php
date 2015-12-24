<?php
include_once( "inc/auth.inc.php" );
require_once( "inc/weixinqy/class/weixinqy.department.funcs.php" );
//( );
$department = new WeiXinQYDepartment( );
$xtree = $department->getDepartmentList( intval( $_GET['dept_id'] ) );
if ( !$xtree )
{
	echo _( "尚未同步OA组织架构到微信企业号" );
	exit( );
}
$HTML_PAGE_TITLE = _( "基础功能 - OA数据同步" );
$HTML_PAGE_BASE_STYLE = FALSE;
include_once( "inc/header.inc.php" );
echo "<body class=\"abody\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/js/bootstrap/css/bootstrap.css\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/modules/weixinqy/style.css\">\r\n<script type=\"text/javascript\" src=\"";
echo MYOA_JS_SERVER;
echo "/static/js/jquery-1.10.2/jquery.min.js";
echo $GZIP_POSTFIX;
echo "\"></script>\r\n<script type=\"text/javascript\" src=\"";
echo MYOA_JS_SERVER;
echo "/static/js/bootstrap/js/bootstrap.min.js";
echo $GZIP_POSTFIX;
echo "\"></script>\r\n<script type=\"text/javascript\">\r\n\$(document).ready(function(){\r\n    \$(\"#btn-del-dept\").click(function(){\r\n        var Nodes = \$(\"#listTree\").dynatree(\"getSelectedNodes\");\r\n        var dept_id = dept_title = '';\r\n        for(var item in Nodes)\r\n        {\r\n            dept_id = Nodes[item].data.dept_id;\r\n            dept_title = Nodes[item].data.title;   \r\n        }\r\n\r\n        if(dept_id == \"\")\r\n        {\r\n            alert(\"";
echo _( "请先选择部门" );
echo "\");\r\n        }\r\n        else\r\n        {\r\n            \$(\"#del-dept-name\").html(dept_title);\r\n            \$.noConflict();\r\n            \$(\"#del-modal\").modal('show');\r\n            window.\$=window.jQuery;\r\n		\$(\"#del-modal\").attr(\"deptId\", dept_id);\r\n        }\r\n    });\r\n\r\n    \$(\"#confirm-del\").click(function(){\r\n        var dept_id = \$(\"#del-modal\").attr(\"deptId\");\r\n        \$.ajax({\r\n            type: \"GET\",\r\n            url: \"weixin_org.php\",\r\n            data: {'action': 'deleteDept', 'dept_id': dept_id},\r\n            dataType: 'json',\r\n            success: function(msg){\r\n            \$.noConflict();\r\n            \$(\"#del-modal\").modal('hide');\r\n            window.\$=window.jQuery;\r\n		if(msg.errcode!=0)\r\n                {\r\n                    alert(\"";		//add '\$.noConflict();\r\n            ','window.\$=window.jQuery;\r\n		'
echo _( "删除失败：" );
echo "\" + msg.errmsg);\r\n                }else{\r\n                    //tree && tree.reload();\r\n                    tree && tree.deleteNode ({'key':'dept_' + dept_id})\r\n                }\r\n            }\r\n        });\r\n    });\r\n\r\n    \$(\"#confirm-rename\").click(function(){\r\n        var dept_id = \$(\"#inputDeptID\").val();\r\n        var dept_name = \$(\"#inputDeptName\").val();\r\n        var dept_parentid = \$(\"#inputDeptParent\").val();\r\n\r\n        \$.ajax({\r\n            type: \"POST\",\r\n            url: \"weixin_org.php\",\r\n            data: {\r\n                'action': 'updateDept', \r\n                'dept_id': dept_id, \r\n                'dept_name': dept_name,\r\n                'dept_parentid': dept_parentid\r\n            },\r\n            dataType: 'json',\r\n            success: function(msg){\r\n            \$.noConflict();\r\n            \$(\"#rename-modal\").modal('hide');\r\n            window.\$=window.jQuery;\r\n		if(msg.errcode!=0)\r\n                {\r\n                    alert(\"";		//add '\$.noConflict();\r\n            ','window.\$=window.jQuery;\r\n		'
echo _( "更新失败：" );
echo "\" + msg.errmsg);\r\n                }else{\r\n                    //tree && tree.reload();\r\n                    tree && tree.editNode({'key':'dept_' + dept_id, 'title': dept_name, 'parentid': dept_parentid})\r\n                }\r\n            }\r\n        });\r\n    });\r\n\r\n    \$(\"#btn-rename-dept\").click(function(){\r\n        var Nodes = \$(\"#listTree\").dynatree(\"getSelectedNodes\");\r\n        var dept_id = dept_title = '';\r\n        for(var item in Nodes)\r\n        {\r\n            dept_id = Nodes[item].data.dept_id;\r\n            dept_title = Nodes[item].data.title;\r\n            dept_parentid = Nodes[item].data.parentid;\r\n        }\r\n\r\n        if(dept_id == \"\")\r\n        {\r\n            alert(\"";
echo _( "请先选择部门" );
echo "\");\r\n        }\r\n        else\r\n        {\r\n            \$(\"#inputDeptName\").val(dept_title);\r\n            \$(\"#inputDeptID\").val(dept_id);\r\n            \$(\"#inputDeptParent\").val(dept_parentid);\r\n            \$.noConflict();\r\n            \$(\"#rename-modal\").modal('show');\r\n        	window.\$=window.jQuery;\r\n		}\r\n    });\r\n\r\n});\r\n</script>\r\n<div class=\"dept-list-item\">\r\n    <button class=\"btn btn-small btn-primary\" type=\"button\" id=\"btn-del-dept\">";		//add '\$.noConflict();\r\n            ','window.\$=window.jQuery;\r\n		'
echo _( "删除选中部门" );
echo "</button>\r\n    <button class=\"btn btn-small btn-primary\" type=\"button\" id=\"btn-rename-dept\">";
echo _( "编辑选中部门" );
echo "</button>\r\n</div>\r\n<div id=\"listTree\" class=\"dept-list-tree\"></div>\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/images/org/ui.dynatree.css";
echo $GZIP_POSTFIX;
echo "\">\r\n<script type=\"text/javascript\" src=\"/inc/js_lang.php\"></script>\r\n<script type=\"text/javascript\" src=\"";
echo MYOA_JS_SERVER;
echo "/static/js/tree.js";
echo $GZIP_POSTFIX;
echo "\"></script>\r\n<script type=\"text/javascript\">\r\n   var tree = new Tree(\"listTree\", \"weixin_org.php?action=getList&dept_id=1\", '";
echo MYOA_STATIC_SERVER;
echo "/static/images/org/', true, 1, {\"minExpandLevel\":2});\r\n   tree.BuildTree();\r\n</script>\r\n";
//( );
$department = new WeiXinQYDepartment( );
$xtree = $department->getDepartmentList( 1 );
$dept = $department->list;
echo "<div id=\"del-modal\" class=\"modal hide fade\">\r\n    <div class=\"modal-header\">\r\n        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>\r\n        <h3>";
echo _( "删除部门" );
echo "</h3>\r\n    </div>\r\n    <div class=\"modal-body\">\r\n        <p>";
echo sprintf( _( "确定要删除 %s 吗？" ), "<span id='del-dept-name'></span>" );
echo "</p>\r\n    </div>\r\n    <div class=\"modal-footer\">\r\n        <button type=\"botton\" class=\"btn\" data-dismiss=\"modal\">";
echo _( "取消" );
echo "</button>\r\n        <button id=\"confirm-del\" type=\"botton\" class=\"btn btn-danger\">";
echo _( "确认删除" );
echo "</button>\r\n    </div>\r\n</div>\r\n\r\n<div id=\"rename-modal\" class=\"modal hide fade\">\r\n    <div class=\"modal-header\">\r\n        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">&times;</button>\r\n        <h3>";
echo _( "编辑部门" );
echo "</h3>\r\n    </div>\r\n    <div class=\"modal-body\">\r\n        <form class=\"form-horizontal\">\r\n            <div class=\"control-group\">\r\n                <label class=\"control-label\" for=\"inputDeptID\">";
echo _( "部门ID" );
echo "</label>\r\n                <div class=\"controls\">\r\n                  <input type=\"text\" id=\"inputDeptID\" value=\"\" readonly>\r\n                </div>\r\n            </div>\r\n            <div class=\"control-group\">\r\n                <label class=\"control-label\" for=\"inputDeptName\">";
echo _( "部门名称" );
echo "</label>\r\n                <div class=\"controls\">\r\n                  <input type=\"text\" id=\"inputDeptName\" placeholder=\"";
echo _( "请输入部门名称" );
echo "\">\r\n                </div>\r\n            </div>\r\n            <div class=\"control-group\">\r\n                <label class=\"control-label\" for=\"inputDeptParent\">";
echo _( "父级部门" );
echo "</label>\r\n                <div class=\"controls\">\r\n                    <select name=\"inputDeptParent\" id=\"inputDeptParent\">\r\n                        ";
echo $department->buildHtml( "select" );
echo "                    </select>\r\n                </div>\r\n            </div>\r\n        </form>    \r\n    </div>\r\n    <div class=\"modal-footer\">\r\n        <button type=\"botton\" class=\"btn\" data-dismiss=\"modal\">";
echo _( "取消" );
echo "</button>\r\n        <button id=\"confirm-rename\" type=\"botton\" class=\"btn btn-primary\">";
echo _( "确认" );
echo "</button>\r\n    </div>\r\n</div>\r\n</body>\r\n</html>";
?>
