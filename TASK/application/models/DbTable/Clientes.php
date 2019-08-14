<?php
class Application_Model_DbTable_Clientes extends Zend_Db_Table {
	
	protected $_name = "clientes";
	protected $_primary = 'ID';
	
	public function getClientes()
	{
	    $select = parent::select()->order('nombre asc');
	    $result = parent::fetchAll($select)->toArray();
	    return $result;
	}
	
	/**
	 * Get cliente name by id
	 * @param int $id
	 */
	public function getClienteNameByID($id)
	{
		$select = parent::select()->from($this,array('nombre'))->where('ID =?',$id);
		$result = parent::fetchRow($select);
		return $result;
	}
	
	/**
	 * Obtiene el Id de un cliente a partir del nombre
	 * @param string $name
	 */
	public function getClienteIdByName($name)
	{
		//file_put_contents("namemodels.txt", $name);
		$result = array();
	    $select = parent::select()->from($this,array('ID'))->where('nombre =?',$name);
	   
	    $result = parent::fetchRow($select);
	if(isset($result)){
		$result = $result->toArray();
		if( count($result)>0)
	    return $result['ID'];
	}
		
	    else return "KO";
	}

	public function getNombre($clienteid)
	{
	    $select = parent::select()->from($this,array('nombre'))->where('ID =?',$clienteid);
	    $result = parent::fetchRow($select)->toArray();
	    return $result['nombre'];
	    
	}
	
	/**
	 * Actualiza un cliente
	 * @param string $id
	 * @param string $data
	 */
	public function updateCliente($id, $data,$tipo)
	{
	    $values["nombre"] = $data;
	    $values["Tipo"] = $tipo;
	    return parent::update($values, 'ID = '.$id);
	    
	}
	
	/**
	 * Gets clientes from view related with events
	 */
	public function getClientesEventosView()
	{
		$this->_name = "cal_clientes_view";
		$this->_primary = 'cli';
		$select = parent::select();
		$result = parent::fetchAll($select)->toArray();
		return $result;
		
	}
	
	/**
	 * Elimina un cliente
	 * @param string $id
	 */
	public function deleteCliente($id)
	{
	    return parent::delete('ID = '.$id);
	}
	
	
	/**
	 * Inserta un cliente
	 * @param string $data
	 */
	public function insertCliente($data,$tipo)
	{
	    $arraydatos = array();
	    $arradatos["ID"] = null;
	    $arraydatos["nombre"] = $data;
	    $arraydatos["Tipo"] = $tipo;
	    return parent::insert($arraydatos);
	}
	
	
	
	
	
}

?>