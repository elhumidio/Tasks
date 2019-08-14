<?php
class Application_Model_DbTable_AppPermisos extends Zend_Db_Table {
	
	protected $_name = "app_permisos";
	protected $_primary = 'ID';
	
	public function __get($propiedad) {
		return $this->$propiedad;
	}
	
	public function deleteByRolId($rol_id)
	{
		return self::delete("app_roles_id=$rol_id");
	}
	
	public function getbyeRoleId($rol_id)
	{
		$select = self::select()->where("app_roles_id = $rol_id");
		$result = self::fetchAll($select)->toArray();
	
		return $result;
	}
}

?>