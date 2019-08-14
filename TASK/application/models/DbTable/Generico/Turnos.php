<?php
class Application_Model_DbTable_Generico_Turnos extends Zend_Db_Table {
	
	protected $_name = "t_turnos";
	protected $_primary = 'ID';

	public function getTurnosGrupo($grupo){
    
	    $sql = $this->select()->from($this->_name)->where("grupo = ?", $grupo)->order('orden asc');
        $result = $this->fetchAll($sql)->toArray();
	    
	    return count($result) > 0 ? $result : false;
    	    
	}
	
}

?>