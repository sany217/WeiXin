<?php
include( "oa.modules.php" );
class News extends Modules
{
	public $table = "news";
	public $relation = "";

	public function getById( $field = "", $params = array( ) )
	{
		return $this->find( $field, $params );
	}
}

?>
