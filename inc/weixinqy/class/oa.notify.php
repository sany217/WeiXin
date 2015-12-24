<?php
include( "oa.modules.php" );
class Notify extends Modules
{
	public $table = "notify";
	public $relation = "";

	public function getById( $field = "", $params = array( ) )
	{
		return $this->find( $field, $params );
	}
}

?>
