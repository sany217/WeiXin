<?php
class Modules
{
	public $condition = "";
	public $table = "";

	public function findBySql( $sql )
	{
		$cursor = exequery( TD::conn( ), $sql );
		$row = mysql_fetch_array( $cursor );
		return $row;
	}

	public function find( $field = "", $params = array( ) )
	{
		if ( 0 < count( $params ) )
		{
			foreach ( $params as $key => $value )
			{
				if ( $this->condition != "" )
				{
					$this->condition .= " AND ";
				}
				$this->condition .= " ".$key." = ".$value;
			}
		}
		$query = "SELECT ".$field." FROM ".$this->table." WHERE ".$this->condition;
		return $this->findBySql( $query );
	}

	public function getFirstImage( $module, $attachment_id, $attachment_name )
	{
		if ( $attachment_id == "" )
		{
			return "";
		}
		$attachment_id_array = explode( ",", $attachment_id );
		$attachment_name_array = explode( "*", $attachment_name );
		$array_count = sizeof( $attachment_id_array );
		$i = 0;
		for ( ;	$i < $array_count;	++$i	)
		{
			if ( $attachment_id_array[$i] == "" )
			{
							continue;
			}
			$attachment_id1 = $attachment_id_array[$i];
			$ym = substr( $attachment_id1, 0, strpos( $attachment_id1, "_" ) );
			if ( $ym )
			{
							$attachment_id1 = substr( $attachment_id1, strpos( $attachment_id, "_" ) + 1 );
			}
			$attachment_id_encoded = attach_id_encode( $attachment_id1, $attachment_name_array[$i] );
			$url_array = attach_url( $attachment_id_array[$i], $attachment_name_array[$i], $module, $other );
			if ( !is_image( $attachment_name_array[$i] ) )
			{
				continue;
			}
			return "/inc/attach.php?MODULE=".$module."&YM=".$ym."&ATTACHMENT_ID=".$attachment_id_encoded."&ATTACHMENT_NAME=".urlencode( $attachment_name_array[$i] )."&DIRECT_VIEW=1";
		}
		return "";
	}

}

include_once( "inc/conn.php" );
include_once( "inc/utility_file.php" );
?>
