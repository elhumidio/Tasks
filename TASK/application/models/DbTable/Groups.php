<?php
class Application_Model_DbTable_Groups extends Zend_Db_Table {
	
	protected $_name = "cal_groups";
	protected $_primary = 'id';
	
	public function getGroups()
	{
	    //$select = parent::select()->order('group asc');
	    $select = parent::select()->from(array('cal_groups'),array('id','group'))->order('group asc');
	    $result = parent::fetchAll($select)->toArray();
	    return $result;
	}
	

	
	/**
	 * Actualiza un group
	 * @param string $id
	 * @param string $data
	 */
	public function updateGroup($id, $data)
	{
	    $values["group"] = $data;
	    return parent::update($values, 'id = '.$id);
	    
	}
	
	/**
	 * Elimina un grupo
	 * @param string $id
	 */
	public function deleteGroup($id)
	{
	    return parent::delete('id = '.$id);
	}
	
	
	/**
	 * Inserta un grupo
	 * @param string $data
	 */
	public function insertGroup($data)
	{
	    $arraydatos = array();
	    $arradatos["id"] = null;
	    $arraydatos["group"] = $data;
	    return parent::insert($arraydatos);
	}
	
	
	
	
	
}

?>