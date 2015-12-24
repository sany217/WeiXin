<?php
include_once( "weixinqy.funcs.php" );
class WeiXinQYDepartment extends WeiXinQY
{

				public $list = array( );
				public $html = array( );
				private $url = array
				(
								"list" => "department/list",
								"delete" => "department/delete",
								"update" => "department/update",
								"create" => "department/create"
				);

				public function getDepartmentList( $parentid )
				{
								if ( count( $this->list ) == 0 )
								{
												$data = $this->getData( $this->url['list'], NULL );
												if ( $data['errmsg'] == "ok" && 0 < count( $data['department'] ) )
												{
																$this->list = $data['department'];
												}
								}
								if ( $parentid == 1 )
								{
												$dept_array = $this->getListForTree( 1 );
												$org_array = array(
																"title" => $data['department'][0]['name'],
																"isFolder" => TRUE,
																"isLazy" => FALSE,
																"expand" => TRUE,
																"key" => "dept_1",
																"dept_id" => "1",
																"icon" => "root.png",
																"parentid" => $data['department'][0]['parentid'],
																"children" => $dept_array
												);
								}
								else
								{
												$org_array = $this->getListForTree( $parentid );
								}
								return json_encode( $org_array );
				}

				public function getListForTree( $dept_id )
				{
								foreach ( $this->list as $key => $value )
								{
												if ( !( $value['parentid'] != $dept_id ) )
												{
																$json = "";
																if ( $this->hasChildren( $value['id'] ) )
																{
																				$isLazy = TRUE;
																				$json = "weixin_org.php?action=getList&dept_id=".$value['id'];
																}
																else
																{
																				$isLazy = FALSE;
																}
																$dept_array[] = array(
																				"title" => $value['name'],
																				"isFolder" => TRUE,
																				"isLazy" => $this->hasChildren( $value['id'] ),
																				"key" => "dept_".$value['id'],
																				"dept_id" => $value['id'],
																				"parentid" => $value['parentid'],
																				"icon" => $IS_ORG == 1 ? "org.png" : FALSE,
																				"json" => $json
																);
												}
								}
								return $dept_array;
				}

				public function hasChildren( $dept_id )
				{
								foreach ( $this->list as $key => $value )
								{
												if ( !( $value['parentid'] == $dept_id ) )
												{
																continue;
												}
												return TRUE;
								}
								return FALSE;
				}

				public function deleteDept( $dept_id )
				{
								$dept_id = intval( $dept_id );
								$data = $this->getData( $this->url['delete'], array(
												"id" => $dept_id
								) );
								return $data;
				}

				public function createDept( $dept_id )
				{
								$dept_id_arr = explode( ",", $dept_id );
								foreach ( $dept_id_arr as $key => $value )
								{
												if ( $value != "" )
												{
																$this->createChildrenDept( $value );
												}
								}
								return "ok";
				}

				public function getDept( $dept_id )
				{
								foreach ( $this->list as $key => $value )
								{
												if ( !( $value['id'] == $dept_id ) )
												{
																continue;
												}
												return $value;
								}
				}

				public function updateDept( $params = array( ) )
				{
								if ( !$params )
								{
												return;
								}
								$rs = $this->postData( $this->url['update'], array(
												"id" => $params['id'],
												"name" => $params['name'],
												"parentid" => $params['parentid']
								) );
								return $rs;
				}

				public function createChildrenDept( $dept_id, $weixin_dept_parent = NULL )
				{
								$dept_arr = array( );
								$query = "SELECT DEPT_ID,DEPT_NAME,DEPT_NO,DEPT_PARENT,WEIXIN_DEPT_ID FROM department WHERE DEPT_PARENT = '".$dept_id."' ORDER BY DEPT_NO";
								$cursor = exequery( TD::conn( ), $query, TRUE );
								while ( $ROW = mysql_fetch_array( $cursor ) )
								{
												$DEPT_ID = $ROW['DEPT_ID'];
												$DEPT_NAME = $ROW['DEPT_NAME'];
												$DEPT_NO = $ROW['DEPT_NO'];
												$DEPT_PARENT = $ROW['DEPT_PARENT'];
												$WEIXIN_DEPT_ID = $ROW['WEIXIN_DEPT_ID'];
												if ( 0 < $WEIXIN_DEPT_ID )
												{
																break;
												}
												$dept_parent = $weixin_dept_parent ? $weixin_dept_parent : $DEPT_PARENT;
												if ( $dept_parent == 0 )
												{
																$dept_parent = 1;
												}
												$rs = $this->postData( $this->url['create'], array(
																"name" => $DEPT_NAME,
																"parentid" => $dept_parent,
																"order" => ltrim( substr( $DEPT_NO, -3 ), 0 )
												) );
												if ( $rs['errcode'] == 0 )
												{
																$new_dept_id = $rs['id'];
																$query_update = "UPDATE department SET WEIXIN_DEPT_ID = '".$new_dept_id."' WHERE DEPT_ID = '{$DEPT_ID}'";
																exequery( TD::conn( ), $query_update );
																$dept_arr[$DEPT_ID] = $new_dept_id;
												}
								}
								if ( 0 < count( $dept_arr ) )
								{
												foreach ( $dept_arr as $key => $value )
												{
																if ( $this->oaDeptHasChildren( $key ) )
																{
																				$this->createChildrenDept( $key, $value );
																}
												}
								}
				}

				public function oaDeptHasChildren( $dept_id )
				{
								$query = "SELECT COUNT(*) FROM department WHERE DEPT_PARENT = '".$dept_id."'";
								$cursor = exequery( TD::conn( ), $query, TRUE );
								$ROW = mysql_fetch_array( $cursor );
								if ( 0 < $ROW[0] )
								{
												return TRUE;
								}
								return FALSE;
				}

				public function buildHtml( $type = "select" )
				{
								$this->html['select'] .= $this->buildChildrenHtml( 0, 0 );
								return $this->html['select'];
				}

				public function buildChildrenHtml( $dept_id, $level )
				{
								foreach ( $this->list as $key => $value )
								{
												if ( $value['parentid'] == $dept_id )
												{
																$str = "";
																$i = 0;
																for ( ;	$i < $level;	++$i	)
																{
																				$str .= "©®";
																}
																$str .= "©¦-";
																$this->html['select'] .= "<option value=\"".$value['id']."\">".$str.iconv( "UTF-8", MYOA_CHARSET, $value['name'] )."</option>";
																$this->buildChildrenHtml( $value['id'], $level + 1 );
												}
								}
				}

}

?>
