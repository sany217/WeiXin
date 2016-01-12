<?php
class WeiXinQY
{
	public $corpid = "";
	public $corpsecret = "";
	public $tokens = "";
	public $base_url = "https://qyapi.weixin.qq.com/cgi-bin/";
	private $weixinqy_token_url = "gettoken";
	private $weixinqy_user_url = "user/getuserinfo";
	public $_base_config = array( );
	public $deptinfo = array( );
	public $debug = TRUE;
	public $_logcallback = "logg";

	public function __construct( )
	{
		$para_array = get_sys_para( "WEIXINQY_CORPID,WEIXINQY_SECRET" );
		$weixinqy_corpid = $para_array['WEIXINQY_CORPID'];
		$weixinqy_secret = $para_array['WEIXINQY_SECRET'];
		if ( $weixinqy_corpid == "" || $weixinqy_secret == "" )
		{
			echo _( "ERR: 系统未绑定微信企业账号" );
			exit( );
		}
		$this->corpid = $weixinqy_corpid;
		$this->corpsecret = $weixinqy_secret;
		$this->getTokens( );
		$this->getDeptInfo( );
		include( "weixinqy.config.php" );
		$this->_base_config = $WXQY_CONFIG;
	}

	private function getTokens( )
	{
		if ( $this->tokens != "" )
		{
			return $this->tokens;
		}
		$tokens = TD::get_cache( "WEIXINQY_TOKENS" );
		if ( $tokens === FALSE )
		{
			$request_str = http_build_query( array(
							"corpid" => $this->corpid,
							"corpsecret" => $this->corpsecret
			) );
			if ( $request_str != "" )
			{
							$request_url = $this->base_url.$this->weixinqy_token_url."?".$request_str;
			}
			$result = file_get_contents( $request_url );
			if ( $result === FALSE )
			{
							echo _( "ERR: 无法连接到微信企业微信官方平台获取Tokens" );
							exit( );
			}
			$data = json_decode( $result, TRUE );
			$this->tokens = $data['access_token'];
			TD::set_cache( "WEIXINQY_TOKENS", $this->tokens, $data['expires_in'] );
		}
		else
		{
			$this->tokens = $tokens;
		}
	}

	public function getData( $url, $params = NULL )
	{
		$params['access_token'] = $this->tokens;
		$request_str = http_build_query( $params );
		if ( $url != "" )
		{
						$url = $this->base_url.$url."?".$request_str;
		}
		$result = file_get_contents( $url );
		if ( $result === FALSE )
		{
						echo _( "ERR: 获取返回值失败" );
						exit( );
		}
		$data = json_decode( $result, TRUE );
		return $data;
	}

	public function postData( $url, $params = NULL, $urlFixParams = NULL )
	{
		$result = "";
		$url = $this->base_url.$url."?access_token=".$this->tokens;
		if ( $urlFixParams )
		{
						$url .= "&".http_build_query( $urlFixParams );
		}
		if ( is_array( $params ) )
		{
						$options = array(
										"http" => array(
														"method" => "POST",
														"content" => self::encode_json( gbk2utf8( $params ) )
										)
						);
						$context = stream_context_create( $options );
						$result = file_get_contents( $url, FALSE, $context );
		}
		return json_decode( $result, TRUE );
	}

	public function getDeptInfo( )
	{
		$data = array( );
		if ( count( $this->deptinfo ) == 0 )
		{
			$query = "SELECT DEPT_ID,DEPT_NAME,WEIXIN_DEPT_ID FROM department";
			$cursor = exequery( TD::conn( ), $query );
			while ( $ROW = mysql_fetch_array( $cursor ) )
			{
							$dept_id = $ROW['DEPT_ID'];
							$dept_name = $ROW['DEPT_NAME'];
							$weixin_dept_id = $ROW['WEIXIN_DEPT_ID'];
							$data[$dept_id] = array(
											"dept_id" => $dept_id,
											"dept_name" => $dept_name,
											"weixin_dept_id" => $weixin_dept_id
							);
			}
			$this->deptinfo = $data;
		}
	}

	public static function _trans( $data )
	{
					return td_iconv( $data, "utf-8", MYOA_CHARSET );
	}

	public static function url_encode( $str )
	{
		if ( is_array( $str ) )
		{
						foreach ( $str as $key => $value )
						{
										$str[urlencode( $key )] = self::url_encode( $value );
						}
						return $str;
		}
		$str = urlencode( $str );
		return $str;
	}

	public static function encode_json( $str )
	{
					return urldecode( json_encode( self::url_encode( $str ) ) );
	}

	public function logs( $type, $data )
	{
		include( "inc/td_config.php" );			//include( "inc/oa_config.php" );
		$root_path = $ATTACH_PATH."weixinqy/";
		if ( !file_exists( $root_path ) )
		{
						mkdir( $root_path, 448 );
		}
		$target_path = $root_path.$type;
		if ( !file_exists( $target_path ) )
		{
						mkdir( $target_path, 448 );
		}
		$file_name = $target_path."/".time( ).".php";
		file_put_contents( $file_name, $data );
	}

	public function showlogs( $type )
	{
		include( "inc/td_config.php" );		//include( "inc/oa_config.php" );
		$root_path = $ATTACH_PATH."weixinqy/";
		$path = $root_path.$type;
		$rs = array( );
		$current_dir = opendir( $path );
		while ( ( $file = readdir( $current_dir ) ) !== FALSE )
		{
			if ($file !== "." && $file !== "..")
			{
				$filename = rtrim( $file, ".php" );
				$content = file_get_contents( $path."/".$file );
				$content = unserialize( $content );
				$rs[$filename] = sprintf( _( "成功： %s 失败：%s 忽略：%s" ), count( $content['success'] ), count( $content['failed'] ), count( $content['exists'] ) );
			}
		}
		return $rs;
	}

	public function logsDetail( $type, $file )
	{
		include( "inc/td_config.php" );		//include( "inc/oa_config.php" );
		$file_path = $ATTACH_PATH."weixinqy".DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$file.".php";
		$content = array( );
		$content = file_get_contents( $file_path );
		$content = unserialize( $content );
		return $content;
	}

	public function getUserId( $params )
	{
		$rs = array( );
		$rs = $this->getData( $this->weixinqy_user_url, array(
						"code" => $params['code'],
						"agentid" => $params['agentid']
		) );
		return $rs['UserId'];
	}

	public function http_post( $url, $param, $post_file = FALSE )
	{
		$oCurl = curl_init( );
		if ( stripos( $url, "https://" ) !== FALSE )
		{
			curl_setopt( $oCurl, CURLOPT_SSL_VERIFYPEER, FALSE );
			curl_setopt( $oCurl, CURLOPT_SSL_VERIFYHOST, FALSE );
		}
		if ( is_string( $param ) || $post_file )
		{
			$strPOST = $param;
		}
		else
		{
			$aPOST = array( );
			foreach ( $param as $key => $val )
			{
							$aPOST[] = $key."=".urlencode( $val );
			}
			$strPOST = join( "&", $aPOST );
		}
		curl_setopt( $oCurl, CURLOPT_URL, $url );
		curl_setopt( $oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $oCurl, CURLOPT_POST, TRUE );
		curl_setopt( $oCurl, CURLOPT_POSTFIELDS, $strPOST );
		$sContent = curl_exec( $oCurl );
		$aStatus = curl_getinfo( $oCurl );
		curl_close( $oCurl );
		if ( intval( $aStatus['http_code'] ) == 200 )
		{
						return $sContent;
		}
		return FALSE;
	}

	public function log( $log )
	{
		if ( $this->debug && function_exists( $this->_logcallback ) )
		{
			if ( is_array( $log ) )
			{
							$log = print_r( $log, TRUE );
			}
			return call_user_func( $this->_logcallback, $log );
		}
	}

	public function buildUrl( $params )
	{
		$url = BASE_URL.$this->_base_config['webapp'][$params['module_action']]."?".http_build_query( $params['params'] );
		$url = iconv( MYOA_CHARSET, "UTF-8", $url );
		return $url;
	}

	public function OAuth2Url( $redirect_uri, $agentid )
	{
		return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->corpid."&redirect_uri=".urlencode( $redirect_uri )."&response_type=code&scope=snsapi_base&state=fromWX_".$agentid."#wechat_redirect";
	}

	public function cContent( $content )
	{
					return str_replace( array( "&amp;", "&nbsp;", " " ), array( "", "", "" ), $content );
	}

	public function buildAttachUrl( $attach_url, $agentid )
	{
		$code = "fromWX_".$agentid;
		$state = td_authcode( $code, "ENCODE", "weixinqy" );
		return $attach_url."&state=".$state;
	}
}

include_once( "inc/conn.php" );
include_once( "inc/utility_all.php" );
include_once( "inc/utility_cache.php" );
if ( !function_exists( "gbk2utf8" ) )
{
	function gbk2utf8( $data )
	{
		if ( is_array( $data ) )
		{
						return array_map( "gbk2utf8", $data );
		}
		return iconv( MYOA_CHARSET, "utf-8", $data );
	}
}
?>
