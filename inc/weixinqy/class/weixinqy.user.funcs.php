<?php
include_once( "weixinqy.funcs.php" );
class WeiXinQYUser extends WeiXinQY
{
	public $userinfo = array( );
	public $html = array( );
	private $url = array
	(
					"create" => "user/create"
	);

	public function __construct( )
	{
					//$FN_-2147483647( );
					parent::__construct();
					if ( count( $this->deptinfo ) == 0 )
					{
									$this->getDeptInfo( );
					}
	}

	public function createUser( $user_id )
	{
		$user_ids = "";
		$user_arr = explode( ",", $user_id );
		foreach ( $user_arr as $key => $value )
		{
						$user_ids .= "'".$value."',";
		}
		$user_ids = rtrim( $user_ids, "," );
		$sync = array( );
		$query = "SELECT USER_ID,USER_NAME,DEPT_ID,DEPT_ID_OTHER,USER_PRIV_NAME,USER_PRIV,MOBIL_NO,SEX,TEL_NO_DEPT,EMAIL FROM USER where USER_ID IN (".$user_ids.")";
		$cursor = exequery( TD::conn( ), $query );
		while ( $ROW = mysql_fetch_array( $cursor ) )
		{
			$USER_ID = $ROW['USER_ID'];
			$USER_NAME = $ROW['USER_NAME'];
			$DEPT_ID = $ROW['DEPT_ID'];
			$DEPT_ID_OTHER = $ROW['DEPT_ID_OTHER'];
			$USER_PRIV_NAME = $ROW['USER_PRIV_NAME'];
			$USER_PRIV = $ROW['USER_PRIV'];
			$MOBIL_NO = $ROW['MOBIL_NO'];
			$SEX = $ROW['SEX'];
			$TEL_NO_DEPT = $ROW['TEL_NO_DEPT'];
			$EMAIL = $ROW['EMAIL'];
			if ( $EMAIL == "" && !preg_match( "/^([+-]?)\\d*\\.?\\d+\$/", $MOBIL_NO ) )
			{
							$sync['failed'][] = sprintf( "%s(%s)", $USER_NAME, $this->deptinfo[$DEPT_ID]['dept_name'] );
			}
			else
			{
				$_dept = array( );
				$_dept[] = $this->deptinfo[$DEPT_ID]['weixin_dept_id'];
				if ( $DEPT_ID_OTHER != "" )
				{
								$_dept_arr = array_filter( explode( ",", $DEPT_ID_OTHER ) );
								foreach ( $_dept_arr as $key => $value )
								{
												$_dept[] = $this->deptinfo[$value]['weixin_dept_id'];
								}
				}
				$rs = $this->postData( $this->url['create'], array(
								"userid" => $USER_ID,
								"name" => $USER_NAME,
								"department" => $_dept,
								"position" => $USER_PRIV_NAME,
								"mobile" => preg_match( "/^([+-]?)\\d*\\.?\\d+\$/", $MOBIL_NO ) ? $MOBIL_NO : "",
								"gender" => $SEX,
								"tel" => $TEL_NO_DEPT,
								"email" => $EMAIL
				) );
				if ( $rs['errcode'] == 0 )
				{
								$sync['success'][] = sprintf( "%s(%s)", $USER_NAME, $this->deptinfo[$DEPT_ID]['dept_name'] );
				}
				else if ( $rs['errcode'] == 60102 )
				{
								$sync['exists'][] = sprintf( "%s(%s)", $USER_NAME, $this->deptinfo[$DEPT_ID]['dept_name'] );
				}
			}
		}
		parent::logs( "user_import", serialize( $sync ) );
		return array(
						"success" => count( $sync['success'] ),
						"failed" => count( $sync['failed'] ),
						"exists" => count( $sync['exists'] )
		);
	}

}

?>
