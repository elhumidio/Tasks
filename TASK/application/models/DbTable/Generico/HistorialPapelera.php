<?php
class Application_Model_DbTable_Generico_HistorialPapelera extends Zend_Db_Table {
	
	protected $_name = "t_historial_papelera";
	protected $_primary = 'ID';

	public function insertaArray($registros){
	
	    for($i=0;$i<count($registros);$i++){
	        $this->insert($registros[$i]);
	    }
	     
	}
	
	public function getEntrada($id, $parent_name){
	
	    $sql = $this->select()->where('parent_id = ?', $id)->where('parent_name = ?', $parent_name)->order(array('fecha'));
	    $result = $this->fetchAll($sql);
	
	    return $result->toArray();
	
	}
	
	public function insertaRegistroRecuperado($registro)
	{
	    $this->insert($registro);
	}
	
	/**
	 * Updates entradas with new parent_id
	 * @param array $entradas
	 * @param int $newParentId
	 */
	public function updateEntradas($entradas,$newParentId)
	{
	    $data['parent_id'] = $newParentId;
	     
	    for($i = 0; $i < count($entradas); $i++)
	    {
	    $where['ID = ?'] = $entradas[$i];
	    $this->update($data,$where);
	    }
	}
	
	/**
	 * Elimina un registro
	 * @param string $id
	 */
	public function delete($id)
	{
	    $where['ID = '.$id] = $id;
	    //file_put_contents("delete1.txt",$where);
	    parent::delete($where);
	}
	
}

?>