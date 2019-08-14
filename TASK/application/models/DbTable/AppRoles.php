<?php
class Application_Model_DbTable_AppRoles extends Zend_Db_Table {
	
	protected $_name = "app_roles";
	protected $_primary = 'ID';
	
	
	/**
	 * Devuelve el nombre de un rol
	 * @param int $ID
	 */
	public function getName($ID)
	{
		if(is_null($ID)) return NULL;
		
		$select = parent::find($ID);
		return $select[0]['name'];
	}
	
	
	public function checkExiste($name)
	{
		$select = parent::select()->where('name=?',$name);
		$result = parent::fetchRow($select);
	
		return count($result)==0?true:false;
	}
	
	
	public function checkActivo($name)
	{
		$sql = parent::select()->where('name=?',$name);
		$result = parent::fetchRow($sql);
		
		return (($result['estado']=='on')?true:false);
	}
	
	
	public function checkActivoById($id)
	{
		$sql = parent::select()->where('ID=?',$id);
	
		$result = self::fetchRow($sql);
	
		return (($result['estado']=='on')?true:false);
	}
	
	public function getRoles()
	{
		$select = parent::select();
		$result = parent::fetchAll($select)->toArray();
		return $result;
	}
	
	
	public function getRolesOn()
	{
		$select = parent::select()->where('estado = ?','on');
		$result = parent::fetchAll($select)->toArray();
		return $result;
	}
	
	
	public function getRolesName()
	{
		$select = parent::select();
		$result = parent::fetchAll($select)->toArray();
		return $result;
	}
	
	
	/**
	 * Dado un nombre de role devuelve el ID del mismo
	 * @param array $name
	 * @return array
	 */
	public function getIbyName($name)
	{
		if($name == 'God') return 0;
		
		$select = parent::select()->where('name=?',$name);
		$result = parent::fetchRow($select);
		return $result['ID'];
	}
	
	/**
	 * Dado un array con nombres de roles devuelve un array con los ID en el mismo orden
	 * @param array $name
	 * @return array
	 */
	public function getIdByArrayName($name)
	{
		$salida = array();
		
		foreach($name as $rol)
		{
			if($rol == 'God') $salida[] = 0;
			else {
				$select = parent::select()->where('name=?',$rol);
				$result = parent::fetchRow($select)->toArray();
				$salida[] = $result['ID'];
			}
		}
		
		
		return $salida;
	}
	
	
	/**
	 * Dado un array con id de roles devuelve un array con los nombres en el mismo orden
	 * @param array $id
	 * @return array
	 */
	public function getNameByArrayId($id)
	{
		$salida = array();
	
		foreach($id as $rol)
		{
			if($rol == '0') $salida[] = 'God';
			else {
				$select = parent::select()->where('ID=?',$rol);
				$result = parent::fetchRow($select)->toArray();
				$salida[] = $result['name'];
			}
		}
	
		return $salida;
	}
	
	/**
	 * Actualiza el estado de un rol
	 * @param int $id
	 * @param int $status
	 */
	public function updateStatus($id, $status)
	{
		$select = self::find($id);
		$rowset = $select->current();
		$rowset->estado = $status;
		return $rowset->save();
	}
	
}

?>