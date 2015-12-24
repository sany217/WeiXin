<?php
class WeiXinQYMessage extends WeiXinQY
{

	private $url = array
	(
					"send" => "message/send"
	);

	public function message( $params )
	{
		$postData = array( );
		switch ( $params['type'] )
		{
		case "text" :
						$postData = array(
										"touser" => $this->cUser( $params['user'] ),
										"msgtype" => "text",
										"agentid" => $this->_base_config['APP']['message']['agentid'],
										"text" => array(
														"content" => $params['content']
										),
										"safe" => "0"
						);
		}
		$rs = $this->postData( $this->url['send'], $postData );
		if ( $rs['errcode'] == 0 )
		{
						return TRUE;
		}
		return FALSE;
	}

	public function sms( $params )
	{
		$module = $params['module'];
		$url = "";
		if ( $params['params'] )
		{
			$url = $this->OAuth2Url( $this->buildUrl( $params ), $this->_base_config['APP']['sms']['agentid'] );
		}
		$postData = array( );
		switch ( $params['module'] )
		{
			case "email" :
				$content = $url != "" ? $params['content'].( "\n<a href='".$url."'>" )._( "ÔÄ¶ÁÓÊ¼ş" )."</a>" : $params['content'];
				$postData = array(
								"touser" => $this->cUser( $params['user'] ),
								"toparty" => $this->cDept( $params['dept'] ),
								"msgtype" => "text",
								"agentid" => $this->_base_config['APP']['sms']['agentid'],
								"text" => array(
												"content" => $content
								),
								"safe" => "0"
				);
				break;
			case "news" :
				$picurl = "";
				include_once( "oa.news.php" );
				//( );
				$News = new News( );
				$row = $News->getById( "SUBJECT,CONTENT,ATTACHMENT_ID,ATTACHMENT_NAME,TO_ID,USER_ID,SUMMARY", $params['params'] );
				$picurl = $News->getFirstImage( "news", $row['ATTACHMENT_ID'], $row['ATTACHMENT_NAME'] );
				$description = $row['SUMMARY'] == "" ? csubstr( strip_tags( $this->cContent( $row['CONTENT'] ) ), 0, 30, TRUE, 1 )."..." : strip_tags( $this->cContent( $row['SUMMARY'] ) );
				if ( $picurl != "" )
				{
					$picurl = $this->buildAttachUrl( "http://".BASE_URL.$picurl, $this->_base_config['APP']['sms']['agentid'] );
				}
				$postData = array(
								"touser" => $this->cUser( $row['USER_ID'], $row['TO_ID'] ),
								"toparty" => $row['TO_ID'] == "ALL_DEPT" ? "" : $this->cDept( $row['TO_ID'] ),
								"msgtype" => "news",
								"agentid" => $this->_base_config['APP']['sms']['agentid'],
								"news" => array(
												"articles" => array(
																array(
																	"title" => strip_tags( $row['SUBJECT'] ),
																	"description" => $description,
																	"url" => $url,
																	"picurl" => $picurl
																)
												)
								)
				);
				//parent::logs("test",$url);
				break;
			case "notify" :
				$picurl = "";
				include_once( "oa.notify.php" );
				//( );
				$Notify = new Notify( );
				$row = $Notify->getById( "SUBJECT,CONTENT,ATTACHMENT_ID,ATTACHMENT_NAME,TO_ID,USER_ID,SUMMARY", $params['params'] );
				$picurl = $Notify->getFirstImage( "notify", $row['ATTACHMENT_ID'], $row['ATTACHMENT_NAME'] );
				$description = $row['SUMMARY'] == "" ? csubstr( strip_tags( $this->cContent( $row['CONTENT'] ) ), 0, 30, TRUE, 1 )."..." : strip_tags( $this->cContent( $row['SUMMARY'] ) );
				if ( $picurl != "" )
				{
					$picurl = $this->buildAttachUrl( "http://".BASE_URL.$picurl, $this->_base_config['APP']['sms']['agentid'] );
				}
				$postData = array("touser" => $this->cUser( $row['USER_ID'], $row['TO_ID'] ),
								"toparty" => $row['TO_ID'] == "ALL_DEPT" ? "" : $this->cDept( $row['TO_ID'] ),
								"msgtype" => "news",
								"agentid" => $this->_base_config['APP']['sms']['agentid'],
								//"text" => array( "content" => "aaaaaa" ),
								"news" => array("articles" => array(array(
																	"title" => strip_tags( $row['SUBJECT'] ),
																	"description" => $description,
																	"url" => $url,
																	"picurl" => $picurl
																	)
																)
												)
								);
				//parent::logs("test",$url);
				break;
			default :
				$postData = array(
								"touser" => $this->cUser( $params['user'] ),
								"toparty" => $this->cDept( $params['dept'] ),
								"msgtype" => "text",
								"agentid" => $this->_base_config['APP']['sms']['agentid'],
								"text" => array(
												"content" => $params['content']
								),
								"safe" => "0"
				);
		}
		$rs = $this->postData( $this->url['send'], $postData );
	}

	public function cUser( $users, $dept = NULL )
	{
		if ( $dept == "ALL_DEPT" || $users == "all" )
		{
			return "@all";
		}
		if ( is_array( $users ) )
		{
			return implode( "|", $users );
		}
		return rtrim( str_replace( ",", "|", $users ), "|" );
	}

	public function cDept( $depts )
	{
		if ( $depts == "" )
		{
			return;
		}
		$dept_final = array( );
		$dept_arr = array_filter( explode( ",", $depts ) );
		foreach ( $dept_arr as $value )
		{
			if ( $this->deptinfo[$value]['weixin_dept_id'] != "" )
			{
				$dept_final[] = $this->deptinfo[$value]['weixin_dept_id'];
			}
		}
		return implode( "|", $dept_final );
	}

}

?>
