<?php
class Application_Model_DbTable_Generico_Historial extends Zend_Db_Table {
	
	protected $_name = "t_historial";
	protected $_primary = 'ID';

	public function add($tabla, $id_tabla, $usuario_login, $usuario, $mensaje){
	    try {
	        $this->insert(array('parent_name'=>$tabla,  'parent_id'=>$id_tabla, 'usuario_login'=>$usuario_login, 'usuario'=>$usuario, 'mensaje'=>$mensaje));
	    } catch (Exception $e){
	        file_put_contents('test.txt', print_r($e->getMessage(), true));
	    }
	    
	}
	
	public function addWithDate($tabla, $id_tabla,$fecha, $usuario_login, $usuario, $mensaje){
	    try {
	        $this->insert(array('parent_name'=>$tabla,  'parent_id'=>$id_tabla,'fecha'=>$fecha, 'usuario_login'=>$usuario_login, 'usuario'=>$usuario, 'mensaje'=>$mensaje));
	    } catch (Exception $e){
	        file_put_contents('test.txt', print_r($e->getMessage(), true));
	    }
	     
	}
	
	public function getEntrada($id, $parent_name){
	    
	    $sql = $this->select()->where('parent_id = ?', $id)->where('parent_name = ?', $parent_name)->order(array('fecha'));
	    $result = $this->fetchAll($sql);
	    
	    return $result->toArray();
	    
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
	
}

?>