<?php
function itask( $cmd_array )
{
				$PARA_VALUE = get_sys_para( "TASK_HOST,TASK_PORT" );
				$SERVICE_HOST = trim( $PARA_VALUE['TASK_HOST'] );
				$SERVICE_PORT = intval( trim( $PARA_VALUE['TASK_PORT'] ) );
				if ( $SERVICE_HOST == "" || $SERVICE_PORT <= 0 || 65535 <= $SERVICE_PORT )
				{
								return FALSE;
				}
				$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
				socket_set_option( $socket, SOL_SOCKET, SO_SNDTIMEO, array( "sec" => 5, "usec" => 0 ) );
				socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array( "sec" => 5, "usec" => 0 ) );
				if ( @socket_connect( $socket, $SERVICE_HOST, $SERVICE_PORT ) )
				{
				}
				if ( !is_array( $cmd_array ) )
				{
								return FALSE;
				}
				$result = array( );
				while ( list( $key, $cmd ) = each( &$cmd_array ) )
				{
								$bytes = @socket_write( $socket, $cmd, @strlen( $cmd ) );
								if ( $bytes === FALSE )
								{
												return FALSE;
								}
								$data = @socket_read( $socket, 2048 );
								if ( $data === FALSE )
								{
												return FALSE;
								}
								if ( strtoupper( bin2hex( substr( $data, 0, 3 ) ) ) == "EFBBBF" )
								{
												$result[$key] = iconv( "utf-8", MYOA_CHARSET, substr( $data, 3 ) );
								}
								else
								{
												$result[$key] = iconv( "gbk", MYOA_CHARSET, $data );
								}
				}
				@socket_close( $socket );
				return $result;
}

function itask_last_error( )
{
				$err_no = socket_last_error( );
				if ( 10050 <= $err_no && $err_no <= 10065 )
				{
								return _( "服务未启动或设置不正确" );
				}
				return iconv( MYOA_OS_CHARSET, MYOA_CHARSET, socket_strerror( $err_no ) );
}

function itask_last_errno( )
{
				return socket_last_error( );
}

function imtask( $cmd )
{
				$PARA_VALUE = get_sys_para( "IM_HOST,IM_PORT" );
				$SERVICE_HOST = trim( $PARA_VALUE['IM_HOST'] );
				$SERVICE_PORT = intval( trim( $PARA_VALUE['IM_PORT'] ) );
				if ( $SERVICE_HOST == "" || $SERVICE_PORT <= 0 || 65535 <= $SERVICE_PORT )
				{
								return FALSE;
				}
				$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
				$bytes = @socket_sendto( $socket, $cmd, @strlen( $cmd ), 0, $SERVICE_HOST, $SERVICE_PORT );
				if ( $bytes === FALSE )
				{
								return FALSE;
				}
				@socket_close( $socket );
				return TRUE;
}

function imailtask( $cmd )
{
				$PARA_VALUE = get_sys_para( "MAIL_HOST,MAIL_PORT" );
				$SERVICE_HOST = trim( $PARA_VALUE['MAIL_HOST'] );
				$SERVICE_PORT = intval( trim( $PARA_VALUE['MAIL_PORT'] ) );
				if ( $SERVICE_HOST == "" || $SERVICE_PORT <= 0 || 65535 <= $SERVICE_PORT )
				{
								return FALSE;
				}
				$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
				socket_set_option( $socket, SOL_SOCKET, SO_SNDTIMEO, array( "sec" => 10, "usec" => 0 ) );
				socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array( "sec" => 10, "usec" => 0 ) );
				if ( !@socket_connect( $socket, $SERVICE_HOST, $SERVICE_PORT ) )
				{
								return FALSE;
				}
				$bytes = @socket_write( $socket, $cmd, @strlen( $cmd ) );
				if ( $bytes === FALSE )
				{
								return FALSE;
				}
				$data = @socket_read( $socket, 1024 );
				if ( $data === FALSE )
				{
								return FALSE;
				}
				@socket_close( $socket );
				return $data;
}

function iIndexTask( $cmd )
{
				$PARA_VALUE = get_sys_para( "INDEX_HOST,INDEX_PORT" );
				$SERVICE_HOST = trim( $PARA_VALUE['INDEX_HOST'] );
				$SERVICE_PORT = intval( trim( $PARA_VALUE['INDEX_PORT'] ) );
				if ( $SERVICE_HOST == "" || $SERVICE_PORT <= 0 || 65535 <= $SERVICE_PORT )
				{
								return FALSE;
				}
				$socket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
				socket_set_option( $socket, SOL_SOCKET, SO_SNDTIMEO, array( "sec" => 50, "usec" => 0 ) );
				socket_set_option( $socket, SOL_SOCKET, SO_RCVTIMEO, array( "sec" => 50, "usec" => 0 ) );
				if ( !@socket_connect( $socket, $SERVICE_HOST, $SERVICE_PORT ) )
				{
								return FALSE;
				}
				$bytes = @socket_write( $socket, $cmd, @strlen( $cmd ) );
				if ( $bytes === FALSE )
				{
								return FALSE;
				}
				$data = "";
				while ( $read = @socket_read( $socket, 10000, PHP_BINARY_READ ) )
				{
								$data .= $read;
								if ( !( strlen( $read ) < 10000 ) )
								{
												continue;
								}
								break;
				}
				if ( $read === FALSE )
				{
								return FALSE;
				}
				@socket_close( $socket );
				return $data;
}

function mobile_push_notification( $uid_sent, $content, $module, $options = NULL )
{
	$org_content = $content;
	$PARA_ARRAY = get_sys_para( "MOBILE_PUSH_OPTION,PCONLINE_MOBILE_PUSH", FALSE );
	while ( list( $PARA_NAME, $PARA_VALUE ) = each( &$PARA_ARRAY ) )
	{
		$$PARA_NAME = $PARA_VALUE;
	}
	if ( $MOBILE_PUSH_OPTION == "1" )
	{
		if ( $uid_sent == "" )
		{
			return;
		}
		if ( substr( $uid_sent, -1 ) != "," )
		{
			$uid_sent .= ",";
		}
		$module = strtolower( $module );
		$mp_to_uids = td_trim( $uid_sent );
		$a_uid_sent = $a_unpush_uid = array( );
		$a_uid_sent = explode( ",", $mp_to_uids );
		if ( $PCONLINE_MOBILE_PUSH == "0" )
		{
			$query = "select DISTINCT(UID) from user_online where UID in(".$mp_to_uids.") and CLIENT!=5 and CLIENT!=6";
			$cursor = exequery( TD::conn( ), $query );
			while ( $ROW = mysql_fetch_array( $cursor ) )
			{
				$a_unpush_uid[] = $ROW['UID'];
			}
			foreach ( $a_uid_sent as $k => $v )
			{
				if ( !( $PCONLINE_MOBILE_PUSH == "0" ) && !is_array( $a_unpush_uid ) && !in_array( $v, $a_unpush_uid ) )
				{
					unset( $a_uid_sent[$k] );
				}
			}
			$a_uid_sent = array_filter( $a_uid_sent );
			$uid_sent = implode( ",", $a_uid_sent );
			$uid_sent .= ",";
		}
		if ( $module == "message" )
		{
			include_once( "task/message_push/funcs.php" );
			$C_MOBILE_DEVICES = TD::get_cache( "C_MOBILE_DEVICES" );
			if ( $C_MOBILE_DEVICES === FALSE )
			{
				rebuildmobilecache( );
				$C_MOBILE_DEVICES = TD::get_cache( "C_MOBILE_DEVICES" );
			}
			if ( $C_MOBILE_DEVICES && 0 < count( $C_MOBILE_DEVICES ) )
			{
				$M_STA = $M_ENT = array( );
				foreach ( $a_uid_sent as $k => $v )
				{
					if ( $C_MOBILE_DEVICES[$v] )
					{
						if ( $C_MOBILE_DEVICES[$v]['client_ver'] == 1 )
						{
							$M_STA[$v][] = array(
											"content" => $content,
											"module" => "message"
							);
						}
						else if ( $C_MOBILE_DEVICES[$v]['client_ver'] == 2 )
						{
							$M_ENT[$v][] = array(
											"content" => $content,
											"module" => "message"
							);
						}
					}
				}
			}
			tdmobilepush( array(
							"sta" => $M_STA,
							"ent" => $M_ENT
			) );
		}
		$content = $org_content = strip_tags( $content );
		if ( strtolower( MYOA_CHARSET ) != "utf-8" )
		{
			$content = td_iconv( $content, MYOA_CHARSET, "UTF-8" );
		}
		imtask( "C^m^n^".$uid_sent."^".$module."^".$content );
		if ( !$MYOA_WEIXINQY_PUSH_ACTIVE )
		{
			$useble_module = array( "email", "news", "notify" );
			if ( !in_array( $module, $useble_module ) )
			{
				$uid_sents = td_trim( $uid_sent );
				$query = "SELECT USER_ID FROM USER WHERE UID IN (".$uid_sents.")";
				$cursor = exequery( TD::conn( ), $query );
				while ( $ROW = mysql_fetch_array( $cursor ) )
				{
					$user_id_arr[] = $ROW['USER_ID'];
				}
				wxqy_sms( array(
								"user" => $user_id_arr,
								"module" => $module,
								"content" => $org_content
				) );
			}
		}
	}
}

function WXQY_SMS( $params )
{
	require_once( "inc/weixinqy/class/weixinqy.funcs.php" );
	require_once( "inc/weixinqy/class/weixinqy.message.funcs.php" );
	//( );
	$wx = new WeiXinQYMessage( );
	$wx->sms( $params );
}

include_once( "inc/utility_all.php" );
?>
