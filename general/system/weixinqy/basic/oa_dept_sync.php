<?php
include_once( "inc/auth.inc.php" );
include_once( "inc/header.inc.php" );
require_once( "inc/weixinqy/class/weixinqy.department.funcs.php" );
$PARA_URL = "#";
$xname = "dept_select";
$showButton = 0;
$DEPT_IDS = "0,";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/js/bootstrap/css/bootstrap.css\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo MYOA_STATIC_SERVER;
echo "/static/modules/weixinqy/style.css\">\r\n<script type=\"text/javascript\" src=\"";
echo MYOA_JS_SERVER;
echo "/static/js/jquery-1.10.2/jquery.min.js";
echo $GZIP_POSTFIX;
echo "\"></script>\r\n<body class=\"abody\">\r\n<script type=\"text/javascript\">\r\n\$(document).ready(function(){\r\n    \$(\"#btn-sync-dept\").click(function(){\r\n        var dept_id = '";
echo $DEPT_IDS;
echo "';\r\n        if(dept_id == \"\")\r\n        {\r\n            alert(\"";
echo _( "����ѡ����" );
echo "\");\r\n        }\r\n        else\r\n        {\r\n            \$.ajax({\r\n                type: \"GET\",\r\n                url: \"oa_org.php\",\r\n                data: {'action': 'createDept', 'dept_id': dept_id},\r\n                success: function(msg){\r\n                    if(msg != \"ok\")\r\n                    {\r\n                        alert(\"";
echo _( "��ʼ��ʧ��" );
echo "\");\r\n                    }else{\r\n                        alert(\"";
echo _( "��ʼ�����ųɹ�" );
echo "\");\r\n                        parent && parent.document.location.reload();\r\n                    }\r\n                }\r\n            });\r\n        }\r\n    });\r\n});\r\n</script>\r\n<div>\r\n    <div class=\"sync-item\" style=\"display: none;\">\r\n        <button id=\"btn-sync-dept\" class=\"btn btn-small btn-primary\" type=\"button\">";
echo _( "ͬ�����в���" );
echo "</button>\r\n    </div>\r\n    <div class=\"sync-tree\">\r\n        ";
include_once( "inc/dept_list/index.php" );
echo "       \r\n    </div>\r\n</div>\r\n</body>\r\n</html>";
?>