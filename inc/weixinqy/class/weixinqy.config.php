<?php
include_once( "inc/utility_all.php" );
$PARA_ARRAY = get_sys_para( "WEIXINQY_OAURL,WEIXINQY_APP_SMS" );
$WEIXINQY_OAURL = $PARA_ARRAY['WEIXINQY_OAURL'];
$WEIXINQY_APP_SMS = $PARA_ARRAY['WEIXINQY_APP_SMS'];
define( BASE_URL, $WEIXINQY_OAURL );
define( REQUEST_BASE_URL, "http://".$WEIXINQY_OAURL );
$WXQY_CONFIG['webapp'] = array(
							"news.read" => "/pda/news/wxread.php",
							"notify.read" => "/pda/notify/wxread.php",
							"email.read" => "/pda/email/wxread.php"
							);
if ( $WEIXINQY_APP_SMS != "" )
{
	$WXQY_CONFIG['APP']['sms'] = array(
									"agentid" => $WEIXINQY_APP_SMS,
									"token" => "",
									"encodingAesKey" => ""
									);
}
?>