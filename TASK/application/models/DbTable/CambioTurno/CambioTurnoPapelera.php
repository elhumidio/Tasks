<?php
class Application_Model_DbTable_CambioTurno_CambioTurnoPapelera extends Zend_Db_Table {
	
	protected $_name = "t_cambio_turno_papelera";
	protected $_primary = 'ID';

	public function get(){
	    $sql = $this->select()->order(array('fecha'));
	    $result = $this->fetchAll($sql);
	    return $result->toArray();
	}
	
	public function getEntradaId($id){
	    return  $this->find($id)->toArray();
	}
	
	public function delete($id)
	{
	    $where['ID = '.$id] = $id;
	    //file_put_contents("delete1.txt",$where);
	    parent::delete($where);
	}
	
	
		
}

?>